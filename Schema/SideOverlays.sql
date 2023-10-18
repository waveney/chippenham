CREATE TABLE `SideOverlays` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SideId` int NOT NULL,
  `Festival` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `IsType` int NOT NULL,
  `Description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Blurb` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
