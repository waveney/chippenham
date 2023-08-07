CREATE TABLE `LogFile` (
  `LogId` int NOT NULL AUTO_INCREMENT,
  `Who` int NOT NULL,
  `changed` text COLLATE utf8mb4_general_ci,
  `What` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`LogId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
