<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

require_once '../redsocial_db.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['accion'])) {
        throw new Exception('Acción no especificada');
    }

    $database = new Database();
    $db = $database->getConnection();
    $current_user_id = $_SESSION['user_id'];

    if ($input['accion'] === 'enviar_solicitud') {
        if (!isset($input['receptor_id'])) {
            throw new Exception('ID de receptor requerido');
        }
        
        $receptor_id = $input['receptor_id'];
        
        // Verificar si ya existe relación
        $check_query = "SELECT id, estado FROM amistades 
                        WHERE (solicitante_id = :u1 AND receptor_id = :u2) 
                           OR (solicitante_id = :u2 AND receptor_id = :u1)";
        $stmt_check = $db->prepare($check_query);
        $stmt_check->execute([':u1' => $current_user_id, ':u2' => $receptor_id]);
        
        if ($stmt_check->rowCount() > 0) {
            $existing = $stmt_check->fetch(PDO::FETCH_ASSOC);
            if ($existing['estado'] === 'pendiente') {
                echo json_encode(['status' => 'success', 'message' => 'Solicitud ya enviada']);
            } else if ($existing['estado'] === 'aceptada') {
                echo json_encode(['status' => 'success', 'message' => 'Ya son amigos']);
            } else {
                 // Si fue rechazada, se podría permitir reenviar, pero por ahora lo dejamos simple
                 echo json_encode(['status' => 'error', 'message' => 'No se puede enviar solicitud']);
            }
            exit;
        }

        // Insertar nueva solicitud
        $query = "INSERT INTO amistades (solicitante_id, receptor_id, estado) VALUES (:u1, :u2, 'pendiente')";
        $stmt = $db->prepare($query);
        $stmt->execute([':u1' => $current_user_id, ':u2' => $receptor_id]);
        
        echo json_encode(['status' => 'success', 'message' => 'Solicitud enviada']);
    } 
    elseif ($input['accion'] === 'aceptar_solicitud') {
        if (!isset($input['solicitud_id'])) {
            throw new Exception('ID de solicitud requerido');
        }
        
        $solicitud_id = $input['solicitud_id'];
        
        // Verificar que la solicitud existe y el usuario actual es el receptor
        $query = "UPDATE amistades SET estado = 'aceptada' 
                  WHERE id = :id AND receptor_id = :uid AND estado = 'pendiente'";
        
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $solicitud_id, ':uid' => $current_user_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Solicitud aceptada']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo aceptar la solicitud']);
        }
    }
    elseif ($input['accion'] === 'rechazar_solicitud') {
        if (!isset($input['solicitud_id'])) {
            throw new Exception('ID de solicitud requerido');
        }
        
        $solicitud_id = $input['solicitud_id'];
        
        // Eliminar la solicitud
        $query = "DELETE FROM amistades 
                  WHERE id = :id AND receptor_id = :uid AND estado = 'pendiente'";
        
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $solicitud_id, ':uid' => $current_user_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Solicitud rechazada']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo rechazar la solicitud']);
        }
    }
    else {
        throw new Exception('Acción no válida');
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
