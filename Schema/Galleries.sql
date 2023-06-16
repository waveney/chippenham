CREATE TABLE `Galleries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text NOT NULL,
  `Credits` text NOT NULL,
  `Media` tinyint NOT NULL,
  `Banner` text NOT NULL,
  `MenuBarOrder` int NOT NULL,
  `Description` text NOT NULL,
  `GallerySet` text NOT NULL,
  `Image` int NOT NULL,
  `Level` int NOT NULL,
  `SetOrder` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
