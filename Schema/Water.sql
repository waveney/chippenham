CREATE TABLE `Water` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Image` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Web` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Year` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Test1` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
