CREATE TABLE `VenueYear` (
  `id` int NOT NULL AUTO_INCREMENT,
  `VenueId` int NOT NULL,
  `Year` int NOT NULL,
  `Complete` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
