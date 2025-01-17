CREATE TABLE `TradeHistory` (
  `id` int NOT NULL,
  `Trader` int NOT NULL,
  `TradeYear` int NOT NULL,
  `Text` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
