CREATE TABLE `Documents` (
  `DocId` int NOT NULL AUTO_INCREMENT,
  `SN` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Who` int NOT NULL,
  `Created` int NOT NULL,
  `Dir` int NOT NULL,
  `Filename` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `filesize` int NOT NULL,
  `State` tinyint NOT NULL DEFAULT '0',
  `Access` int NOT NULL DEFAULT '666',
  PRIMARY KEY (`DocId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
