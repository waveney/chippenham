CREATE TABLE `EventChanges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `EventId` int NOT NULL,
  `Year` text COLLATE latin1_general_ci NOT NULL,
  `Field` text COLLATE latin1_general_ci NOT NULL,
  `Changes` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
