CREATE TABLE `PerformerTypes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE utf8mb4_general_ci,
  `ListState` tinyint NOT NULL,
  `Year` text COLLATE utf8mb4_general_ci,
  `FullName` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
