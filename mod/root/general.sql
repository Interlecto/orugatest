-- --------------------------------------------------------

--
-- Table structure for table `gen_country`
--

CREATE TABLE IF NOT EXISTS `gen_country` (
  `code` char(3) CHARACTER SET ascii NOT NULL,
  `name` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`code`)
);

-- --------------------------------------------------------

--
-- Table structure for table `gen_location`
--

CREATE TABLE IF NOT EXISTS `gen_location` (
  `idx` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  `in` int(20) DEFAULT NULL,
  `locstatus` char(6) CHARACTER SET ascii DEFAULT NULL,
  `country` char(3) CHARACTER SET ascii DEFAULT NULL,
  PRIMARY KEY (`idx`),
  UNIQUE KEY `idx` (`idx`),
  KEY `in` (`in`),
  KEY `country` (`country`)
);

-- --------------------------------------------------------

--
-- Table structure for table `gen_locstatus`
--

CREATE TABLE IF NOT EXISTS `gen_locstatus` (
  `id` char(6) CHARACTER SET ascii NOT NULL,
  `description` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `id` char(2) CHARACTER SET ascii NOT NULL,
  `code` char(5) CHARACTER SET ascii DEFAULT NULL,
  `local` char(12) COLLATE utf8_spanish_ci DEFAULT NULL,
  `english` char(12) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
);

--
-- Dumping data for table `language`
--

INSERT INTO `language` (`id`, `code`, `local`, `english`) VALUES
('en', 'en-US', 'English', 'English')
ON DUPLICATE KEY UPDATE `id`=`id`;
