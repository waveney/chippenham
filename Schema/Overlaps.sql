CREATE TABLE `Overlaps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Sid1` int NOT NULL,
  `Sid2` int NOT NULL,
  `Cat1` tinyint NOT NULL,
  `Cat2` tinyint NOT NULL,
  `OType` tinyint NOT NULL,
  `Major` tinyint NOT NULL,
  `Days` tinyint NOT NULL,
  `Active` tinyint NOT NULL,
  `Notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
