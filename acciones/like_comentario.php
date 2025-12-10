<?php
session_start();
header('Content-Type: application/json');
require_once '../redsocial_db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $comentario_id = $input['comentario_id'] ?? null;

    if (!$comentario_id) {
        throw new Exception('ID inválido');
    }

    $database = new Database();
    $db = $database->getConnection();
    $usuario_id = $_SESSION['user_id'];

    // Check existing like
    $sqlCheck = "SELECT id FROM likes_comentarios WHERE usuario_id = :uid AND comentario_id = :cid";
    $stmtCheck = $db->prepare($sqlCheck);
    $stmtCheck->execute([':uid' => $usuario_id, ':cid' => $comentario_id]);

    $liked = false;
    if ($stmtCheck->rowCount() > 0) {
        // Remove like
        $sqlDel = "DELETE FROM likes_comentarios WHERE usuario_id = :uid AND comentario_id = :cid";
        $stmtDel = $db->prepare($sqlDel);
        $stmtDel->execute([':uid' => $usuario_id, ':cid' => $comentario_id]);
    } else {
        // Add like
        $sqlAdd = "INSERT INTO likes_comentarios (usuario_id, comentario_id) VALUES (:uid, :cid)";
        $stmtAdd = $db->prepare($sqlAdd);
        $stmtAdd->execute([':uid' => $usuario_id, ':cid' => $comentario_id]);
        $liked = true;
    }

    // Get new count
    $sqlCount = "SELECT COUNT(*) as total FROM likes_comentarios WHERE comentario_id = :cid";
    $stmtCount = $db->prepare($sqlCount);
    $stmtCount->execute([':cid' => $comentario_id]);
    $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode(['status' => 'success', 'liked' => $liked, 'likes_count' => $total]);

} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
