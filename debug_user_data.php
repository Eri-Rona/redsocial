<?php
require_once 'redsocial_db.php';
try {
    $db = (new Database())->getConnection();
    $stmt = $db->query("SELECT id, LENGTH(fotoperfil) as len, LEFT(fotoperfil, 50) as start FROM usuarios LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($user);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
