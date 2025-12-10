<?php
session_start();
$_SESSION['user_id'] = 1; // Simular usuario eri.rona
require_once 'redsocial_db.php';
header('Content-Type: text/plain');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Simulando API Cumpleaños para User ID 1...\n";
    
    $query = "SELECT u.id, u.username, u.fotoperfil
              FROM usuarios u
              JOIN amistades a ON (a.solicitante_id = u.id OR a.receptor_id = u.id)
              WHERE (a.solicitante_id = :uid OR a.receptor_id = :uid)
              AND a.estado = 'aceptada'
              AND u.id != :uid
              AND u.fecha_nacimiento IS NOT NULL";
              
    $stmt = $db->prepare($query);
    $stmt->execute([':uid' => 1]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $len = strlen($row['fotoperfil'] ?? '');
        echo "Amigo encontrado: {$row['username']} (ID: {$row['id']})\n";
        echo "Largo de fotoperfil recuperado: {$len} bytes\n";
        echo "Inicio de cadena: " . substr($row['fotoperfil'] ?? '', 0, 30) . "...\n\n";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
