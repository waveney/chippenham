CREATE TABLE `VolCats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ShortName` text NOT NULL,
  `Name` text NOT NULL,
  `Description` text NOT NULL,
  `Listofwhen` text NOT NULL,
  `Email` text NOT NULL,
  `Props` int NOT NULL,
  `OtherQ1` text NOT NULL,
  `OtherQ2` text NOT NULL,
  `OtherQ3` text NOT NULL,
  `OtherQ4` text NOT NULL,
  `Q4Extra` text NOT NULL,
  `LExtra` text NOT NULL,
  `DExtra` text NOT NULL,
  `Q1Extra` text NOT NULL,
  `Q2Extra` text NOT NULL,
  `Q3Extra` text NOT NULL,
  `Image` text NOT NULL,
  `LongDesc` text NOT NULL,
  `Importance` int NOT NULL,
  `VolName` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
