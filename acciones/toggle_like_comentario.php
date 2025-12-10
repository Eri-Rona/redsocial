<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../redsocial_db.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $comentario_id = filter_var($input['comentario_id'] ?? null, FILTER_VALIDATE_INT);
    
    if (!$comentario_id) {
        throw new Exception('ID de comentario inválido');
    }
    
    $database = new Database();
    $db = $database->getConnection();
    $user_id = $_SESSION['user_id'];
    
    // Verificar si ya dio like
    $checkQuery = "SELECT id FROM likes_comentarios WHERE usuario_id = :uid AND comentario_id = :cid";
    $stmtCheck = $db->prepare($checkQuery);
    $stmtCheck->execute([':uid' => $user_id, ':cid' => $comentario_id]);
    
    if ($stmtCheck->rowCount() > 0) {
        // Quitar like
        $deleteQuery = "DELETE FROM likes_comentarios WHERE usuario_id = :uid AND comentario_id = :cid";
        $stmtDelete = $db->prepare($deleteQuery);
        $stmtDelete->execute([':uid' => $user_id, ':cid' => $comentario_id]);
        $liked = false;
    } else {
        // Dar like
        $insertQuery = "INSERT INTO likes_comentarios (usuario_id, comentario_id) VALUES (:uid, :cid)";
        $stmtInsert = $db->prepare($insertQuery);
        $stmtInsert->execute([':uid' => $user_id, ':cid' => $comentario_id]);
        $liked = true;
    }
    
    // Contar total de likes
    $countQuery = "SELECT COUNT(*) as total FROM likes_comentarios WHERE comentario_id = :cid";
    $stmtCount = $db->prepare($countQuery);
    $stmtCount->execute([':cid' => $comentario_id]);
    $totalLikes = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'status' => 'success', 
        'liked' => $liked, 
        'likes_count' => $totalLikes
    ]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
