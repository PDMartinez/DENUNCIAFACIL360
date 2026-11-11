<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/auth.php';
$tituloPagina = 'Gestión de denuncias';

$estados = ['Recibida','Pendiente de revision','En revision','En analisis','Informacion adicional solicitada','Concluida'];
$busqueda = trim($_GET['buscar'] ?? '');
$estadoFiltro = trim($_GET['estado'] ?? '');
$categoriaFiltro = filter_input(INPUT_GET, 'categoria', FILTER_VALIDATE_INT) ?: 0;
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina = 10;
$condiciones = [];
$params = [];

if ($busqueda !== '') {
    $condiciones[] = "(d.codigo LIKE :buscar OR d.titulo LIKE :buscar OR d.descripcion LIKE :buscar)";
    $params[':buscar'] = '%' . $busqueda . '%';
}
if (in_array($estadoFiltro, $estados, true)) {
    $condiciones[] = "d.estado = :estado";
    $params[':estado'] = $estadoFiltro;
}
if ($categoriaFiltro > 0) {
    $condiciones[] = "d.categoria_id = :categoria";
    $params[':categoria'] = $categoriaFiltro;
}
$where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';

try {
    $categorias = $conexion->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    $countStmt = $conexion->prepare("SELECT COUNT(*) FROM denuncias d $where");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();
    $totalPaginas = max(1, (int)ceil($total / $porPagina));
    $pagina = min($pagina, $totalPaginas);
    $offset = ($pagina - 1) * $porPagina;

    $sql = "SELECT d.id, d.codigo, d.titulo, d.estado, d.fecha_registro,
                   c.nombre AS categoria, i.nombre AS institucion
            FROM denuncias d
            LEFT JOIN categorias c ON c.id = d.categoria_id
            LEFT JOIN instituciones i ON i.id = d.institucion_id
            $where
            ORDER BY d.fecha_registro DESC, d.id DESC
            LIMIT $porPagina OFFSET $offset";
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $denuncias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    error_log('Listado de denuncias: ' . $ex->getMessage());
    $denuncias = [];
    $categorias = [];
    $total = 0;
    $totalPaginas = 1;
    $errorListado = 'No se pudo cargar el listado. Verificá la conexión y las columnas de la base de datos.';
}

function estadoClaseLista(string $estado): string {
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
function urlPagina(int $n): string {
    $params = $_GET;
    $params['pagina'] = $n;
    return '?' . http_build_query($params);
}
require __DIR__ . '/header.php';
?>
<div class="page-welcome"><div><span class="eyebrow-admin">GESTIÓN</span><h1>Denuncias recibidas</h1><p>Buscá, filtrá y consultá los registros de la plataforma.</p></div><div class="total-pill"><i class="bi bi-folder2-open"></i> <?= $total ?> resultado(s)</div></div>
<?php if (!empty($errorListado)): ?><div class="alert alert-warning"><?= h($errorListado) ?></div><?php endif; ?>
<section class="admin-panel">
<form class="filters-form" method="GET">
<div class="filter-search"><label for="buscar">Buscar</label><div class="search-input"><i class="bi bi-search"></i><input id="buscar" name="buscar" value="<?= h($busqueda) ?>" placeholder="Código, asunto o descripción..."></div></div>
<div><label for="estado">Estado</label><select id="estado" name="estado" class="form-select"><option value="">Todos los estados</option><?php foreach ($estados as $estado): ?><option value="<?= h($estado) ?>" <?= $estadoFiltro === $estado ? 'selected' : '' ?>><?= h($estado) ?></option><?php endforeach; ?></select></div>
<div><label for="categoria">Categoría</label><select id="categoria" name="categoria" class="form-select"><option value="">Todas</option><?php foreach ($categorias as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= $categoriaFiltro === (int)$cat['id'] ? 'selected' : '' ?>><?= h($cat['nombre']) ?></option><?php endforeach; ?></select></div>
<div class="filter-buttons"><button class="btn btn-admin-primary" type="submit"><i class="bi bi-funnel"></i> Filtrar</button><a class="btn btn-filter-clear" href="denuncias.php">Limpiar</a></div>
</form>
<div class="table-responsive"><table class="table admin-table align-middle"><thead><tr><th>Código</th><th>Asunto</th><th>Categoría</th><th>Institución</th><th>Fecha</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
<?php if (!$denuncias): ?><tr><td colspan="7" class="text-center py-5 text-muted">No se encontraron denuncias con esos criterios.</td></tr><?php endif; ?>
<?php foreach ($denuncias as $d): ?><tr>
<td><strong class="case-code"><?= h($d['codigo']) ?></strong></td><td class="case-title"><?= h($d['titulo']) ?></td><td><?= h($d['categoria'] ?? '—') ?></td><td><?= h($d['institucion'] ?? '—') ?></td><td><?= h(date('d/m/Y', strtotime($d['fecha_registro']))) ?></td><td><span class="status-badge <?= h(estadoClaseLista($d['estado'])) ?>"><?= h($d['estado']) ?></span></td>
<td><a class="btn-view" href="detalle.php?id=<?= (int)$d['id'] ?>"><i class="bi bi-eye"></i> Ver</a></td>
</tr><?php endforeach; ?>
</tbody></table></div>
<div class="table-bottom"><small>Mostrando <?= $total ? $offset + 1 : 0 ?> a <?= min($offset + $porPagina, $total) ?> de <?= $total ?> registros</small>
<?php if ($totalPaginas > 1): ?><nav><ul class="pagination pagination-sm mb-0"><?php for ($p = 1; $p <= $totalPaginas; $p++): ?><li class="page-item <?= $p === $pagina ? 'active' : '' ?>"><a class="page-link" href="<?= h(urlPagina($p)) ?>"><?= $p ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
</div>
</section>
<?php require __DIR__ . '/footer.php'; ?>
