CREATE TABLE `Water` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Web` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Year` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Test1` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
