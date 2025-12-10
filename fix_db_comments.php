<?php
require_once 'redsocial_db.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>DB Fix Start</h1>";

    // 1. Fix parent_id
    echo "Checking 'parent_id' in 'comentarios'...<br>";
    $stmt = $db->query("SHOW COLUMNS FROM comentarios LIKE 'parent_id'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        echo "Column missing. Adding...<br>";
        $sql = "ALTER TABLE comentarios ADD COLUMN parent_id INT DEFAULT NULL AFTER id";
        $db->exec($sql);
        echo "SUCCESS: Column 'parent_id' added.<br>";
    } else {
        echo "INFO: Column 'parent_id' already exists.<br>";
    }

    // 2. Create table likes_comentarios
    echo "<hr>Checking 'likes_comentarios' table...<br>";
    $sqlCreate = "CREATE TABLE IF NOT EXISTS likes_comentarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        comentario_id INT NOT NULL,
        fecha_like DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (comentario_id) REFERENCES comentarios(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_comment_like (usuario_id, comentario_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sqlCreate);
    echo "SUCCESS: Table 'likes_comentarios' ensured.<br>";

    // 3. Final Verification
    echo "<hr><h3>Final Verification</h3>";
    $finalCheck = $db->query("SHOW COLUMNS FROM comentarios LIKE 'parent_id'")->fetch();
    echo "parent_id: " . ($finalCheck ? "OK" : "MISSING") . "<br>";
    
    try {
        $db->query("SELECT 1 FROM likes_comentarios LIMIT 1");
        echo "likes_comentarios: OK<br>";
    } catch(Exception $e) {
        echo "likes_comentarios: MISSING (" . $e->getMessage() . ")<br>";
    }

} catch(PDOException $e) {
    echo "FATAL ERROR: " . $e->getMessage();
}
?>
