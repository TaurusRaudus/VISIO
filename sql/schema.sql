-- ------------------------------
-- 1. Tabla Usuario
-- ------------------------------
CREATE TABLE Usuario (
  id                   UUID PRIMARY KEY, -- CU-001
  nickname             VARCHAR(50) NOT NULL UNIQUE,
  contraseña           TEXT NOT NULL,
  correo_electronico   VARCHAR(100) NOT NULL UNIQUE,
  foto                 VARCHAR(255) DEFAULT 'default.jpg',  -- Nuevo campo para la foto de perfil
  fecha_de_registro    TIMESTAMP NOT NULL
  -- saldo está derivado, se implementa en una vista
);
-- ------------------------------
-- 2. Tabla Administrador
-- ------------------------------
CREATE TABLE Administrador (
  id                  SERIAL PRIMARY KEY,
  nickname            VARCHAR(50) NOT NULL UNIQUE,
  contraseña          TEXT NOT NULL,
  correo_electronico  VARCHAR(100) NOT NULL UNIQUE
);

-- ------------------------------
-- 3. Tabla TipoArchivo
-- ------------------------------
CREATE TABLE TipoArchivo (
  id                SERIAL PRIMARY KEY,
  nombre_del_tipo   VARCHAR(10) NOT NULL UNIQUE,
  mimetype          VARCHAR(50) NOT NULL
);

-- ------------------------------
-- 4. Tabla Categoria
-- ------------------------------
CREATE TABLE IF NOT EXISTS Categoria (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  descripcion TEXT,
  padre_id INTEGER REFERENCES Categoria(id),
  estado VARCHAR(20) NOT NULL DEFAULT 'activa' CHECK (estado IN ('activa', 'inactiva'))
);

-- ------------------------------
-- 5. Tabla Promocion
-- ------------------------------
CREATE TABLE Promocion (
  id                     SERIAL PRIMARY KEY,
  porcentaje_de_descuento NUMERIC(5,2) NOT NULL,
  fecha_inicio           DATE NOT NULL,
  fecha_fin              DATE NOT NULL
  -- estado derivado (activo/inactivo) va en vista
);

-- ------------------------------
-- 6. Tabla Contenido
-- ------------------------------
CREATE TABLE Contenido (
  id                    SERIAL PRIMARY KEY,
  titulo                VARCHAR(100) NOT NULL,
  autor                 VARCHAR(100) NOT NULL,
  descripcion           TEXT,
  precio_original       NUMERIC(10,2) NOT NULL,
  estado                VARCHAR(20) NOT NULL DEFAULT 'disponible' CHECK (estado IN ('disponible', 'no disponible')), -- Nuevo campo para el estado
  -- precio_actual derivado en vista
  tamaño_MB             NUMERIC(8,2) NOT NULL,
  fecha_de_subida       TIMESTAMP NOT NULL,
  numero_de_descargas   INTEGER,  -- derivado en vista
  promedio_de_calificacion NUMERIC, -- derivado en vista
  tipo_archivo_id       INTEGER REFERENCES TipoArchivo(id),
  categoria_id          INTEGER REFERENCES Categoria(id),
  promocion_id          INTEGER REFERENCES Promocion(id),
  archivo VARCHAR(255)  -- Columna nueva
);

-- ------------------------------
-- 7. Tabla Ranking
-- ------------------------------
CREATE TABLE Ranking (
  id                   SERIAL PRIMARY KEY,
  fecha_inicio         DATE NOT NULL,
  numero_de_descargas  INTEGER NOT NULL
);

-- ------------------------------
-- 8. Relación Ranking–Contenido (M:N)
-- ------------------------------
CREATE TABLE Ranking_Contenido (
  ranking_id   INTEGER REFERENCES Ranking(id),
  contenido_id INTEGER REFERENCES Contenido(id),
  PRIMARY KEY (ranking_id, contenido_id)
);

-- ------------------------------
-- 9. Tabla Descarga (1:N Usuario, 1:N Contenido)
-- ------------------------------
CREATE TABLE Descarga (
  id                 SERIAL PRIMARY KEY,
  fecha_de_compra    TIMESTAMP NOT NULL,
  precio_pagado      NUMERIC(10,2) NOT NULL,
  aplica_descuento   BOOLEAN NOT NULL,
  es_regalo          BOOLEAN NOT NULL,
  usuario_id         UUID REFERENCES Usuario(id),  
  contenido_id       INTEGER REFERENCES Contenido(id)
);

-- ------------------------------
-- 10. Tabla Calificacion (1:1 Usuario, 1:1 Contenido)
-- ------------------------------
CREATE TABLE Calificacion (
  id                      SERIAL PRIMARY KEY,
  fecha_de_calificacion   TIMESTAMP NOT NULL,
  nota                    SMALLINT NOT NULL CHECK (nota BETWEEN 1 AND 10),
  mensaje                 TEXT,
  usuario_id              UUID REFERENCES Usuario(id),
  contenido_id            INTEGER REFERENCES Contenido(id),
  UNIQUE (usuario_id, contenido_id)
);

-- ------------------------------
-- 11. Tabla Recarga (1:N Usuario, 1:N Administrador)
-- ------------------------------
CREATE TABLE Recarga (
  id                  SERIAL PRIMARY KEY,
  monto               NUMERIC(10,2) NOT NULL,
  fecha_de_recarga    TIMESTAMP NOT NULL,
  usuario_id          UUID REFERENCES Usuario(id),
  administrador_id    INTEGER REFERENCES Administrador(id)
);

-- Esto aun no se usa
-- ------------------------------
-- 12. Relación Admin–Contenido (M:N)
-- ------------------------------
CREATE TABLE Admin_Contenido (
  administrador_id INTEGER REFERENCES Administrador(id),
  contenido_id     INTEGER REFERENCES Contenido(id),
  PRIMARY KEY (administrador_id, contenido_id)
);

-- ------------------------------
-- 13. Relación Admin–Categoria (M:N)
-- ------------------------------
CREATE TABLE Admin_Categoria (
  administrador_id INTEGER REFERENCES Administrador(id),
  categoria_id     INTEGER REFERENCES Categoria(id),
  PRIMARY KEY (administrador_id, categoria_id)
);

-- ------------------------------
-- 14. Autorrelación Regala (1:1)
-- ------------------------------
---- Reparacion de la tabla Regala
DROP TABLE IF EXISTS Regala;

CREATE TABLE Regala (
  id             SERIAL PRIMARY KEY,
  donante_id     UUID      NOT NULL REFERENCES Usuario(id),
  receptor_id    UUID      NOT NULL REFERENCES Usuario(id),
  contenido_id   INTEGER   NOT NULL REFERENCES Contenido(id),
  fecha_regalo   TIMESTAMP NOT NULL DEFAULT NOW()
);

-- ------------------------------
-- 15. Vista del Saldo del Usuario
-- ------------------------------
CREATE OR REPLACE VIEW VistaSaldo AS
SELECT
    u.id AS usuario_id,
    COALESCE(
        (SELECT SUM(r.monto) FROM Recarga r WHERE r.usuario_id = u.id), 0
    ) -
    COALESCE(
        (SELECT SUM(d.precio_pagado) FROM Descarga d WHERE d.usuario_id = u.id AND (d.es_regalo = FALSE OR d.contenido_id IS NOT NULL)), 0
    ) -
    COALESCE(
        (SELECT SUM(d2.precio_pagado)
         FROM Descarga d2
         WHERE d2.usuario_id = u.id AND d2.es_regalo = TRUE AND d2.contenido_id IS NULL
        ), 0
    ) AS saldo
FROM Usuario u;
--- La anteorior estaba bug, ahora es correcta