CREATE TABLE `Donations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `InUse` int NOT NULL,
  `Image` text NOT NULL,
  `Value` text NOT NULL,
  `Text` text NOT NULL,
  `ButtonId` text NOT NULL,
  `Importance` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
