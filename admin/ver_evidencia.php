
<?php

// Iniciar búfer para evitar que otros archivos envíen contenido
// antes de los encabezados y los datos de la imagen.
ob_start();

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/auth.php';

// ==========================================
// 1. VALIDAR IDENTIFICADOR
// ==========================================

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    http_response_code(404);
    exit('Archivo no encontrado.');
}

try {

    // ==========================================
    // 2. CONSULTAR EVIDENCIA
    // ==========================================

    $stmt = $conexion->prepare("
        SELECT nombre_original, nombre_archivo, tipo_archivo
        FROM evidencias
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $archivo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$archivo) {
        http_response_code(404);
        exit('Archivo no encontrado.');
    }

    // ==========================================
    // 3. VALIDAR RUTA FÍSICA
    // ==========================================

    $base = realpath(__DIR__ . '/../storage/evidencias');

    if (!$base || !is_dir($base)) {
        throw new RuntimeException('No existe el directorio de evidencias.');
    }

    $nombreArchivo = basename($archivo['nombre_archivo']);

    $ruta = realpath(
        $base . DIRECTORY_SEPARATOR . $nombreArchivo
    );

    if (
        !$ruta ||
        !is_file($ruta) ||
        strpos($ruta, $base . DIRECTORY_SEPARATOR) !== 0
    ) {
        http_response_code(404);
        exit('Archivo no disponible.');
    }

    // ==========================================
    // 4. VALIDAR TIPO MIME REAL
    // ==========================================

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tipoReal = $finfo->file($ruta);

    $tiposPermitidos = [
        'application/pdf',
        'image/jpeg',
        'image/png'
    ];

    if (!in_array($tipoReal, $tiposPermitidos, true)) {
        http_response_code(415);
        exit('Formato de archivo no permitido.');
    }

    // ==========================================
    // 5. LIMPIAR CUALQUIER SALIDA ANTERIOR
    // ==========================================

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // ==========================================
    // 6. ENVIAR ARCHIVO AL NAVEGADOR
    // ==========================================

    $nombreOriginal = basename($archivo['nombre_original']);

    $nombreSeguro = preg_replace(
        '/[^A-Za-z0-9._-]/',
        '_',
        $nombreOriginal
    );

    header('X-Content-Type-Options: nosniff');
    header('Content-Type: ' . $tipoReal);
    header('Content-Disposition: inline; filename="' . $nombreSeguro . '"');
    header('Content-Length: ' . filesize($ruta));
    header('Cache-Control: private, no-store, no-cache, must-revalidate');

    readfile($ruta);
    exit;

} catch (Throwable $e) {

    error_log('Error al visualizar evidencia: ' . $e->getMessage());

    http_response_code(500);
    exit('No se pudo abrir el archivo.');

}
