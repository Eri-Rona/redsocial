<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

require_once 'redsocial_db.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $user_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
    $current_user_id = $_SESSION['user_id'];
    
    $query = "SELECT p.id, p.contenido, p.fotopublicacion as imagen, p.fecha_publicacion as fecha, 
                     u.id as usuario_id, u.username as nombre, u.fotoperfil as foto_usuario,
                     (SELECT COUNT(*) FROM likes WHERE publicacion_id = p.id) as likes_count,
                     (SELECT COUNT(*) FROM likes WHERE publicacion_id = p.id AND usuario_id = :current_user_id) as user_liked
              FROM publicaciones p
              JOIN usuarios u ON p.usuario_id = u.id
              WHERE p.usuario_id = :uid
              ORDER BY p.fecha_publicacion DESC";
              
    $stmt = $db->prepare($query);
    $stmt->execute([':uid' => $user_id, ':current_user_id' => $current_user_id]);
    
    $publicaciones = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $publicaciones[] = [
            'id' => $row['id'],
            'contenido' => $row['contenido'],
            'imagen' => $row['imagen'],
            'fecha' => $row['fecha'],
            'usuario_id' => $row['usuario_id'],
            'nombre' => $row['nombre'],
            'foto_usuario' => $row['foto_usuario'],
            'likes_count' => $row['likes_count'],
            'liked_by_user' => $row['user_liked'] > 0
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'publicaciones' => $publicaciones
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error de base de datos']);
}
?>
