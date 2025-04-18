CREATE TABLE `FoodAndDrink` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SN` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Website` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `PostCode` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Phone` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Photo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Lat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Lng` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Vegan` tinyint NOT NULL,
  `Year` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Vegetarian` tinyint NOT NULL,
  `Food` tinyint NOT NULL,
  `Drink` tinyint NOT NULL,
  `Notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Importance` int NOT NULL,
  `MapImp` int NOT NULL,
  `Directions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Type` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
