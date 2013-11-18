-- --------------------------------------------------------

--
-- Table structure for table `cat_action`
--

CREATE TABLE IF NOT EXISTS `cat_action` (
  `id` int(2) NOT NULL,
  `verb` char(12) COLLATE utf8_spanish_ci NOT NULL,
  `description` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  `macro` int(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `macro` (`macro`)
);

-- --------------------------------------------------------

--
-- Table structure for table `cat_area`
--

CREATE TABLE IF NOT EXISTS `cat_area` (
  `id` char(24) CHARACTER SET ascii NOT NULL,
  `description` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  `avatar` int(20) DEFAULT NULL,
  `banner` int(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `cat_category`
--

CREATE TABLE IF NOT EXISTS `cat_category` (
  `id` char(24) CHARACTER SET ascii NOT NULL,
  `description` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  `area` char(24) CHARACTER SET ascii DEFAULT NULL,
  `macro` int(2) DEFAULT NULL,
  `avatar` int(20) DEFAULT NULL,
  `banner` int(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `area` (`area`),
  KEY `macro` (`macro`)
);

-- --------------------------------------------------------

--
-- Table structure for table `cat_macro`
--

CREATE TABLE IF NOT EXISTS `cat_macro` (
  `id` int(2) NOT NULL,
  `description` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `cat_param`
--

CREATE TABLE IF NOT EXISTS `cat_param` (
  `product` int(20) NOT NULL DEFAULT '0',
  `param` char(16) CHARACTER SET ascii NOT NULL DEFAULT '',
  `param_idx` int(2) NOT NULL DEFAULT '0',
  `value` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`product`,`param`,`param_idx`),
  KEY `product` (`product`)
);

-- --------------------------------------------------------

--
-- Table structure for table `cat_product`
--

CREATE TABLE IF NOT EXISTS `cat_product` (
  `idx` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  `description` text COLLATE utf8_spanish_ci,
  `status` int(1) DEFAULT NULL,
  `action` int(2) DEFAULT NULL,
  `category` char(24) CHARACTER SET ascii DEFAULT NULL,
  `location` int(20) DEFAULT NULL,
  `avatar` int(20) DEFAULT NULL,
  `banner` int(20) DEFAULT NULL,
  PRIMARY KEY (`idx`),
  UNIQUE KEY `idx` (`idx`),
  KEY `action` (`action`),
  KEY `category` (`category`)
);
