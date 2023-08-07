CREATE TABLE `Tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Year` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `SN` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `Type` tinyint NOT NULL,
  `Carer` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
