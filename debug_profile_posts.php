<?php
session_start();
$_SESSION['user_id'] = 1; 
require_once 'redsocial_db.php';
header('Content-Type: text/plain');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $user_id = 1; // Testing for user ID 1
    echo "Testing posts for User ID: $user_id\n";
    
    // Check if user exists
    $stmtU = $db->prepare("SELECT id, username FROM usuarios WHERE id = ?");
    $stmtU->execute([$user_id]);
    $user = $stmtU->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo "User found: " . $user['username'] . "\n";
    } else {
        echo "User NOT found.\n";
    }

    // Check posts raw
    $stmtP = $db->prepare("SELECT COUNT(*) as total FROM publicaciones WHERE usuario_id = ?");
    $stmtP->execute([$user_id]);
    $count = $stmtP->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Total posts in DB for this user: $count\n";

    // Run the actual query from API
    $query = "SELECT p.id, p.contenido, p.fecha_creacion as fecha, u.username 
              FROM publicaciones p
              JOIN usuarios u ON p.usuario_id = u.id
              WHERE p.usuario_id = :uid
              ORDER BY p.fecha_creacion DESC";
              
    $stmt = $db->prepare($query);
    $stmt->execute([':uid' => $user_id]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Query returned " . count($posts) . " rows.\n";
    print_r($posts);

} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
