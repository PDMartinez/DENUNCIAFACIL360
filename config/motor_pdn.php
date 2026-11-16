<?php
/**
 * Configuración de integración con el motor PDN.
 * Mantener las rutas fuera de htdocs y ajustar según la instalación local.
 */
return [
    // Carpeta donde se extrajo motor_analisis_pdn_produccion.
    'motor_root' => 'C:/xampp/htdocs/motor_analisis_pdn',
    // Python del entorno virtual creado con instalar_entorno_windows.bat.
    'python_exe' => 'C:/xampp/htdocs/motor_analisis_pdn/.venv/Scripts/python.exe',
];
