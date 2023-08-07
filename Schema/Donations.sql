CREATE TABLE `Donations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `InUse` int NOT NULL,
  `Image` text COLLATE latin1_general_ci NOT NULL,
  `Value` text COLLATE latin1_general_ci NOT NULL,
  `Text` text COLLATE latin1_general_ci NOT NULL,
  `ButtonId` text COLLATE latin1_general_ci NOT NULL,
  `Importance` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
