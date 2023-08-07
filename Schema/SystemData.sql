CREATE TABLE `SystemData` (
  `FestName` text COLLATE utf8mb4_general_ci,
  `ShortName` text COLLATE utf8mb4_general_ci,
  `PlanYear` text COLLATE utf8mb4_general_ci,
  `ShowYear` text COLLATE utf8mb4_general_ci,
  `id` int NOT NULL AUTO_INCREMENT,
  `Capabilities` text COLLATE utf8mb4_general_ci,
  `Features` text COLLATE utf8mb4_general_ci,
  `CurVersion` text COLLATE utf8mb4_general_ci,
  `VersionDate` int NOT NULL,
  `HostURL` text COLLATE utf8mb4_general_ci,
  `Analytics` text COLLATE utf8mb4_general_ci,
  `FooterText` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
