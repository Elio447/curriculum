-- ######################################################################
-- # PROYECTO: ANÁLISIS DE VENTAS PARA UNA TIENENDA EN LÍNEA          #
-- # Este script crea un esquema de base de datos para un proyecto    #
-- # de análisis de ventas. Incluye la creación de tablas, la         #
-- # inserción de datos de ejemplo y varias consultas de análisis.    #
-- ######################################################################


-- ======================================================================
-- 1. CREACIÓN DE LAS TABLAS
-- ======================================================================

-- Tabla 'clientes':
-- Almacena la información de los clientes.
-- Se usa 'cliente_id' como clave primaria.
CREATE TABLE clientes (
    cliente_id INT PRIMARY KEY,
    nombre VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    ciudad VARCHAR(50),
    pais VARCHAR(50)
);

-- Tabla 'productos':
-- Almacena la información de los productos.
-- Se usa 'producto_id' como clave primaria.
CREATE TABLE productos (
    producto_id INT PRIMARY KEY,
    nombre_producto VARCHAR(100),
    categoria VARCHAR(50),
    precio DECIMAL(10, 2)
);

-- Tabla 'ventas':
-- Almacena los registros de cada venta.
-- 'venta_id' es la clave primaria.
-- 'cliente_id' y 'producto_id' son claves foráneas para establecer
-- las relaciones con las tablas 'clientes' y 'productos'.
CREATE TABLE ventas (
    venta_id INT PRIMARY KEY,
    fecha DATE,
    cliente_id INT,
    producto_id INT,
    cantidad INT,
    precio_total DECIMAL(10, 2),
    -- Definición de las claves foráneas
    FOREIGN KEY (cliente_id) REFERENCES clientes(cliente_id),
    FOREIGN KEY (producto_id) REFERENCES productos(producto_id)
);


-- ======================================================================
-- 2. INSERCIÓN DE DATOS DE EJEMPLO
-- ======================================================================

-- Datos para la tabla 'clientes'
INSERT INTO clientes (cliente_id, nombre, email, ciudad, pais) VALUES
(1, 'Ana Pérez', 'ana.perez@email.com', 'Madrid', 'España'),
(2, 'Juan Gómez', 'juan.gomez@email.com', 'Barcelona', 'España'),
(3, 'María Lopez', 'maria.lopez@email.com', 'Valencia', 'España'),
(4, 'Pedro Garcia', 'pedro.garcia@email.com', 'Sevilla', 'España'),
(5, 'Isabel Martín', 'isabel.martin@email.com', 'Madrid', 'España');

-- Datos para la tabla 'productos'
INSERT INTO productos (producto_id, nombre_producto, categoria, precio) VALUES
(101, 'Smartphone X', 'Electrónica', 800.00),
(102, 'Laptop Pro', 'Electrónica', 1200.00),
(103, 'Silla Ergonómica', 'Muebles', 250.00),
(104, 'Mesa de Oficina', 'Muebles', 350.00),
(105, 'Auriculares', 'Accesorios', 80.00),
(106, 'Teclado Mecánico', 'Accesorios', 120.00);

-- Datos para la tabla 'ventas'
INSERT INTO ventas (venta_id, fecha, cliente_id, producto_id, cantidad, precio_total) VALUES
(1, '2024-01-15', 1, 101, 1, 800.00),
(2, '2024-01-18', 2, 103, 2, 500.00),
(3, '2024-02-20', 3, 102, 1, 1200.00),
(4, '2024-02-25', 1, 105, 3, 240.00),
(5, '2024-03-01', 4, 104, 1, 350.00),
(6, '2024-03-05', 2, 106, 1, 120.00),
(7, '2024-03-10', 5, 101, 2, 1600.00),
(8, '2024-04-02', 3, 105, 1, 80.00),
(9, '2024-04-05', 4, 103, 1, 250.00),
(10, '2024-04-10', 5, 102, 1, 1200.00);


-- ======================================================================
-- 3. CONSULTAS DE ANÁLISIS
-- ======================================================================

-- 3.1. Ventas totales por mes y año
-- Demuestra el uso de GROUP BY y funciones de fecha.
-- Se calcula la suma de 'precio_total' por cada mes y año de la venta.
SELECT
    strftime('%Y-%m', fecha) AS 'mes_de_venta',
    SUM(precio_total) AS 'ventas_totales'
FROM
    ventas
GROUP BY
    'mes_de_venta'
ORDER BY
    'mes_de_venta';

-- 3.2. Productos más vendidos
-- Utiliza JOIN para combinar 'ventas' y 'productos'.
-- Calcula la cantidad total vendida de cada producto y los ordena de mayor a menor.
SELECT
    p.nombre_producto,
    p.categoria,
    SUM(v.cantidad) AS 'cantidad_total_vendida'
FROM
    ventas AS v
JOIN
    productos AS p ON v.producto_id = p.producto_id
GROUP BY
    p.nombre_producto,
    p.categoria
ORDER BY
    'cantidad_total_vendida' DESC;

-- 3.3. Clientes con mayor gasto
-- Utiliza JOIN para vincular 'ventas' y 'clientes'.
-- Suma el gasto total de cada cliente y los clasifica.
SELECT
    c.nombre,
    SUM(v.precio_total) AS 'gasto_total'
FROM
    ventas AS v
JOIN
    clientes AS c ON v.cliente_id = c.cliente_id
GROUP BY
    c.nombre
ORDER BY
    'gasto_total' DESC;

-- 3.4. Análisis de categorías populares y rentables
-- Combina 'ventas' y 'productos' para analizar las ventas por categoría.
SELECT
    p.categoria,
    COUNT(v.venta_id) AS 'numero_de_ventas',
    SUM(v.precio_total) AS 'ingresos_totales_categoria'
FROM
    ventas AS v
JOIN
    productos AS p ON v.producto_id = p.producto_id
GROUP BY
    p.categoria
ORDER BY
    'ingresos_totales_categoria' DESC;

-- 3.5. Ingresos por ciudad del cliente
-- Utiliza JOIN para combinar 'ventas' y 'clientes'.
-- Agrupa las ventas por ciudad para ver de dónde provienen los ingresos.
SELECT
    c.ciudad,
    SUM(v.precio_total) AS 'ingresos_ciudad'
FROM
    ventas AS v
JOIN
    clientes AS c ON v.cliente_id = c.cliente_id
GROUP BY
    c.ciudad
ORDER BY
    'ingresos_ciudad' DESC;

-- 3.6. Consulta avanzada: Ranking de ventas por cliente
-- Utiliza una FUNCIÓN DE VENTANA (`RANK()`) para clasificar
-- a los clientes según su gasto total.
-- Esto es útil para mostrar habilidades avanzadas en SQL.
SELECT
    nombre,
    gasto_total,
    RANK() OVER (ORDER BY gasto_total DESC) AS 'ranking_cliente'
FROM (
    SELECT
        c.nombre,
        SUM(v.precio_total) AS 'gasto_total'
    FROM
        ventas AS v
    JOIN
        clientes AS c ON v.cliente_id = c.cliente_id
    GROUP BY
        c.nombre
) AS 'gasto_por_cliente'
ORDER BY
    'ranking_cliente';