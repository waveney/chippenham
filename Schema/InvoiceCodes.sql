CREATE TABLE `InvoiceCodes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Code` int NOT NULL,
  `SN` text COLLATE latin1_general_ci NOT NULL,
  `Notes` text COLLATE latin1_general_ci NOT NULL,
  `Hide` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
