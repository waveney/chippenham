CREATE TABLE `CampsiteUse` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text NOT NULL,
  `Number` int NOT NULL,
  `Who` text NOT NULL,
  `Priority` int NOT NULL,
  `Year` int NOT NULL,
  `Notes` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
