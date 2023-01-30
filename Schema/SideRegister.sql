CREATE TABLE `SideRegister` (
  `id` int NOT NULL AUTO_INCREMENT,
  `State` int NOT NULL,
  `SN` text NOT NULL,
  `Contact` text NOT NULL,
  `Email` text NOT NULL,
  `VerifyReason` text NOT NULL,
  `Description` text NOT NULL,
  `Type` text NOT NULL,
  `Website` text NOT NULL,
  `YouTube` text NOT NULL,
  `Phone` text NOT NULL,
  `Mobile` text NOT NULL,
  `DateSubmitted` int NOT NULL,
  `History` text NOT NULL,
  `SideId` int NOT NULL,
  `Reason` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
