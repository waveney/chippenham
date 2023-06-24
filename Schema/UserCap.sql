CREATE TABLE `UserCap` (
  `id` int NOT NULL AUTO_INCREMENT,
  `User` int NOT NULL,
  `Capability` int NOT NULL,
  `Level` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
