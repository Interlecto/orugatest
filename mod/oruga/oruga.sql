-- Catalog config
INSERT INTO `cat_macro` (`id`, `description`) VALUES
(1, 'maquinaria'),
(2, 'repuesto')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `cat_action` (`id`, `verb`, `description`, `macro`) VALUES
(1, 'venta', 'venta de maquinaria', 1),
(2, 'repuestos', 'venta de repuestos', 2),
(3, 'alquiler', 'alquiler de maquinaria', 1)
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `cat_area` (`id`, `description`, `avatar`, `banner`) VALUES
('agro', 'Sector agrícola', NULL, NULL),
('amarilla', 'Maquinaria amarilla', NULL, NULL),
('construccion', 'Construcción vertical', NULL, NULL),
('industria', 'Sector industrial', NULL, NULL),
('vias', 'Construcción de vías', NULL, NULL)
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `res_section` (`id`, `case`, `priority`, `description`, `engine`) VALUES
('venta', '(venta|alquiler)/(.*)', 2, 'maquinaria', 'catalog'),
('venta', '(repuestos)/(.*)', 2, 'repuestos', 'catalog')
ON DUPLICATE KEY UPDATE `id`=`id`;
