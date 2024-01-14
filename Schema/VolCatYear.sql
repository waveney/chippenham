CREATE TABLE `VolCatYear` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Volid` int NOT NULL,
  `CatId` int NOT NULL,
  `Year` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Status` int NOT NULL,
  `Likes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Dislikes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Other1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Other2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Other3` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Other4` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Experience` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `VolOrder` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
