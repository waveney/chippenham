CREATE TABLE `Bugs` (
  `BugId` int NOT NULL AUTO_INCREMENT,
  `Who` int NOT NULL,
  `Created` int NOT NULL,
  `SN` text NOT NULL,
  `Description` text NOT NULL,
  `State` int NOT NULL,
  `Response` text NOT NULL,
  `Severity` int NOT NULL,
  `LastUpdate` int NOT NULL,
  PRIMARY KEY (`BugId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
