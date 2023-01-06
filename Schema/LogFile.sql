CREATE TABLE `LogFile` (
  `LogId` int NOT NULL AUTO_INCREMENT,
  `Who` int NOT NULL,
  `changed` text NOT NULL,
  `What` text NOT NULL,
  PRIMARY KEY (`LogId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
