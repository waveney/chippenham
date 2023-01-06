CREATE TABLE `InvoiceCodes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Code` int NOT NULL,
  `SN` text NOT NULL,
  `Notes` text NOT NULL,
  `Hide` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
