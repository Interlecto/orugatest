--
-- Dumping data for table `gen_country`
--

INSERT INTO `gen_country` (`code`, `name`) VALUES
('col', 'Colombia')
ON DUPLICATE KEY UPDATE `code`=`code`;

--
-- Dumping data for table `gen_location`
--

INSERT INTO `gen_location` (`idx`, `name`, `in`, `locstatus`, `country`) VALUES
(1, 'Bogotá', NULL, 'dc', 'col'),
(2, 'Amazonas', NULL, 'dep', 'col'),
(3, 'Antioquia', NULL, 'dep', 'col'),
(4, 'Arauca', NULL, 'dep', 'col'),
(5, 'Atlántico', NULL, 'dep', 'col'),
(6, 'Bolívar', NULL, 'dep', 'col'),
(7, 'Boyacá', NULL, 'dep', 'col'),
(8, 'Caldas', NULL, 'dep', 'col'),
(9, 'Caquetá', NULL, 'dep', 'col'),
(10, 'Casanare', NULL, 'dep', 'col'),
(11, 'Cauca', NULL, 'dep', 'col'),
(12, 'Cesar', NULL, 'dep', 'col'),
(13, 'Chocó', NULL, 'dep', 'col'),
(14, 'Córdoba', NULL, 'dep', 'col'),
(15, 'Cundinamarca', NULL, 'dep', 'col'),
(16, 'Guainía', NULL, 'dep', 'col'),
(17, 'Guaviare', NULL, 'dep', 'col'),
(18, 'Huila', NULL, 'dep', 'col'),
(19, 'La Guajira', NULL, 'dep', 'col'),
(20, 'Magdalena', NULL, 'dep', 'col'),
(21, 'Meta', NULL, 'dep', 'col'),
(22, 'Nariño', NULL, 'dep', 'col'),
(23, 'Norte de Santander', NULL, 'dep', 'col'),
(24, 'Putumayo', NULL, 'dep', 'col'),
(25, 'Quindío', NULL, 'dep', 'col'),
(26, 'Risararalda', NULL, 'dep', 'col'),
(27, 'San Andrés Islas', NULL, 'dep', 'col'),
(28, 'Santander', NULL, 'dep', 'col'),
(29, 'Sucre', NULL, 'dep', 'col'),
(30, 'Tolima', NULL, 'dep', 'col'),
(31, 'Valle del Cauca', NULL, 'dep', 'col'),
(32, 'Vaupez', NULL, 'dep', 'col'),
(33, 'Vichada', NULL, 'dep', 'col'),
(34, 'Leticia', 2, 'mun', 'col'),
(35, 'Medellín', 3, 'mun', 'col'),
(36, 'Arauca (Capital)', 4, 'mun', 'col'),
(37, 'Barranquilla', 5, 'dis', 'col'),
(38, 'Cartagena', 6, 'dis', 'col'),
(39, 'Tunja', 7, 'mun', 'col'),
(40, 'Manizales', 8, 'mun', 'col'),
(41, 'Florencia', 9, 'mun', 'col'),
(42, 'Yopal', 10, 'mun', 'col'),
(43, 'Popayán', 11, 'mun', 'col'),
(44, 'Valledupar', 12, 'mun', 'col'),
(45, 'Quibdó', 13, 'mun', 'col'),
(46, 'Montería', 14, 'mun', 'col'),
(47, 'Mitú', 16, 'mun', 'col'),
(48, 'San José del Guaviare', 17, 'mun', 'col'),
(49, 'Neiva', 18, 'mun', 'col'),
(40, 'Riohacha', 19, 'mun', 'col'),
(51, 'Santa Marta', 20, 'dis', 'col'),
(52, 'Villavicencio', 21, 'mun', 'col'),
(53, 'Pasto', 22, 'mun', 'col'),
(54, 'Cúcuta', 23, 'mun', 'col'),
(55, 'Mocoa', 24, 'mun', 'col'),
(56, 'Armenia', 25, 'mun', 'col'),
(57, 'Pereira', 26, 'mun', 'col'),
(58, 'San Andrés', 27, NULL, 'col'),
(59, 'Bucaramanga', 28, 'mun', 'col'),
(60, 'Sincelejo', 29, 'mun', 'col'),
(61, 'Ibagué', 30, 'mun', 'col'),
(62, 'Cali', 31, 'mun', 'col'),
(63, 'Inírida', 32, 'mun', 'col'),
(64, 'Puerto Carreño', 33, 'mun', 'col')
ON DUPLICATE KEY UPDATE `idx`=`idx`;

--
-- Dumping data for table `gen_locstatus`
--

INSERT INTO `gen_locstatus` (`id`, `description`) VALUES
('dc', 'Distrito Capital'),
('dep', 'Departamento'),
('dis', 'Distrito'),
('mun', 'Municipio')
ON DUPLICATE KEY UPDATE `id`=`id`;

--
-- Dumping data for table `language`
--

INSERT INTO `language` (`id`, `code`, `local`, `english`) VALUES
('es', 'es-CO', 'español', 'Spanish')
ON DUPLICATE KEY UPDATE `id`=`id`;
