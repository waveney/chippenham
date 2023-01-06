CREATE TABLE `TradePitch` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Year` text NOT NULL,
  `Loc` int NOT NULL,
  `X` double NOT NULL,
  `Y` double NOT NULL,
  `Angle` double NOT NULL,
  `Posn` int NOT NULL,
  `Xsize` double NOT NULL,
  `Ysize` double NOT NULL,
  `Type` tinyint NOT NULL,
  `SN` text NOT NULL,
  `Colour` text NOT NULL,
  `Font` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
