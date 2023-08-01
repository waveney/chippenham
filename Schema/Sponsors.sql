CREATE TABLE `Sponsors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `Website` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Year` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Importance` int NOT NULL,
  `Image` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `ImageHeight` int NOT NULL,
  `ImageWidth` int NOT NULL,
  `IandT` tinyint NOT NULL,
  `SponsorId` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
