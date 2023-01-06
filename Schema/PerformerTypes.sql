CREATE TABLE `PerformerTypes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text NOT NULL,
  `ListState` tinyint NOT NULL,
  `Year` int NOT NULL,
  `FullName` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
