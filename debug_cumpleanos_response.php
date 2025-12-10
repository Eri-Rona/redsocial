<?php
session_start();
$_SESSION['user_id'] = 1; // Force user ID
require_once 'redsocial_db.php';

// Capture output of api_cumpleanos logic (re-implemented to avoid header issues)
try {
    $database = new Database();
    $db = $database->getConnection();
    $current_user_id = 1;
    
    $query = "SELECT u.id, u.username, u.fecha_nacimiento, u.fotoperfil,
                     COALESCE(u.username, u.email) as display_name
              FROM usuarios u
              JOIN amistades a ON (a.solicitante_id = u.id OR a.receptor_id = u.id)
              WHERE (a.solicitante_id = :uid OR a.receptor_id = :uid)
              AND a.estado = 'aceptada'
              AND u.id != :uid
              AND u.fecha_nacimiento IS NOT NULL";
              
    $stmt = $db->prepare($query);
    $stmt->execute([':uid' => $current_user_id]);
    
    $results = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['fotoperfil_len'] = strlen($row['fotoperfil'] ?? '');
        $row['fotoperfil_preview'] = substr($row['fotoperfil'] ?? '', 0, 50);
        unset($row['fotoperfil']); // Remove huge string for readability in debug
        $results[] = $row;
    }
    
    echo "<pre>";
    print_r($results);
    echo "</pre>";

} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
