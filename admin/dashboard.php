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
<?php require __DIR__ . '/footer.php'; ?>
