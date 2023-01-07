CREATE TABLE `Bugs` (
  `BugId` int NOT NULL AUTO_INCREMENT,
  `Who` int NOT NULL,
  `Created` int NOT NULL,
  `SN` text COLLATE latin1_general_ci NOT NULL,
  `Description` text COLLATE latin1_general_ci NOT NULL,
  `State` int NOT NULL,
  `Response` text COLLATE latin1_general_ci NOT NULL,
  `Severity` int NOT NULL,
  `LastUpdate` int NOT NULL,
  `Notes1` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`BugId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
