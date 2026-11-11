<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit('Archivo no encontrado.');
}

try {
    $stmt = $conexion->prepare("SELECT nombre_original, nombre_archivo, tipo_archivo FROM evidencias WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $archivo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$archivo) {
        http_response_code(404);
        exit('Archivo no encontrado.');
    }

    $base = realpath(__DIR__ . '/../storage/evidencias');
    $ruta = $base ? realpath($base . DIRECTORY_SEPARATOR . $archivo['nombre_archivo']) : false;
    if (!$base || !$ruta || strpos($ruta, $base . DIRECTORY_SEPARATOR) !== 0 || !is_file($ruta)) {
        http_response_code(404);
        exit('Archivo no disponible.');
    }

    $tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($archivo['tipo_archivo'], $tiposPermitidos, true)) {
        http_response_code(415);
        exit('Tipo de archivo no permitido.');
    }

    header('X-Content-Type-Options: nosniff');
    header('Content-Type: ' . $archivo['tipo_archivo']);
    header('Content-Disposition: inline; filename="' . str_replace('"', '', basename($archivo['nombre_original'])) . '"');
    header('Content-Length: ' . filesize($ruta));
    readfile($ruta);
    exit;
} catch (PDOException $ex) {
    error_log('Descarga de evidencia: ' . $ex->getMessage());
    http_response_code(500);
    exit('No se pudo abrir el archivo.');
}
