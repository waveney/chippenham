CREATE TABLE `MapPoints` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Type` int NOT NULL,
  `Lat` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Lng` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `MapImp` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Notes` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `InUse` tinyint NOT NULL,
  `Link` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `AddText` int NOT NULL,
  `Directions` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
