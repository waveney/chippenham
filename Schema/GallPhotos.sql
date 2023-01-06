CREATE TABLE `GallPhotos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Galid` int NOT NULL,
  `Credit` text NOT NULL,
  `File` text NOT NULL,
  `Caption` text NOT NULL,
  `RelOrder` int NOT NULL,
  `ImageHeight` int NOT NULL,
  `ImageWidth` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
