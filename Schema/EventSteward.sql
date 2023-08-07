CREATE TABLE `EventSteward` (
  `id` int NOT NULL AUTO_INCREMENT,
  `HowMany` text COLLATE latin1_general_ci NOT NULL,
  `HowWent` text COLLATE latin1_general_ci NOT NULL,
  `Name` text COLLATE latin1_general_ci NOT NULL,
  `RandId` int NOT NULL,
  `EventId` int NOT NULL,
  `SubEvent` int NOT NULL,
  `Year` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
