CREATE TABLE `Documents` (
  `DocId` int NOT NULL AUTO_INCREMENT,
  `SN` text NOT NULL,
  `Who` int NOT NULL,
  `Created` int NOT NULL,
  `Dir` int NOT NULL,
  `Filename` text NOT NULL,
  `filesize` int NOT NULL,
  `State` tinyint NOT NULL DEFAULT '0',
  `Access` int NOT NULL DEFAULT '666',
  PRIMARY KEY (`DocId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
