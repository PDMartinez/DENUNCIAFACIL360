<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: denuncias.php');
    exit;
}
$tituloPagina = 'Detalle de denuncia';
try {
    $stmt = $conexion->prepare(
        "SELECT d.*, c.nombre AS categoria, i.nombre AS institucion
         FROM denuncias d
         LEFT JOIN categorias c ON c.id = d.categoria_id
         LEFT JOIN instituciones i ON i.id = d.institucion_id
         WHERE d.id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $id]);
    $denuncia = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$denuncia) {
        header('Location: denuncias.php?no_encontrada=1');
        exit;
    }

    $personaStmt = $conexion->prepare("SELECT nombre_completo, cargo, institucion_laboral, otros_datos FROM personas_denunciadas WHERE denuncia_id = ?");
    $personaStmt->execute([$id]);
    $persona = $personaStmt->fetch(PDO::FETCH_ASSOC);

    $evidenciaStmt = $conexion->prepare("SELECT id, nombre_original, tipo_archivo, tamanio FROM evidencias WHERE denuncia_id = ? ORDER BY id");
    $evidenciaStmt->execute([$id]);
    $evidencias = $evidenciaStmt->fetchAll(PDO::FETCH_ASSOC);

    $historialStmt = $conexion->prepare(
        "SELECT s.estado_anterior, s.estado_nuevo, s.mensaje_publico, s.observacion_interna,
                s.fecha_registro, a.nombre AS administrador
         FROM seguimiento s
         LEFT JOIN administradores a ON a.id = s.administrador_id
         WHERE s.denuncia_id = ?
         ORDER BY s.fecha_registro DESC, s.id DESC"
    );
    $historialStmt->execute([$id]);
    $historial = $historialStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    error_log('Detalle de denuncia: ' . $ex->getMessage());
    http_response_code(500);
    exit('No se pudo cargar el detalle de la denuncia.');
}
require __DIR__ . '/header.php';
?>
<div class="page-welcome"><div><span class="eyebrow-admin">EXPEDIENTE CIUDADANO</span><h1>Detalle de denuncia</h1><p>Revisá la información y gestioná las actualizaciones.</p></div><a href="denuncias.php" class="btn btn-filter-clear"><i class="bi bi-arrow-left"></i> Volver al listado</a></div>
<div class="detail-grid">
<section class="admin-panel detail-main">
<div class="detail-title-row"><div><small>CÓDIGO DE DENUNCIA</small><h2><?= h($denuncia['codigo']) ?></h2></div><span class="status-badge status-detail"><?= h($denuncia['estado']) ?></span></div>
<h3 class="detail-section-title">Información de los hechos</h3>
<div class="detail-fields">
<div><small>Título / asunto</small><p><?= h($denuncia['titulo']) ?></p></div>
<div><small>Categoría</small><p><?= h($denuncia['categoria'] ?? '—') ?></p></div>
<div><small>Institución involucrada</small><p><?= h($denuncia['institucion'] ?? '—') ?></p></div>
<div><small>Fecha de los hechos</small><p><?= h($denuncia['fecha_hecho']) ?></p></div>
<div><small>Dependencia o área</small><p><?= h($denuncia['dependencia'] ?? '—') ?></p></div>
<div><small>Municipio / localidad</small><p><?= h($denuncia['municipio'] ?? '—') ?></p></div>
<div class="full-field"><small>Lugar específico</small><p><?= h($denuncia['lugar_hecho']) ?></p></div>
<div class="full-field"><small>Descripción detallada</small><p class="description-text"><?= nl2br(h($denuncia['descripcion'])) ?></p></div>
</div>
<h3 class="detail-section-title">Persona denunciada</h3>
<?php if ($persona): ?>
<div class="detail-fields">
<div><small>Nombre completo</small><p><?= h($persona['nombre_completo'] ?? '—') ?></p></div>
<div><small>Cargo o función</small><p><?= h($persona['cargo'] ?? '—') ?></p></div>
<div><small>Institución laboral</small><p><?= h($persona['institucion_laboral'] ?? '—') ?></p></div>
<div class="full-field"><small>Otros datos</small><p><?= nl2br(h($persona['otros_datos'] ?? '—')) ?></p></div>
</div>
<?php else: ?><p class="text-muted small">No se proporcionaron datos de una persona denunciada.</p><?php endif; ?>

<h3 class="detail-section-title">Evidencias adjuntas</h3>
<?php if ($evidencias): ?><div class="evidence-list"><?php foreach ($evidencias as $ev): ?><div class="evidence-item"><i class="bi bi-file-earmark"></i><span><?= h($ev['nombre_original']) ?><small><?= number_format(((int)$ev['tamanio'])/1024, 1) ?> KB</small></span><a href="ver_evidencia.php?id=<?= (int)$ev['id'] ?>" target="_blank" class="btn-view"><i class="bi bi-download"></i> Abrir</a></div><?php endforeach; ?></div><?php else: ?><p class="text-muted small">No se adjuntaron evidencias.</p><?php endif; ?>
</section>

<aside class="detail-side">
<section class="admin-panel update-panel">
<h3><i class="bi bi-pencil-square"></i> Actualizar denuncia</h3>
<p>Modificá el estado y agregá el mensaje que podrá consultar el ciudadano.</p>
<form action="actualizar_estado.php" method="POST">
<input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_admin']) ?>">
<input type="hidden" name="denuncia_id" value="<?= (int)$denuncia['id'] ?>">
<label class="form-label" for="estado">Nuevo estado</label>
<select class="form-select mb-3" id="estado" name="estado" required>
<?php foreach (['Recibida','Pendiente de revision','En revision','En analisis','Informacion adicional solicitada','Concluida'] as $estado): ?>
<option value="<?= h($estado) ?>" <?= $denuncia['estado'] === $estado ? 'selected' : '' ?>><?= h($estado) ?></option>
<?php endforeach; ?>
</select>
<label class="form-label" for="mensaje_publico">Mensaje para el ciudadano</label>
<textarea class="form-control mb-3" id="mensaje_publico" name="mensaje_publico" rows="4" maxlength="2000" placeholder="Escribí una actualización que será visible en el seguimiento..."></textarea>
<label class="form-label" for="observacion_interna">Observación interna</label>
<textarea class="form-control mb-3" id="observacion_interna" name="observacion_interna" rows="4" maxlength="5000" placeholder="Nota privada para el equipo administrativo..."></textarea>
<div class="private-hint"><i class="bi bi-lock-fill"></i> La observación interna no será visible para el ciudadano.</div>
<button type="submit" class="btn btn-admin-primary w-100"><i class="bi bi-save"></i> Guardar actualización</button>
</form>
</section>
<section class="admin-panel history-panel"><h3><i class="bi bi-clock-history"></i> Historial de gestión</h3>
<?php if ($historial): ?><div class="admin-history"><?php foreach ($historial as $mov): ?><article><small><?= h(date('d/m/Y H:i', strtotime($mov['fecha_registro']))) ?></small><strong><?= h($mov['estado_nuevo']) ?></strong><?php if (!empty($mov['mensaje_publico'])): ?><p><b>Mensaje público:</b> <?= nl2br(h($mov['mensaje_publico'])) ?></p><?php endif; ?><?php if (!empty($mov['observacion_interna'])): ?><p class="internal-note"><b><i class="bi bi-lock"></i> Nota interna:</b> <?= nl2br(h($mov['observacion_interna'])) ?></p><?php endif; ?><small>Responsable: <?= h($mov['administrador'] ?? 'Sistema') ?></small></article><?php endforeach; ?></div><?php else: ?><p class="text-muted small">No hay movimientos registrados.</p><?php endif; ?>
</section>
</aside>
</div>
<?php require __DIR__ . '/footer.php'; ?>
