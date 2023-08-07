CREATE TABLE `BigEvent` (
  `Event` int NOT NULL,
  `Type` text COLLATE utf8mb4_general_ci,
  `Identifier` int NOT NULL,
  `BigEid` int NOT NULL AUTO_INCREMENT,
  `EventOrder` int NOT NULL,
  `Notes` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`BigEid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
