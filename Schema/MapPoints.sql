CREATE TABLE `MapPoints` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text NOT NULL,
  `Type` int NOT NULL,
  `Lat` text NOT NULL,
  `Lng` text NOT NULL,
  `MapImp` text NOT NULL,
  `Notes` text NOT NULL,
  `InUse` tinyint NOT NULL,
  `Link` text NOT NULL,
  `AddText` int NOT NULL,
  `Directions` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
