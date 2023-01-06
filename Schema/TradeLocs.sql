CREATE TABLE `TradeLocs` (
  `TLocId` int NOT NULL AUTO_INCREMENT,
  `SN` text,
  `HasPower` tinyint NOT NULL,
  `Pitches` int NOT NULL,
  `Notes` text NOT NULL,
  `InUse` tinyint NOT NULL,
  `Days` tinyint NOT NULL,
  `ArtisanMsgs` tinyint NOT NULL,
  `prefix` tinyint NOT NULL,
  `InvoiceCode` int NOT NULL,
  `MapImage` text NOT NULL,
  `Mapscale` float NOT NULL,
  `Showscale` double NOT NULL,
  `NoList` tinyint NOT NULL,
  PRIMARY KEY (`TLocId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
