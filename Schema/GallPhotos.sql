CREATE TABLE `GallPhotos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Galid` int NOT NULL,
  `Credit` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `File` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Caption` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `RelOrder` int NOT NULL,
  `ImageHeight` int NOT NULL,
  `ImageWidth` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
