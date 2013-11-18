-- --------------------------------------------------------

--
-- Table structure for table `avatar`
--

CREATE TABLE IF NOT EXISTS `avatar` (
  `idx` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `basedir` char(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `tiny` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  `small` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  `big` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  `full` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`idx`),
  UNIQUE KEY `idx` (`idx`)
);

-- --------------------------------------------------------

--
-- Table structure for table `banner`
--

CREATE TABLE IF NOT EXISTS `banner` (
  `idx` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `basedir` char(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `small` char(24) COLLATE utf8_spanish_ci DEFAULT NULL,
  `big` char(24) COLLATE utf8_spanish_ci DEFAULT NULL,
  `full` char(24) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`idx`),
  UNIQUE KEY `idx` (`idx`)
);

-- --------------------------------------------------------

--
-- Table structure for table `group`
--

CREATE TABLE IF NOT EXISTS `group` (
  `id` char(24) CHARACTER SET ascii NOT NULL,
  `name` char(56) COLLATE utf8_spanish_ci DEFAULT NULL,
  `avatar` int(20) unsigned DEFAULT NULL,
  `banner` int(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `group`(`id`,`name`,`avatar`,`banner`) VALUES
('this_site','This Site',NULL,NULL)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` char(24) CHARACTER SET ascii NOT NULL,
  `name` char(56) COLLATE utf8_spanish_ci DEFAULT NULL,
  `hash` char(32) CHARACTER SET ascii DEFAULT NULL,
  `avatar` int(20) unsigned DEFAULT NULL,
  `banner` int(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `avatar` (`avatar`),
  KEY `banner` (`banner`)
);

INSERT INTO `user`(`id`,`name`,`hatch`,`avatar`,`banner`) VALUES
('super','Super User','6ae5f59efb0b7fc0f2676e4d7fba6390',NULL,NULL)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- --------------------------------------------------------

--
-- Table structure for table `user_data`
--

CREATE TABLE IF NOT EXISTS `user_data` (
  `user` char(24) CHARACTER SET ascii NOT NULL,
  `param` char(16) CHARACTER SET ascii NOT NULL,
  `param_idx` int(2) NOT NULL DEFAULT '0',
  `value` char(255) COLLATE utf8_spanish_ci DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table `user_group`
--

CREATE TABLE IF NOT EXISTS `user_group` (
  `user` char(24) CHARACTER SET ascii NOT NULL,
  `group` char(24) CHARACTER SET ascii NOT NULL,
  `role` int(1) NOT NULL,
  PRIMARY KEY (`user`,`group`),
  KEY `role` (`role`),
  KEY `group` (`group`),
  KEY `user` (`user`)
);

INSERT INTO `user_group`(`user`,`group`,`role`) VALUES
('super','this_site',9)
ON DUPLICATE KEY UPDATE `user`=`user`;

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

CREATE TABLE IF NOT EXISTS `user_role` (
  `id` int(1) NOT NULL,
  `role` char(12) CHARACTER SET ascii NOT NULL,
  `description` char(48) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role` (`role`)
);

INSERT INTO `user_role` (`id`, `role`, `description`) VALUES
(0, 'guest', 'visitante'),
(1, 'bot', 'usuario automatico'),
(2, 'user', 'usuario'),
(4, 'mailbox', 'contacto'),
(5, 'editor', 'editor'),
(6, 'publisher', 'publicador'),
(7, 'admin', 'administrador'),
(9, 'superuser', 'superusuario')
ON DUPLICATE KEY UPDATE `id`=`id`;
