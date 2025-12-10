<?php
session_start();
require_once 'redsocial_db.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();

    // Consulta para obtener publicaciones con datos del usuario
    // Se asume que la tabla de usuarios tiene 'username' y 'foto_perfil' (o similar)
    // Ajustar nombres de columnas de usuario si es necesario. Basado en inicio.html parece ser 'username' y 'foto_perfil' (o similar en JS)
    // En database.sql vi: username, email, password_hash. No vi foto_perfil en el CREATE TABLE original pero el JS usa 'foto_perfil'.
    // Asumiré que existe o se maneja. Si no, el LEFT JOIN funcionará pero foto será null.
    
    // Verificar si se solicita el feed de un usuario específico
    $target_user_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : null;

    if ($target_user_id) {
        // Publicaciones de un usuario específico (para el perfil)
        $sql = "SELECT p.id, p.usuario_id, p.contenido, p.fotopublicacion, p.fecha_publicacion, 
                       u.username, u.email, u.fotoperfil,
                       (SELECT COUNT(*) FROM likes WHERE publicacion_id = p.id) as likes_count,
                       (SELECT COUNT(*) FROM likes WHERE publicacion_id = p.id AND usuario_id = :current_user_id) as user_liked
                FROM publicaciones p 
                JOIN usuarios u ON p.usuario_id = u.id 
                WHERE p.usuario_id = :target_user_id
                ORDER BY p.fecha_publicacion DESC
                LIMIT :limit OFFSET :offset";
    } else {
        // Feed general (usuario actual + amigos)
        $sql = "SELECT p.id, p.usuario_id, p.contenido, p.fotopublicacion, p.fecha_publicacion, 
                       u.username, u.email, u.fotoperfil,
                       (SELECT COUNT(*) FROM likes WHERE publicacion_id = p.id) as likes_count,
                       (SELECT COUNT(*) FROM likes WHERE publicacion_id = p.id AND usuario_id = :current_user_id) as user_liked
                FROM publicaciones p 
                JOIN usuarios u ON p.usuario_id = u.id 
                WHERE p.usuario_id = :current_user_id
                   OR p.usuario_id IN (
                       SELECT solicitante_id FROM amistades WHERE receptor_id = :current_user_id AND estado = 'aceptada'
                       UNION
                       SELECT receptor_id FROM amistades WHERE solicitante_id = :current_user_id AND estado = 'aceptada'
                   )
                ORDER BY p.fecha_publicacion DESC
                LIMIT :limit OFFSET :offset";
    }
            
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
            
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':current_user_id', $_SESSION['user_id']);
    
    if ($target_user_id) {
        $stmt->bindParam(':target_user_id', $target_user_id);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $publicaciones = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        
        $foto_perfil = !empty($row['fotoperfil']) ? $row['fotoperfil'] : 'assets/blanco y negro.webp';

        $publicacion = [
            'id' => $row['id'],
            'usuario_id' => $row['usuario_id'],
            'username' => $row['username'],
            'nombre' => $row['username'], 
            'foto_usuario' => $foto_perfil,
            'contenido' => $row['contenido'],
            'imagen' => $row['fotopublicacion'],
            'fecha' => $row['fecha_publicacion'],
            'likes_count' => $row['likes_count'],
            'liked_by_user' => $row['user_liked'] > 0
        ];
        
        $publicaciones[] = $publicacion;
    }

    echo json_encode(['status' => 'success', 'publicaciones' => $publicaciones]);

} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener publicaciones: ' . $e->getMessage()]);
}
?>
