CREATE TABLE `News` (
  `id` int NOT NULL AUTO_INCREMENT,
  `display` tinyint NOT NULL,
  `SN` text COLLATE utf8mb4_general_ci,
  `Type` tinyint NOT NULL,
  `content` text COLLATE utf8mb4_general_ci,
  `image` text COLLATE utf8mb4_general_ci,
  `caption` text COLLATE utf8mb4_general_ci,
  `author` text COLLATE utf8mb4_general_ci,
  `created` int NOT NULL,
  `Link` text COLLATE utf8mb4_general_ci,
  `LinkText` text COLLATE utf8mb4_general_ci,
  `Participant` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
