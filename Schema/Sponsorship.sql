CREATE TABLE `Sponsorship` (
  `id` int DEFAULT NULL,
  `Year` int NOT NULL,
  `SponsorId` int NOT NULL,
  `ThingType` int NOT NULL,
  `ThingId` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
