CREATE TABLE `OtherPayments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Amount` int NOT NULL,
  `State` tinyint NOT NULL,
  `Year` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `IssueDate` int NOT NULL,
  `Source` int NOT NULL,
  `SourceId` int NOT NULL,
  `Notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `DueDate` int NOT NULL,
  `PayDate` int NOT NULL,
  `SN` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `PaidTotal` int NOT NULL,
  `History` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
