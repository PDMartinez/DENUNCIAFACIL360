<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/auth.php';
protegerAccion();

$id = filter_input(INPUT_POST, 'denuncia_id', FILTER_VALIDATE_INT);
$estadoNuevo = trim($_POST['estado'] ?? '');
$mensaje = trim($_POST['mensaje_publico'] ?? '');
$observacion = trim($_POST['observacion_interna'] ?? '');
$estadosPermitidos = ['Recibida','Pendiente de revision','En revision','En analisis','Informacion adicional solicitada','Concluida'];

if (!$id || !in_array($estadoNuevo, $estadosPermitidos, true) || mb_strlen($mensaje) > 2000 || mb_strlen($observacion) > 5000) {
    header('Location: denuncias.php?error=datos');
    exit;
}
if ($mensaje === '' && $observacion === '') {
    header('Location: detalle.php?id=' . $id . '&error=vacio');
    exit;
}

try {
    $conexion->beginTransaction();
    $stmt = $conexion->prepare("SELECT estado FROM denuncias WHERE id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $actual = $stmt->fetchColumn();
    if ($actual === false) {
        $conexion->rollBack();
        header('Location: denuncias.php?error=no_encontrada');
        exit;
    }

    $update = $conexion->prepare("UPDATE denuncias SET estado = :estado WHERE id = :id");
    $update->execute([':estado' => $estadoNuevo, ':id' => $id]);

    $insert = $conexion->prepare(
        "INSERT INTO seguimiento
         (denuncia_id, administrador_id, estado_anterior, estado_nuevo, mensaje_publico, observacion_interna)
         VALUES (:denuncia_id, :administrador_id, :anterior, :nuevo, :mensaje, :observacion)"
    );
    $insert->execute([
        ':denuncia_id' => $id,
        ':administrador_id' => $_SESSION['admin_id'],
        ':anterior' => $actual,
        ':nuevo' => $estadoNuevo,
        ':mensaje' => $mensaje !== '' ? $mensaje : null,
        ':observacion' => $observacion !== '' ? $observacion : null
    ]);

    $conexion->commit();
    header('Location: detalle.php?id=' . $id . '&actualizado=1');
    exit;
} catch (Throwable $ex) {
    if ($conexion->inTransaction()) $conexion->rollBack();
    error_log('Actualización de denuncia: ' . $ex->getMessage());
    header('Location: detalle.php?id=' . $id . '&error=guardar');
    exit;
}
