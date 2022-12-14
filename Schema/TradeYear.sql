CREATE TABLE `TradeYear` (
  `TYid` int NOT NULL AUTO_INCREMENT,
  `Tid` int NOT NULL,
  `Year` text NOT NULL,
  `Days` tinyint NOT NULL,
  `Insurance` tinyint NOT NULL,
  `RiskAssessment` tinyint NOT NULL,
  `HealthChecked` tinyint NOT NULL,
  `PitchSize0` text NOT NULL,
  `PitchSize1` text NOT NULL,
  `PitchSize2` text NOT NULL,
  `Power0` int NOT NULL,
  `Power1` int NOT NULL,
  `Power2` int NOT NULL,
  `PitchNum0` text NOT NULL,
  `PitchNum1` text NOT NULL,
  `PitchNum2` text NOT NULL,
  `PitchLoc0` smallint NOT NULL,
  `PitchLoc1` smallint NOT NULL,
  `PitchLoc2` smallint NOT NULL,
  `BookingState` tinyint NOT NULL,
  `Fee` int NOT NULL,
  `TotalPaid` int NOT NULL,
  `YNotes` text NOT NULL,
  `PNotes` text NOT NULL,
  `Date` int NOT NULL,
  `History` text NOT NULL,
  `SentInvite` int NOT NULL,
  `SentConfirm` int NOT NULL,
  `SentLocation` int NOT NULL,
  `SentArrive` int NOT NULL,
  `DepositCode` text NOT NULL,
  `BalanceCode` text NOT NULL,
  `OtherCode` text NOT NULL,
  `DateChange` int NOT NULL,
  PRIMARY KEY (`TYid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
