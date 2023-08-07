CREATE TABLE `VolCatYear` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Volid` int NOT NULL,
  `CatId` int NOT NULL,
  `Year` text COLLATE utf8mb4_general_ci,
  `Status` int NOT NULL,
  `Likes` text COLLATE utf8mb4_general_ci,
  `Dislikes` text COLLATE utf8mb4_general_ci,
  `Other1` text COLLATE utf8mb4_general_ci,
  `Other2` text COLLATE utf8mb4_general_ci,
  `Other3` text COLLATE utf8mb4_general_ci,
  `Other4` text COLLATE utf8mb4_general_ci,
  `Experience` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
