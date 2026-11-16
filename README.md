# DenunciaFácil 360
## Sistema Web de Gestión y Seguimiento de Denuncias

### 1. Descripción del proyecto

DenunciaFácil 360 es un sistema web desarrollado para facilitar el registro, la gestión y el seguimiento de denuncias. Permite a los ciudadanos registrar denuncias, adjuntar evidencias y consultar su estado mediante un código de seguimiento.

El sistema incorpora un panel administrativo para gestionar las denuncias recibidas y un módulo de verificación que permite consultar información de datos abiertos de la Plataforma Digital Nacional (PDN), mediante la integración con un motor de análisis desarrollado en Python.

### 2. Tecnologías utilizadas

- **Backend:** PHP.
- **Frontend:** HTML, CSS, JavaScript y Bootstrap.
- **Base de datos:** MySQL / MariaDB.
- **Servidor local:** Apache mediante XAMPP.
- **Mapas y geolocalización:** Leaflet y OpenStreetMap.
- **Análisis de datos:** Python y bibliotecas de análisis de datos y aprendizaje automático.
- **Integración:** Comunicación entre PHP y el motor de análisis Python.

### 3. Requisitos del sistema

Para ejecutar el sistema se requiere:

- Windows 10 o superior.
- XAMPP instalado.
- Apache y MySQL habilitados.
- Navegador web actualizado.
- Python instalado y configurado para la integración con el motor PDN.

### 4. Instalación y configuración

#### 4.1. Copiar el proyecto

Copiar la carpeta del sistema dentro del directorio `htdocs` de XAMPP.

La ruta recomendada es:

```text
C:\xampp\htdocs\DENUNCIAFACIL360
```

#### 4.2. Iniciar los servicios

Abrir el panel de control de XAMPP e iniciar los siguientes servicios:

- Apache.
- MySQL.

#### 4.3. Crear la base de datos

1. Ingresar a phpMyAdmin desde el navegador:

   http://localhost/phpmyadmin

2. Crear una nueva base de datos con el siguiente nombre:

```text
denunciafacil360
```

3. Seleccionar la base de datos recién creada.
4. Ingresar a la pestaña **Importar**.
5. Seleccionar el archivo:

```text
database/denunciafacil360_backup.sql
```

6. Ejecutar la importación.

**Importante:** El archivo `denunciafacil360_backup.sql` contiene la copia de la base de datos utilizada para la instalación. Para una instalación nueva, se debe importar únicamente este archivo.

No es necesario ejecutar por separado los siguientes scripts, siempre que sus modificaciones ya estén incluidas en el backup:

- `migracion_geolocalizacion.sql`
- `migracion_verificacion_datos_abiertos.sql`

Estos archivos se conservan como referencia y respaldo de las migraciones realizadas durante el desarrollo.

No se debe importar el backup sobre una base de datos que ya contiene las tablas del sistema. Para una instalación desde cero, utilizar una base de datos nueva o realizar previamente una copia de seguridad y reemplazar la anterior.

#### 4.4. Configurar la conexión a la base de datos

Verificar el archivo de configuración de conexión a la base de datos y comprobar que los parámetros coincidan con la instalación local.

Configurar:

- Servidor: `localhost`
- Base de datos: `denunciafacil360`
- Usuario: `root` (configuración habitual de XAMPP).
- Contraseña: dejar vacía si la instalación local de MySQL no tiene contraseña configurada.

Si se utiliza otro usuario o contraseña de MySQL, actualizar los datos de conexión correspondientes.

#### 4.5. Acceder al sistema

Una vez iniciados Apache y MySQL, ingresar desde el navegador:

http://localhost/DENUNCIAFACIL360/

### 5. Acceso al panel administrativo

El sistema incluye una cuenta de administrador predeterminada para acceder al panel de gestión.

**Credenciales de acceso inicial:**

|   Campo    |       Valor     |
|------------|-----------------|
| Usuario    | `danilo`        |
| Contraseña | `Aratic.net123` |
| Rol        | Administrador   |

Ingresar al sistema mediante la opción de acceso administrativo.

Se recomienda cambiar la contraseña después del primer ingreso y no mantener las credenciales predeterminadas en una instalación de producción.

### 6. Funcionalidades principales

#### 6.1. Registro de denuncias

Permite registrar denuncias mediante un formulario que contempla:

- Datos de los hechos denunciados.
- Información de la persona denunciada.
- Categoría de la denuncia.
- Institución relacionada, cuando corresponda.
- Ubicación geográfica de los hechos.
- Adjuntar evidencias documentales y fotográficas.

#### 6.2. Consulta y seguimiento

Permite consultar el estado de una denuncia utilizando el código de seguimiento correspondiente.

#### 6.3. Panel administrativo

Permite a los administradores:

- Visualizar las denuncias recibidas.
- Consultar los detalles de cada denuncia.
- Revisar las evidencias adjuntas.
- Consultar la ubicación geográfica.
- Actualizar el estado de las denuncias.
- Registrar observaciones internas.
- Enviar mensajes de seguimiento al denunciante.
- Acceder al módulo de verificación de datos abiertos.

#### 6.4. Verificación de datos abiertos

El sistema incorpora un módulo que permite ejecutar el motor de análisis patrimonial desarrollado en Python, utilizando información disponible en la Plataforma Digital Nacional (PDN).

Los resultados se presentan dentro del panel administrativo para facilitar su consulta y revisión.

### 7. Integración con el motor de análisis PDN

El sistema web se comunica con un proyecto independiente desarrollado en Python.

La configuración de esta integración se encuentra en:

```text
config/motor_pdn.php
```

En este archivo se deben verificar las rutas correspondientes al directorio del motor y al ejecutable de Python.

Ejemplo de configuración local:

```php
<?php
return [
    'motor_root' => 'C:/xampp/htdocs/motor_analisis_pdn',
    'python_exe' => 'C:/xampp/htdocs/motor_analisis_pdn/.venv/Scripts/python.exe',
];
```

Es necesario que el motor Python esté instalado y configurado correctamente para que la funcionalidad de análisis pueda ejecutarse desde el sistema web.

Para conocer los pasos de instalación, dependencias, estructura de carpetas, ejecución y generación de resultados, consultar el README específico del proyecto del motor PDN.

### 8. Consideraciones importantes

- Mantener Apache y MySQL activos mientras se utiliza el sistema localmente.
- Verificar que la conexión con la base de datos esté correctamente configurada.
- Comprobar que las rutas del motor Python coincidan con la ubicación real de su instalación.
- Para utilizar la geolocalización desde otros dispositivos, tener en cuenta que los navegadores generalmente requieren HTTPS o un contexto seguro.
- Los archivos de evidencias deben mantenerse protegidos y accesibles únicamente mediante los permisos correspondientes.
- La información del motor PDN debe estar disponible en la estructura de directorios configurada para su ejecución.
- No publicar archivos de configuración, contraseñas, modelos ni datos originales de la PDN en repositorios públicos.

### 9. Consideraciones sobre los resultados del análisis

Los resultados generados por el motor PDN constituyen información de apoyo para la revisión administrativa.

Se deben tener en cuenta las siguientes advertencias:

- Una coincidencia nominal no confirma la identidad de una persona.
- Una clasificación NORMAL no demuestra la ausencia de irregularidades.
- Una clasificación ATÍPICO no demuestra la existencia de un delito o conducta ilícita.
- Los resultados requieren revisión humana y corroboración independiente.

El sistema no reemplaza los procedimientos de investigación ni constituye por sí mismo una determinación de responsabilidad.

### 10. Estructura general del proyecto

```text
DENUNCIAFACIL360/
│
├── config/
│   └── motor_pdn.php
│
├── database/
│   ├── denunciafacil360_backup.sql
│   ├── migracion_geolocalizacion.sql
│   └── migracion_verificacion_datos_abiertos.sql
│
├── denuncias/
├── evidencias/
├── admin/
├── assets/
├── includes/
├── index.php
└── README.md
```

*Nota: La estructura es referencial. Pueden existir otros directorios y archivos correspondientes a los módulos del sistema.*

### 11. Autoría

**Proyecto:** DenunciaFácil 360  
**Año:** 2026  
**Tecnologías principales:** PHP, MySQL, JavaScript y Python.

Sistema desarrollado como propuesta tecnológica para la recepción, gestión y seguimiento de denuncias, con integración de herramientas de análisis de datos abiertos.