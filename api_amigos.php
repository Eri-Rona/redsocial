<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

require_once 'redsocial_db.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $current_user_id = $_SESSION['user_id'];
    
    // Obtener solicitudes pendientes (donde soy receptor)
    $query_solicitudes = "
        SELECT a.id as solicitud_id, u.id as usuario_id, u.username, u.fotoperfil,
               COALESCE(u.username, u.email) as display_name, a.fecha_creacion
        FROM amistades a
        JOIN usuarios u ON a.solicitante_id = u.id
        WHERE a.receptor_id = :uid AND a.estado = 'pendiente'
        ORDER BY a.fecha_creacion DESC";
        
    $stmt = $db->prepare($query_solicitudes);
    $stmt->execute([':uid' => $current_user_id]);
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener amigos (donde soy solicitante o receptor, y estado es aceptada)
    $query_amigos = "
        SELECT u.id, u.username, u.fotoperfil,
               COALESCE(u.username, u.email) as display_name
        FROM amistades a
        JOIN usuarios u ON (a.solicitante_id = u.id OR a.receptor_id = u.id)
        WHERE (a.solicitante_id = :uid OR a.receptor_id = :uid)
          AND u.id != :uid
          AND a.estado = 'aceptada'
        ORDER BY u.username ASC";
        
    $stmt = $db->prepare($query_amigos);
    $stmt->execute([':uid' => $current_user_id]);
    $amigos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'solicitudes' => $solicitudes,
        'amigos' => $amigos
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
