<?php
session_start();
header('Content-Type: application/json');
require_once 'redsocial_db.php';

if (!isset($_GET['publicacion_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Falta ID de publicación']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $publicacion_id = (int)$_GET['publicacion_id'];
    $current_user_id = $_SESSION['user_id'] ?? 0;

    $query = "SELECT c.id, c.publicacion_id, c.usuario_id, c.contenido, c.fecha_comentario, c.parent_id,
                     u.username, u.fotoperfil,
                     (SELECT COUNT(*) FROM likes_comentarios WHERE comentario_id = c.id) as likes_count,
                     (SELECT COUNT(*) FROM likes_comentarios WHERE comentario_id = c.id AND usuario_id = :uid) as user_liked
              FROM comentarios c
              JOIN usuarios u ON c.usuario_id = u.id
              WHERE c.publicacion_id = :pid
              ORDER BY c.fecha_comentario ASC";

    $stmt = $db->prepare($query);
    $stmt->execute([':pid' => $publicacion_id, ':uid' => $current_user_id]);
    
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organizar en estructura de árbol si es necesario, o enviarlo plano y que JS lo maneje.
    // Para simplificar, enviamos plano. El frontend ordenará respuestas.

    echo json_encode(['status' => 'success', 'comentarios' => $comentarios]);

} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
