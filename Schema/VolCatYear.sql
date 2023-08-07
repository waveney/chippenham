CREATE TABLE `VolCatYear` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Volid` int NOT NULL,
  `CatId` int NOT NULL,
  `Year` text COLLATE latin1_general_ci NOT NULL,
  `Status` int NOT NULL,
  `Likes` text COLLATE latin1_general_ci NOT NULL,
  `Dislikes` text COLLATE latin1_general_ci NOT NULL,
  `Other1` text COLLATE latin1_general_ci NOT NULL,
  `Other2` text COLLATE latin1_general_ci NOT NULL,
  `Other3` text COLLATE latin1_general_ci NOT NULL,
  `Other4` text COLLATE latin1_general_ci NOT NULL,
  `Experience` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
