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
  `Notes` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
