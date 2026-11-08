-- Versión PostgreSQL de empleados.sql (para Render)
-- La app la ejecuta sola la primera vez si las tablas no existen.

CREATE TABLE IF NOT EXISTS empleados (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    departamento VARCHAR(100) NOT NULL,
    puesto VARCHAR(100) NOT NULL,
    salario NUMERIC(10, 2) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO empleados (nombre, departamento, puesto, salario) VALUES
    ('Joel Mejia', 'Calidad', 'Ingeniero', 25000),
    ('Edgardo García', 'Produccion', 'Supervisor', 22000),
    ('Miguel Navarro', 'Ingenieria', 'Tecnico', 18000),
    ('Maria Gonzalez', 'Recursos Humanos', 'Coordinador', 24000),
    ('Pedro Sanchez', 'Calidad', 'Inspector', 16000),
    ('Laura Hernandez', 'Ingenieria', 'Ingeniero', 28000),
    ('Jorge Torres', 'Produccion', 'Operador', 14000),
    ('Sofia Morales', 'Calidad', 'Auditor', 26000),
    ('Daniel Flores', 'Logistica', 'Analista', 21000),
    ('Elena Castro', 'Finanzas', 'Contador', 27000),
    ('Ricardo Vega', 'Produccion', 'Supervisor', 23000),
    ('Fernanda Ruiz', 'Calidad', 'Ingeniero', 29000);

CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    token VARCHAR(255) DEFAULT NULL,
    token_expira TIMESTAMP DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Usuario de prueba: admin / admin123
INSERT INTO usuarios (usuario, password_hash) VALUES
    ('admin', '$2y$10$kH15xk4YPklV7iXjfhrcROLU1XyUCuqCsRl2ZuSV/XTDdFRdnrrJa');
