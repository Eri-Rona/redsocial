<?php
header('Content-Type: application/json');
require_once 'redsocial_db.php';

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

        // Check if email already exists
        $query = "SELECT id FROM usuarios WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $response['message'] = 'Este correo electrónico ya está registrado';
            echo json_encode($response);
            exit;
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $fecha_creacion = date('Y-m-d H:i:s');
        $fecha_actualizacion = $fecha_creacion;

        // Insert new user
        $query = "INSERT INTO usuarios (nombre, apellido, email, telefono, fecha_nacimiento, password, fecha_creacion, fecha_actualizacion) 
                 VALUES (:nombre, :apellido, :email, :telefono, :fecha_nacimiento, :password, :fecha_creacion, :fecha_actualizacion)";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':fecha_creacion', $fecha_creacion);
        $stmt->bindParam(':fecha_actualizacion', $fecha_actualizacion);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = '¡Registro exitoso! Ahora puedes iniciar sesión';
            $response['redirect'] = 'redsocial_login.html';
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
