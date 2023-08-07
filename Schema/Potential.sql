CREATE TABLE `Potential` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE latin1_general_ci NOT NULL,
  `Description` text COLLATE latin1_general_ci NOT NULL,
  `Contact` text COLLATE latin1_general_ci NOT NULL,
  `Email` text COLLATE latin1_general_ci NOT NULL,
  `Website` text COLLATE latin1_general_ci NOT NULL,
  `YouTube` text COLLATE latin1_general_ci NOT NULL,
  `Phone` text COLLATE latin1_general_ci NOT NULL,
  `Mobile` text COLLATE latin1_general_ci NOT NULL,
  `Type` text COLLATE latin1_general_ci NOT NULL,
  `Status` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
