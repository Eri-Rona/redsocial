<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../redsocial_login.html');
    exit;
}

// Usar la conexión de base de datos existente
require_once __DIR__ . '/../redsocial_db.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $usuarioId = $_SESSION['user_id'];
    
    // Verificar si se subió un archivo
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error_foto'] = 'Error al subir el archivo. Intenta nuevamente.';
        header('Location: ../editar-perfil.html');
        exit;
    }
    
    $archivo = $_FILES['foto'];
    $nombreOriginal = $archivo['name'];
    $ext = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
    $ext = strtolower($ext);
    
    // Validar extensión
    $permitidas = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($ext, $permitidas, true)) {
        $_SESSION['error_foto'] = 'Formato de archivo no permitido. Usa JPG, PNG o GIF.';
        header('Location: ../editar-perfil.html');
        exit;
    }
    
    // Validar tamaño (máximo 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($archivo['size'] > $maxSize) {
        $_SESSION['error_foto'] = 'El archivo es demasiado grande. Máximo 5MB.';
        header('Location: ../editar-perfil.html');
        exit;
    }
    
    // Convertir imagen a base64
    $imageData = file_get_contents($archivo['tmp_name']);
    $base64Image = base64_encode($imageData);
    
    // Determinar el tipo MIME según la extensión
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];
    $mimeType = $mimeTypes[$ext] ?? 'image/jpeg';
    
    // Crear el data URI completo
    $dataUri = "data:" . $mimeType . ";base64," . $base64Image;
    
    // Guardar en la base de datos
    $stmt = $pdo->prepare('UPDATE usuarios SET fotoperfil = :foto WHERE id = :id');
    $stmt->execute([
        ':foto' => $dataUri,
        ':id' => $usuarioId,
    ]);
    
    $_SESSION['success_foto'] = 'Foto de perfil actualizada correctamente.';
    header('Location: ../editar-perfil.html');
    exit;
    
} catch(PDOException $e) {
    $_SESSION['error_foto'] = 'Error en la base de datos: ' . $e->getMessage();
    header('Location: ../editar-perfil.html');
    exit;
} catch(Exception $e) {
    $_SESSION['error_foto'] = 'Error: ' . $e->getMessage();
    header('Location: ../editar-perfil.html');
    exit;
}
?>
