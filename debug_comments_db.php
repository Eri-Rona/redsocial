<?php
require_once 'redsocial_db.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>Verificación de Base de Datos para Comentarios</h2>";

    // 1. Check comentarios table
    $stmt = $db->query("DESCRIBE comentarios");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>Columnas en 'comentarios':</h3>";
    echo "<pre>" . print_r($columns, true) . "</pre>";
    
    if (in_array('parent_id', $columns)) {
        echo "<p style='color:green'>SUCCESS: Columna 'parent_id' existe.</p>";
    } else {
        echo "<p style='color:red'>ERROR: Columna 'parent_id' FALTA.</p>";
    }

    // 2. Check likes_comentarios table
    try {
        $stmt = $db->query("DESCRIBE likes_comentarios");
        echo "<p style='color:green'>SUCCESS: Tabla 'likes_comentarios' existe.</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>ERROR: Tabla 'likes_comentarios' FALTA (" . $e->getMessage() . ")</p>";
    }
    
    // 3. Test API Query manually
    echo "<h3>Prueba de Query de API:</h3>";
    try {
        $sql = "SELECT c.id, (SELECT COUNT(*) FROM likes_comentarios WHERE comentario_id = c.id) as likes FROM comentarios c LIMIT 1";
        $db->query($sql);
        echo "<p style='color:green'>SUCCESS: Query con subselect a likes_comentarios funciona.</p>";
    } catch (Exception $e) {
         echo "<p style='color:red'>ERROR QUERY: " . $e->getMessage() . "</p>";
    }

} catch(PDOException $e) {
    echo "FATAL ERROR: " . $e->getMessage();
}
?>
