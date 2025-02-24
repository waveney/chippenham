CREATE TABLE `FeatureHelp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Name` text COLLATE utf8mb4_general_ci NOT NULL,
  `FeatureGroup` text COLLATE utf8mb4_general_ci NOT NULL,
  `DefaultValue` text COLLATE utf8mb4_general_ci NOT NULL,
  `Explanation` text COLLATE utf8mb4_general_ci NOT NULL,
  `Priority` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
