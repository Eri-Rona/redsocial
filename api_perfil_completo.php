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
    
    // Permitir ver perfil de otros usuarios
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
    
    // Obtener datos del usuario
    $query = "SELECT id, username, email, fotoperfil, bio, ciudad, telefono, facebook, instagram, twitter, fecha_nacimiento FROM usuarios WHERE id = :id";
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

    // Contar publicaciones
    $queryPubs = "SELECT COUNT(*) as total FROM publicaciones WHERE usuario_id = :uid";
    $stmtPubs = $db->prepare($queryPubs);
    $stmtPubs->execute([':uid' => $userId]);
    $totalPubs = $stmtPubs->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($user) {
        // Verificar si es el propio perfil para permisos de edición
        $isOwnProfile = ($userId == $_SESSION['user_id']);

        echo json_encode([
            'status' => 'success',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'foto_perfil' => $user['fotoperfil'],
                'stats' => [
                    'amigos' => $totalAmigos,
                    'publicaciones' => $totalPubs,
                    'seguidores' => 0 // Aún no implementado
                ],
                'is_own_profile' => $isOwnProfile,
                // Datos adicionales del perfil
                'nombre' => $user['username'] ? explode(' ', $user['username'])[0] : '',
                'apellido' => $user['username'] ? implode(' ', array_slice(explode(' ', $user['username']), 1)) : '',
                'biografia' => $user['bio'] ?? '',
                'ciudad' => $user['ciudad'] ?? '',
                'fecha_nacimiento' => $user['fecha_nacimiento'] ?? '',
                'telefono' => $user['telefono'] ?? '',
                'facebook' => $user['facebook'] ?? '',
                'instagram' => $user['instagram'] ?? '',
                'twitter' => $user['twitter'] ?? ''
            ]
        ]);
    } else {
        // Usar datos de sesión como respaldo
        echo json_encode([
            'status' => 'success',
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['user_name'] ?? 'Usuario',
                'email' => $_SESSION['user_email'] ?? '',
                'foto_perfil' => null,
                'nombre' => $_SESSION['user_name'] ? explode(' ', $_SESSION['user_name'])[0] : '',
                'apellido' => $_SESSION['user_name'] ? implode(' ', array_slice(explode(' ', $_SESSION['user_name']), 1)) : '',
                'biografia' => '',
                'ciudad' => '',
                'telefono' => ''
            ],
            'warning' => 'Usuario no encontrado en BD, usando datos de sesión'
        ]);
    }
    
} catch(PDOException $e) {
    // Usar datos de sesión como respaldo
    echo json_encode([
        'status' => 'success',
        'user' => [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['user_name'] ?? 'Usuario',
            'email' => $_SESSION['user_email'] ?? '',
            'foto_perfil' => null,
            'nombre' => $_SESSION['user_name'] ? explode(' ', $_SESSION['user_name'])[0] : '',
            'apellido' => $_SESSION['user_name'] ? implode(' ', array_slice(explode(' ', $_SESSION['user_name']), 1)) : '',
            'biografia' => '',
            'ciudad' => '',
            'telefono' => ''
        ],
        'warning' => 'Error de BD, usando datos de sesión: ' . $e->getMessage()
    ]);
}
?>
