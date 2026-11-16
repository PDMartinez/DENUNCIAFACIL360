# DENUNCIAFACIL360
SISTEMA PARA REALIZAR DENUNCIAS DE FUNCIONARIOS PÚBLICOS SOBRE HECHOS DE CORRUPCIÓN CON ANÁLISIS DE DATOS HISTÓRICOS

## Verificación con datos abiertos

Para habilitar esta sección, ejecutá el archivo `database/migracion_verificacion_datos_abiertos.sql` desde phpMyAdmin, seleccionando la base `denunciafacil360`. La sección aparecerá en el detalle administrativo cuando exista una persona denunciada y permitirá registrar solicitudes e historial.

**Alcance de esta versión:** se registra la solicitud con estado `Pendiente`; todavía no se consulta la API de la Plataforma Digital Nacional ni se ejecuta un modelo de IA. Esa integración se implementará en una etapa posterior. La migración utiliza `BIGINT` para `denuncia_id` y `INT` para `administrador_id`, de acuerdo con la estructura de las tablas compartidas.
