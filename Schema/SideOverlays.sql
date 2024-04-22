CREATE TABLE `SideOverlays` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SideId` int NOT NULL,
  `Festival` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `SN` text COLLATE utf8mb4_general_ci NOT NULL,
  `IsType` text COLLATE utf8mb4_general_ci NOT NULL,
  `Description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Blurb` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Photo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Website` text COLLATE utf8mb4_general_ci NOT NULL,
  `Twitter` text COLLATE utf8mb4_general_ci NOT NULL,
  `Facebook` text COLLATE utf8mb4_general_ci NOT NULL,
  `Instagram` text COLLATE utf8mb4_general_ci NOT NULL,
  `Spotify` text COLLATE utf8mb4_general_ci NOT NULL,
  `ImageHeight` int NOT NULL,
  `ImageWidth` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
