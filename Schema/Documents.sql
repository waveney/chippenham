CREATE TABLE `Documents` (
  `DocId` int NOT NULL AUTO_INCREMENT,
  `SN` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Who` int NOT NULL,
  `Created` int NOT NULL,
  `Dir` int NOT NULL,
  `Filename` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `filesize` int NOT NULL,
  `State` tinyint NOT NULL DEFAULT '0',
  `Access` int NOT NULL DEFAULT '666',
  PRIMARY KEY (`DocId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
