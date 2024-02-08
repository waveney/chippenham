CREATE TABLE `TradeLog` (
  `id` int DEFAULT NULL,
  `Tid` int NOT NULL,
  `TYid` int NOT NULL,
  `WhenHappen` int NOT NULL,
  `Message` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
