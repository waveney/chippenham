CREATE TABLE `CollectingUnit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Name` text COLLATE latin1_general_ci NOT NULL,
  `Type` int NOT NULL,
  `Status` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
