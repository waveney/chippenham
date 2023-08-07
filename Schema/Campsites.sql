CREATE TABLE `Campsites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Name` text COLLATE utf8mb4_general_ci,
  `Postcode` text COLLATE utf8mb4_general_ci,
  `Address` text COLLATE utf8mb4_general_ci,
  `ShortDesc` text COLLATE utf8mb4_general_ci,
  `LongDesc` text COLLATE utf8mb4_general_ci,
  `Props` int NOT NULL,
  `Image` text COLLATE utf8mb4_general_ci,
  `Importance` int NOT NULL,
  `Restriction` text COLLATE utf8mb4_general_ci,
  `Comment` text COLLATE utf8mb4_general_ci,
  `MapPoint` int NOT NULL,
  `RulesName` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
