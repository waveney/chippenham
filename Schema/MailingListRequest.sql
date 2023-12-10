CREATE TABLE `MailingListRequest` (
  `id` int NOT NULL AUTO_INCREMENT,
  `FirstName` text COLLATE utf8mb4_general_ci NOT NULL,
  `LastName` text COLLATE utf8mb4_general_ci NOT NULL,
  `Email` text COLLATE utf8mb4_general_ci NOT NULL,
  `Tags` text COLLATE utf8mb4_general_ci NOT NULL,
  `Notes` text COLLATE utf8mb4_general_ci NOT NULL,
  `SubmitTime` int NOT NULL,
  `Status` int NOT NULL,
  `AccessKey` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
