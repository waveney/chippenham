CREATE TABLE `LogFile` (
  `LogId` int NOT NULL AUTO_INCREMENT,
  `Who` int NOT NULL,
  `changed` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `What` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`LogId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
