CREATE TABLE `CampsiteUse` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE utf8mb4_general_ci,
  `Number` int NOT NULL,
  `Who` text COLLATE utf8mb4_general_ci,
  `Priority` int NOT NULL,
  `Year` text COLLATE utf8mb4_general_ci,
  `Notes` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
