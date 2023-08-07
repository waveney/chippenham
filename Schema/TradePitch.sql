CREATE TABLE `TradePitch` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Year` text COLLATE utf8mb4_general_ci,
  `Loc` int NOT NULL,
  `X` double NOT NULL,
  `Y` double NOT NULL,
  `Angle` double NOT NULL,
  `Posn` int NOT NULL,
  `Xsize` double NOT NULL,
  `Ysize` double NOT NULL,
  `Type` tinyint NOT NULL,
  `SN` text COLLATE utf8mb4_general_ci,
  `Colour` text COLLATE utf8mb4_general_ci,
  `Font` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
