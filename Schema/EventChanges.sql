CREATE TABLE `EventChanges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `EventId` int NOT NULL,
  `Year` text COLLATE utf8mb4_general_ci,
  `Field` text COLLATE utf8mb4_general_ci,
  `Changes` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
