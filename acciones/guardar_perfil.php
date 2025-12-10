<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../redsocial_login.html');
    exit;
}

require_once __DIR__ . '/../redsocial_db.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $usuarioId = $_SESSION['user_id'];
    $tipo = $_POST['tipo'] ?? '';
    
    if ($tipo === 'basicos') {
        // Actualizar datos básicos
        $nombre = trim($_POST['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $biografia = trim($_POST['biografia'] ?? '');
        $ciudad = trim($_POST['ciudad'] ?? '');
        $fechaNacimiento = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
        
        // Actualizar username y datos adicionales en la tabla usuarios
        if ($usuario !== '') {
            $sql = 'UPDATE usuarios SET username = :u, bio = :b, ciudad = :c, fecha_nacimiento = :f WHERE id = :id';
            $stmtUser = $pdo->prepare($sql);
            $stmtUser->execute([
                ':u' => $usuario, 
                ':b' => $biografia,
                ':c' => $ciudad,
                ':f' => $fechaNacimiento,
                ':id' => $usuarioId
            ]);
            $_SESSION['user_name'] = $usuario;
        }

    } elseif ($tipo === 'contacto') {
        // Actualizar información de contacto
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if ($correo !== '') {
            $stmtUser = $pdo->prepare('UPDATE usuarios SET email = :e, telefono = :t WHERE id = :id');
            $stmtUser->execute([
                ':e' => $correo,
                ':t' => $telefono,
                ':id' => $usuarioId
            ]);
            $_SESSION['user_email'] = $correo;
        }

    } elseif ($tipo === 'redes') {
        // Actualizar redes sociales
        $facebook = trim($_POST['facebook'] ?? '');
        $instagram = trim($_POST['instagram'] ?? '');
        $twitter = trim($_POST['twitter'] ?? '');

        $stmt = $pdo->prepare('UPDATE usuarios SET facebook = :f, instagram = :i, twitter = :t WHERE id = :id');
        $stmt->execute([
            ':f' => $facebook,
            ':i' => $instagram,
            ':t' => $twitter,
            ':id' => $usuarioId
        ]);

    } elseif ($tipo === 'preferencias') {
        // Actualizar preferencias
        // Esto necesitaría tablas adicionales para guardar preferencias
    }

    // Redirigir con mensaje de éxito
    header('Location: ../editar-perfil.html?success=perfil');
    exit;
    
} catch (Throwable $e) {
    $_SESSION['error_perfil'] = 'Error al actualizar el perfil: ' . $e->getMessage();
    header('Location: ../editar-perfil.html');
    exit;
}
?>
