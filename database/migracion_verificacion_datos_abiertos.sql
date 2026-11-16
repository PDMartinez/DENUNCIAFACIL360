-- DenunciaFácil 360: registro de solicitudes de verificación con datos abiertos.
-- Ejecutar en la base de datos denunciafacil360.

CREATE TABLE IF NOT EXISTS verificaciones_datos_abiertos (
    id INT NOT NULL AUTO_INCREMENT,
    denuncia_id BIGINT NOT NULL,
    administrador_id INT NOT NULL,
    persona_nombre VARCHAR(200) NOT NULL,
    persona_cargo VARCHAR(150) NULL,
    persona_institucion VARCHAR(200) NULL,
    fuente VARCHAR(100) NOT NULL DEFAULT 'PDN - S1',
    estado ENUM('Pendiente', 'Procesando', 'Completada', 'Error') NOT NULL DEFAULT 'Pendiente',
    resultado LONGTEXT NULL,
    fuentes_consultadas LONGTEXT NULL,
    version_modelo VARCHAR(50) NULL,
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_finalizacion DATETIME NULL,
    PRIMARY KEY (id),
    INDEX idx_verificacion_denuncia (denuncia_id),
    INDEX idx_verificacion_estado (estado),
    INDEX idx_verificacion_fecha (fecha_solicitud),
    CONSTRAINT fk_verificacion_denuncia FOREIGN KEY (denuncia_id)
        REFERENCES denuncias(id) ON DELETE CASCADE,
    CONSTRAINT fk_verificacion_admin FOREIGN KEY (administrador_id)
        REFERENCES administradores(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
