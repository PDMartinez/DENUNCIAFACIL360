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

    $verificacionesStmt = $conexion->prepare(
        "SELECT v.*, a.nombre AS administrador
         FROM verificaciones_datos_abiertos v
         LEFT JOIN administradores a ON a.id = v.administrador_id
         WHERE v.denuncia_id = ?
         ORDER BY v.fecha_solicitud DESC, v.id DESC"
    );
    $verificacionesStmt->execute([$id]);
    $verificaciones = $verificacionesStmt->fetchAll(PDO::FETCH_ASSOC);

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
<?php if (($_GET['verificacion'] ?? '') === 'solicitada'): ?>
<div class="alert alert-success">La solicitud de verificación fue registrada correctamente. El análisis todavía no se ha ejecutado.</div>
<?php elseif (($_GET['verificacion'] ?? '') === 'sin_persona'): ?>
<div class="alert alert-warning">No hay información suficiente de la persona denunciada para solicitar la verificación.</div>
<?php elseif (($_GET['verificacion'] ?? '') === 'iniciada'): ?>
<div class="alert alert-success">El análisis PDN fue iniciado en segundo plano. El resultado aparecerá aquí automáticamente.</div>
<?php elseif (($_GET['verificacion'] ?? '') === 'datos_incompletos'): ?>
<div class="alert alert-warning">Completá nombre(s) y primer apellido para iniciar la búsqueda.</div>
<?php elseif (($_GET['verificacion'] ?? '') === 'error'): ?>
<div class="alert alert-danger">No se pudo iniciar el análisis. Revisá la configuración del motor y los registros del servidor.</div>
<?php endif; ?>
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
<div class="full-field">
<small>Geolocalización</small>
<?php if (isset($denuncia['latitud'], $denuncia['longitud']) && $denuncia['latitud'] !== null && $denuncia['longitud'] !== null): ?>
<div id="mapaDetalleDenuncia" class="admin-geo-map" data-lat="<?= h($denuncia['latitud']) ?>" data-lng="<?= h($denuncia['longitud']) ?>"></div>
<p class="geo-coordinates">Latitud: <?= h($denuncia['latitud']) ?> · Longitud: <?= h($denuncia['longitud']) ?></p>
<a class="btn-view" href="https://www.openstreetmap.org/?mlat=<?= rawurlencode((string)$denuncia['latitud']) ?>&mlon=<?= rawurlencode((string)$denuncia['longitud']) ?>#map=17/<?= rawurlencode((string)$denuncia['latitud']) ?>/<?= rawurlencode((string)$denuncia['longitud']) ?>" target="_blank" rel="noopener">Abrir en OpenStreetMap <i class="bi bi-box-arrow-up-right"></i></a>
<?php else: ?><p class="text-muted small">No se seleccionó una geolocalización para esta denuncia.</p><?php endif; ?>
</div>
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

<section class="verification-panel">
<h3 class="detail-section-title"><i class="bi bi-database-check"></i> Verificación con datos abiertos</h3>
<p>El motor consultará declaraciones de la Plataforma Digital Nacional (PDN) y generará una interpretación estadística. La coincidencia nominal no confirma identidad ni demuestra irregularidad.</p>
<?php if ($persona && trim((string)($persona['nombre_completo'] ?? '')) !== ''): ?>
<div class="verification-person">
    <div><small>Persona a verificar</small><strong><?= h($persona['nombre_completo']) ?></strong></div>
    <div><small>Cargo o función</small><span><?= h($persona['cargo'] ?: 'No informado') ?></span></div>
    <div><small>Institución</small><span><?= h($persona['institucion_laboral'] ?: 'No informada') ?></span></div>
</div>
<form action="solicitar_verificacion.php" method="POST" class="pdn-request-form" onsubmit="return confirm('¿Iniciar el análisis PDN con estos nombres y apellidos?');">
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_admin'] ?? '') ?>">
    <input type="hidden" name="denuncia_id" value="<?= (int)$denuncia['id'] ?>">
    <div class="row g-2 mb-3">
      <div class="col-md-4"><label class="form-label" for="nombre_busqueda">Nombre(s) *</label><input class="form-control" id="nombre_busqueda" name="nombre_busqueda" maxlength="150" required placeholder="Ej.: JUAN CARLOS"></div>
      <div class="col-md-4"><label class="form-label" for="primer_apellido_busqueda">Primer apellido *</label><input class="form-control" id="primer_apellido_busqueda" name="primer_apellido_busqueda" maxlength="100" required placeholder="Ej.: PEREZ"></div>
      <div class="col-md-4"><label class="form-label" for="segundo_apellido_busqueda">Segundo apellido</label><input class="form-control" id="segundo_apellido_busqueda" name="segundo_apellido_busqueda" maxlength="100" placeholder="Opcional"></div>
    </div>
    <p class="text-muted small">Separá los nombres y apellidos según la información disponible. No se deducen automáticamente para evitar coincidencias incorrectas.</p>
    <button type="submit" class="btn btn-admin-primary"><i class="bi bi-search"></i> Ejecutar análisis PDN</button>
</form>
<?php else: ?>
<div class="private-hint"><i class="bi bi-info-circle"></i> No se puede solicitar la verificación porque esta denuncia no contiene un nombre de persona denunciada.</div>
<?php endif; ?>
<h4 class="verification-history-title">Solicitudes registradas</h4>
<?php if ($verificaciones): ?>
<div class="table-responsive">
<table class="table verification-table align-middle">
<thead><tr><th>Fecha</th><th>Persona</th><th>Fuente prevista</th><th>Estado</th><th>Responsable</th></tr></thead>
<tbody>
<?php foreach ($verificaciones as $ver): ?>
<tr>
<td><?= h(date('d/m/Y H:i', strtotime($ver['fecha_solicitud']))) ?></td>
<td><?= h($ver['persona_nombre']) ?></td>
<td><?= h($ver['fuente']) ?></td>
<td><span class="verification-status status-<?= strtolower(str_replace(' ', '-', $ver['estado'])) ?>"><?= h($ver['estado']) ?></span></td>
<td><?= h($ver['administrador'] ?? 'Administrador') ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div>
<?php else: ?>
<p class="text-muted small mb-0">Todavía no se registraron solicitudes de verificación para esta denuncia.</p>
<?php endif; ?>
<?php foreach ($verificaciones as $ver): ?>
<?php $resultadoPdn = !empty($ver['resultado']) ? json_decode($ver['resultado'], true) : null; ?>
<div class="pdn-result-card" data-verificacion-id="<?= (int)$ver['id'] ?>">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2"><h4>Resultado de verificación #<?= (int)$ver['id'] ?></h4><span class="verification-status status-<?= strtolower(str_replace(' ', '-', $ver['estado'])) ?>"><?= h($ver['estado']) ?></span></div>
  <?php if (in_array($ver['estado'], ['Pendiente','Procesando'], true)): ?>
    <p class="small text-muted mb-0 pdn-live-status">El motor está procesando la solicitud. Esta sección se actualizará automáticamente.</p>
  <?php elseif ($ver['estado'] === 'Error'): ?>
    <div class="alert alert-danger mb-0"><?= h($ver['mensaje_error'] ?? 'No se pudo completar el análisis. Consultá el log privado del motor.') ?></div>
  <?php elseif ($ver['estado'] === 'Sin coincidencias'): ?>
    <div class="alert alert-warning mb-0">No se encontraron declaraciones con los datos exactos proporcionados. Esto no permite concluir nada sobre la situación patrimonial de la persona.</div>
  <?php elseif (is_array($resultadoPdn)): ?>
    <?php $rResumen = $resultadoPdn['resumen'] ?? []; $rModelo = $rResumen['resultados_modelo'] ?? []; ?>
    <div class="pdn-kpi-grid">
      <div><strong><?= (int)($rResumen['resumen_origen']['declaraciones_unicas'] ?? count($resultadoPdn['declaraciones'] ?? [])) ?></strong><small>Declaraciones analizadas</small></div>
      <div><strong><?= count($resultadoPdn['comparaciones'] ?? []) ?></strong><small>Comparaciones</small></div>
      <div><strong><?= (int)($rModelo['NORMAL'] ?? 0) ?></strong><small>Clasificación NORMAL</small></div>
      <div><strong><?= (int)($rModelo['ATIPICO'] ?? 0) ?></strong><small>Clasificación ATÍPICO</small></div>
    </div>
    <p class="small text-muted">Resultados del modelo: NORMAL <?= (int)($rModelo['NORMAL'] ?? 0) ?> · ATÍPICO <?= (int)($rModelo['ATIPICO'] ?? 0) ?> · NO EVALUADO <?= (int)($rModelo['NO_EVALUADO'] ?? 0) ?>.</p>
    <div class="alert alert-warning small"><b>Advertencia:</b> NORMAL no demuestra ausencia de irregularidades; ATÍPICO no demuestra una conducta ilícita. La coincidencia de nombres no confirma identidad. Se requiere revisión humana y corroboración independiente.</div>
    <details><summary>Ver declaraciones encontradas (<?= count($resultadoPdn['declaraciones'] ?? []) ?>)</summary><div class="table-responsive mt-2"><table class="table table-sm"><thead><tr><th>Tipo</th><th>Resultado</th><th>Score</th><th>Observaciones</th></tr></thead><tbody>
    <?php foreach (($resultadoPdn['declaraciones'] ?? []) as $decl): ?><tr><td><?= h($decl['tipo_declaracion'] ?? 'No informado') ?></td><td><?= h($decl['resultado_modelo'] ?? 'No evaluado') ?></td><td><?= h($decl['score_anomalia'] ?? '—') ?></td><td><?= h(implode('; ', array_slice($decl['observaciones'] ?? [], 0, 3))) ?></td></tr><?php endforeach; ?>
    </tbody></table></div></details>
    <details class="mt-2"><summary>Ver comparaciones patrimoniales (<?= count($resultadoPdn['comparaciones'] ?? []) ?>)</summary><div class="table-responsive mt-2"><table class="table table-sm"><thead><tr><th>Comparación</th><th>Tipo</th><th>Interpretación</th></tr></thead><tbody>
    <?php foreach (($resultadoPdn['comparaciones'] ?? []) as $comp): ?><tr><td><?= h(($comp['registro_anterior'] ?? 'Anterior') . ' → ' . ($comp['registro_posterior'] ?? 'Posterior')) ?></td><td><?= h($comp['tipo_comparacion'] ?? '—') ?></td><td><?= h(implode('; ', array_slice($comp['observaciones'] ?? [], 0, 3))) ?></td></tr><?php endforeach; ?>
    </tbody></table></div></details>
  <?php endif; ?>
</div>
<?php endforeach; ?>
</section>

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
<?php if (isset($denuncia['latitud'], $denuncia['longitud']) && $denuncia['latitud'] !== null && $denuncia['longitud'] !== null): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>.admin-geo-map{height:280px;width:100%;margin-top:8px;border:1px solid #eadfea;border-radius:9px;z-index:1}.geo-coordinates{margin:7px 0;color:#8a7f8d;font-size:10px}
.pdn-request-form{margin-top:14px}.pdn-result-card{border:1px solid #eadfea;border-radius:10px;padding:16px;margin-top:16px;background:#fff}.pdn-result-card h4{font-size:14px;color:#514354;margin:0}.pdn-kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:14px 0}.pdn-kpi-grid>div{background:#fcf7fb;border:1px solid #f0e7ef;border-radius:9px;padding:12px;display:flex;flex-direction:column;gap:4px}.pdn-kpi-grid strong{font-size:22px;color:#6e4770}.pdn-kpi-grid small{font-size:11px;color:#827687}.pdn-result-card details{border-top:1px solid #eee5ee;padding-top:10px}.pdn-result-card summary{cursor:pointer;font-size:13px;font-weight:600;color:#624b66}.pdn-result-card .table{font-size:11px}.status-sin-coincidencias{background:#fff4df;color:#94651c}
@media(max-width:700px){.pdn-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}

</style>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const el = document.getElementById('mapaDetalleDenuncia');
  if (!el || typeof L === 'undefined') return;
  const lat = Number(el.dataset.lat), lng = Number(el.dataset.lng);
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
  const map = L.map(el).setView([lat, lng], 16);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>'
  }).addTo(map);
  L.marker([lat, lng]).addTo(map).bindPopup('Lugar indicado en la denuncia').openPopup();
});
</script>
<?php endif; ?>
<style>
.verification-panel{border:1px solid #eadfea;border-radius:12px;padding:20px;margin:22px 0;background:#fff}
.verification-panel>p{color:#827687;font-size:13px;line-height:1.6}
.verification-person{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin:16px 0;padding:14px;background:#fcf7fb;border-radius:9px}
.verification-person div{display:flex;flex-direction:column;gap:5px;min-width:0}
.verification-person small{font-size:11px;color:#8a7f8d}
.verification-person strong,.verification-person span{font-size:13px;overflow-wrap:anywhere}
.verification-history-title{font-size:15px;margin:22px 0 10px;color:#514354}
.verification-table{font-size:12px;margin-bottom:0}
.verification-status{display:inline-block;padding:4px 8px;border-radius:20px;background:#f3edf3;color:#765d76;font-size:11px}
.status-completada{background:#e8f6ee;color:#267348}.status-error{background:#fdecec;color:#a33c3c}.status-procesando{background:#fff4df;color:#94651c}
@media(max-width:700px){.verification-person{grid-template-columns:1fr}}

.pdn-request-form{margin-top:14px}.pdn-result-card{border:1px solid #eadfea;border-radius:10px;padding:16px;margin-top:16px;background:#fff}.pdn-result-card h4{font-size:14px;color:#514354;margin:0}.pdn-kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:14px 0}.pdn-kpi-grid>div{background:#fcf7fb;border:1px solid #f0e7ef;border-radius:9px;padding:12px;display:flex;flex-direction:column;gap:4px}.pdn-kpi-grid strong{font-size:22px;color:#6e4770}.pdn-kpi-grid small{font-size:11px;color:#827687}.pdn-result-card details{border-top:1px solid #eee5ee;padding-top:10px}.pdn-result-card summary{cursor:pointer;font-size:13px;font-weight:600;color:#624b66}.pdn-result-card .table{font-size:11px}.status-sin-coincidencias{background:#fff4df;color:#94651c}
@media(max-width:700px){.pdn-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}

</style>

<script>
(function(){
  const pending = Array.from(document.querySelectorAll('.pdn-result-card[data-verificacion-id]'))
    .filter(el => el.querySelector('.pdn-live-status'));
  if (!pending.length) return;
  let attempts = 0;
  const timer = setInterval(async () => {
    attempts++;
    for (const card of pending) {
      try {
        const response = await fetch('consultar_verificacion.php?id=' + encodeURIComponent(card.dataset.verificacionId), {headers:{'Accept':'application/json'}});
        if (!response.ok) continue;
        const data = await response.json();
        if (!['Pendiente','Procesando'].includes(data.estado)) { clearInterval(timer); window.location.reload(); return; }
      } catch(e) { /* se reintentará en el siguiente ciclo */ }
    }
    if (attempts >= 180) clearInterval(timer);
  }, 5000);
})();
</script>
<?php require __DIR__ . '/footer.php'; ?>
