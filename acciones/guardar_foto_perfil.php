<?php

use Bd\Conexion;

require_once __DIR__ . '/../bd/Conexion.php';

$conexion = new Conexion();
$pdo = $conexion->getConnection();

// TODO: reemplazar por el ID del usuario en sesión cuando tengas login
$usuarioId = 1;

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    header('Location: ../editar-perfil.html');
    exit;
}

$archivo = $_FILES['foto'];
$nombreOriginal = $archivo['name'];
$ext = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
$ext = strtolower($ext);

$permitidas = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($ext, $permitidas, true)) {
    header('Location: ../editar-perfil.html');
    exit;
}

$nombreNuevo = 'perfil_' . $usuarioId . '_' . time() . '.' . $ext;
$rutaCarpeta = __DIR__ . '/../assets/images/perfiles';
if (!is_dir($rutaCarpeta)) {
    mkdir($rutaCarpeta, 0777, true);
}
$rutaDestino = $rutaCarpeta . '/' . $nombreNuevo;

if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    header('Location: ../editar-perfil.html');
    exit;
}

// Guardar ruta relativa en la BD
$rutaRelativa = 'assets/images/perfiles/' . $nombreNuevo;

$stmt = $pdo->prepare('UPDATE redsocial_perfiles SET foto_perfil = :f WHERE usuario_id = :id');
$stmt->execute([
    ':f' => $rutaRelativa,
    ':id' => $usuarioId,
]);

header('Location: ../editar-perfil.html');
exit;
