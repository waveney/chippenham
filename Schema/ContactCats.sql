CREATE TABLE `ContactCats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text COLLATE utf8mb4_general_ci,
  `OpenState` tinyint NOT NULL,
  `Description` text COLLATE utf8mb4_general_ci,
  `Email` text COLLATE utf8mb4_general_ci,
  `RelOrder` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
