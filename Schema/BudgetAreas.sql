CREATE TABLE `BudgetAreas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Year` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Budget` int NOT NULL,
  `CommittedSoFar` int NOT NULL,
  `Who` int NOT NULL,
  `Who2` int NOT NULL,
  `Who3` int NOT NULL,
  `Who4` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
