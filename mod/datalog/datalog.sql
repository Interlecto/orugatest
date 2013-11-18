-- --------------------------------------------------------

--
-- Table structure for table `dl_instrument`
--

CREATE TABLE IF NOT EXISTS `dl_instrument` (
  `station` char(20) CHARACTER SET ascii NOT NULL DEFAULT '',
  `address` int(3) NOT NULL DEFAULT '0',
  `reference` char(24) CHARACTER SET ascii DEFAULT NULL,
  `description` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  `avatar` int(20) DEFAULT NULL,
  PRIMARY KEY (`station`,`address`),
  KEY `station` (`station`)
);

-- --------------------------------------------------------

--
-- Table structure for table `dl_meter`
--

CREATE TABLE IF NOT EXISTS `dl_meter` (
  `station` char(20) CHARACTER SET ascii NOT NULL DEFAULT '',
  `address` int(3) NOT NULL DEFAULT '0',
  `keyword` char(20) CHARACTER SET ascii NOT NULL DEFAULT '',
  `type` enum('common','alarm','flag','param') COLLATE utf8_spanish_ci DEFAULT NULL,
  `name` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  `format` char(20) COLLATE utf8_spanish_ci DEFAULT NULL,
  `min` double(4,1) DEFAULT NULL,
  `max` double(4,1) DEFAULT NULL,
  `avatar` int(20) DEFAULT NULL,
  PRIMARY KEY (`station`,`address`,`keyword`),
  KEY `inst_id` (`station`,`address`),
  KEY `station` (`station`)
);

-- --------------------------------------------------------

--
-- Table structure for table `dl_param`
--

CREATE TABLE IF NOT EXISTS `dl_param` (
  `station` char(20) CHARACTER SET ascii NOT NULL DEFAULT '',
  `address` int(3) NOT NULL DEFAULT '0',
  `param` char(16) CHARACTER SET ascii NOT NULL DEFAULT '',
  `param_idx` int(2) NOT NULL DEFAULT '0',
  `value` char(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`station`,`address`,`param`,`param_idx`),
  KEY `inst_id` (`station`,`address`),
  KEY `station` (`station`)
);

-- --------------------------------------------------------

--
-- Table structure for table `dl_station`
--

CREATE TABLE IF NOT EXISTS `dl_station` (
  `station` char(20) CHARACTER SET ascii NOT NULL,
  `name` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  `ip` char(40) CHARACTER SET ascii DEFAULT NULL,
  `group` char(24) CHARACTER SET ascii DEFAULT NULL,
  `public` int(1) DEFAULT '0',
  PRIMARY KEY (`station`),
  KEY `group` (`group`)
);

-- --------------------------------------------------------

--
-- Table structure for table `dl_status_i`
--

CREATE TABLE IF NOT EXISTS `dl_status_i` (
  `idx` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `station` char(20) CHARACTER SET ascii NOT NULL DEFAULT '',
  `address` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`time`,`station`,`address`),
  UNIQUE KEY `idx` (`idx`),
  KEY `inst_id` (`station`,`address`),
  KEY `station` (`station`)
);

-- --------------------------------------------------------

--
-- Table structure for table `dl_status_p`
--

CREATE TABLE IF NOT EXISTS `dl_status_p` (
  `status` bigint(20) unsigned NOT NULL,
  `keyword` char(20) CHARACTER SET ascii NOT NULL DEFAULT '',
  `value` double(6,1) DEFAULT NULL,
  PRIMARY KEY (`status`,`keyword`),
  KEY `status` (`status`)
);

-- --------------------------------------------------------

--
-- Table structure for table `dl_userstation`
--

CREATE TABLE IF NOT EXISTS `dl_userstation` (
  `user` char(24) CHARACTER SET ascii NOT NULL,
  `station` char(24) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`user`,`station`),
  KEY `user` (`user`,`station`)
);

INSERT INTO `res_section` (`id`, `case`, `priority`, `description`, `engine`) VALUES
('datalog', '(dataloq)/(.*)', 2, 'datalog', 'datalog'),
('item', '(item|base)/(.*)', 2, 'item', 'item')
ON DUPLICATE KEY UPDATE `id`=`id`;
