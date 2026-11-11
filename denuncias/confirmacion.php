<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$datos = $_SESSION['denuncia_confirmacion'] ?? null;
if (!$datos) {
    header('Location: nueva.php');
    exit;
}
unset($_SESSION['denuncia_confirmacion']);
?>
<!doctype html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Denuncia recibida | DenunciaFácil 360</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="../assets/css/estilos.css" rel="stylesheet"><link href="../assets/css/formulario-denuncia.css" rel="stylesheet">
</head><body>
<header class="site-header"><nav class="navbar"><div class="container"><a class="navbar-brand brand" href="../index.php"><span class="brand-mark"><i class="bi bi-shield-check"></i></span><span><strong>DenunciaFácil <b>360</b></strong><small>Plataforma ciudadana de denuncias</small></span></a></div></nav></header>
<main class="form-page"><div class="container py-5"><div class="success-panel text-center">
<div class="success-icon"><i class="bi bi-check-lg"></i></div><span class="kicker">REGISTRO COMPLETADO</span>
<h1>Tu denuncia fue recibida</h1><p>La información se registró correctamente. Guardá las siguientes credenciales para consultar el seguimiento.</p>
<div class="credential-card text-start">
<div class="credential-row"><div><small>Código de denuncia</small><strong id="codigo"><?= htmlspecialchars($datos['codigo'], ENT_QUOTES, 'UTF-8') ?></strong></div><button class="copy-btn" type="button" data-copy="codigo"><i class="bi bi-copy"></i></button></div>
<div class="credential-row"><div><small>Clave privada de seguimiento</small><strong id="clave"><?= htmlspecialchars($datos['clave'], ENT_QUOTES, 'UTF-8') ?></strong></div><button class="copy-btn" type="button" data-copy="clave"><i class="bi bi-copy"></i></button></div>
</div>
<div class="credential-warning text-start"><i class="bi bi-exclamation-triangle-fill"></i><div><strong>Guardá estas credenciales ahora</strong><p>La clave se muestra una sola vez. Si la perdés, no podremos recuperarla porque la denuncia es anónima.</p></div></div>
<div class="d-flex flex-wrap justify-content-center gap-2 mt-4"><button class="btn btn-primary-custom" id="imprimir"><i class="bi bi-printer"></i> Imprimir comprobante</button><a href="../seguimiento/consultar.php" class="btn btn-outline-custom">Consultar seguimiento <i class="bi bi-arrow-right"></i></a></div>
</div></div></main>
<footer class="footer"><div class="container"><div class="footer-bottom"><span>© <?= date('Y') ?> DenunciaFácil 360</span><a href="../index.php">Volver al inicio</a></div></div></footer>
<script>
document.querySelectorAll('[data-copy]').forEach(btn => btn.addEventListener('click', async () => {
  const value = document.getElementById(btn.dataset.copy).textContent;
  try { await navigator.clipboard.writeText(value); btn.innerHTML = '<i class="bi bi-check2"></i>'; }
  catch(e) { alert('No se pudo copiar automáticamente. Seleccioná el texto y copialo.'); }
}));
document.getElementById('imprimir').addEventListener('click', () => window.print());
</script>
</body></html>
