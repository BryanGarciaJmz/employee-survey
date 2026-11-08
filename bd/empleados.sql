-- Base de datos: empleados_db / Generado a partir de empleados.xml /Incluye tabla de empleados y tabla de usuarios (para login/token)
CREATE DATABASE IF NOT EXISTS empleados_db CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_spanish_ci;

USE empleados_db;

-- Tabla: empleados
DROP TABLE IF EXISTS empleados;

CREATE TABLE
    empleados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        departamento VARCHAR(100) NOT NULL,
        puesto VARCHAR(100) NOT NULL,
        salario DECIMAL(10, 2) NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Datos extraídos de empleados.xml
INSERT INTO
    empleados (id, nombre, departamento, puesto, salario)
VALUES
    (1, 'Joel Mejia', 'Calidad', 'Ingeniero', 25000),
    (
        2,
        'Edgardo García',
        'Produccion',
        'Supervisor',
        22000
    ),
    (
        3,
        'Miguel Navarro',
        'Ingenieria',
        'Tecnico',
        18000
    ),
    (
        4,
        'Maria Gonzalez',
        'Recursos Humanos',
        'Coordinador',
        24000
    ),
    (5, 'Pedro Sanchez', 'Calidad', 'Inspector', 16000),
    (
        6,
        'Laura Hernandez',
        'Ingenieria',
        'Ingeniero',
        28000
    ),
    (
        7,
        'Jorge Torres',
        'Produccion',
        'Operador',
        14000
    ),
    (8, 'Sofia Morales', 'Calidad', 'Auditor', 26000),
    (
        9,
        'Daniel Flores',
        'Logistica',
        'Analista',
        21000
    ),
    (10, 'Elena Castro', 'Finanzas', 'Contador', 27000),
    (
        11,
        'Ricardo Vega',
        'Produccion',
        'Supervisor',
        23000
    ),
    (
        12,
        'Fernanda Ruiz',
        'Calidad',
        'Ingeniero',
        29000
    );

-- Tabla: usuarios / Para autenticación y generación de token de sesión
DROP TABLE IF EXISTS usuarios;

CREATE TABLE
    usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        token VARCHAR(255) DEFAULT NULL,
        token_expira DATETIME DEFAULT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Usuario de prueba: usuario = admin / password = admin123 / (El hash se generó con password_hash('admin123', PASSWORD_BCRYPT) en PHP)
INSERT INTO
    usuarios (usuario, password_hash)
VALUES
    (
        'admin',
        '$2y$10$kH15xk4YPklV7iXjfhrcROLU1XyUCuqCsRl2ZuSV/XTDdFRdnrrJa'
    );