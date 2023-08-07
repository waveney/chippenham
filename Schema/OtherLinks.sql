CREATE TABLE `OtherLinks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `LinkType` int NOT NULL,
  `SN` text COLLATE utf8mb4_general_ci,
  `URL` text COLLATE utf8mb4_general_ci,
  `Image` text COLLATE utf8mb4_general_ci,
  `Year` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
