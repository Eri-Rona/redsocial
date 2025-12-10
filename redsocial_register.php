<?php
session_start();
header('Content-Type: application/json');
require_once 'redsocial_db.php';

// Respuesta base
$response = array('status' => 'error', 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input data
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
    $fecha_nacimiento = filter_input(INPUT_POST, 'fecha_nacimiento', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if (empty($nombre) || empty($apellido) || empty($email) || empty($telefono) || 
        empty($fecha_nacimiento) || empty($password) || empty($confirm_password)) {
        $response['message'] = 'Todos los campos son obligatorios';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'El formato del correo electrónico no es válido';
        echo json_encode($response);
        exit;
    }

    if (strlen($password) < 8) {
        $response['message'] = 'La contraseña debe tener al menos 8 caracteres';
        echo json_encode($response);
        exit;
    }

    if ($password !== $confirm_password) {
        $response['message'] = 'Las contraseñas no coinciden';
        echo json_encode($response);
        exit;
    }

    try {
        $database = new Database();
        $db = $database->getConnection();

        // Comprobar si el correo ya existe
        $query = "SELECT id FROM usuarios WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $response['message'] = 'Este correo electrónico ya está registrado';
            echo json_encode($response);
            exit;
        }

        // Generar username sencillo a partir del nombre y apellido (fallback al email si hace falta)
        $baseUsername = trim(strtolower(preg_replace('/\s+/', '', $nombre . '.' . $apellido)));
        if ($baseUsername === '' && !empty($email)) {
            $baseUsername = strstr($email, '@', true);
        }
        if ($baseUsername === '' ) {
            $baseUsername = 'user' . time();
        }

        // Hash de la contraseña usando la columna password_hash
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insertar nuevo usuario según el esquema real (username, email, password_hash)
        $query = "INSERT INTO usuarios (username, email, password_hash) 
                 VALUES (:username, :email, :password_hash)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $baseUsername);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $hashed_password);

        if ($stmt->execute()) {
            // Obtener el ID del usuario recién creado
            $userId = $db->lastInsertId();

            // Crear sesión de usuario automáticamente después del registro
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $baseUsername;
            $_SESSION['user_email'] = $email;

            $response['status'] = 'success';
            $response['message'] = '¡Registro exitoso! Redirigiendo a tu inicio...';
            $response['redirect'] = 'inicio.html';
        } else {
            $response['message'] = 'Error al registrar el usuario';
        }
    } catch(PDOException $e) {
        $response['message'] = 'Error de base de datos: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Método no permitido';
}

echo json_encode($response);
?>
