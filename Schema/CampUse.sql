CREATE TABLE `CampUse` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SideYearId` int NOT NULL,
  `CampSite` int NOT NULL,
  `Number` int NOT NULL,
  `CampType` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
