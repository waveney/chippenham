CREATE TABLE `InvoiceCodes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Code` int NOT NULL,
  `SN` text COLLATE utf8mb4_general_ci,
  `Notes` text COLLATE utf8mb4_general_ci,
  `Hide` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
