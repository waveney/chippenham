CREATE TABLE `TradePitch` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Year` text COLLATE latin1_general_ci NOT NULL,
  `Loc` int NOT NULL,
  `X` double NOT NULL,
  `Y` double NOT NULL,
  `Angle` double NOT NULL,
  `Posn` int NOT NULL,
  `Xsize` double NOT NULL,
  `Ysize` double NOT NULL,
  `Type` tinyint NOT NULL,
  `SN` text COLLATE latin1_general_ci NOT NULL,
  `Colour` text COLLATE latin1_general_ci NOT NULL,
  `Font` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
