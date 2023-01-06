CREATE TABLE `Water` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text NOT NULL,
  `Image` text NOT NULL,
  `Web` text NOT NULL,
  `Year` int NOT NULL,
  `Test1` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
