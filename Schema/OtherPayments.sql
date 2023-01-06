CREATE TABLE `OtherPayments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Code` text NOT NULL,
  `Amount` int NOT NULL,
  `State` tinyint NOT NULL,
  `Year` int NOT NULL,
  `IssueDate` int NOT NULL,
  `Source` int NOT NULL,
  `SourceId` int NOT NULL,
  `Notes` text NOT NULL,
  `DueDate` int NOT NULL,
  `PayDate` int NOT NULL,
  `SN` text NOT NULL,
  `Reason` text NOT NULL,
  `PaidTotal` int NOT NULL,
  `History` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
