CREATE TABLE `Bugs` (
  `BugId` int NOT NULL AUTO_INCREMENT,
  `Who` int NOT NULL,
  `Created` int NOT NULL,
  `SN` text COLLATE utf8mb4_general_ci,
  `Description` text COLLATE utf8mb4_general_ci,
  `State` int NOT NULL,
  `Response` text COLLATE utf8mb4_general_ci,
  `Severity` int NOT NULL,
  `LastUpdate` int NOT NULL,
  `Notes1` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`BugId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
