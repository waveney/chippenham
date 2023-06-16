CREATE TABLE `CollectingUse` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Year` text NOT NULL,
  `AssignType` int NOT NULL,
  `AssignTo` int NOT NULL,
  `AssignName` text NOT NULL,
  `Value` int NOT NULL,
  `TimeOut` int NOT NULL,
  `TimeIn` int NOT NULL,
  `Notes` text NOT NULL,
  `CollectionUnitId` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
