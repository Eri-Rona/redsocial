<?php
require_once 'redsocial_db.php';
try {
    $db = (new Database())->getConnection();
    $stmt = $db->query("SELECT id FROM usuarios LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo "User ID: " . $user['id'];
    } else {
        echo "No users found.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
