CREATE TABLE `OtherContact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Name` text COLLATE latin1_general_ci NOT NULL,
  `Phone` text COLLATE latin1_general_ci NOT NULL,
  `Email` text COLLATE latin1_general_ci NOT NULL,
  `ForType` int NOT NULL,
  `ForId` int NOT NULL,
  `Role` text COLLATE latin1_general_ci NOT NULL,
  `Notes` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
