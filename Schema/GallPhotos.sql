CREATE TABLE `GallPhotos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Galid` int NOT NULL,
  `Credit` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `File` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Caption` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `RelOrder` int NOT NULL,
  `ImageHeight` int NOT NULL,
  `ImageWidth` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
