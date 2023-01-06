CREATE TABLE `Articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text NOT NULL,
  `Type` smallint NOT NULL,
  `Link` text NOT NULL,
  `Text` text NOT NULL,
  `Image` text NOT NULL,
  `ImageHeight` int NOT NULL,
  `ImageWidth` int NOT NULL,
  `Importance` tinyint NOT NULL,
  `StartDate` int NOT NULL,
  `StopDate` int NOT NULL,
  `Format` tinyint NOT NULL,
  `UsedOn` text NOT NULL,
  `HideTitle` tinyint NOT NULL,
  `RelOrder` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
