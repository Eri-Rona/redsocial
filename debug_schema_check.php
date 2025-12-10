<?php
require_once 'redsocial_db.php';
try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Connected successfully.\n";

    $tables = ['usuarios', 'publicaciones', 'likes', 'amistades', 'comentarios'];
    
    foreach ($tables as $table) {
        echo "DESCRIBE $table:\n";
        try {
            $stmt = $db->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $col) {
                echo $col['Field'] . " - " . $col['Type'] . "\n";
            }
        } catch (PDOException $e) {
            echo "Error describing $table: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
