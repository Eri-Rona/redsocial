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
    
    $userId = $_SESSION['user_id'];
    
    // Obtener datos completos del usuario incluyendo foto de perfil
    $query = "SELECT id, username, email, fotoperfil FROM usuarios WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Contar amigos
    $queryAmigos = "SELECT COUNT(*) as total FROM amistades 
    WHERE (solicitante_id = :uid OR receptor_id = :uid) AND estado = 'aceptada'";
    $stmtAmigos = $db->prepare($queryAmigos);
    $stmtAmigos->execute([':uid' => $userId]);
    $totalAmigos = $stmtAmigos->fetch(PDO::FETCH_ASSOC)['total'];

    // Contar solicitudes pendientes (donde el usuario es el receptor)
    $queryPendientes = "SELECT COUNT(*) as total FROM amistades 
    WHERE receptor_id = :uid AND estado = 'pendiente'";
    $stmtPendientes = $db->prepare($queryPendientes);
    $stmtPendientes->execute([':uid' => $userId]);
    $totalPendientes = $stmtPendientes->fetch(PDO::FETCH_ASSOC)['total'];

    // Contar publicaciones
    $queryPubs = "SELECT COUNT(*) as total FROM publicaciones WHERE usuario_id = :uid";
    $stmtPubs = $db->prepare($queryPubs);
    $stmtPubs->execute([':uid' => $userId]);
    $totalPubs = $stmtPubs->fetch(PDO::FETCH_ASSOC)['total'];

    if ($user) {
        echo json_encode([
            'status' => 'success',
            'user' => [
                'id' => $user['id'],
                'name' => $user['username'],
                'email' => $user['email'],
                'foto_perfil' => $user['fotoperfil'],
                'stats' => [ // Agregar estadísticas
                    'amigos' => $totalAmigos,
                    'publicaciones' => $totalPubs,
                    'solicitudes' => $totalPendientes
                ]
            ]
        ]);
    } else {
        // Si no encuentra en BD, usar datos de sesión como respaldo
        echo json_encode([
            'status' => 'success',
            'user' => [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'] ?? 'Usuario',
                'email' => $_SESSION['user_email'] ?? '',
                'foto_perfil' => null
            ],
            'warning' => 'Usuario no encontrado en BD, usando datos de sesión'
        ]);
    }
    
} catch(PDOException $e) {
    // En caso de error de BD, usar datos de sesión como respaldo
    echo json_encode([
        'status' => 'success',
        'user' => [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? 'Usuario',
            'email' => $_SESSION['user_email'] ?? '',
            'foto_perfil' => null
        ],
        'warning' => 'Error de BD, usando datos de sesión: ' . $e->getMessage()
    ]);
}
?>
