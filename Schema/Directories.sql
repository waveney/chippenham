CREATE TABLE `Directories` (
  `DirId` int NOT NULL AUTO_INCREMENT,
  `SN` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Created` int NOT NULL,
  `Who` int NOT NULL,
  `Parent` int NOT NULL,
  `State` tinyint NOT NULL DEFAULT '0',
  `AccessLevel` int NOT NULL,
  `AccessSections` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `ExtraData` int NOT NULL,
  PRIMARY KEY (`DirId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
