<?php
// Evitar cualquier salida antes de los headers
ob_start();

// Configuración de errores
ini_set('display_errors', 0);
error_reporting(0);

// Headers para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Iniciar sesión
session_start();

// Incluir la conexión a la base de datos
require_once 'redsocial_db.php';

// Respuesta por defecto
$response = [
    'status' => 'error',
    'message' => 'Error desconocido',
    'redirect' => ''
];

try {
    // Verificar método de la petición
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener y validar datos
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    // Validar campos requeridos
    if (empty($email) || empty($password)) {
        throw new Exception('Por favor ingresa tu correo y contraseña');
    }

    // Conectar a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    // Buscar usuario por email
    $query = "SELECT id, nombre, apellido, email, password, rol FROM usuarios WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar contraseña
        if (password_verify($password, $row['password'])) {
            // Regenerar ID de sesión por seguridad
            session_regenerate_id(true);
            
            // Guardar datos en sesión
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = trim($row['nombre'] . ' ' . $row['apellido']);
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_role'] = $row['rol'];
            
            // Actualizar último acceso
            $updateQuery = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':id', $row['id']);
            $updateStmt->execute();
            
            // Respuesta exitosa
            $response = [
                'status' => 'success',
                'message' => 'Inicio de sesión exitoso',
                'redirect' => 'redsocial_dashboard.php'
            ];
        } else {
            throw new Exception('Correo o contraseña incorrectos');
        }
    } else {
        throw new Exception('Correo o contraseña incorrectos');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Limpiar cualquier salida no deseada
ob_end_clean();

// Enviar respuesta JSON
echo json_encode($response);
exit;

