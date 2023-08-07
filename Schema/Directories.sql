CREATE TABLE `Directories` (
  `DirId` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE utf8mb4_general_ci,
  `Created` int NOT NULL,
  `Who` int NOT NULL,
  `Parent` int NOT NULL,
  `State` tinyint NOT NULL DEFAULT '0',
  `AccessLevel` int NOT NULL,
  `AccessSections` text COLLATE utf8mb4_general_ci,
  `ExtraData` int NOT NULL,
  PRIMARY KEY (`DirId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
