CREATE TABLE `OtherLinks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `LinkType` int NOT NULL,
  `SN` text,
  `URL` text NOT NULL,
  `Image` text NOT NULL,
  `Year` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
