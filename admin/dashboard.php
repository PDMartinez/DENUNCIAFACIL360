<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/auth.php';

$tituloPagina = 'Dashboard';
$estados = [
    'Recibida' => ['icon' => 'bi-inbox', 'class' => 'pink'],
    'Pendiente de revision' => ['icon' => 'bi-hourglass-split', 'class' => 'orange'],
    'En revision' => ['icon' => 'bi-search', 'class' => 'purple'],
    'En analisis' => ['icon' => 'bi-graph-up', 'class' => 'blue'],
    'Informacion adicional solicitada' => ['icon' => 'bi-chat-left-text', 'class' => 'teal'],
    'Concluida' => ['icon' => 'bi-check2-circle', 'class' => 'green']
];

$total = 0;
$conteos = array_fill_keys(array_keys($estados), 0);
$ultimas = [];
$puntosMapa = [];
$categoriasMapa = [];

try {
    $total = (int)$conexion->query("SELECT COUNT(*) FROM denuncias")->fetchColumn();
    $conteoStmt = $conexion->query("SELECT estado, COUNT(*) AS cantidad FROM denuncias GROUP BY estado");
    foreach ($conteoStmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
        if (array_key_exists($fila['estado'], $conteos)) $conteos[$fila['estado']] = (int)$fila['cantidad'];
    }

    $ultimas = $conexion->query(
        "SELECT d.id, d.codigo, d.titulo, d.estado, d.fecha_registro,
                c.nombre AS categoria
         FROM denuncias d
         LEFT JOIN categorias c ON c.id = d.categoria_id
         ORDER BY d.fecha_registro DESC, d.id DESC
         LIMIT 7"
    )->fetchAll(PDO::FETCH_ASSOC);

    // Solo se consultan coordenadas y datos generales; nunca datos del denunciante.
    $puntosMapa = $conexion->query(
        "SELECT d.latitud, d.longitud, d.estado, c.nombre AS categoria
         FROM denuncias d
         LEFT JOIN categorias c ON c.id = d.categoria_id
         WHERE d.latitud IS NOT NULL
           AND d.longitud IS NOT NULL
           AND d.latitud BETWEEN -90 AND 90
           AND d.longitud BETWEEN -180 AND 180"
    )->fetchAll(PDO::FETCH_ASSOC);

    $categoriasMapa = $conexion->query(
        "SELECT DISTINCT c.nombre
         FROM denuncias d
         INNER JOIN categorias c ON c.id = d.categoria_id
         WHERE d.latitud IS NOT NULL AND d.longitud IS NOT NULL
         ORDER BY c.nombre"
    )->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $ex) {
    error_log('Dashboard: ' . $ex->getMessage());
    $errorDashboard = 'No se pudieron cargar todos los indicadores. Verificá la estructura de la base de datos.';
}

function estadoClaseDash(string $estado): string {
    return match ($estado) {
        'Recibida' => 'badge-pink',
        'Pendiente de revision' => 'badge-orange',
        'En revision' => 'badge-purple',
        'En analisis' => 'badge-blue',
        'Informacion adicional solicitada' => 'badge-teal',
        'Concluida' => 'badge-green',
        default => 'badge-gray'
    };
}
require __DIR__ . '/header.php';
?>
<div class="page-welcome"><div><span class="eyebrow-admin">RESUMEN GENERAL</span><h1>¡Hola, <?= h($admin['nombre']) ?>!</h1><p>Este es el resumen de actividad de DenunciaFácil 360.</p></div><a href="denuncias.php" class="btn btn-admin-primary"><i class="bi bi-folder2-open"></i> Ver denuncias</a></div>
<?php if (!empty($errorDashboard)): ?><div class="alert alert-warning"><?= h($errorDashboard) ?></div><?php endif; ?>
<div class="stats-grid">
<div class="stat-card stat-total"><div class="stat-top"><span>Total de denuncias</span><i class="bi bi-collection"></i></div><strong><?= $total ?></strong><small>Registros recibidos en la plataforma</small></div>
<?php foreach ($estados as $estado => $info): ?>
<div class="stat-card"><div class="stat-top"><span><?= h($estado) ?></span><i class="bi <?= h($info['icon']) ?> <?= h($info['class']) ?>"></i></div><strong><?= $conteos[$estado] ?></strong><small>Denuncias en este estado</small></div>
<?php endforeach; ?>
</div>

<section class="admin-panel geo-dashboard-panel mt-4">
    <div class="panel-heading geo-dashboard-heading">
        <div>
            <h2><i class="bi bi-geo-alt-fill"></i> Mapa geográfico de denuncias</h2>
            <p>Distribución de las denuncias que tienen una ubicación seleccionada.</p>
        </div>
        <span class="geo-map-total"><i class="bi bi-geo"></i> <strong id="totalPuntosMapa"><?= count($puntosMapa) ?></strong> ubicaciones</span>
    </div>

    <div class="geo-map-filters">
        <div>
            <label for="filtroEstadoMapa">Filtrar por estado</label>
            <select id="filtroEstadoMapa" class="form-select">
                <option value="">Todos los estados</option>
                <?php foreach ($estados as $estado => $info): ?>
                    <option value="<?= h($estado) ?>"><?= h($estado) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="filtroCategoriaMapa">Filtrar por categoría</label>
            <select id="filtroCategoriaMapa" class="form-select">
                <option value="">Todas las categorías</option>
                <?php foreach ($categoriasMapa as $categoria): ?>
                    <option value="<?= h($categoria) ?>"><?= h($categoria) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="button" class="btn btn-filter-clear" id="limpiarFiltrosMapa">
            <i class="bi bi-arrow-counterclockwise"></i> Limpiar filtros
        </button>
    </div>

    <div id="mapaGeneralDenuncias" class="geo-dashboard-map" role="application" aria-label="Mapa general de ubicaciones de denuncias"></div>
    <div class="geo-map-legend">
        <span><i class="legend-dot"></i> Denuncia registrada</span>
        <span class="geo-map-note">Los marcadores muestran únicamente ubicación, estado y categoría. No se muestran datos personales ni códigos de seguimiento.</span>
    </div>
    <div id="estadoMapaDashboard" class="geo-map-feedback" aria-live="polite"></div>
</section>

<section class="admin-panel mt-4">
<div class="panel-heading"><div><h2>Últimas denuncias</h2><p>Los registros más recientes de la plataforma.</p></div><a href="denuncias.php">Ver todas <i class="bi bi-arrow-right"></i></a></div>
<div class="table-responsive"><table class="table admin-table align-middle"><thead><tr><th>Código</th><th>Asunto</th><th>Categoría</th><th>Fecha de registro</th><th>Estado</th><th></th></tr></thead><tbody>
<?php if (!$ultimas): ?><tr><td colspan="6" class="text-center py-4 text-muted">Todavía no hay denuncias registradas.</td></tr><?php endif; ?>
<?php foreach ($ultimas as $d): ?><tr>
<td><strong class="case-code"><?= h($d['codigo']) ?></strong></td><td><?= h($d['titulo']) ?></td><td><?= h($d['categoria'] ?? 'Sin categoría') ?></td><td><?= h(date('d/m/Y H:i', strtotime($d['fecha_registro']))) ?></td>
<td><span class="status-badge <?= h(estadoClaseDash($d['estado'])) ?>"><?= h($d['estado']) ?></span></td>
<td><a class="table-action" href="detalle.php?id=<?= (int)$d['id'] ?>" aria-label="Ver detalle"><i class="bi bi-eye"></i></a></td>
</tr><?php endforeach; ?>
</tbody></table></div>
</section>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('mapaGeneralDenuncias');
    if (!mapElement) return;

    const puntos = <?= json_encode($puntosMapa, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const estadoFiltro = document.getElementById('filtroEstadoMapa');
    const categoriaFiltro = document.getElementById('filtroCategoriaMapa');
    const totalElement = document.getElementById('totalPuntosMapa');
    const feedback = document.getElementById('estadoMapaDashboard');

    if (typeof L === 'undefined') {
        feedback.textContent = 'No se pudo cargar la biblioteca del mapa. Verificá tu conexión a Internet.';
        return;
    }

    const map = L.map(mapElement).setView([23.6345, -102.5528], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>'
    }).addTo(map);

    const markersLayer = L.layerGroup().addTo(map);
    let bounds = [];

    function renderMarkers() {
        markersLayer.clearLayers();
        bounds = [];

        const estado = estadoFiltro.value;
        const categoria = categoriaFiltro.value;

        const filtrados = puntos.filter(function (punto) {
            const coincideEstado = !estado || punto.estado === estado;
            const coincideCategoria = !categoria || (punto.categoria || 'Sin categoría') === categoria;
            return coincideEstado && coincideCategoria;
        });

        filtrados.forEach(function (punto) {
            const lat = Number(punto.latitud);
            const lng = Number(punto.longitud);

            if (!Number.isFinite(lat) || !Number.isFinite(lng) ||
                lat < -90 || lat > 90 || lng < -180 || lng > 180) return;

            const marker = L.circleMarker([lat, lng], {
                radius: 8,
                color: '#FFFFFF',
                weight: 2,
                fillColor: '#BD168C',
                fillOpacity: 0.9
            });

            const popup = `
                <div class="geo-popup">
                    <strong>Denuncia registrada</strong>
                    <span><b>Estado:</b> ${escapeHtml(punto.estado || 'Sin estado')}</span>
                    <span><b>Categoría:</b> ${escapeHtml(punto.categoria || 'Sin categoría')}</span>
                </div>
            `;
            marker.bindPopup(popup);
            marker.addTo(markersLayer);
            bounds.push([lat, lng]);
        });

        totalElement.textContent = filtrados.length;

        if (bounds.length === 1) {
            map.setView(bounds[0], 15);
        } else if (bounds.length > 1) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 14 });
        } else if (puntos.length === 0) {
            map.setView([23.6345, -102.5528], 5);
        }

        feedback.textContent = filtrados.length
            ? `Se muestran ${filtrados.length} ubicación(es) con los filtros seleccionados.`
            : 'No hay denuncias geolocalizadas que coincidan con los filtros.';
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;',
                '"': '&quot;', "'": '&#039;'
            })[char];
        });
    }

    estadoFiltro.addEventListener('change', renderMarkers);
    categoriaFiltro.addEventListener('change', renderMarkers);

    document.getElementById('limpiarFiltrosMapa').addEventListener('click', function () {
        estadoFiltro.value = '';
        categoriaFiltro.value = '';
        renderMarkers();
    });

    map.whenReady(function () {
        map.invalidateSize();
        renderMarkers();
    });

    window.addEventListener('resize', function () {
        map.invalidateSize();
    });
});
</script>
<?php require __DIR__ . '/footer.php'; ?>
