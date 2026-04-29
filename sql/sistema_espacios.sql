CREATE DATABASE IF NOT EXISTS sistema_espacios;
USE sistema_espacios;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_roles
        FOREIGN KEY (id_rol) REFERENCES roles(id)
);

CREATE TABLE espacios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    ubicacion VARCHAR(100) NOT NULL,
    capacidad INT NOT NULL,
    equipamiento VARCHAR(255) NOT NULL,
    estado ENUM('disponible', 'ocupado', 'mantenimiento') NOT NULL DEFAULT 'disponible'
);

CREATE TABLE horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_espacio INT NOT NULL,
    grupo_carrera VARCHAR(100) NOT NULL,
    materia_actividad VARCHAR(100) NOT NULL,
    docente VARCHAR(100) NOT NULL,
    dia_semana ENUM('Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    id_usuario_registro INT NOT NULL,
    estado ENUM('activo', 'cancelado') NOT NULL DEFAULT 'activo',
    CONSTRAINT fk_horarios_espacios
        FOREIGN KEY (id_espacio) REFERENCES espacios(id),
    CONSTRAINT fk_horarios_usuarios
        FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id),
    CONSTRAINT chk_hora_valida
        CHECK (hora_inicio < hora_fin)
);

CREATE TABLE incidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_espacio INT NOT NULL,
    tipo ENUM('ausencia_docente', 'falla_espacio', 'otro') NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_incidencia DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_usuario_registro INT NOT NULL,
    estado ENUM('reportada', 'atendida') NOT NULL DEFAULT 'reportada',
    CONSTRAINT fk_incidencias_espacios
        FOREIGN KEY (id_espacio) REFERENCES espacios(id),
    CONSTRAINT fk_incidencias_usuarios
        FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id)
);

CREATE TABLE mantenimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_espacio INT NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_reporte DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'en_proceso', 'resuelto') NOT NULL DEFAULT 'pendiente',
    observaciones TEXT NULL,
    id_usuario_registro INT NOT NULL,
    CONSTRAINT fk_mantenimientos_espacios
        FOREIGN KEY (id_espacio) REFERENCES espacios(id),
    CONSTRAINT fk_mantenimientos_usuarios
        FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id)
);

INSERT INTO roles (nombre) VALUES
('administrador'),
('area_academica'),
('prefecto');

INSERT INTO usuarios (nombre_completo, usuario, password, id_rol, estado) VALUES
('Administrador General', 'admin', '$2y$10$KBmbrtOjcTpdoupiuRr.sOx8yhZx5dxqOQFLfiEvRHx8uW7i3HAbu', 1, 'activo'),
('Area Academica', 'academica', '$2y$10$KvIj2a8A4wYXfMDc9F2eseKb1Y17GzcSgtRT4wxehA1XYf5SwSHj.', 2, 'activo'),
('Prefecto General', 'prefecto', '$2y$10$0MLRU.IoIs9oTapVz8qzH.NU5QJzC30J96ggq9SU9.VAkybYDPC8G', 3, 'activo');

INSERT INTO espacios (nombre, tipo, ubicacion, capacidad, equipamiento, estado) VALUES
('Sala A1', 'Salon', 'Edificio A', 40, 'Proyector, pintarron', 'disponible'),
('Laboratorio L1', 'Laboratorio', 'Edificio B', 30, 'Computadoras, proyector', 'disponible'),
('Centro de Computo 1', 'Centro de computo', 'Edificio C', 35, 'PC, aire acondicionado', 'mantenimiento');
