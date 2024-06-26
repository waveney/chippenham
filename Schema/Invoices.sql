CREATE TABLE `Invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `State` tinyint NOT NULL,
  `BZ` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Contact` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Phone` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Mobile` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `PostCode` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `IssueDate` int NOT NULL,
  `EmailDate` int NOT NULL,
  `DueDate` int NOT NULL,
  `PayDate` int NOT NULL,
  `InvoiceCode` int NOT NULL,
  `InvoiceCode2` int NOT NULL,
  `InvoiceCode3` int NOT NULL,
  `OurRef` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `YourRef` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Total` int NOT NULL,
  `PaidTotal` int NOT NULL,
  `Desc1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Amount1` int NOT NULL,
  `Desc2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Amount2` int NOT NULL,
  `Desc3` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Amount3` int NOT NULL,
  `Source` int NOT NULL,
  `History` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Year` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `SourceId` int NOT NULL,
  `Reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `CNReason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Revision` int NOT NULL,
  `CoverNote` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
