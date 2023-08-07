CREATE TABLE `Campsites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Name` text CHARACTER SET latin7 COLLATE latin7_general_ci NOT NULL,
  `Postcode` text COLLATE latin1_general_ci NOT NULL,
  `Address` text COLLATE latin1_general_ci NOT NULL,
  `ShortDesc` text COLLATE latin1_general_ci NOT NULL,
  `LongDesc` text COLLATE latin1_general_ci NOT NULL,
  `Props` int NOT NULL,
  `Image` text COLLATE latin1_general_ci NOT NULL,
  `Importance` int NOT NULL,
  `Restriction` text COLLATE latin1_general_ci NOT NULL,
  `Comment` text COLLATE latin1_general_ci NOT NULL,
  `MapPoint` int NOT NULL,
  `RulesName` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
