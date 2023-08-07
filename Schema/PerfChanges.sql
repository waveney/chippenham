CREATE TABLE `PerfChanges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SideId` int NOT NULL,
  `syId` int NOT NULL,
  `Year` text COLLATE utf8mb4_general_ci,
  `Field` text COLLATE utf8mb4_general_ci,
  `Changes` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
