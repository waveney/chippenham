CREATE TABLE `EventSteward` (
  `id` int NOT NULL AUTO_INCREMENT,
  `HowMany` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `HowWent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `RandId` int NOT NULL,
  `EventId` int NOT NULL,
  `SubEvent` int NOT NULL,
  `Year` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
