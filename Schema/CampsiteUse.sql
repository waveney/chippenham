CREATE TABLE `CampsiteUse` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE latin1_general_ci NOT NULL,
  `Number` int NOT NULL,
  `Who` text COLLATE latin1_general_ci NOT NULL,
  `Priority` int NOT NULL,
  `Year` text COLLATE latin1_general_ci NOT NULL,
  `Notes` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
