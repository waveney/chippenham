CREATE TABLE `Galleries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Credits` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Media` tinyint NOT NULL,
  `Banner` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `MenuBarOrder` int NOT NULL,
  `Description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `GallerySet` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Image` int NOT NULL,
  `Level` int NOT NULL,
  `SetOrder` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
