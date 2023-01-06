CREATE TABLE `News` (
  `id` int NOT NULL AUTO_INCREMENT,
  `display` tinyint NOT NULL,
  `SN` text COLLATE latin1_general_ci,
  `Type` tinyint NOT NULL,
  `content` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `image` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `caption` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `author` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `created` int NOT NULL,
  `Link` text COLLATE latin1_general_ci NOT NULL,
  `LinkText` text COLLATE latin1_general_ci NOT NULL,
  `Participant` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
