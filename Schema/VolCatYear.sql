CREATE TABLE `VolCatYear` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Volid` int NOT NULL,
  `CatId` int NOT NULL,
  `Year` int NOT NULL,
  `Status` int NOT NULL,
  `Likes` text NOT NULL,
  `Dislikes` text NOT NULL,
  `Other1` text NOT NULL,
  `Other2` text NOT NULL,
  `Other3` text NOT NULL,
  `Other4` text NOT NULL,
  `Experience` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
