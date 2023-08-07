CREATE TABLE `PerformerTypes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE latin1_general_ci NOT NULL,
  `ListState` tinyint NOT NULL,
  `Year` text COLLATE latin1_general_ci NOT NULL,
  `FullName` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
