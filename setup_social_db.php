<?php
require_once 'redsocial_db.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "Iniciando actualización de base de datos...<br>";

    // 1. Añadir columna fecha_nacimiento a usuarios si no existe
    $sql_check_col = "SHOW COLUMNS FROM usuarios LIKE 'fecha_nacimiento'";
    $stmt = $db->prepare($sql_check_col);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $sql_add_dob = "ALTER TABLE usuarios ADD COLUMN fecha_nacimiento DATE NULL";
        $db->exec($sql_add_dob);
        echo "✅ Columna 'fecha_nacimiento' añadida a tabla 'usuarios'.<br>";
        
        // Asignar fechas de nacimiento aleatorias a usuarios existentes para pruebas
        $stmt_users = $db->query("SELECT id FROM usuarios");
        while ($user = $stmt_users->fetch(PDO::FETCH_ASSOC)) {
            // Generar fecha aleatoria entre 18 y 50 años atrás
            // Y forzar algunos para que cumplan años pronto (en los próximos 10 días)
            $rand = rand(0, 10);
            if ($rand < 3) {
                // 30% de probabilidad de cumplir años pronto
                $mes = date('m');
                $dia = date('d') + rand(0, 15); // Próximos 15 días
                // Ajustar si día se pasa del mes (simplificado)
                if ($dia > 28) $dia = 28; 
                $year = date('Y') - rand(18, 50);
                $fecha = "$year-$mes-$dia";
            } else {
                $timestamp = mt_rand(strtotime('-50 years'), strtotime('-18 years'));
                $fecha = date("Y-m-d", $timestamp);
            }
            
            $upd = $db->prepare("UPDATE usuarios SET fecha_nacimiento = ? WHERE id = ?");
            $upd->execute([$fecha, $user['id']]);
        }
        echo "✅ Fechas de nacimiento de prueba asignadas.<br>";
        
    } else {
        echo "ℹ️ La columna 'fecha_nacimiento' ya existe.<br>";
    }

    // 2. Crear tabla de amistades
    $sql_friends = "CREATE TABLE IF NOT EXISTS amistades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        solicitante_id INT NOT NULL,
        receptor_id INT NOT NULL,
        estado ENUM('pendiente', 'aceptada', 'rechazada') DEFAULT 'pendiente',
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_amistad (solicitante_id, receptor_id),
        FOREIGN KEY (solicitante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (receptor_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )";
    
    $db->exec($sql_friends);
    echo "✅ Tabla 'amistades' creada o verificada.<br>";

    echo "<h3>Actualización completada con éxito.</h3>";
    echo "<a href='inicio.html'>Volver a Inicio</a>";

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
