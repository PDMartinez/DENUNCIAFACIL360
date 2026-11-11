<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

function adminActual(): array {
    return [
        'id' => (int)($_SESSION['admin_id'] ?? 0),
        'nombre' => $_SESSION['admin_nombre'] ?? 'Administrador',
        'usuario' => $_SESSION['admin_usuario'] ?? ''
    ];
}

function protegerAccion(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método no permitido.');
    }
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_admin']) || !hash_equals($_SESSION['csrf_admin'], $token)) {
        http_response_code(403);
        exit('La solicitud no pudo validarse. Actualizá la página e intentá nuevamente.');
    }
}

function h($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
