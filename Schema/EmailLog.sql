CREATE TABLE `EmailLog` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Date` int NOT NULL,
  `FromAddr` text NOT NULL,
  `ToAddr` text NOT NULL,
  `Subject` text NOT NULL,
  `TextBody` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
