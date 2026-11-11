<?php
/*
 * UTILIZAR UNA SOLA VEZ EN DESARROLLO LOCAL.
 * Luego de crear el primer administrador, ELIMINAR este archivo.
 */
require_once __DIR__ . '/../config/conexion.php';

$esLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
if (!$esLocal) {
    http_response_code(403);
    exit('Este asistente solo puede ejecutarse desde el equipo local.');
}

$mensaje = '';
$error = '';
$cantidad = (int)$conexion->query("SELECT COUNT(*) FROM administradores")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cantidad === 0) {
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $clave = $_POST['clave'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($nombre === '' || $usuario === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($clave) < 10 || $clave !== $confirmar) {
        $error = 'Completá los campos correctamente. La contraseña debe tener al menos 10 caracteres y coincidir con su confirmación.';
    } else {
        try {
            $hash = password_hash($clave, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare("INSERT INTO administradores (nombre, usuario, correo, password, rol, estado) VALUES (?, ?, ?, ?, 'Administrador', 1)");
            $stmt->execute([$nombre, $usuario, $correo, $hash]);
            $mensaje = 'Administrador creado correctamente. Eliminá ahora el archivo crear_admin_inicial.php y accedé desde login.php.';
            $cantidad = 1;
        } catch (PDOException $ex) {
            error_log('Creación de administrador inicial: ' . $ex->getMessage());
            $error = 'No se pudo crear el administrador. Verificá que el usuario y correo no estén duplicados.';
        }
    }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Crear administrador inicial</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../assets/css/admin.css" rel="stylesheet"></head>
<body class="login-body"><div class="login-wrap"><div class="login-card"><h1>Administrador inicial</h1>
<?php if ($mensaje): ?><div class="alert alert-success"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><a href="login.php" class="btn btn-primary-custom w-100">Ir al inicio de sesión</a>
<?php elseif ($cantidad > 0): ?><div class="alert alert-warning">Ya existe al menos un administrador. Por seguridad, este asistente quedó deshabilitado. Eliminá este archivo del servidor.</div>
<?php else: ?>
<p>Creá el primer usuario administrador de la plataforma.</p>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="POST"><div class="mb-3"><label class="form-label">Nombre completo</label><input class="form-control" name="nombre" required></div><div class="mb-3"><label class="form-label">Usuario</label><input class="form-control" name="usuario" required></div><div class="mb-3"><label class="form-label">Correo administrativo</label><input class="form-control" type="email" name="correo" required></div><div class="mb-3"><label class="form-label">Contraseña (mínimo 10 caracteres)</label><input class="form-control" type="password" name="clave" minlength="10" required></div><div class="mb-3"><label class="form-label">Confirmar contraseña</label><input class="form-control" type="password" name="confirmar" minlength="10" required></div><button class="btn btn-primary-custom w-100">Crear administrador</button></form>
<?php endif; ?></div></div></body></html>
