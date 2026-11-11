<?php
require_once __DIR__ . '/../config/conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_denuncia'])) {
    $_SESSION['csrf_denuncia'] = bin2hex(random_bytes(32));
}

$categorias = $conexion->query("SELECT id, nombre FROM categorias WHERE estado = 1 ORDER BY nombre")->fetchAll();
$instituciones = $conexion->query("SELECT id, nombre FROM instituciones WHERE estado = 1 ORDER BY nombre")->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Realizar una denuncia | DenunciaFácil 360</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="../assets/css/estilos.css" rel="stylesheet">
<link href="../assets/css/formulario-denuncia.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<link href="../assets/css/geolocalizacion.css" rel="stylesheet">
</head>
<body>
<header class="site-header">
<nav class="navbar navbar-expand-lg"><div class="container">
<a class="navbar-brand brand" href="../index.php">
<span class="brand-mark"><i class="bi bi-shield-check"></i></span>
<span><strong>DenunciaFácil <b>360</b></strong><small>Plataforma ciudadana de denuncias</small></span>
</a>
<a class="btn btn-admin ms-auto" href="../index.php"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
</div></nav></header>

<main class="form-page">
<div class="container py-5">
<div class="form-intro text-center">
<span class="kicker">FORMULARIO CIUDADANO</span>
<h1>Realizar una denuncia</h1>
<p>Completá los datos sobre los hechos que deseás comunicar. No necesitás crear una cuenta ni proporcionar tus datos personales.</p>
<div class="privacy-note"><i class="bi bi-shield-lock-fill"></i><span><strong>Denuncia anónima:</strong> no solicitamos tu nombre, documento, correo ni teléfono. Al finalizar recibirás un código y una clave privada para consultar el seguimiento.</span></div>
</div>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger mt-4" role="alert">
No se pudo registrar la denuncia. Verificá los datos e intentá nuevamente. Si el problema continúa, contactá al administrador de la plataforma.
</div>
<?php endif; ?>

<form id="formDenuncia" class="form-panel" action="guardar.php" method="POST" enctype="multipart/form-data" novalidate>
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_denuncia'], ENT_QUOTES, 'UTF-8') ?>">

<div class="form-progress">
<div class="progress-step active" data-step-indicator="0"><span>1</span><small>Hechos</small></div>
<div class="progress-line"></div>
<div class="progress-step" data-step-indicator="1"><span>2</span><small>Persona denunciada</small></div>
<div class="progress-line"></div>
<div class="progress-step" data-step-indicator="2"><span>3</span><small>Evidencias</small></div>
<div class="progress-line"></div>
<div class="progress-step" data-step-indicator="3"><span>4</span><small>Confirmación</small></div>
</div>

<section class="form-step active" data-step="0">
<div class="step-heading"><span class="step-icon"><i class="bi bi-file-earmark-text"></i></span><div><h2>Información de los hechos</h2><p>Contanos qué ocurrió y dónde sucedió.</p></div></div>
<div class="row g-3">
<div class="col-12">
<label class="form-label" for="titulo">Título o asunto de la denuncia <span>*</span></label>
<input class="form-control" type="text" id="titulo" name="titulo" maxlength="200" required placeholder="Ej.: Posible irregularidad en un proceso de contratación">
<div class="invalid-feedback">Ingresá un título para la denuncia.</div>
</div>
<div class="col-md-6">
<label class="form-label" for="categoria_id">Categoría <span>*</span></label>
<select class="form-select" id="categoria_id" name="categoria_id" required>
<option value="">Seleccioná una categoría</option>
<?php foreach ($categorias as $categoria): ?>
<option value="<?= (int)$categoria['id'] ?>"><?= htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
</select><div class="invalid-feedback">Seleccioná una categoría.</div>
</div>
<div class="col-md-6">
<label class="form-label" for="fecha_hecho">Fecha aproximada de los hechos <span>*</span></label>
<input class="form-control" type="date" id="fecha_hecho" name="fecha_hecho" max="<?= date('Y-m-d') ?>" required>
<div class="invalid-feedback">Indicá una fecha válida que no sea futura.</div>
</div>
<div class="col-12">
<label class="form-label" for="descripcion">Descripción detallada <span>*</span></label>
<textarea class="form-control" id="descripcion" name="descripcion" rows="6" required minlength="30" placeholder="Describí qué ocurrió, cómo sucedió y cualquier otro dato que consideres importante. Evitá incluir información personal tuya."></textarea>
<div class="form-hint">Incluí hechos concretos, fechas, lugares y circunstancias. Mínimo 30 caracteres.</div>
<div class="invalid-feedback">La descripción debe contener al menos 30 caracteres.</div>
</div>
<div class="col-md-6">
<label class="form-label" for="institucion_id">Institución involucrada <span>*</span></label>
<select class="form-select" id="institucion_id" name="institucion_id" required>
<option value="">Seleccioná una institución</option>
<?php foreach ($instituciones as $institucion): ?>
<option value="<?= (int)$institucion['id'] ?>"><?= htmlspecialchars($institucion['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
</select><div class="invalid-feedback">Seleccioná una institución.</div>
</div>
<div class="col-md-6">
<label class="form-label" for="dependencia">Dependencia o área (opcional)</label>
<input class="form-control" type="text" id="dependencia" name="dependencia" maxlength="200" placeholder="Ej.: Dirección de compras">
</div>
<div class="col-md-6">
<label class="form-label" for="municipio">Municipio o localidad <span>*</span></label>
<input class="form-control" type="text" id="municipio" name="municipio" maxlength="150" required placeholder="Ingresá el municipio o localidad">
<div class="invalid-feedback">Indicá el municipio o localidad.</div>
</div>
<div class="col-md-6">
<label class="form-label" for="lugar_hecho">Lugar específico de los hechos <span>*</span></label>
<input class="form-control" type="text" id="lugar_hecho" name="lugar_hecho" maxlength="250" required placeholder="Dirección, oficina, establecimiento u otro lugar">
<div class="invalid-feedback">Indicá dónde ocurrieron los hechos.</div>
</div>

<div class="col-12">
<div class="geo-card">
  <div class="geo-heading">
    <span class="geo-icon"><i class="bi bi-geo-alt"></i></span>
    <div><h3>Geolocalización del lugar</h3><p>Marcá en el mapa dónde ocurrieron los hechos. Esta opción es voluntaria.</p></div>
  </div>
  <div class="geo-notice"><i class="bi bi-info-circle"></i> La ubicación corresponde al lugar denunciado, no necesariamente a tu ubicación actual. Podés marcar cualquier punto del mapa.</div>
  <div class="geo-actions">
    <button type="button" class="btn btn-geo" id="btnMiUbicacion"><i class="bi bi-crosshair"></i> Usar mi ubicación actual</button>
    <button type="button" class="btn btn-geo-light" id="btnLimpiarUbicacion"><i class="bi bi-eraser"></i> Quitar ubicación</button>
  </div>
  <div id="mapaDenuncia" class="geo-map" role="application" aria-label="Mapa para seleccionar el lugar de los hechos"></div>
  <div class="geo-help">También podés hacer clic en el mapa para colocar el marcador o arrastrarlo hasta el punto correcto.</div>
  <div id="geoEstado" class="geo-status" aria-live="polite">No seleccionaste una ubicación. Podés continuar sin el mapa.</div>
  <input type="hidden" name="latitud" id="latitud" value="">
  <input type="hidden" name="longitud" id="longitud" value="">
</div>
</div>
</div>
</section>

<section class="form-step" data-step="1">
<div class="step-heading"><span class="step-icon"><i class="bi bi-person-vcard"></i></span><div><h2>Persona denunciada</h2><p>Esta información es opcional. Completá solamente los datos que conozcas.</p></div></div>
<div class="optional-banner"><i class="bi bi-info-circle"></i> Si no conocés la identidad de la persona, podés continuar sin completar esta sección.</div>
<div class="row g-3 mt-1">
<div class="col-12"><label class="form-label" for="persona_nombre">Nombre completo</label><input class="form-control" id="persona_nombre" name="persona_nombre" maxlength="200" placeholder="Nombre y apellido de la persona"></div>
<div class="col-md-6"><label class="form-label" for="persona_cargo">Cargo o función</label><input class="form-control" id="persona_cargo" name="persona_cargo" maxlength="150" placeholder="Cargo que ocupa u ocupaba"></div>
<div class="col-md-6"><label class="form-label" for="persona_institucion">Institución donde trabaja</label><input class="form-control" id="persona_institucion" name="persona_institucion" maxlength="200" placeholder="Institución o entidad"></div>
<div class="col-12"><label class="form-label" for="persona_otros">Otros datos que permitan identificarla</label><textarea class="form-control" id="persona_otros" name="persona_otros" rows="4" placeholder="Por ejemplo: área, función, período en el cargo u otros datos relevantes."></textarea></div>
</div>
</section>

<section class="form-step" data-step="2">
<div class="step-heading"><span class="step-icon"><i class="bi bi-paperclip"></i></span><div><h2>Adjuntar evidencias</h2><p>Podés agregar documentos o imágenes que ayuden a comprender los hechos.</p></div></div>
<div class="upload-area">
<i class="bi bi-cloud-arrow-up"></i>
<h3>Seleccioná los archivos que querés adjuntar</h3>
<p>Formatos permitidos: PDF, JPG, JPEG y PNG. Máximo 5 MB por archivo y hasta 5 archivos.</p>
<input class="form-control" type="file" id="evidencias" name="evidencias[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
<div id="listaArchivos" class="file-list"></div>
</div>
<div class="optional-banner mt-3"><i class="bi bi-lightbulb"></i> Adjuntar evidencias es opcional. No subas archivos que contengan datos personales tuyos si deseás mantener el anonimato.</div>
</section>

<section class="form-step" data-step="3">
<div class="step-heading"><span class="step-icon"><i class="bi bi-clipboard-check"></i></span><div><h2>Revisar y enviar</h2><p>Antes de enviar, verificá la información ingresada.</p></div></div>
<div class="review-box">
<h3><i class="bi bi-check-circle"></i> Declaración de responsabilidad</h3>
<p>Confirmo que la información proporcionada corresponde a hechos que deseo poner en conocimiento de la plataforma y que, según mi leal saber y entender, es correcta.</p>
<div class="form-check mt-3">
<input class="form-check-input" type="checkbox" id="acepto" name="acepto" value="1" required>
<label class="form-check-label" for="acepto">He revisado la información y deseo enviar esta denuncia. <span>*</span></label>
<div class="invalid-feedback">Debés confirmar antes de enviar.</div>
</div>
</div>
<div class="credential-warning"><i class="bi bi-key-fill"></i><div><strong>¡Importante!</strong><p>Al enviar, el sistema generará un código de denuncia y una clave privada. Guardalos en un lugar seguro. La plataforma no podrá recuperar la clave si la perdés.</p></div></div>
<div id="resumenFormulario" class="review-summary"></div>
</section>

<div class="form-actions">
<button type="button" class="btn btn-back" id="btnAnterior" hidden><i class="bi bi-arrow-left"></i> Anterior</button>
<span class="flex-spacer"></span>
<button type="button" class="btn btn-next" id="btnSiguiente">Continuar <i class="bi bi-arrow-right"></i></button>
<button type="submit" class="btn btn-next" id="btnEnviar" hidden><i class="bi bi-send"></i> Enviar denuncia</button>
</div>
</form>
<p class="form-footer-note"><i class="bi bi-lock"></i> No ingreses tu nombre, documento, correo electrónico ni teléfono en los campos de descripción.</p>
</div>
</main>

<footer class="footer"><div class="container"><div class="footer-bottom"><span>© <?= date('Y') ?> DenunciaFácil 360</span><a href="../index.php">Volver a la página principal</a></div></div></footer>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="../assets/js/geolocalizacion.js"></script>
<script src="../assets/js/formulario-denuncia.js"></script>
</body></html>
