CREATE TABLE `EmailLog` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Date` int NOT NULL,
  `FromAddr` text COLLATE utf8mb4_general_ci,
  `ToAddr` text COLLATE utf8mb4_general_ci,
  `Subject` text COLLATE utf8mb4_general_ci,
  `TextBody` text COLLATE utf8mb4_general_ci,
  `Type` int NOT NULL,
  `TypeId` int NOT NULL,
  `Attachments` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
