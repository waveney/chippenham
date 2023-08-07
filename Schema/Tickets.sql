CREATE TABLE `Tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Year` text COLLATE utf8mb4_general_ci,
  `SN` text COLLATE utf8mb4_general_ci,
  `Type` tinyint NOT NULL,
  `Carer` text COLLATE utf8mb4_general_ci,
  `Notes` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
