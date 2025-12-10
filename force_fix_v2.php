<?php
header('Content-Type: text/plain');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'redsocial_db.php';

echo "START V2\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Test connection
    $db->query("SELECT 1");
    echo "DB Connected.\n";

    // 1. Tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(", ", $tables) . "\n";

    // 2. Add parent_id
    echo "Adding parent_id...\n";
    try {
        $db->exec("ALTER TABLE comentarios ADD COLUMN parent_id INT DEFAULT NULL AFTER id");
        echo "ADDED parent_id.\n";
    } catch(PDOException $e) {
        echo "ALTER FAILED (exists?): " . $e->getMessage() . "\n";
    }

    // 3. Add likes_comentarios
    echo "Creating likes_comentarios...\n";
    try {
        $sql = "CREATE TABLE IF NOT EXISTS likes_comentarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            comentario_id INT NOT NULL,
            fecha_like DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY (comentario_id) REFERENCES comentarios(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_comment_like (usuario_id, comentario_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $db->exec($sql);
        echo "CREATED likes_comentarios.\n";
    } catch(PDOException $e) {
        echo "CREATE FAILED: " . $e->getMessage() . "\n";
    }

    echo "DONE.\n";

} catch(Exception $e) {
    echo "FATAL: " . $e->getMessage();
}
?>
