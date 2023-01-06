CREATE TABLE `TaxiCompanies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Authority` tinyint NOT NULL,
  `SN` text,
  `Phone` text NOT NULL,
  `Website` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
