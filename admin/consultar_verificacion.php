<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) { http_response_code(400); echo json_encode(['error'=>'Identificador inválido']); exit; }
$stmt = $conexion->prepare("SELECT id, denuncia_id, estado, clave_ejecucion, resultado, mensaje_error, fecha_finalizacion FROM verificaciones_datos_abiertos WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$v = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$v) { http_response_code(404); echo json_encode(['error'=>'Verificación no encontrada']); exit; }

if (in_array($v['estado'], ['Procesando','Pendiente'], true) && !empty($v['clave_ejecucion'])) {
    $cfg = require __DIR__ . '/../config/motor_pdn.php';
    $root = realpath((string)$cfg['motor_root']);
    if ($root) {
        $manifest = $root . DIRECTORY_SEPARATOR . 'resultados' . DIRECTORY_SEPARATOR . 'denuncias' . DIRECTORY_SEPARATOR . $v['clave_ejecucion'] . DIRECTORY_SEPARATOR . 'estado_ejecucion.json';
        if (is_file($manifest)) {
            $data = json_decode((string)file_get_contents($manifest), true);
            if (is_array($data)) {
                $estado = (string)($data['estado'] ?? 'EN_PROCESO');
                $map = ['FINALIZADO'=>'Completada', 'SIN_COINCIDENCIAS'=>'Sin coincidencias', 'ERROR'=>'Error'];
                if (isset($map[$estado])) {
                    $resultadoJson = null;
                    if ($estado === 'FINALIZADO') {
                        $resultPath = dirname($manifest) . DIRECTORY_SEPARATOR . 'interpretacion_patrimonial.json';
                        if (is_file($resultPath)) $resultadoJson = file_get_contents($resultPath);
                    }
                    $fuentes = json_encode([
                        'motor'=>'PDN - S1', 'estado_motor'=>$estado,
                        'coincidencias_encontradas'=>$data['coincidencias_encontradas'] ?? 0,
                        'declaraciones_analizadas'=>$data['declaraciones_analizadas'] ?? 0,
                        'comparaciones'=>$data['comparaciones'] ?? 0,
                        'resultados_modelo'=>$data['resultados_modelo'] ?? new stdClass(),
                        'archivos'=>$data['archivos'] ?? new stdClass(),
                    ], JSON_UNESCAPED_UNICODE);
                    $up = $conexion->prepare("UPDATE verificaciones_datos_abiertos SET estado=?, resultado=?, fuentes_consultadas=?, mensaje_error=?, fecha_finalizacion=NOW() WHERE id=?");
                    $up->execute([$map[$estado], $resultadoJson, $fuentes, $estado === 'ERROR' ? ($data['mensaje'] ?? 'Error durante el análisis') : null, $id]);
                    $v['estado'] = $map[$estado];
                    $v['resultado'] = $resultadoJson;
                    $v['mensaje_error'] = $estado === 'ERROR' ? ($data['mensaje'] ?? 'Error durante el análisis') : null;
                    $v['fecha_finalizacion'] = date('Y-m-d H:i:s');
                }
            }
        }
    }
}
$resultado = !empty($v['resultado']) ? json_decode($v['resultado'], true) : null;
echo json_encode([
    'id'=>(int)$v['id'], 'denuncia_id'=>(int)$v['denuncia_id'], 'estado'=>$v['estado'],
    'mensaje_error'=>$v['mensaje_error'], 'fecha_finalizacion'=>$v['fecha_finalizacion'],
    'resumen'=>$resultado['resumen'] ?? null,
    'declaraciones'=>isset($resultado['declaraciones']) ? count($resultado['declaraciones']) : 0,
    'comparaciones'=>isset($resultado['comparaciones']) ? count($resultado['comparaciones']) : 0,
], JSON_UNESCAPED_UNICODE);
