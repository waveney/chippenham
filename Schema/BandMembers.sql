CREATE TABLE `BandMembers` (
  `BandMemId` int NOT NULL AUTO_INCREMENT,
  `SN` text NOT NULL,
  `BandId` int NOT NULL,
  PRIMARY KEY (`BandMemId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
