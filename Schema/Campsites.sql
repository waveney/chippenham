CREATE TABLE `Campsites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Postcode` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ShortDesc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `LongDesc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Props` int NOT NULL,
  `Image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Importance` int NOT NULL,
  `Restriction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `MapPoint` int NOT NULL,
  `RulesName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
