CREATE TABLE `Sponsorship` (
  `id` int DEFAULT NULL,
  `Year` int NOT NULL,
  `SponsorId` int NOT NULL,
  `ThingType` int NOT NULL,
  `ThingId` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
