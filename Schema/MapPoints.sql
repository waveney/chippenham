CREATE TABLE `MapPoints` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Type` int NOT NULL,
  `Lat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Lng` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `MapImp` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `InUse` tinyint NOT NULL,
  `Link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `AddText` int NOT NULL,
  `Directions` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
