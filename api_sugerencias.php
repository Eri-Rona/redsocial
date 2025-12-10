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
    
    $currentUserId = $_SESSION['user_id'];
    
    // Obtener usuarios que no sean el usuario actual
    // Y que NO estén en la tabla de amistades con el usuario actual (ni como solicitante ni receptor)
    // Limitar a 5 sugerencias
    $query = "SELECT u.id, u.username, u.email, u.fotoperfil,
                     COALESCE(u.username, u.email) as display_name
              FROM usuarios u
              WHERE u.id != :current_user_id
              AND u.id NOT IN (
                  SELECT receptor_id FROM amistades WHERE solicitante_id = :current_user_id
                  UNION
                  SELECT solicitante_id FROM amistades WHERE receptor_id = :current_user_id
              )
              ORDER BY RAND()
              LIMIT 5";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':current_user_id', $currentUserId);
    $stmt->execute();
    
    $sugerencias = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sugerencias[] = [
            'id' => $row['id'],
            'username' => $row['username'],
            'email' => $row['email'],
            'display_name' => $row['display_name'],
            'foto_perfil' => $row['fotoperfil']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'sugerencias' => $sugerencias,
        'total' => count($sugerencias)
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error al obtener sugerencias: ' . $e->getMessage()
    ]);
}
?>
