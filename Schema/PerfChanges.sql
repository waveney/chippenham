CREATE TABLE `PerfChanges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SideId` int NOT NULL,
  `syId` int NOT NULL,
  `Year` text COLLATE latin1_general_ci NOT NULL,
  `Field` text COLLATE latin1_general_ci NOT NULL,
  `Changes` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
