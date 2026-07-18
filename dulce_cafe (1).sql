-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-07-2026 a las 15:17:26
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
-- Base de datos: `dulce_cafe`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `puntos` int(11) NOT NULL DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `usuario_id`, `nombre`, `email`, `telefono`, `direccion`, `puntos`, `creado_en`) VALUES
(1, 2, 'Carlos Cliente', 'cliente@gmail.com', '955443322', 'Av. Larco 789, Miraflores', 150, '2026-07-08 14:55:00'),
(2, NULL, 'Sofía Ramírez', 'sofia.ramirez@hotmail.com', '944556677', 'Calle Tarapacá 432, Surco', 85, '2026-07-08 14:55:00'),
(3, NULL, 'Jorge Altamirano', 'jorge.alta@gmail.com', '933667788', 'Jr. Junín 567, Cercado de Lima', 40, '2026-07-08 14:55:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedidos`
--

CREATE TABLE `detalle_pedidos` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_pedidos`
--

INSERT INTO `detalle_pedidos` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`) VALUES
(7, 1, 1, 2, 2.50),
(8, 1, 4, 1, 4.50),
(9, 2, 2, 1, 3.80),
(10, 2, 5, 1, 2.20),
(11, 3, 3, 3, 3.50),
(12, 4, 2, 2, 3.80),
(13, 4, 8, 2, 2.90),
(14, 5, 6, 1, 1.80),
(15, 5, 1, 1, 2.50),
(16, 6, 7, 4, 3.20),
(17, 7, 11, 2, 4.20),
(18, 7, 10, 2, 3.90);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','completado','cancelado') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `total`, `fecha`, `estado`) VALUES
(1, 2, 9.50, '2026-07-08 19:55:00', 'completado'),
(2, 2, 6.00, '2026-07-08 19:55:00', 'completado'),
(3, 2, 10.50, '2026-07-08 19:55:00', 'pendiente'),
(4, 2, 13.40, '2026-07-07 19:55:00', 'completado'),
(5, 2, 4.30, '2026-07-05 19:55:00', 'completado'),
(6, 2, 12.80, '2026-06-28 19:55:00', 'completado'),
(7, 2, 16.20, '2026-06-18 19:55:00', 'completado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `imagen`, `categoria`, `stock`, `creado_en`) VALUES
(1, 'Café Espresso Doble', 'Café concentrado de cuerpo robusto, extraído con precisión para resaltar su aroma y crema densa de sabor pronunciado.', 2.50, 'https://a.com.gt/log/imgs/2014/01/espresso-doble-y-leche-condensada.jpg', 'Bebidas', 13, '2026-07-02 04:48:38'),
(2, 'Latte Macchiato Vainilla', 'Delicioso espresso combinado con leche emulsionada y un toque suave de esencia de vainilla natural.', 3.80, 'https://tse4.mm.bing.net/th/id/OIP.ghyzu15MvcdjIqDX_3g_sAHaLG?pid=Api&P=0&h=180', 'Bebidas', 14, '2026-07-02 04:48:38'),
(3, 'Capuccino Canela y Cacao', 'Equilibrio perfecto entre espresso, leche vaporizada y abundante espuma espolvoreada con canela y cacao en polvo.', 3.50, 'https://images.unsplash.com/photo-1534778101976-62847782c213?q=80&w=600', 'Bebidas', 18, '2026-07-02 04:48:38'),
(4, 'Tarta Húmeda de Tres Leches', 'Esponjoso bizcocho tradicional embebido en una mezcla dulce de tres leches, decorado con merengue italiano.', 4.50, 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?q=80&w=400', 'Postres', 10, '2026-07-02 04:48:38'),
(5, 'Muffin Rústico de Arándanos', 'Muffin de vainilla tierno y esponjoso horneado con abundantes arándanos azules frescos enteros y un toque crujiente.', 2.20, 'https://images.unsplash.com/photo-1607958996333-41aef7caefaa?q=80&w=400', 'Postres', 25, '2026-07-02 04:48:38'),
(6, 'Croissant de Mantequilla Francés', 'Clásica pieza de repostería con hojaldrado crujiente y miga suave con aroma a mantequilla pura horneada.', 1.80, 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?q=80&w=400', 'Postres', 30, '2026-07-02 04:48:38'),
(7, 'Té Chai Latte', 'Mezcla de té chai especiado con leche cremosa y un toque de canela para un sabor equilibrado.', 3.20, 'https://www.splenda.com/wp-content/themes/bistrotheme/assets/recipe-images/vanilla-chai-latte.jpg', 'Bebidas', 20, '2026-07-02 04:48:38'),
(8, 'Brownie de Chocolate', 'Brownie húmedo y fundente con trozos de chocolate oscuro y una textura densa y deliciosa.', 2.90, 'https://tse3.mm.bing.net/th/id/OIP.WEix7zunL0mdVl3DIT527QHaEK?rs=1&pid=ImgDetMain&o=7&rm=3', 'Postres', 12, '2026-07-02 04:48:38'),
(9, 'Scone de Vainilla y Almendra', 'Scone artesanal con suaves notas de vainilla y almendras tostadas, perfecto para acompañar tu café.', 2.70, 'https://www.missblasco.com/wp-content/uploads/2020/10/SconesVainilla_portada.jpg', 'Postres', 16, '2026-07-02 04:48:38'),
(10, 'Latte de Miel y Nueces', 'Espresso suave con leche vaporizada, miel natural y un toque crujiente de nueces.', 3.90, 'https://images.unsplash.com/photo-1511920170031-f5291f0e41ef?q=80&w=400', 'Bebidas', 14, '2026-07-02 04:48:38'),
(11, 'Bagel de Salmón Ahumado', 'Bagel recién horneado con salmón ahumado, queso crema y eneldo fresco.', 4.20, 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?q=80&w=400', 'Desayuno', 10, '2026-07-02 04:48:38'),
(12, 'Galleta de Avena y Pasas', 'Galleta casera de avena y pasas con un toque de canela y azúcar moreno.', 1.90, 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?q=80&w=400', 'Postres', 22, '2026-07-02 04:48:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `producto_principal` varchar(100) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `contacto`, `email`, `telefono`, `direccion`, `producto_principal`, `creado_en`) VALUES
(1, 'Distribuidora Café de Altura S.A.', 'Juan Pérez', 'contacto@cafedealtura.com', '987654321', 'Av. Las Palmas 450, Lima', 'Granos de Café Gourmet', '2026-07-08 14:55:00'),
(2, 'Lácteos Cremosos S.A.', 'María Gómez', 'ventas@lacteoscremosos.com', '912345678', 'Jr. Los Claveles 123, Lince', 'Leche y Crema Emulsionada', '2026-07-08 14:55:00'),
(3, 'Empaques Ecológicos del Perú', 'Carlos Torres', 'pedidos@empaqueseco.com', '933445566', 'Calle Metalurgia 890, Ate', 'Vasos y Empaques Biodegradables', '2026-07-08 14:55:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `creado_en`) VALUES
(1, 'admin', 'Administrador general con acceso a todo el sistema.', '2026-07-08 14:55:00'),
(2, 'trabajador', 'Personal operativo con acceso a ventas, pedidos y productos.', '2026-07-08 14:55:00'),
(3, 'cliente', 'Cliente final que realiza compras desde el catálogo.', '2026-07-08 14:55:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajadores`
--

CREATE TABLE `trabajadores` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `cargo` varchar(100) NOT NULL DEFAULT 'Barista',
  `salario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_ingreso` date NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `trabajadores`
--

INSERT INTO `trabajadores` (`id`, `usuario_id`, `nombre`, `email`, `telefono`, `cargo`, `salario`, `fecha_ingreso`, `creado_en`) VALUES
(1, NULL, 'Ana Martínez Barista', 'ana.barista@dulcecafe.com', '988112233', 'Barista Principal', 1500.00, '2025-01-15', '2026-07-08 14:55:00'),
(2, NULL, 'Pedro Sánchez Coche', 'pedro.coche@dulcecafe.com', '977223344', 'Repartidor / Delivery', 1200.00, '2025-03-01', '2026-07-08 14:55:00'),
(3, 5, 'Luis Morales Trabajador', 'trabajador@dulcecafe.com', '966334455', 'Cajero / Atención', 1400.00, '2025-05-10', '2026-07-08 14:55:00'),
(4, NULL, 'Braulio Bellodas', 'braulio@trabajador.com', '922306476', 'Barista', 1200.00, '2026-07-07', '2026-07-11 15:59:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(50) NOT NULL DEFAULT 'cliente',
  `intentos_fallidos` int(11) DEFAULT 0,
  `ultimo_intento` datetime DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `intentos_fallidos`, `ultimo_intento`, `creado_en`) VALUES
(1, 'Administrador Dulce Café', 'admin@dulcecafe.com', '$2y$10$/6K9NhkM2HpEljIHzqgiZufF0uieteKHDnAwFGA7ycxFlvbz.Euva', 'admin', 0, '2026-07-16 05:50:28', '2026-07-02 04:48:38'),
(2, 'Carlos Cliente', 'cliente@gmail.com', '$2y$10$lcZHq6BqH2p3pFTPflaboOwJIPOFl6cbg.Nq6xIL0eGWGXRNvpSja', 'cliente', 0, NULL, '2026-07-02 04:48:38'),
(3, 'Braulio', 'braulio@prueba.com', 'brauliobe10', 'admin', 4, '2026-07-02 06:51:16', '2026-07-02 04:50:22'),
(4, 'Braulio Bellodas', 'braulio10@gmail.com', '$2y$10$Bf5UKCJlwEAntRGXWcXmGey4bdKrKNZ0vjQ1OLRbx463czUPzstyW', 'cliente', 0, NULL, '2026-07-02 04:51:54'),
(5, 'Luis Trabajador', 'trabajador@dulcecafe.com', '$2y$10$IqGEYFOQW8WQaxZB885/AuPUwumRP8QGRMQlkRMppBBZnqK9cN2n2', 'trabajador', 0, NULL, '2026-07-08 14:55:00'),
(6, 'Juan Carlos', 'juan@trabajador.com', '$2y$10$ThbCQ3KG9GHMO75FekcuTOBfTWwAMBdu0PmTDpDLxak0DXoAIhNBm', 'trabajador', 0, NULL, '2026-07-08 15:12:27'),
(7, 'Braulio Bellodas', 'braulio@prueba2.com', '$2y$10$TUlKnkAvCoP8AhF4BJzOCu4veSWhBkh5iClLVgOKVrPZp5liEcL.a', 'cliente', 0, NULL, '2026-07-16 11:37:28');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `trabajadores`
--
ALTER TABLE `trabajadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `trabajadores`
--
ALTER TABLE `trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD CONSTRAINT `detalle_pedidos_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_pedidos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
