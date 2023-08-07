CREATE TABLE `DanceTypes` (
  `TypeId` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE latin1_general_ci NOT NULL,
  `Importance` int NOT NULL,
  `Colour` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`TypeId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
