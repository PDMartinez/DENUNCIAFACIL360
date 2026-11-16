<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/auth.php';
protegerAccion();

$denunciaId = filter_input(INPUT_POST, 'denuncia_id', FILTER_VALIDATE_INT);
$nombre = trim((string)($_POST['nombre_busqueda'] ?? ''));
$primerApellido = trim((string)($_POST['primer_apellido_busqueda'] ?? ''));
$segundoApellido = trim((string)($_POST['segundo_apellido_busqueda'] ?? ''));
if (!$denunciaId || $denunciaId <= 0 || $nombre === '' || $primerApellido === '') {
    header('Location: detalle.php?id=' . (int)$denunciaId . '&verificacion=datos_incompletos');
    exit;
}
$admin = adminActual();
$config = require __DIR__ . '/../config/motor_pdn.php';
$motorRoot = rtrim((string)$config['motor_root'], "\\/");
$pythonExe = (string)$config['python_exe'];
$script = $motorRoot . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'analisis' . DIRECTORY_SEPARATOR . 'ejecutar_analisis.py';
$trabajos = $motorRoot . DIRECTORY_SEPARATOR . 'trabajos';
$logs = $motorRoot . DIRECTORY_SEPARATOR . 'logs';

try {
    if (!is_dir($motorRoot) || !is_file($pythonExe) || !is_file($script)) {
        throw new RuntimeException('No se encontró el motor PDN o el ejecutable Python. Revisá config/motor_pdn.php.');
    }
    if (!is_dir($trabajos) && !mkdir($trabajos, 0770, true) && !is_dir($trabajos)) {
        throw new RuntimeException('No se pudo crear la carpeta privada de trabajos.');
    }
    if (!is_dir($logs) && !mkdir($logs, 0770, true) && !is_dir($logs)) {
        throw new RuntimeException('No se pudo crear la carpeta privada de logs.');
    }

    $stmt = $conexion->prepare("SELECT p.nombre_completo, p.cargo, p.institucion_laboral FROM personas_denunciadas p INNER JOIN denuncias d ON d.id=p.denuncia_id WHERE d.id=? LIMIT 1");
    $stmt->execute([$denunciaId]);
    $persona = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$persona) {
        header('Location: detalle.php?id=' . $denunciaId . '&verificacion=sin_persona');
        exit;
    }

    // Clave única por ejecución para que dos verificaciones de una denuncia no sobrescriban sus resultados.
    $conexion->beginTransaction();
    $insert = $conexion->prepare("INSERT INTO verificaciones_datos_abiertos
        (denuncia_id, administrador_id, persona_nombre, persona_cargo, persona_institucion, fuente, estado,
         nombre_busqueda, primer_apellido_busqueda, segundo_apellido_busqueda)
        VALUES (?, ?, ?, ?, ?, 'PDN - S1', 'Pendiente', ?, ?, ?)");
    $insert->execute([$denunciaId, $admin['id'], $persona['nombre_completo'], $persona['cargo'] ?? null,
        $persona['institucion_laboral'] ?? null, $nombre, $primerApellido, $segundoApellido]);
    $verificacionId = (int)$conexion->lastInsertId();
    $clave = 'denuncia_' . $denunciaId . '_verificacion_' . $verificacionId;
    $upd = $conexion->prepare("UPDATE verificaciones_datos_abiertos SET clave_ejecucion=? WHERE id=?");
    $upd->execute([$clave, $verificacionId]);
    $conexion->commit();

    $requestPath = $trabajos . DIRECTORY_SEPARATOR . 'solicitud_' . $verificacionId . '.json';
    $payload = [
        'clave_ejecucion' => $clave,
        'denuncia_id' => $denunciaId,
        'verificacion_id' => $verificacionId,
        'nombre' => $nombre,
        'primer_apellido' => $primerApellido,
        'segundo_apellido' => $segundoApellido,
    ];
    if (file_put_contents($requestPath, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) === false) {
        throw new RuntimeException('No se pudo guardar la solicitud privada del motor.');
    }

    // El .bat solo contiene rutas configuradas y un identificador numérico; los nombres viajan en JSON.
    $batPath = $trabajos . DIRECTORY_SEPARATOR . 'ejecutar_' . $verificacionId . '.bat';
    $launcherLog = $logs . DIRECTORY_SEPARATOR . 'lanzador_' . $verificacionId . '.log';
    $q = static function (string $v): string { return '"' . str_replace('"', '""', $v) . '"'; };
    $bat = "@echo off\r\n";
    $bat .= 'cd /d ' . $q($motorRoot) . "\r\n";
    $bat .= $q($pythonExe) . ' ' . $q($script) . ' --solicitud-json ' . $q($requestPath) . ' >> ' . $q($launcherLog) . " 2>&1\r\n";
    if (file_put_contents($batPath, $bat, LOCK_EX) === false) {
        throw new RuntimeException('No se pudo crear el lanzador del análisis.');
    }

    $update = $conexion->prepare("UPDATE verificaciones_datos_abiertos SET estado='Procesando' WHERE id=?");
    $update->execute([$verificacionId]);
    // start inicia el .bat de forma desacoplada del request HTTP (Windows/XAMPP).
    $cmd = 'start "" /B "' . str_replace('"', '""', $batPath) . '"';
    @pclose(@popen($cmd, 'r'));

    header('Location: detalle.php?id=' . $denunciaId . '&verificacion=iniciada');
    exit;
} catch (Throwable $e) {
    if ($conexion->inTransaction()) $conexion->rollBack();
    error_log('Integración motor PDN: ' . $e->getMessage());
    if (isset($verificacionId) && $verificacionId > 0) {
        $err = $conexion->prepare("UPDATE verificaciones_datos_abiertos SET estado='Error', mensaje_error=?, fecha_finalizacion=NOW() WHERE id=?");
        $err->execute([mb_substr($e->getMessage(), 0, 1000), $verificacionId]);
    }
    header('Location: detalle.php?id=' . (int)$denunciaId . '&verificacion=error');
    exit;
}
