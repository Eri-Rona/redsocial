<?php
require_once '../redsocial_db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

$user_id = $_SESSION['usuario_id'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Iniciar transacción para asegurar borrado completo o nada
    $db->beginTransaction();

    // 1. Eliminar likes dados a comentarios
    $stmt = $db->prepare("DELETE FROM likes_comentarios WHERE usuario_id = :uid");
    $stmt->execute([':uid' => $user_id]);

    // 2. Eliminar likes dados a publicaciones
    $stmt = $db->prepare("DELETE FROM likes WHERE usuario_id = :uid");
    $stmt->execute([':uid' => $user_id]);

    // 3. Eliminar comentarios hechos por el usuario
    // (Opcional: Si se borra el usuario, los comentarios podrían quedar huérfanos o borrarse por FK cascade si estuviera configurado, 
    // pero lo hacemos explícito para asegurar limpieza)
    $stmt = $db->prepare("DELETE FROM comentarios WHERE usuario_id = :uid");
    $stmt->execute([':uid' => $user_id]);

    // 4. Eliminar publicaciones (y sus imágenes asociadas si las hay)
    // Primero obtenemos las imágenes para borrarlas del disco si fuera necesario (aquí solo DB)
    $stmt = $db->prepare("DELETE FROM publicaciones WHERE usuario_id = :uid");
    $stmt->execute([':uid' => $user_id]);

    // 5. Eliminar amistades (solicitante o receptor)
    $stmt = $db->prepare("DELETE FROM amistades WHERE solicitante_id = :uid OR receptor_id = :uid");
    $stmt->execute([':uid' => $user_id]);

    // 6. Eliminar usuario final
    $stmt = $db->prepare("DELETE FROM usuarios WHERE id = :uid");
    $stmt->execute([':uid' => $user_id]);

    $db->commit();

    // Destruir sesión
    session_destroy();

    echo json_encode(['status' => 'success']);

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Error BD: ' . $e->getMessage()]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>
