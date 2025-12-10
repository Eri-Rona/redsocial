<?php
require_once 'redsocial_db.php';
header('Content-Type: text/plain');

try {
    $database = new Database();
    $db = $database->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Force DB Fix Start\n";

    // 1. Force add parent_id
    try {
        echo "Attempting to ADD parent_id...\n";
        $sql = "ALTER TABLE comentarios ADD COLUMN parent_id INT DEFAULT NULL AFTER id";
        $db->exec($sql);
        echo "SUCCESS: parent_id added.\n";
    } catch (PDOException $e) {
        echo "INFO: parent_id add failed (maybe exists): " . $e->getMessage() . "\n";
    }

    // 2. Force create likes_comentarios
    try {
        echo "Attempting to CREATE likes_comentarios...\n";
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
        echo "SUCCESS: likes_comentarios ensured.\n";
    } catch (PDOException $e) {
         echo "ERROR: likes_comentarios create failed: " . $e->getMessage() . "\n";
    }
    
    // 3. Simple manual verification
    $cols = $db->query("DESCRIBE comentarios")->fetchAll(PDO::FETCH_COLUMN);
    echo "Current columns in comentarios: " . implode(", ", $cols) . "\n";

} catch(PDOException $e) {
    echo "FATAL ERROR: " . $e->getMessage();
}
?>
