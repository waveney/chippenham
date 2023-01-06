CREATE TABLE `BigEvent` (
  `Event` int NOT NULL,
  `Type` text NOT NULL,
  `Identifier` int NOT NULL,
  `BigEid` int NOT NULL AUTO_INCREMENT,
  `EventOrder` int NOT NULL,
  `Notes` text NOT NULL,
  PRIMARY KEY (`BigEid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
