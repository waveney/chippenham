CREATE TABLE `ContactCats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text NOT NULL,
  `OpenState` tinyint NOT NULL,
  `Description` text NOT NULL,
  `Email` text NOT NULL,
  `RelOrder` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
