CREATE TABLE `TradeLocs` (
  `TLocId` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE utf8mb4_general_ci,
  `HasPower` tinyint NOT NULL,
  `Pitches` int NOT NULL,
  `Notes` text COLLATE utf8mb4_general_ci,
  `InUse` tinyint NOT NULL,
  `Days` tinyint NOT NULL,
  `ArtisanMsgs` tinyint NOT NULL,
  `prefix` tinyint NOT NULL,
  `InvoiceCode` int NOT NULL,
  `MapImage` text COLLATE utf8mb4_general_ci,
  `Mapscale` float NOT NULL,
  `Showscale` double NOT NULL,
  `NoList` tinyint NOT NULL,
  PRIMARY KEY (`TLocId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
