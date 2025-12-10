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
    $publicacion_id = filter_var($input['publicacion_id'] ?? null, FILTER_VALIDATE_INT);
    
    if (!$publicacion_id) {
        throw new Exception('ID de publicación inválido');
    }
    
    $database = new Database();
    $db = $database->getConnection();
    $user_id = $_SESSION['user_id'];
    
    // Verificar si ya dio like
    $checkQuery = "SELECT id FROM likes WHERE usuario_id = :uid AND publicacion_id = :pid";
    $stmtCheck = $db->prepare($checkQuery);
    $stmtCheck->execute([':uid' => $user_id, ':pid' => $publicacion_id]);
    
    if ($stmtCheck->rowCount() > 0) {
        // Ya dio like -> Quitar like
        $deleteQuery = "DELETE FROM likes WHERE usuario_id = :uid AND publicacion_id = :pid";
        $stmtDelete = $db->prepare($deleteQuery);
        $stmtDelete->execute([':uid' => $user_id, ':pid' => $publicacion_id]);
        $liked = false;
    } else {
        // No dio like -> Dar like
        $insertQuery = "INSERT INTO likes (usuario_id, publicacion_id) VALUES (:uid, :pid)";
        $stmtInsert = $db->prepare($insertQuery);
        $stmtInsert->execute([':uid' => $user_id, ':pid' => $publicacion_id]);
        $liked = true;
    }
    
    // Contar total de likes
    $countQuery = "SELECT COUNT(*) as total FROM likes WHERE publicacion_id = :pid";
    $stmtCount = $db->prepare($countQuery);
    $stmtCount->execute([':pid' => $publicacion_id]);
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
