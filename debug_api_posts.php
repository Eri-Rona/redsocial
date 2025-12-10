<?php
require_once 'redsocial_db.php';
$current_user_id = 1;
$limit = 10;
$offset = 0;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Connected DB.\n";

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

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':current_user_id', $current_user_id);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    
    if($stmt->execute()) {
        echo "Query executed successfully.\n";
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Count: " . count($results) . "\n";
        print_r($results);
    } else {
        echo "Query execution failed.\n";
        print_r($stmt->errorInfo());
    }

} catch(PDOException $e) {
    echo "PDO Error: " . $e->getMessage();
}
?>
