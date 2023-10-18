CREATE TABLE `SystemData` (
  `FestName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ShortName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `PlanYear` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ShowYear` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `id` int NOT NULL AUTO_INCREMENT,
  `Capabilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Features` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `CurVersion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `VersionDate` int NOT NULL,
  `HostURL` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Analytics` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `FooterText` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
