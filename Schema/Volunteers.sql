CREATE TABLE `Volunteers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text,
  `Email` text NOT NULL,
  `Phone` text NOT NULL,
  `Address` text NOT NULL,
  `PostCode` text NOT NULL,
  `Over18` tinyint NOT NULL,
  `Birthday` text NOT NULL,
  `ContactName` text NOT NULL,
  `ContactPhone` text NOT NULL,
  `DBS` text NOT NULL,
  `Relation` smallint NOT NULL,
  `AccessKey` text NOT NULL,
  `Status` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
