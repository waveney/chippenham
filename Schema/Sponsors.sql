CREATE TABLE `Sponsors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE utf8mb4_general_ci,
  `Website` text COLLATE utf8mb4_general_ci,
  `Description` text COLLATE utf8mb4_general_ci,
  `Year` text COLLATE utf8mb4_general_ci,
  `Importance` int NOT NULL,
  `Image` text COLLATE utf8mb4_general_ci,
  `ImageHeight` int NOT NULL,
  `ImageWidth` int NOT NULL,
  `IandT` tinyint NOT NULL,
  `SponsorId` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
