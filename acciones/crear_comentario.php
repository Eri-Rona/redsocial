<?php
session_start();
header('Content-Type: application/json');
require_once '../redsocial_db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['publicacion_id']) || empty($input['contenido'])) {
        throw new Exception('Datos incompletos');
    }

    $database = new Database();
    $db = $database->getConnection();

    $usuario_id = $_SESSION['user_id'];
    $publicacion_id = (int)$input['publicacion_id'];
    $contenido = trim($input['contenido']);
    $parent_id = !empty($input['parent_id']) ? (int)$input['parent_id'] : null;

    $sql = "INSERT INTO comentarios (publicacion_id, usuario_id, contenido, parent_id, fecha_comentario) 
            VALUES (:pid, :uid, :content, :parent, NOW())";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':pid' => $publicacion_id,
        ':uid' => $usuario_id,
        ':content' => $contenido,
        ':parent' => $parent_id
    ]);

    $newId = $db->lastInsertId();

    // Devolver datos del comentario creado para insertarlo en el DOM sin recargar
    // Necesitamos foto y username del usuario actual
    $stmtUser = $db->prepare("SELECT username, fotoperfil FROM usuarios WHERE id = ?");
    $stmtUser->execute([$usuario_id]);
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'comentario' => [
            'id' => $newId,
            'publicacion_id' => $publicacion_id,
            'usuario_id' => $usuario_id,
            'contenido' => $contenido,
            'fecha_comentario' => date('Y-m-d H:i:s'), // Aproximado
            'parent_id' => $parent_id,
            'username' => $userData['username'],
            'fotoperfil' => $userData['fotoperfil'],
            'likes_count' => 0,
            'user_liked' => 0
        ]
    ]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
