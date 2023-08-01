CREATE TABLE `Sponsorship` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Year` text COLLATE latin1_general_ci NOT NULL,
  `SponsorId` int NOT NULL,
  `ThingType` int NOT NULL,
  `ThingId` int NOT NULL,
  `Importance` int NOT NULL DEFAULT '0',
  `InvoiceId` int NOT NULL,
  `Status` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
