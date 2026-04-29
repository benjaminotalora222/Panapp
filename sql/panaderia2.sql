CREATE TABLE `usuarios` (
  `id_usuario` bigint PRIMARY KEY AUTO_INCREMENT,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(150) UNIQUE NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` varchar(20) NOT NULL COMMENT 'ADMIN|CAJERO',
  `activo` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `admin` (
  `id_admin` bigint PRIMARY KEY AUTO_INCREMENT,
  `id_usuario` bigint UNIQUE,
  `permisos` text
);

CREATE TABLE `cajero` (
  `id_cajero` bigint PRIMARY KEY AUTO_INCREMENT,
  `id_usuario` bigint UNIQUE,
  `turno` varchar(50)
);

CREATE TABLE `proveedores` (
  `id_proveedor` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20),
  `correo` varchar(100),
  `direccion` varchar(150),
  `estado` varchar(20),
  `fecha_registro` datetime DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `metodos_pago` (
  `id_metodo_pago` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL
);

CREATE TABLE `insumos` (
  `id_insumo` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(150),
  `unidad_medida` varchar(50),
  `id_proveedor` int
);

CREATE TABLE `inventario_insumos` (
  `id_inventario` int PRIMARY KEY AUTO_INCREMENT,
  `id_insumo` int NOT NULL,
  `cantidad_actual` int DEFAULT 0,
  `fecha_actualizacion` datetime DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `movimientos_inventario` (
  `id_movimiento` int PRIMARY KEY AUTO_INCREMENT,
  `id_insumo` int NOT NULL,
  `tipo` varchar(20) COMMENT 'entrada | salida',
  `cantidad` int,
  `motivo` varchar(50),
  `fecha` datetime DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `productos` (
  `id_producto` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(150),
  `categoria` varchar(50),
  `precio` decimal(10,2) NOT NULL,
  `unidad_medida` varchar(50)
);

CREATE TABLE `producto_insumo` (
  `id_producto` int,
  `id_insumo` int,
  `cantidad_usada` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_producto`, `id_insumo`)
);

CREATE TABLE `ventas` (
  `id_venta` int PRIMARY KEY AUTO_INCREMENT,
  `fecha` datetime DEFAULT (CURRENT_TIMESTAMP),
  `total` decimal(10,2),
  `id_usuario` bigint,
  `id_metodo_pago` int,
  `estado` varchar(20)
);

CREATE TABLE `detalle_venta` (
  `id_detalle` int PRIMARY KEY AUTO_INCREMENT,
  `id_venta` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2),
  `subtotal` decimal(10,2)
);

ALTER TABLE `admin` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `cajero` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `insumos` ADD FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`);

ALTER TABLE `inventario_insumos` ADD FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id_insumo`);

ALTER TABLE `movimientos_inventario` ADD FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id_insumo`);

ALTER TABLE `producto_insumo` ADD FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

ALTER TABLE `producto_insumo` ADD FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id_insumo`);

ALTER TABLE `ventas` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `ventas` ADD FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodos_pago` (`id_metodo_pago`);

ALTER TABLE `detalle_venta` ADD FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`);

ALTER TABLE `detalle_venta` ADD FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);
