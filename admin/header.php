<?php
require_once __DIR__ . '/auth.php';
$admin = adminActual();
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($tituloPagina ?? 'Administración') ?> | DenunciaFácil 360</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="../assets/css/admin.css" rel="stylesheet">
</head><body class="admin-body">
<div class="admin-layout">
<aside class="admin-sidebar" id="sidebar">
<a class="admin-brand" href="dashboard.php"><span class="brand-mark"><i class="bi bi-shield-check"></i></span><span><strong>DenunciaFácil <b>360</b></strong><small>Administración</small></span></a>
<div class="sidebar-label">MENÚ PRINCIPAL</div>
<a class="side-link <?= $paginaActual === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php"><i class="bi bi-grid-1x2"></i> Dashboard</a>
<a class="side-link <?= in_array($paginaActual, ['denuncias.php','detalle.php','actualizar_estado.php'], true) ? 'active' : '' ?>" href="denuncias.php"><i class="bi bi-folder2-open"></i> Denuncias</a>
<div class="sidebar-bottom"><a class="side-link" href="../index.php" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Ver sitio público</a><a class="side-link logout-link" href="logout.php"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</a></div>
</aside>
<div class="admin-main">
<header class="admin-topbar">
<button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menú"><i class="bi bi-list"></i></button>
<div class="topbar-title"><?= h($tituloPagina ?? 'Administración') ?></div>
<div class="admin-user"><span class="admin-avatar"><i class="bi bi-person"></i></span><span><strong><?= h($admin['nombre']) ?></strong><small><?= h($_SESSION['admin_rol'] ?? 'Administrador') ?></small></span></div>
</header>
<main class="admin-content">
