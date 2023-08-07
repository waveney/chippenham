CREATE TABLE `Potential` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE utf8mb4_general_ci,
  `Description` text COLLATE utf8mb4_general_ci,
  `Contact` text COLLATE utf8mb4_general_ci,
  `Email` text COLLATE utf8mb4_general_ci,
  `Website` text COLLATE utf8mb4_general_ci,
  `YouTube` text COLLATE utf8mb4_general_ci,
  `Phone` text COLLATE utf8mb4_general_ci,
  `Mobile` text COLLATE utf8mb4_general_ci,
  `Type` text COLLATE utf8mb4_general_ci,
  `Status` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
