CREATE TABLE `Potential` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Contact` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Website` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `YouTube` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Phone` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Mobile` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Status` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
