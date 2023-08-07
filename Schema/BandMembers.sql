CREATE TABLE `BandMembers` (
  `BandMemId` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE utf8mb4_general_ci,
  `BandId` int NOT NULL,
  PRIMARY KEY (`BandMemId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
