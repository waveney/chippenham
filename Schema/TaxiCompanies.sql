CREATE TABLE `TaxiCompanies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Authority` tinyint NOT NULL,
  `SN` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `Phone` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Website` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
