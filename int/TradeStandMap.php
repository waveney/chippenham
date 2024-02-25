<?php
  include_once("fest.php");

  dostaffhead("Trade Stand Map");

  include_once("TradeLib.php");
/* If logged in or trade stae >=partial view actual traders, otherwise just the grid */

  global $Pitches,$tloc,$loc,$YEARDATA,$EType_States,$Traders;

  $loc = $_REQUEST['l'] ?? Feature('TradeBaseMap');
  if (!is_numeric($loc)) Error_Page("No Hacking please");
  $Traders = [];
  if (Access('Staff') || $YEARDATA['TradeState']>= (array_flip($EType_States))['Partial']) $Traders = Get_Traders_For($loc, (Access('Staff')?1:0));
  $Pitches = Get_Trade_Pitches($loc);  

  $tloc = Get_Trade_Loc($loc);
  
  if(!$tloc) Error_Page("Unknown Map");
  
  if (Access('Staff')) echo "Any Trader in White has not PAID<p>";
      
  Pitch_Map($tloc,$Pitches,$Traders,1,1,1);
  if ($loc != Feature('TradeBaseMap')) echo "<h2><a href=TradeStandMap>Return to main map</a></h2>";

  dotail();
  