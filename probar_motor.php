
<?php

$motor = 'C:/xampp/htdocs/motor_analisis_pdn';
$python = $motor . '/.venv/Scripts/python.exe';
$script = $motor . '/src/analisis/ejecutar_analisis.py';

$comando = '"' . $python . '" "' . $script . '" --help';

$descriptores = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w']
];

$proceso = proc_open(
    $comando,
    $descriptores,
    $pipes,
    $motor
);

if (!is_resource($proceso)) {
    die('ERROR: No se pudo iniciar proc_open().');
}

fclose($pipes[0]);

$salida = stream_get_contents($pipes[1]);
$error = stream_get_contents($pipes[2]);

fclose($pipes[1]);
fclose($pipes[2]);

$codigo = proc_close($proceso);

echo '<h2>Prueba del motor PDN</h2>';

echo '<h3>Salida:</h3>';
echo '<pre>' . htmlspecialchars($salida) . '</pre>';

echo '<h3>Errores:</h3>';
echo '<pre>' . htmlspecialchars($error) . '</pre>';

echo '<h3>Código de salida:</h3>';
echo $codigo;
