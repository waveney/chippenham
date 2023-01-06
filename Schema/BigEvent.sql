CREATE TABLE `BigEvent` (
  `Event` int NOT NULL,
  `Type` text COLLATE latin1_general_ci NOT NULL,
  `Identifier` int NOT NULL,
  `BigEid` int NOT NULL AUTO_INCREMENT,
  `EventOrder` int NOT NULL,
  `Notes` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`BigEid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
