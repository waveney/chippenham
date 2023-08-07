CREATE TABLE `OtherPayments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Code` text COLLATE latin1_general_ci NOT NULL,
  `Amount` int NOT NULL,
  `State` tinyint NOT NULL,
  `Year` text COLLATE latin1_general_ci NOT NULL,
  `IssueDate` int NOT NULL,
  `Source` int NOT NULL,
  `SourceId` int NOT NULL,
  `Notes` text COLLATE latin1_general_ci NOT NULL,
  `DueDate` int NOT NULL,
  `PayDate` int NOT NULL,
  `SN` text COLLATE latin1_general_ci NOT NULL,
  `Reason` text COLLATE latin1_general_ci NOT NULL,
  `PaidTotal` int NOT NULL,
  `History` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
