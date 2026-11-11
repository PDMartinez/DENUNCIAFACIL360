<?php
require_once __DIR__ . '/../config/conexion.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $clave = $_POST['clave'] ?? '';

    if ($usuario === '' || $clave === '') {
        $error = 'Ingresá tu usuario y contraseña.';
    } else {
        try {
            $stmt = $conexion->prepare(
                "SELECT id, nombre, usuario, password, rol, estado
                 FROM administradores
                 WHERE usuario = :usuario
                 LIMIT 1"
            );
            $stmt->execute([':usuario' => $usuario]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && (int)$admin['estado'] === 1 && password_verify($clave, $admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int)$admin['id'];
                $_SESSION['admin_nombre'] = $admin['nombre'];
                $_SESSION['admin_usuario'] = $admin['usuario'];
                $_SESSION['admin_rol'] = $admin['rol'];
                $_SESSION['csrf_admin'] = bin2hex(random_bytes(32));

                $update = $conexion->prepare("UPDATE administradores SET ultimo_acceso = NOW() WHERE id = ?");
                $update->execute([(int)$admin['id']]);

                header('Location: dashboard.php');
                exit;
            }
            $error = 'Usuario o contraseña incorrectos, o cuenta deshabilitada.';
        } catch (PDOException $ex) {
            error_log('Error de inicio de sesión administrativo: ' . $ex->getMessage());
            $error = 'No se pudo iniciar sesión. Verificá la conexión con la base de datos.';
        }
    }
}
?>
<!doctype html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Acceso administrativo | DenunciaFácil 360</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="../assets/css/estilos.css" rel="stylesheet"><link href="../assets/css/admin.css" rel="stylesheet">
</head><body class="login-body">
<div class="login-wrap">
<a class="navbar-brand brand justify-content-center mb-4" href="../index.php">
<span class="brand-mark"><i class="bi bi-shield-check"></i></span>
<span><strong>DenunciaFácil <b>360</b></strong><small>Panel de administración</small></span>
</a>
<div class="login-card">
<div class="login-icon"><i class="bi bi-person-lock"></i></div>
<h1>Bienvenido</h1><p>Ingresá tus credenciales para administrar la plataforma.</p>
<?php if ($error !== ''): ?><div class="alert alert-danger py-2 small"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="POST" action="login.php">
<div class="mb-3"><label class="form-label" for="usuario">Usuario</label><input class="form-control" id="usuario" name="usuario" required autocomplete="username" autofocus value="<?= htmlspecialchars($_POST['usuario'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
<div class="mb-4"><label class="form-label" for="clave">Contraseña</label><input class="form-control" type="password" id="clave" name="clave" required autocomplete="current-password"></div>
<button class="btn btn-primary-custom w-100" type="submit"><i class="bi bi-box-arrow-in-right"></i> Iniciar sesión</button>
</form>
</div>
<a class="login-back" href="../index.php"><i class="bi bi-arrow-left"></i> Volver a la página principal</a>
</div>
</body></html>
