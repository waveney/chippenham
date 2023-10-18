CREATE TABLE `CollectingUse` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Year` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `AssignType` int NOT NULL,
  `AssignTo` int NOT NULL,
  `AssignName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Value` int NOT NULL,
  `TimeOut` int NOT NULL,
  `TimeIn` int NOT NULL,
  `Notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `CollectionUnitId` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
