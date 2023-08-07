CREATE TABLE `VenueYear` (
  `id` int NOT NULL AUTO_INCREMENT,
  `VenueId` int NOT NULL,
  `Year` text COLLATE latin1_general_ci NOT NULL,
  `Complete` tinyint NOT NULL,
  `SponsoredBy` int NOT NULL,
  `QRCount` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
