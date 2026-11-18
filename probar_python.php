
<?php

$python = 'C:/xampp/htdocs/motor_analisis_pdn/.venv/Scripts/python.exe';

echo "<h2>Prueba de conexión PHP - Python</h2>";

if (!file_exists($python)) {
    die("ERROR: No se encontró Python en la ruta configurada.");
}

$comando = '"' . $python . '" --version 2>&1';

$resultado = [];
$codigo = 0;

exec($comando, $resultado, $codigo);

echo "<p>Resultado:</p>";
echo "<pre>";
print_r($resultado);
echo "</pre>";

echo "<p>Código de salida: " . $codigo . "</p>";

if ($codigo === 0) {
    echo "<strong>PHP puede ejecutar Python correctamente.</strong>";
} else {
    echo "<strong>PHP no pudo ejecutar Python.</strong>";
}

