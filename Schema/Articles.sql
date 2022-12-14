CREATE TABLE `Articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE latin1_general_ci NOT NULL,
  `Type` smallint NOT NULL,
  `Link` text COLLATE latin1_general_ci NOT NULL,
  `Text` text COLLATE latin1_general_ci NOT NULL,
  `Image` text COLLATE latin1_general_ci NOT NULL,
  `ImageHeight` int NOT NULL,
  `ImageWidth` int NOT NULL,
  `Importance` tinyint NOT NULL,
  `StartDate` int NOT NULL,
  `StopDate` int NOT NULL,
  `Format` tinyint NOT NULL,
  `UsedOn` text COLLATE latin1_general_ci NOT NULL,
  `HideTitle` tinyint NOT NULL,
  `RedTitle` tinyint NOT NULL,
  `RelOrder` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
