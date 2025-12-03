<?php

use Bd\Conexion;

require_once __DIR__ . '/../bd/Conexion.php';

$conexion = new Conexion();
$pdo = $conexion->getConnection();

// TODO: reemplazar por el ID del usuario en sesión cuando tengas login
$usuarioId = 1;

$tipo = $_POST['tipo'] ?? '';

try {
    if ($tipo === 'basicos') {
        $nombre    = trim($_POST['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $usuario   = trim($_POST['usuario'] ?? '');
        $bio       = trim($_POST['biografia'] ?? '');
        $ciudad    = trim($_POST['ciudad'] ?? '');

        // Actualizar tabla usuarios (nombre de usuario)
        if ($usuario !== '') {
            $stmtUser = $pdo->prepare('UPDATE redsocial_usuarios SET username = :u WHERE id = :id');
            $stmtUser->execute([':u' => $usuario, ':id' => $usuarioId]);
        }

        // Actualizar/insertar perfil
        $stmt = $pdo->prepare('UPDATE redsocial_perfiles SET nombre = :n, apellidos = :a, biografia = :b, ciudad = :c WHERE usuario_id = :id');
        $stmt->execute([
            ':n'  => $nombre,
            ':a'  => $apellidos,
            ':b'  => $bio,
            ':c'  => $ciudad,
            ':id' => $usuarioId,
        ]);

    } elseif ($tipo === 'contacto') {
        $correo   = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if ($correo !== '') {
            $stmtUser = $pdo->prepare('UPDATE redsocial_usuarios SET email = :e WHERE id = :id');
            $stmtUser->execute([':e' => $correo, ':id' => $usuarioId]);
        }

        $stmt = $pdo->prepare('UPDATE redsocial_perfiles SET telefono = :t WHERE usuario_id = :id');
        $stmt->execute([':t' => $telefono, ':id' => $usuarioId]);

    } elseif ($tipo === 'redes') {
        $facebook  = trim($_POST['facebook'] ?? '');
        $instagram = trim($_POST['instagram'] ?? '');
        $twitter   = trim($_POST['twitter'] ?? '');

        $stmt = $pdo->prepare('UPDATE redsocial_perfiles SET facebook = :f, instagram = :i, twitter = :t WHERE usuario_id = :id');
        $stmt->execute([
            ':f'  => $facebook,
            ':i'  => $instagram,
            ':t'  => $twitter,
            ':id' => $usuarioId,
        ]);

    } elseif ($tipo === 'preferencias') {
        $perfilPublico  = isset($_POST['perfil_publico']) ? 1 : 0;
        $notificaciones = isset($_POST['notificaciones']) ? 1 : 0;

        $stmt = $pdo->prepare('UPDATE redsocial_perfiles SET perfil_publico = :p, notificaciones = :n WHERE usuario_id = :id');
        $stmt->execute([
            ':p'  => $perfilPublico,
            ':n'  => $notificaciones,
            ':id' => $usuarioId,
        ]);
    }

    header('Location: ../editar-perfil.html');
    exit;
} catch (Throwable $e) {
    header('Location: ../editar-perfil.html');
    exit;
}
