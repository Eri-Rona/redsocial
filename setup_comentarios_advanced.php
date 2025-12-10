<?php
require_once 'redsocial_db.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. Agregar columna parent_id a comentarios si no existe
    $stmt = $db->query("DESCRIBE comentarios parent_id");
    if (!$stmt) {
        $sqlAlter = "ALTER TABLE comentarios ADD COLUMN parent_id INT DEFAULT NULL AFTER id";
        $db->exec($sqlAlter);
        echo "Columna 'parent_id' agregada a tabla 'comentarios'.<br>";
        
        // Agregar FK para consistencia (opcional pero recomendado)
        // $db->exec("ALTER TABLE comentarios ADD CONSTRAINT fk_comentario_padre FOREIGN KEY (parent_id) REFERENCES comentarios(id) ON DELETE CASCADE");
    } else {
        echo "Columna 'parent_id' ya existe.<br>";
    }

    // 2. Crear tabla likes_comentarios
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
    echo "Tabla 'likes_comentarios' creada o verificada.<br>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
