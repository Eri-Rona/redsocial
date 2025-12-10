<?php
session_start();
require_once '../redsocial_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No has iniciado sesión']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$publicacion_id = isset($data['id']) ? $data['id'] : null;
$nuevo_contenido = isset($data['contenido']) ? trim($data['contenido']) : '';

if (!$publicacion_id) {
    echo json_encode(['status' => 'error', 'message' => 'ID de publicación no proporcionado']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Verificar propiedad
    $check_sql = "SELECT usuario_id FROM publicaciones WHERE id = :id";
    $check_stmt = $db->prepare($check_sql);
    $check_stmt->bindParam(':id', $publicacion_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Publicación no encontrada']);
        exit;
    }

    $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row['usuario_id'] != $_SESSION['user_id']) {
        echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para editar esta publicación']);
        exit;
    }

    // Actualizar contenido
    $update_sql = "UPDATE publicaciones SET contenido = :contenido WHERE id = :id";
    $update_stmt = $db->prepare($update_sql);
    $update_stmt->bindParam(':contenido', $nuevo_contenido);
    $update_stmt->bindParam(':id', $publicacion_id);

    if ($update_stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Publicación actualizada']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la publicación']);
    }

} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>
