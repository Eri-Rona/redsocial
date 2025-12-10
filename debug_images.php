<?php
require_once 'redsocial_db.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    header('Content-Type: text/plain');
    echo "ID | Usuario | Bytes | Estado\n";
    echo "--------------------------------\n";

    $stmt = $db->query("SELECT id, username, fotoperfil FROM usuarios");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $len = strlen($row['fotoperfil'] ?? '');
        $estado = "OK";
        
        if ($len == 0) {
            $estado = "Sin foto";
        } elseif ($len == 65535) {
            $estado = "TRUNCADA";
        } elseif ($len < 100) {
            $estado = "Muy corta";
        }
        
        echo "{$row['id']} | {$row['username']} | {$len} | {$estado}\n";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
