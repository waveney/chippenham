CREATE TABLE `Sponsors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text,
  `Website` text NOT NULL,
  `Description` text NOT NULL,
  `Year` text NOT NULL,
  `Importance` int NOT NULL,
  `Image` text NOT NULL,
  `ImageHeight` int NOT NULL,
  `ImageWidth` int NOT NULL,
  `IandT` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
