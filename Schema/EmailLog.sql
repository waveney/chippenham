CREATE TABLE `EmailLog` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Date` int NOT NULL,
  `FromAddr` text COLLATE latin1_general_ci NOT NULL,
  `ToAddr` text COLLATE latin1_general_ci NOT NULL,
  `Subject` text COLLATE latin1_general_ci NOT NULL,
  `TextBody` text COLLATE latin1_general_ci NOT NULL,
  `Type` int NOT NULL,
  `TypeId` int NOT NULL,
  `Attachments` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
