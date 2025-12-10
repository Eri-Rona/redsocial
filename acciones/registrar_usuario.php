<?php

use Bd\Conexion;

require_once __DIR__ . '/../bd/Conexion.php';

$conexion = new Conexion();
$pdo = $conexion->getConnection();

// Validar datos básicos
$nombre = trim($_POST['nombre_completo'] ?? '');
$usuario = trim($_POST['usuario'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$password = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';

if ($nombre === '' || $usuario === '' || $correo === '' || $password === '' || $password !== $password2) {
    header('Location: ../login.html');
    exit;
}

try {
    // Registrar usuario básico (ajusta los nombres de tabla/campos según tu BD)
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare('INSERT INTO redsocial_usuarios (username, email, password) VALUES (:u, :e, :p)');
    $stmt->execute([
        ':u' => $usuario,
        ':e' => $correo,
        ':p' => $hash,
    ]);

    $usuarioId = (int)$pdo->lastInsertId();

    // Crear perfil vacío asociado
    $stmtPerfil = $pdo->prepare('INSERT INTO redsocial_perfiles (usuario_id, nombre, apellidos, biografia, ciudad) VALUES (:id, :n, :a, :b, :c)');
    $stmtPerfil->execute([
        ':id' => $usuarioId,
        ':n'  => $nombre,
        ':a'  => '',
        ':b'  => '',
        ':c'  => '',
    ]);

    header('Location: ../inicio.html');
    exit;
} catch (Throwable $e) {
    // En caso de error simple volvemos al registro
    header('Location: ../login.html');
    exit;
}
