<?php
// Script para agregar la columna foto_perfil a la tabla usuarios
require_once 'redsocial_db.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar si la columna fotoperfil existe
    $query = "SHOW COLUMNS FROM usuarios LIKE 'fotoperfil'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // La columna no existe, agregarla como TEXT para base64
        $alterQuery = "ALTER TABLE usuarios ADD COLUMN fotoperfil TEXT NULL AFTER email";
        $db->exec($alterQuery);
        echo "Columna 'fotoperfil' agregada exitosamente a la tabla usuarios (tipo TEXT para base64).<br>";
    } else {
        echo "La columna 'fotoperfil' ya existe en la tabla usuarios.<br>";
    }
    
    echo "Base de datos actualizada correctamente.";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
