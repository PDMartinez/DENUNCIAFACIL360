<?php
require_once __DIR__ . '/../config/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_seguimiento'])) {
    $_SESSION['csrf_seguimiento'] = bin2hex(random_bytes(32));
}

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$error = '';
$denuncia = null;
$movimientos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_seguimiento']) || !hash_equals($_SESSION['csrf_seguimiento'], $token)) {
        $error = 'No se pudo procesar la consulta. Actualizá la página e intentá nuevamente.';
    } else {
        $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
        $clave = trim($_POST['clave'] ?? '');

        if ($codigo === '' || $clave === '') {
            $error = 'Ingresá el código y la clave privada de seguimiento.';
        } elseif (mb_strlen($codigo) > 40 || mb_strlen($clave) > 100) {
            $error = 'Las credenciales ingresadas no son válidas.';
        } else {
            try {
                $stmt = $conexion->prepare(
                    "SELECT id, codigo, clave_seguimiento_hash, titulo, estado
                     FROM denuncias
                     WHERE codigo = :codigo
                     LIMIT 1"
                );
                $stmt->execute([':codigo' => $codigo]);
                $registro = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($registro && password_verify($clave, $registro['clave_seguimiento_hash'])) {
                    $denuncia = [
                        'codigo' => $registro['codigo'],
                        'titulo' => $registro['titulo'],
                        'estado' => $registro['estado']
                    ];

                    // Solo se recuperan mensajes públicos. Las observaciones internas nunca se muestran.
                    $historialStmt = $conexion->prepare(
                        "SELECT estado_anterior, estado_nuevo, mensaje_publico, fecha_registro
                         FROM seguimiento
                         WHERE denuncia_id = :denuncia_id
                           AND mensaje_publico IS NOT NULL
                           AND mensaje_publico <> ''
                         ORDER BY fecha_registro DESC, id DESC"
                    );
                    $historialStmt->execute([':denuncia_id' => $registro['id']]);
                    $movimientos = $historialStmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    // Mensaje genérico para no revelar si existe un código registrado.
                    $error = 'No pudimos validar las credenciales. Verificá el código y la clave e intentá nuevamente.';
                }
            } catch (PDOException $ex) {
                error_log('Error en consulta de seguimiento: ' . $ex->getMessage());
                $error = 'Ocurrió un inconveniente al consultar el seguimiento. Intentá nuevamente más tarde.';
            }
        }
    }
}

function claseEstado(string $estado): string {
    $normalizado = mb_strtolower($estado);
    if (str_contains($normalizado, 'recibida')) return 'status-received';
    if (str_contains($normalizado, 'pendiente')) return 'status-pending';
    if (str_contains($normalizado, 'revision')) return 'status-review';
    if (str_contains($normalizado, 'analisis')) return 'status-analysis';
    if (str_contains($normalizado, 'informacion')) return 'status-info';
    if (str_contains($normalizado, 'concluida')) return 'status-closed';
    return 'status-default';
}

function fechaLegible(string $fecha): string {
    try {
        return (new DateTime($fecha))->format('d/m/Y \a \l\a\s H:i');
    } catch (Exception $ex) {
        return $fecha;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Consultar seguimiento | DenunciaFácil 360</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="../assets/css/estilos.css" rel="stylesheet">
<link href="../assets/css/seguimiento.css" rel="stylesheet">
</head>
<body>
<header class="site-header">
<nav class="navbar">
<div class="container">
<a class="navbar-brand brand" href="../index.php">
<span class="brand-mark"><i class="bi bi-shield-check"></i></span>
<span><strong>DenunciaFácil <b>360</b></strong><small>Plataforma ciudadana de denuncias</small></span>
</a>
<a class="btn btn-admin ms-auto" href="../index.php"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
</div>
</nav>
</header>

<main class="tracking-page">
<div class="container py-5">
<div class="tracking-intro text-center">
<span class="kicker">CONSULTA CIUDADANA</span>
<h1>Seguimiento de denuncia</h1>
<p>Ingresá el código y la clave privada que recibiste al registrar tu denuncia para consultar su estado y las actualizaciones publicadas.</p>
</div>

<div class="tracking-card">
<?php if (!$denuncia): ?>
<div class="tracking-icon"><i class="bi bi-search"></i></div>
<h2>Consultar mi denuncia</h2>
<p class="card-description">Completá los dos campos para acceder a la información de seguimiento.</p>

<?php if ($error !== ''): ?>
<div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<form method="POST" action="consultar.php" novalidate>
<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_seguimiento']) ?>">
<div class="mb-3">
<label class="form-label" for="codigo">Código de denuncia</label>
<div class="input-icon"><i class="bi bi-ticket-perforated"></i><input class="form-control" type="text" id="codigo" name="codigo" maxlength="40" required placeholder="Ej.: DF360-2026-A1B2C3D4" autocomplete="off" value="<?= e($_POST['codigo'] ?? '') ?>"></div>
</div>
<div class="mb-3">
<label class="form-label" for="clave">Clave privada de seguimiento</label>
<div class="input-icon"><i class="bi bi-key"></i><input class="form-control" type="password" id="clave" name="clave" maxlength="100" required placeholder="Ingresá tu clave privada" autocomplete="off"></div>
</div>
<button type="submit" class="btn btn-primary-custom w-100"><i class="bi bi-search"></i> Consultar seguimiento</button>
</form>
<div class="tracking-help"><i class="bi bi-info-circle"></i><span>Por seguridad, necesitás ambas credenciales. Si perdiste la clave privada, no es posible recuperarla.</span></div>

<?php else: ?>
<div class="result-heading">
<div class="result-check"><i class="bi bi-check-lg"></i></div>
<span class="kicker">CONSULTA VALIDADA</span>
<h2>Información de tu denuncia</h2>
<p>Estos son el estado y los mensajes públicos disponibles.</p>
</div>
<div class="case-summary">
<div><small>Código de denuncia</small><strong><?= e($denuncia['codigo']) ?></strong></div>
<div><small>Asunto</small><strong><?= e($denuncia['titulo']) ?></strong></div>
<div class="current-status"><small>Estado actual</small><span class="status-pill <?= e(claseEstado($denuncia['estado'])) ?>"><?= e($denuncia['estado']) ?></span></div>
</div>

<div class="history-heading"><h3><i class="bi bi-clock-history"></i> Historial de seguimiento</h3><span><?= count($movimientos) ?> actualización(es)</span></div>
<?php if ($movimientos): ?>
<div class="timeline">
<?php foreach ($movimientos as $index => $movimiento): ?>
<article class="timeline-item">
<div class="timeline-marker"><i class="bi bi-check2"></i></div>
<div class="timeline-content">
<div class="timeline-meta">
<strong><?= e($movimiento['estado_nuevo']) ?></strong>
<time><?= e(fechaLegible($movimiento['fecha_registro'])) ?></time>
</div>
<?php if (!empty($movimiento['mensaje_publico'])): ?>
<p><?= nl2br(e($movimiento['mensaje_publico'])) ?></p>
<?php endif; ?>
</div>
</article>
<?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-history"><i class="bi bi-hourglass-split"></i><p>Aún no hay actualizaciones públicas adicionales para esta denuncia.</p></div>
<?php endif; ?>

<div class="tracking-help"><i class="bi bi-shield-lock"></i><span>Esta consulta muestra únicamente información pública de seguimiento. Las observaciones internas de gestión no son visibles.</span></div>
<div class="result-actions">
<a href="consultar.php" class="btn btn-outline-custom"><i class="bi bi-arrow-repeat"></i> Consultar otra denuncia</a>
<a href="../index.php" class="btn btn-primary-custom">Volver al inicio</a>
</div>
<?php endif; ?>
</div>
<div class="tracking-bottom-note"><i class="bi bi-exclamation-circle"></i> El estado “Concluida” indica que finalizó la gestión interna dentro de esta plataforma; no constituye una determinación oficial sobre los hechos denunciados.</div>
</div>
</main>

<footer class="footer"><div class="container"><div class="footer-bottom"><span>© <?= date('Y') ?> DenunciaFácil 360</span><a href="../index.php">Volver a la página principal</a></div></div></footer>
</body>
</html>
