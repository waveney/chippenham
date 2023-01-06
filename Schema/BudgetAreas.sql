CREATE TABLE `BudgetAreas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE latin1_general_ci NOT NULL,
  `Year` text COLLATE latin1_general_ci NOT NULL,
  `Budget` int NOT NULL,
  `CommittedSoFar` int NOT NULL,
  `Who` int NOT NULL,
  `Who2` int NOT NULL,
  `Who3` int NOT NULL,
  `Who4` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
