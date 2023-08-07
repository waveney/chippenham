CREATE TABLE `UserCap` (
  `id` int NOT NULL AUTO_INCREMENT,
  `User` int NOT NULL,
  `Capability` int NOT NULL,
  `Level` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
