CREATE TABLE `CollectingUse` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Year` text COLLATE latin1_general_ci NOT NULL,
  `AssignType` int NOT NULL,
  `AssignTo` int NOT NULL,
  `AssignName` text COLLATE latin1_general_ci NOT NULL,
  `Value` int NOT NULL,
  `TimeOut` int NOT NULL,
  `TimeIn` int NOT NULL,
  `Notes` text COLLATE latin1_general_ci NOT NULL,
  `CollectionUnitId` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
