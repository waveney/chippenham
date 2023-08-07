CREATE TABLE `VenueYear` (
  `id` int NOT NULL AUTO_INCREMENT,
  `VenueId` int NOT NULL,
  `Year` text COLLATE utf8mb4_general_ci,
  `Complete` tinyint NOT NULL,
  `SponsoredBy` int NOT NULL,
  `QRCount` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
