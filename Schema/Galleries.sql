CREATE TABLE `Galleries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE utf8mb4_general_ci,
  `Credits` text COLLATE utf8mb4_general_ci,
  `Media` tinyint NOT NULL,
  `Banner` text COLLATE utf8mb4_general_ci,
  `MenuBarOrder` int NOT NULL,
  `Description` text COLLATE utf8mb4_general_ci,
  `GallerySet` text COLLATE utf8mb4_general_ci,
  `Image` int NOT NULL,
  `Level` int NOT NULL,
  `SetOrder` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
