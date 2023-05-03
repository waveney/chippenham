CREATE TABLE `PerfChanges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `syId` int NOT NULL,
  `Year` int NOT NULL,
  `Changes` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
