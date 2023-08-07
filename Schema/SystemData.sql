CREATE TABLE `SystemData` (
  `FestName` text COLLATE latin1_general_ci NOT NULL,
  `ShortName` text COLLATE latin1_general_ci NOT NULL,
  `PlanYear` text COLLATE latin1_general_ci NOT NULL,
  `ShowYear` text COLLATE latin1_general_ci NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  `Capabilities` text COLLATE latin1_general_ci NOT NULL,
  `Features` text COLLATE latin1_general_ci NOT NULL,
  `CurVersion` text COLLATE latin1_general_ci NOT NULL,
  `VersionDate` int NOT NULL,
  `HostURL` text COLLATE latin1_general_ci NOT NULL,
  `Analytics` text COLLATE latin1_general_ci NOT NULL,
  `FooterText` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
