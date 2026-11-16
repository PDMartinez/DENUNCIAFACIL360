-- DenunciaFácil 360 - Base de datos limpia para entrega
-- Conserva únicamente la cuenta administrativa existente (usuario: danilo).
-- Todas las demás tablas se crean vacías.
-- El hash de contraseña se conserva exactamente como en la base exportada.
-- Importar en MariaDB/MySQL desde phpMyAdmin.
--
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-11-2026 a las 19:44:45
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `denunciafacil360`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `usuario` varchar(60) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('superadmin','administrador','analista') NOT NULL DEFAULT 'analista',
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_acceso` datetime DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `administradores`

--

INSERT INTO `administradores` (`id`, `nombre`, `usuario`, `correo`, `password`, `rol`, `estado`, `ultimo_acceso`, `fecha_creacion`) VALUES
(1, 'Danilo', 'danilo', 'danilom012.dms@gmail.com', '$2y$10$AHrFwZiR5L/pnrjnfesNPuFwz8JZ8PtANC01g3mijEv8VF9U77DdC', 'administrador', 1, '2026-11-11 00:06:32', '2026-11-11 01:51:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tabla `categorias` sin registros iniciales.

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `denuncias`
--

CREATE TABLE `denuncias` (
  `id` bigint(20) NOT NULL,
  `codigo` varchar(40) NOT NULL,
  `clave_seguimiento_hash` varchar(255) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_hecho` date NOT NULL,
  `lugar_hecho` varchar(250) NOT NULL,
  `institucion_id` int(11) NOT NULL,
  `dependencia` varchar(200) DEFAULT NULL,
  `municipio` varchar(150) NOT NULL,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL,
  `estado` enum('Recibida','Pendiente de revision','En revision','En analisis','Informacion adicional solicitada','Concluida') NOT NULL DEFAULT 'Recibida',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tabla `denuncias` sin registros iniciales.

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evidencias`
--

CREATE TABLE `evidencias` (
  `id` bigint(20) NOT NULL,
  `denuncia_id` bigint(20) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `tipo_archivo` varchar(100) NOT NULL,
  `tamanio` bigint(20) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tabla `evidencias` sin registros iniciales.

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instituciones`
--

CREATE TABLE `instituciones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tabla `instituciones` sin registros iniciales.

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas_denunciadas`
--

CREATE TABLE `personas_denunciadas` (
  `id` bigint(20) NOT NULL,
  `denuncia_id` bigint(20) NOT NULL,
  `nombre_completo` varchar(200) DEFAULT NULL,
  `cargo` varchar(150) DEFAULT NULL,
  `institucion_laboral` varchar(200) DEFAULT NULL,
  `otros_datos` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tabla `personas_denunciadas` sin registros iniciales.

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento`
--

CREATE TABLE `seguimiento` (
  `id` bigint(20) NOT NULL,
  `denuncia_id` bigint(20) NOT NULL,
  `administrador_id` int(11) DEFAULT NULL,
  `estado_anterior` varchar(60) DEFAULT NULL,
  `estado_nuevo` varchar(60) NOT NULL,
  `mensaje_publico` text DEFAULT NULL,
  `observacion_interna` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tabla `seguimiento` sin registros iniciales.

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `verificaciones_datos_abiertos`
--

CREATE TABLE `verificaciones_datos_abiertos` (
  `id` int(11) NOT NULL,
  `denuncia_id` bigint(20) NOT NULL,
  `administrador_id` int(11) NOT NULL,
  `persona_nombre` varchar(200) NOT NULL,
  `persona_cargo` varchar(150) DEFAULT NULL,
  `persona_institucion` varchar(200) DEFAULT NULL,
  `fuente` varchar(100) NOT NULL DEFAULT 'PDN - S1',
  `estado` enum('Pendiente','Procesando','Completada','Error') NOT NULL DEFAULT 'Pendiente',
  `resultado` longtext DEFAULT NULL,
  `fuentes_consultadas` longtext DEFAULT NULL,
  `version_modelo` varchar(50) DEFAULT NULL,
  `fecha_solicitud` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_finalizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tabla `verificaciones_datos_abiertos` sin registros iniciales.

-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `denuncias`
--
ALTER TABLE `denuncias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `fk_denuncia_institucion` (`institucion_id`),
  ADD KEY `idx_denuncia_estado` (`estado`),
  ADD KEY `idx_denuncia_fecha` (`fecha_registro`),
  ADD KEY `idx_denuncia_categoria` (`categoria_id`),
  ADD KEY `idx_denuncias_geolocalizacion` (`latitud`,`longitud`);

--
-- Indices de la tabla `evidencias`
--
ALTER TABLE `evidencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_evidencia_denuncia` (`denuncia_id`);

--
-- Indices de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `personas_denunciadas`
--
ALTER TABLE `personas_denunciadas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_persona_denuncia` (`denuncia_id`),
  ADD KEY `idx_persona_nombre` (`nombre_completo`);

--
-- Indices de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_seguimiento_administrador` (`administrador_id`),
  ADD KEY `idx_seguimiento_denuncia` (`denuncia_id`),
  ADD KEY `idx_seguimiento_fecha` (`fecha_registro`);

--
-- Indices de la tabla `verificaciones_datos_abiertos`
--
ALTER TABLE `verificaciones_datos_abiertos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_verificacion_admin` (`administrador_id`),
  ADD KEY `idx_verificacion_denuncia` (`denuncia_id`),
  ADD KEY `idx_verificacion_estado` (`estado`),
  ADD KEY `idx_verificacion_fecha` (`fecha_solicitud`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `denuncias`
--
ALTER TABLE `denuncias`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `evidencias`
--
ALTER TABLE `evidencias`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `personas_denunciadas`
--
ALTER TABLE `personas_denunciadas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `verificaciones_datos_abiertos`
--
ALTER TABLE `verificaciones_datos_abiertos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `denuncias`
--
ALTER TABLE `denuncias`
  ADD CONSTRAINT `fk_denuncia_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  ADD CONSTRAINT `fk_denuncia_institucion` FOREIGN KEY (`institucion_id`) REFERENCES `instituciones` (`id`);

--
-- Filtros para la tabla `evidencias`
--
ALTER TABLE `evidencias`
  ADD CONSTRAINT `fk_evidencia_denuncia` FOREIGN KEY (`denuncia_id`) REFERENCES `denuncias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `personas_denunciadas`
--
ALTER TABLE `personas_denunciadas`
  ADD CONSTRAINT `fk_persona_denuncia` FOREIGN KEY (`denuncia_id`) REFERENCES `denuncias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  ADD CONSTRAINT `fk_seguimiento_administrador` FOREIGN KEY (`administrador_id`) REFERENCES `administradores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_seguimiento_denuncia` FOREIGN KEY (`denuncia_id`) REFERENCES `denuncias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `verificaciones_datos_abiertos`
--
ALTER TABLE `verificaciones_datos_abiertos`
  ADD CONSTRAINT `fk_verificacion_admin` FOREIGN KEY (`administrador_id`) REFERENCES `administradores` (`id`),
  ADD CONSTRAINT `fk_verificacion_denuncia` FOREIGN KEY (`denuncia_id`) REFERENCES `denuncias` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
