CREATE TABLE `Tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Year` text NOT NULL,
  `SN` text,
  `Type` tinyint NOT NULL,
  `Carer` text NOT NULL,
  `Notes` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
