CREATE TABLE `Sponsorship` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Year` text COLLATE utf8mb4_general_ci,
  `SponsorId` int NOT NULL,
  `ThingType` int NOT NULL,
  `ThingId` int NOT NULL,
  `Importance` int NOT NULL DEFAULT '0',
  `InvoiceId` int NOT NULL,
  `Status` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
