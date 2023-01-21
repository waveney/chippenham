CREATE TABLE `Potential` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text NOT NULL,
  `Description` text NOT NULL,
  `Contact` text NOT NULL,
  `Email` text NOT NULL,
  `Website` text NOT NULL,
  `YouTube` text NOT NULL,
  `Phone` text NOT NULL,
  `Mobile` text NOT NULL,
  `Type` text NOT NULL,
  `Status` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
