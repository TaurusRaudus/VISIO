-- Generar Admins aqui

-- Insertar un administrador inicial con la estructura correcta
INSERT INTO Administrador (nickname, correo_electronico, contraseña)
VALUES ('Manu', 'jacintopichimawido27@gmail.com', '$2y$10$pylu/oqabkBGLSYk8B2sVeJ3rH1XpQqaANx24p9xpXO8s1B2HjjjO');

INSERT INTO Administrador (nickname, correo_electronico, contraseña)
VALUES ('Rodri', 'rodrigogomez@gmail.com', '$2y$10$pylu/oqabkBGLSYk8B2sVeJ3rH1XpQqaANx24p9xpXO8s1B2HjjjO');

INSERT INTO Administrador (nickname, correo_electronico, contraseña)
VALUES ('Franco', 'francozegarra@gmail.com', '$2y$10$pylu/oqabkBGLSYk8B2sVeJ3rH1XpQqaANx24p9xpXO8s1B2HjjjO');

-- Las claves de los 3 son Idk123...Kappa

-- Si quieren agregas mas admins, tiene que ejecutar este comando en php
--<?php
--echo password_hash("Idk123...Kappa", PASSWORD_DEFAULT);
--?>
-- La clave cambia pero son equivalentes, por eso es un cifrado 
-- Y colocarlo arriba
-- Esto lo pueden colocar en https://www.programiz.com/php/online-compiler/

-- Generar Mime Types Aqui:

-- Seed para la tabla TipoArchivo

INSERT INTO TipoArchivo (nombre_del_tipo, mimetype) VALUES ('JPEG', 'image/jpeg');
INSERT INTO TipoArchivo (nombre_del_tipo, mimetype) VALUES ('PNG', 'image/png');
INSERT INTO TipoArchivo (nombre_del_tipo, mimetype) VALUES ('QuickTime', 'video/quicktime');
INSERT INTO TipoArchivo (nombre_del_tipo, mimetype) VALUES ('MP4', 'video/mp4');
INSERT INTO TipoArchivo (nombre_del_tipo, mimetype) VALUES ('MPEG', 'audio/mpeg');
INSERT INTO TipoArchivo (nombre_del_tipo, mimetype) VALUES ('M4V', 'video/x-m4v');