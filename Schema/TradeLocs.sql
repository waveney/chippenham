CREATE TABLE `TradeLocs` (
  `TLocId` int NOT NULL AUTO_INCREMENT,
  `SN` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `HasPower` tinyint NOT NULL,
  `Pitches` int NOT NULL,
  `Notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `InUse` tinyint NOT NULL,
  `Days` tinyint NOT NULL,
  `ArtisanMsgs` tinyint NOT NULL,
  `prefix` tinyint NOT NULL,
  `InvoiceCode` int NOT NULL,
  `MapImage` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Mapscale` float NOT NULL,
  `Showscale` double NOT NULL,
  `NoList` tinyint NOT NULL,
  PRIMARY KEY (`TLocId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
