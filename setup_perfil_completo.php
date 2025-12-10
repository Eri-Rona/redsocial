<?php
// Script para agregar columnas adicionales de perfil a la tabla usuarios
require_once 'redsocial_db.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $columnsToAdd = [
        'bio' => 'TEXT NULL AFTER fotoperfil',
        'ciudad' => 'VARCHAR(100) NULL AFTER bio',
        'telefono' => 'VARCHAR(20) NULL AFTER ciudad',
        'facebook' => 'VARCHAR(255) NULL AFTER telefono',
        'instagram' => 'VARCHAR(255) NULL AFTER facebook',
        'twitter' => 'VARCHAR(255) NULL AFTER instagram'
    ];
    
    foreach ($columnsToAdd as $columnName => $columnDefinition) {
        // Verificar si la columna existe
        $query = "SHOW COLUMNS FROM usuarios LIKE '$columnName'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            // La columna no existe, agregarla
            $alterQuery = "ALTER TABLE usuarios ADD COLUMN $columnName $columnDefinition";
            $db->exec($alterQuery);
            echo "Columna '$columnName' agregada exitosamente.<br>";
        } else {
            echo "La columna '$columnName' ya existe.<br>";
        }
    }
    
    echo "<br>Base de datos actualizada correctamente.";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
