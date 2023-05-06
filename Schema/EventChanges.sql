CREATE TABLE `EventChanges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `EventId` int NOT NULL,
  `Year` int NOT NULL,
  `Field` text NOT NULL,
  `Changes` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
