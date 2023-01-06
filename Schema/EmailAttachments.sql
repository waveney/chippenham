CREATE TABLE `EmailAttachments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `EmailId` int NOT NULL,
  `AttName` int NOT NULL,
  `AttBody` blob NOT NULL,
  `AttFileName` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
