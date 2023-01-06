CREATE TABLE `TsAndCs` (
  `TradeTnC` text NOT NULL,
  `VolTnC` text NOT NULL,
  `PerfTnC` text NOT NULL,
  `DummyContract` text NOT NULL,
  `DanceFAQ` text NOT NULL,
  `MusicFAQ` text NOT NULL,
  `TradeFAQ` text NOT NULL,
  `TicketTnC` text NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  `TradeTimes` text NOT NULL,
  `CampGen` text NOT NULL,
  `MailChimp` text NOT NULL,
  `TicketHeader` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
