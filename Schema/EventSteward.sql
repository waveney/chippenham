CREATE TABLE `EventSteward` (
  `id` int NOT NULL AUTO_INCREMENT,
  `HowMany` text NOT NULL,
  `HowWent` text NOT NULL,
  `Name` text NOT NULL,
  `RandId` int NOT NULL,
  `EventId` int NOT NULL,
  `SubEvent` int NOT NULL,
  `Year` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
