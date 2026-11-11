<?php
require_once __DIR__ . '/../config/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function volverConError(): never {
    header('Location: nueva.php?error=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: nueva.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_denuncia']) || !hash_equals($_SESSION['csrf_denuncia'], $token)) {
    volverConError();
}

$titulo = trim($_POST['titulo'] ?? '');
$categoriaId = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
$descripcion = trim($_POST['descripcion'] ?? '');
$fechaHecho = $_POST['fecha_hecho'] ?? '';
$lugarHecho = trim($_POST['lugar_hecho'] ?? '');
$institucionId = filter_input(INPUT_POST, 'institucion_id', FILTER_VALIDATE_INT);
$dependencia = trim($_POST['dependencia'] ?? '');
$municipio = trim($_POST['municipio'] ?? '');
$latitudRaw = trim($_POST['latitud'] ?? '');
$longitudRaw = trim($_POST['longitud'] ?? '');
$latitud = $latitudRaw === '' ? null : filter_var($latitudRaw, FILTER_VALIDATE_FLOAT);
$longitud = $longitudRaw === '' ? null : filter_var($longitudRaw, FILTER_VALIDATE_FLOAT);
$coordenadasValidas = ($latitudRaw === '' && $longitudRaw === '') ||
    ($latitud !== false && $longitud !== false && $latitud !== null && $longitud !== null &&
     $latitud >= -90 && $latitud <= 90 && $longitud >= -180 && $longitud <= 180);
$personaNombre = trim($_POST['persona_nombre'] ?? '');
$personaCargo = trim($_POST['persona_cargo'] ?? '');
$personaInstitucion = trim($_POST['persona_institucion'] ?? '');
$personaOtros = trim($_POST['persona_otros'] ?? '');

$date = DateTime::createFromFormat('Y-m-d', $fechaHecho);
$fechaValida = $date && $date->format('Y-m-d') === $fechaHecho && $fechaHecho <= date('Y-m-d');

if (
    $titulo === '' || mb_strlen($titulo) > 200 ||
    !$categoriaId || !$institucionId ||
    mb_strlen($descripcion) < 30 ||
    !$fechaValida ||
    $lugarHecho === '' || mb_strlen($lugarHecho) > 250 ||
    !$coordenadasValidas ||
    $municipio === '' || mb_strlen($municipio) > 150 ||
    mb_strlen($dependencia) > 200 ||
    !isset($_POST['acepto']) ||
    mb_strlen($personaNombre) > 200 ||
    mb_strlen($personaCargo) > 150 ||
    mb_strlen($personaInstitucion) > 200
) {
    volverConError();
}

try {
    $checkCategoria = $conexion->prepare("SELECT id FROM categorias WHERE id = ? AND estado = 1");
    $checkCategoria->execute([$categoriaId]);
    if (!$checkCategoria->fetch()) volverConError();

    $checkInstitucion = $conexion->prepare("SELECT id FROM instituciones WHERE id = ? AND estado = 1");
    $checkInstitucion->execute([$institucionId]);
    if (!$checkInstitucion->fetch()) volverConError();
} catch (PDOException $e) {
    error_log('Validación de catálogos: ' . $e->getMessage());
    volverConError();
}

/* Validación previa de archivos: no se guardan hasta validar todos. */
$archivos = [];
$maxArchivos = 5;
$maxBytes = 5 * 1024 * 1024;
$permitidos = [
    'pdf' => ['application/pdf'],
    'jpg' => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png' => ['image/png']
];

if (isset($_FILES['evidencias']) && is_array($_FILES['evidencias']['name'])) {
    $total = count($_FILES['evidencias']['name']);
    if ($total > $maxArchivos) volverConError();

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    for ($i = 0; $i < $total; $i++) {
        if ($_FILES['evidencias']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        if ($_FILES['evidencias']['error'][$i] !== UPLOAD_ERR_OK) volverConError();

        $tmp = $_FILES['evidencias']['tmp_name'][$i];
        $size = (int)$_FILES['evidencias']['size'][$i];
        $original = basename($_FILES['evidencias']['name'][$i]);
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $mime = $finfo->file($tmp);

        if ($size <= 0 || $size > $maxBytes || !isset($permitidos[$ext]) || !in_array($mime, $permitidos[$ext], true)) {
            volverConError();
        }

        $archivos[] = [
            'tmp' => $tmp,
            'size' => $size,
            'original' => mb_substr($original, 0, 255),
            'ext' => $ext,
            'mime' => $mime
        ];
    }
}

$codigo = 'DF360-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
$claveSeguimiento = strtoupper(bin2hex(random_bytes(6)));
$claveHash = password_hash($claveSeguimiento, PASSWORD_DEFAULT);

$storageDir = __DIR__ . '/../storage/evidencias';
if (!is_dir($storageDir) && !mkdir($storageDir, 0750, true)) {
    error_log('No se pudo crear el directorio de evidencias.');
    volverConError();
}

$archivosMovidos = [];

try {
    $conexion->beginTransaction();

    $sql = "INSERT INTO denuncias
        (codigo, clave_seguimiento_hash, titulo, categoria_id, descripcion, fecha_hecho,
         lugar_hecho, institucion_id, dependencia, municipio, latitud, longitud, estado)
        VALUES
        (:codigo, :clave_hash, :titulo, :categoria_id, :descripcion, :fecha_hecho,
         :lugar_hecho, :institucion_id, :dependencia, :municipio, :latitud, :longitud, 'Recibida')";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':codigo' => $codigo,
        ':clave_hash' => $claveHash,
        ':titulo' => $titulo,
        ':categoria_id' => $categoriaId,
        ':descripcion' => $descripcion,
        ':fecha_hecho' => $fechaHecho,
        ':lugar_hecho' => $lugarHecho,
        ':institucion_id' => $institucionId,
        ':dependencia' => $dependencia !== '' ? $dependencia : null,
        ':municipio' => $municipio,
        ':latitud' => $latitud,
        ':longitud' => $longitud
    ]);

    $denunciaId = (int)$conexion->lastInsertId();

    if ($personaNombre !== '' || $personaCargo !== '' || $personaInstitucion !== '' || $personaOtros !== '') {
        $personaStmt = $conexion->prepare("INSERT INTO personas_denunciadas
            (denuncia_id, nombre_completo, cargo, institucion_laboral, otros_datos)
            VALUES (:denuncia_id, :nombre, :cargo, :institucion, :otros)");
        $personaStmt->execute([
            ':denuncia_id' => $denunciaId,
            ':nombre' => $personaNombre !== '' ? $personaNombre : null,
            ':cargo' => $personaCargo !== '' ? $personaCargo : null,
            ':institucion' => $personaInstitucion !== '' ? $personaInstitucion : null,
            ':otros' => $personaOtros !== '' ? $personaOtros : null
        ]);
    }

    $evidenciaStmt = $conexion->prepare("INSERT INTO evidencias
        (denuncia_id, nombre_original, nombre_archivo, ruta_archivo, tipo_archivo, tamanio)
        VALUES (:denuncia_id, :original, :archivo, :ruta, :tipo, :tamanio)");

    foreach ($archivos as $archivo) {
        $nombreGuardado = bin2hex(random_bytes(16)) . '.' . $archivo['ext'];
        $destino = $storageDir . '/' . $nombreGuardado;
        if (!move_uploaded_file($archivo['tmp'], $destino)) {
            throw new RuntimeException('No se pudo guardar un archivo adjunto.');
        }
        $archivosMovidos[] = $destino;

        $rutaRelativa = 'storage/evidencias/' . $nombreGuardado;
        $evidenciaStmt->execute([
            ':denuncia_id' => $denunciaId,
            ':original' => $archivo['original'],
            ':archivo' => $nombreGuardado,
            ':ruta' => $rutaRelativa,
            ':tipo' => $archivo['mime'],
            ':tamanio' => $archivo['size']
        ]);
    }

    $seguimientoStmt = $conexion->prepare("INSERT INTO seguimiento
        (denuncia_id, administrador_id, estado_anterior, estado_nuevo, mensaje_publico, observacion_interna)
        VALUES (:denuncia_id, NULL, NULL, 'Recibida', :mensaje, NULL)");
    $seguimientoStmt->execute([
        ':denuncia_id' => $denunciaId,
        ':mensaje' => 'La denuncia fue recibida correctamente y está pendiente de revisión.'
    ]);

    $conexion->commit();

    $_SESSION['denuncia_confirmacion'] = [
        'codigo' => $codigo,
        'clave' => $claveSeguimiento
    ];
    unset($_SESSION['csrf_denuncia']);
    header('Location: confirmacion.php');
    exit;

} catch (Throwable $e) {
    if ($conexion->inTransaction()) $conexion->rollBack();
    foreach ($archivosMovidos as $archivoMovido) {
        if (is_file($archivoMovido)) @unlink($archivoMovido);
    }
    error_log('Error al registrar denuncia: ' . $e->getMessage());
    volverConError();
}
