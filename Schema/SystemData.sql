CREATE TABLE `SystemData` (
  `FestName` text NOT NULL,
  `ShortName` text NOT NULL,
  `PlanYear` text NOT NULL,
  `ShowYear` text NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  `Capabilities` text NOT NULL,
  `Features` text NOT NULL,
  `HostURL` text NOT NULL,
  `Analytics` text NOT NULL,
  `FooterText` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
