-- --------------------------------------------------------

--
-- Table structure for table `res_engine`
--

CREATE TABLE IF NOT EXISTS `res_engine` (
  `id` char(12) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`id`)
);

--
-- Dumping data for table `res_engine`
--

INSERT INTO `res_engine` (`id`) VALUES
('menu'),
('static'),
('status')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- --------------------------------------------------------

--
-- Table structure for table `res_extension`
--

CREATE TABLE IF NOT EXISTS `res_extension` (
  `ext` char(6) CHARACTER SET ascii NOT NULL,
  `format` char(6) CHARACTER SET ascii DEFAULT NULL,
  `action_type` enum('static','dynamic','active') COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`ext`),
  KEY `format` (`format`)
);

--
-- Dumping data for table `res_extension`
--

INSERT INTO `res_extension` (`ext`, `format`, `action_type`) VALUES
('cgi', 'html', 'active'),
('csv', 'csv', 'dynamic'),
('exe', 'html', 'active'),
('htm', 'html', 'static'),
('html', 'html', 'static'),
('json', 'json', 'dynamic'),
('odf', 'odf', 'static'),
('odf!', 'odf', 'dynamic'),
('pdf', 'pdf', 'static'),
('pdf!', 'pdf', 'dynamic'),
('php', 'html', 'dynamic'),
('rss', 'rss', 'dynamic'),
('text', 'txt', 'static'),
('txt', 'txt', 'static'),
('txt!', 'txt', 'dynamic'),
('xhtml', 'html', 'static'),
('xml', 'xml', 'dynamic')
ON DUPLICATE KEY UPDATE `ext`=`ext`;

-- --------------------------------------------------------

--
-- Table structure for table `res_format`
--

CREATE TABLE IF NOT EXISTS `res_format` (
  `id` char(4) CHARACTER SET ascii NOT NULL,
  `mime_type` char(40) CHARACTER SET ascii DEFAULT NULL,
  `description` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  `static` char(6) CHARACTER SET ascii DEFAULT NULL,
  `dynamic` char(6) CHARACTER SET ascii DEFAULT NULL,
  `active` char(6) CHARACTER SET armscii8 DEFAULT NULL,
  PRIMARY KEY (`id`)
);

--
-- Dumping data for table `res_format`
--

INSERT INTO `res_format` (`id`, `mime_type`, `description`, `static`, `dynamic`, `active`) VALUES
('csv', 'text/csv', 'Comma Separated Vector', NULL, 'csv', NULL),
('html', 'text/html', 'HTML page', 'html', 'php', 'cgi'),
('json', 'application/json', 'JSON data', NULL, 'json', NULL),
('odf', 'application/odf', 'ODF document', 'odf', 'odf!', NULL),
('pdf', 'application/pdf', 'PDF document', 'pdf', 'pdf!', NULL),
('rss', 'application/xml+rss', 'RSS feed', NULL, 'rss', NULL),
('txt', 'text/plain', 'Text file', 'txt', 'txt!', NULL),
('xml', 'application/xml', 'XML document', NULL, 'xml', NULL)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- --------------------------------------------------------

--
-- Table structure for table `res_rel`
--

CREATE TABLE IF NOT EXISTS `res_rel` (
  `for` bigint(20) NOT NULL,
  `to` bigint(20) NOT NULL,
  `rel` enum('child','next') COLLATE utf8_spanish_ci NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table `res_section`
--

CREATE TABLE IF NOT EXISTS `res_section` (
  `id` char(12) CHARACTER SET ascii NOT NULL,
  `case` char(32) COLLATE utf8_spanish_ci DEFAULT NULL,
  `priority` tinyint(1) DEFAULT NULL,
  `description` char(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  `engine` char(20) CHARACTER SET ascii DEFAULT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `res_section` (`id`, `case`, `priority`, `description`, `engine`) VALUES
('menu', '(menu)/(\\w*)', 1, 'menu', 'menu'),
('root', '()(.*)', 0, 'root static content', 'static'),
('status', '(status)/(\\d{3})', 1, 'http status messages', 'status')
ON DUPLICATE KEY UPDATE `id`=`id`;

