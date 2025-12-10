<?php
session_start();
require_once '../redsocial_db.php';

header('Content-Type: application/json');

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No has iniciado sesión']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$usuario_id = $_SESSION['user_id'];
$texto = isset($_POST['texto']) ? trim($_POST['texto']) : '';
$imagen_url = ''; // Inicializar vacío

// Validar que haya al menos texto o imagen
if (empty($texto) && (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK)) {
    echo json_encode(['status' => 'error', 'message' => 'La publicación no puede estar vacía']);
    exit;
}

// Procesar imagen si existe
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/webm', 'video/ogg'
    ];
    $file_type = mime_content_type($_FILES['imagen']['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => 'Tipo de archivo no permitido. Solo imágenes (JPG, PNG, GIF, WEBP) o videos (MP4, WEBM, OGG).']);
        exit;
    }

    // Crear directorio si no existe
    $upload_dir = '../assets/img/publicaciones/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generar nombre único
    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('pub_') . '_' . time() . '.' . $extension;
    $target_path = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_path)) {
        // Guardar ruta relativa para la BD
        $imagen_url = 'assets/img/publicaciones/' . $filename;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen']);
        exit;
    }
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Usar los nombres de columna correctos: usuario_id, contenido, fotopublicacion
    $sql = "INSERT INTO publicaciones (usuario_id, contenido, fotopublicacion) VALUES (:usuario_id, :contenido, :fotopublicacion)";
    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':contenido', $texto);
    $stmt->bindParam(':fotopublicacion', $imagen_url);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Publicación creada correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar en la base de datos']);
    }

} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>
