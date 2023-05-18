CREATE TABLE `Campsites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Name` text NOT NULL,
  `Postcode` text NOT NULL,
  `Address` text NOT NULL,
  `ShortDesc` text NOT NULL,
  `LongDesc` text NOT NULL,
  `Props` int NOT NULL,
  `Image` text NOT NULL,
  `Importance` int NOT NULL,
  `Restriction` text NOT NULL,
  `Comment` text NOT NULL,
  `MapPoint` int NOT NULL,
  `RulesName` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
