<?php
require_once 'redsocial_db.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->query("DESCRIBE comentarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Estructura de tabla 'comentarios'</h2>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";

} catch(PDOException $e) {
    echo "Error (probablemente tabla no existe): " . $e->getMessage();
}
?>
