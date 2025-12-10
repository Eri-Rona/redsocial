<?php
require_once 'redsocial_db.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>Reparando base de datos...</h2>";
    
    // Cambiar fotoperfil a LONGTEXT para soportar imágenes grandes en Base64
    $sql = "ALTER TABLE usuarios MODIFY fotoperfil LONGTEXT NULL";
    $db->exec($sql);
    
    echo "✅ Columna 'fotoperfil' actualizada a LONGTEXT.<br>";
    echo "Ahora las imágenes se guardarán completas.<br>";
    echo "<br><strong>IMPORTANTE:</strong> Las imágenes subidas anteriormente estaban truncadas (cortadas). Por favor, sube tu foto de perfil nuevamente.";
    echo "<br><a href='inicio.html'>Volver al Inicio</a>";

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
