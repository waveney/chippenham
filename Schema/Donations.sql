CREATE TABLE `Donations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `InUse` int NOT NULL,
  `Image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ButtonId` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Importance` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
