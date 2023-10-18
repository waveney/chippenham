CREATE TABLE `Galleries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Credits` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Media` tinyint NOT NULL,
  `Banner` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `MenuBarOrder` int NOT NULL,
  `Description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `GallerySet` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Image` int NOT NULL,
  `Level` int NOT NULL,
  `SetOrder` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
