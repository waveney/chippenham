CREATE TABLE `BandMembers` (
  `BandMemId` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE latin1_general_ci NOT NULL,
  `BandId` int NOT NULL,
  PRIMARY KEY (`BandMemId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
