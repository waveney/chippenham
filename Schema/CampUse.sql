CREATE TABLE `CampUse` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SideYearId` int NOT NULL,
  `CampSite` int NOT NULL,
  `Number` int NOT NULL,
  `CampType` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
