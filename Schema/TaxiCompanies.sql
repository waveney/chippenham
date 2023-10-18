CREATE TABLE `TaxiCompanies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Authority` tinyint NOT NULL,
  `SN` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Phone` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Website` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
