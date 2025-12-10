<?php
session_start();
header('Content-Type: application/json');

// Mostrar información de depuración
echo json_encode([
    'session_data' => $_SESSION,
    'user_id_exists' => isset($_SESSION['user_id']),
    'user_id_value' => $_SESSION['user_id'] ?? 'NO EXISTE',
    'user_name' => $_SESSION['user_name'] ?? 'NO EXISTE',
    'user_email' => $_SESSION['user_email'] ?? 'NO EXISTE'
]);
?>
