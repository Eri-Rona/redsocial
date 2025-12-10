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
    
    // Obtener amigos con fecha de nacimiento
    $query = "SELECT u.id, u.username, u.fecha_nacimiento, u.fotoperfil,
                     COALESCE(u.username, u.email) as display_name
              FROM usuarios u
              JOIN amistades a ON (a.solicitante_id = u.id OR a.receptor_id = u.id)
              WHERE (a.solicitante_id = :uid OR a.receptor_id = :uid)
              AND a.estado = 'aceptada'
              AND u.id != :uid
              AND u.fecha_nacimiento IS NOT NULL";
              
    $stmt = $db->prepare($query);
    $stmt->execute([':uid' => $current_user_id]);
    
    $lista_cumpleanos = [];
    $meses = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
    ];
    
    $hoy = new DateTime();
    $hoy->setTime(0, 0, 0); // Normalizar hora para comparaciones precisas
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $fecha_nac = new DateTime($row['fecha_nacimiento']);
        $dia = $fecha_nac->format('d');
        $mes = $fecha_nac->format('n');
        
        // Calcular próximo cumpleaños
        $proximo_cumple = new DateTime(date('Y') . '-' . $mes . '-' . $dia);
        $proximo_cumple->setTime(0,0,0);
        
        // Si ya pasó hoy, es el del año que viene (pero si es HOY, lo dejamos como hoy)
        if ($proximo_cumple < $hoy) {
            $proximo_cumple->modify('+1 year');
        }
        
        $intervalo = $hoy->diff($proximo_cumple);
        $dias_faltantes = $intervalo->days;
        
        // Formatear texto
        $texto_fecha = "";
        if ($dias_faltantes == 0) {
            $texto_fecha = "¡Es hoy!";
        } elseif ($dias_faltantes == 1) {
            $texto_fecha = "Mañana";
        } else {
            $texto_fecha = $dia . " de " . $meses[$mes];
        }
        
        $lista_cumpleanos[] = [
            'id' => $row['id'],
            'display_name' => $row['display_name'],
            'username' => $row['username'],
            'foto_perfil' => $row['fotoperfil'],
            'texto_fecha' => $texto_fecha,
            'dias_faltantes' => $dias_faltantes
        ];
    }
    
    // Ordenar por días faltantes (ascendente)
    usort($lista_cumpleanos, function($a, $b) {
        return $a['dias_faltantes'] - $b['dias_faltantes'];
    });
    
    // Filtrar solo los próximos 30 días? El usuario dijo "acomodarlos por mes para que se acercan más próximo"
    // Probablemente quiera ver los siguientes inmediatos, aunque falten meses.
    // Devolveremos los top 5 más cercanos.
    
    $proximos = array_slice($lista_cumpleanos, 0, 5);
    
    echo json_encode([
        'status' => 'success',
        'cumpleanos' => $proximos
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
