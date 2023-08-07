CREATE TABLE `Camptypes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Name` text COLLATE latin1_general_ci NOT NULL,
  `Comments` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
