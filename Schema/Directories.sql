CREATE TABLE `Directories` (
  `DirId` int NOT NULL AUTO_INCREMENT,
  `SN` text NOT NULL,
  `Created` int NOT NULL,
  `Who` int NOT NULL,
  `Parent` int NOT NULL,
  `State` tinyint NOT NULL DEFAULT '0',
  `AccessLevel` int NOT NULL,
  `AccessSections` text NOT NULL,
  `ExtraData` int NOT NULL,
  PRIMARY KEY (`DirId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
