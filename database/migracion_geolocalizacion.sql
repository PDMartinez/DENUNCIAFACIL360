-- DenunciaFácil 360: agregar geolocalización opcional a las denuncias
-- Ejecutar una sola vez sobre la base de datos existente.
ALTER TABLE denuncias
    ADD COLUMN latitud DECIMAL(10,7) NULL AFTER municipio,
    ADD COLUMN longitud DECIMAL(10,7) NULL AFTER latitud;

-- Las denuncias anteriores conservan NULL: no se les asigna una ubicación ficticia.
