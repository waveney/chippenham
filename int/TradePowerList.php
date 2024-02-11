<?php
  include_once("fest.php");
  A_Check('Steward');

  dostaffhead("List of Power Requirements");
  global $PLANYEAR,$TradeTypeStates,$Trade_States,$db;

  include_once("TradeLib.php");
  
  echo "<div class=content><h2>List of Power Requirements</h2>\n";
  
  $Powers = Gen_Get_All('TradePower');
  $qry = "SELECT y.*, t.* FROM Trade AS t LEFT JOIN TradeYear AS y ON t.Tid=y.Tid AND y.Year='$PLANYEAR' " .
         "WHERE (y.BookingState=6 OR y.BookingState=7 OR y.BookingState=8 OR y.BookingState=9 OR y.BookingState=11) AND y.Power0>0 ORDER BY SN";

  $Trads = $db->query($qry);
  
  $TradeTypeData = Get_Trade_Types(1);
  $TradeLocData = Get_Trade_Locs(1); 
  
  $coln = 0;
  $t = [];
  
  echo "<form method=post>";
  echo "<div class=Scrolltable+><table id=indextable border>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Type</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Book State</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Location</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Stall</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Amps</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Phases</a>\n";
  echo "</thead><tbody>";
  
  echo "<tr><th colspan=6><h2>Traders</h2>";
  if ($Trads) foreach($Trads as $t) {
    echo "<tr><td>" . $t['SN'] . "<td>" . $TradeTypeData[$t['TradeType']]['SN'] . "<td>" . $Trade_States[$t['BookingState']] .
         "<td>" . $TradeLocData[$t['PitchLoc0']]['SN'] . "<td>" . (empty($t['PitchNum0'])?'Not Assigned':$t['PitchNum0']) . 
         "<td>" . $Powers[$t['Power0']]['Amps'] . "<td>" . $Powers[$t['Power0']]['Phases'] . "\n";
  }

    echo "<tr><th colspan=6><h2>Infrastructure</h2>";
    
  $Infs = Gen_Get_Cond('Infrastructure',"Power>1");
  echo "<tr><td>Name<td><td>From<td>To<td>Number<td>Amps<td>Phases\n";
  
  foreach ($Infs as $In) {
    echo "<tr><td>" . $In['Name'] . "<td><td>" . ($In['PowerFrom']??'') . "<td>" . ($In['PowerTo']??'') . "<td>" . $In['NumberPower'] . "<td>" .
         $Powers[$In['Power']]['Amps'] . "<td>" . $Powers[$In['Power']]['Phases'] . "\n";
  }
  
  echo "</table></div>\n";
  echo "</div>";

  dotail();
