CREATE TABLE `FestUsers` (
  `UserId` int NOT NULL AUTO_INCREMENT,
  `Login` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `password` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `AccessLevel` int NOT NULL,
  `Roll` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `SN` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Abrev` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Phone` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Docs` tinyint NOT NULL,
  `Dance` tinyint NOT NULL,
  `Music` tinyint NOT NULL,
  `Trade` tinyint NOT NULL,
  `Users` tinyint NOT NULL,
  `Venues` tinyint NOT NULL,
  `Sponsors` tinyint NOT NULL,
  `Finance` tinyint NOT NULL,
  `Craft` tinyint NOT NULL,
  `Other` tinyint NOT NULL,
  `TLine` tinyint NOT NULL,
  `Bugs` tinyint NOT NULL,
  `Photos` tinyint NOT NULL,
  `Comedy` tinyint NOT NULL,
  `Family` tinyint NOT NULL,
  `News` tinyint NOT NULL,
  `ChangeSent` int NOT NULL,
  `AccessKey` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `LastAccess` int NOT NULL,
  `Yale` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `FestEmail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Contacts` tinyint NOT NULL,
  `NoTasks` tinyint NOT NULL,
  `Prefs` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `LogUse` tinyint NOT NULL,
  `RelOrder` int NOT NULL,
  `ClassEmail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `DefaultBudget` int NOT NULL,
  `ErrorCount` int NOT NULL,
  PRIMARY KEY (`UserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
