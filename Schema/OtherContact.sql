CREATE TABLE `OtherContact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Name` text COLLATE utf8mb4_general_ci,
  `Phone` text COLLATE utf8mb4_general_ci,
  `Email` text COLLATE utf8mb4_general_ci,
  `ForType` int NOT NULL,
  `ForId` int NOT NULL,
  `Role` text COLLATE utf8mb4_general_ci,
  `Notes` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
