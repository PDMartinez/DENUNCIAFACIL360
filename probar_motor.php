
<?php
$config = require __DIR__ . '/config/motor_pdn.php';

echo "<h2>Verificación del motor PDN</h2>";

echo "Motor: ";
echo is_dir($config['motor_root']) ? "ENCONTRADO" : "NO ENCONTRADO";

echo "<br>Python: ";
echo is_file($config['python_exe']) ? "ENCONTRADO" : "NO ENCONTRADO";
?>
