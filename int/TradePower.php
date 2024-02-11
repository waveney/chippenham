<?php
  include_once("fest.php");
  A_Check('Committee','Trade');

  dostaffhead("Manage Trade Power and Prices");
  global $PLANYEAR,$TradeTypeStates;

  include_once("TradeLib.php");
  include_once("InvoiceLib.php");
  
  echo "<div class=content><h2>Manage Trade Power and Prices</h2>\n";
  
  $Trads = Gen_Get_All('TradePower');
  if (UpdateMany('TradePower','',$Trads,0)) $Trads=Gen_Get_All('TradePower');

  echo "This is for Power.  Properties bit map of locns<p>\n";
  
  echo "If cosst <0 then not available as a trade option<p>\n";

  
  $coln = 0;
  $t = [];
  
  echo "<form method=post>";
  echo "<div class=Scrolltable+><table id=indextable border>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Index</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Properties</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Cost</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Amps</a>\n";
  echo "</thead><tbody>";
  if ($Trads) foreach($Trads as $t) {
    $i = $t['id'];
    echo "<tr><td>$i" . fm_text1("",$t,'Name',1,'','',"Name$i");
    echo fm_number1('',$t,'Props','','',"Props$i");
    echo fm_number1('',$t,'Cost','','',"Cost$i");
    echo fm_number1('',$t,'Amps','','',"Amps$i");
    echo "\n";
  }
  echo "<tr><td><td><input type=text size=16 name=Name0 >";
  echo "<td><input type=number name=Props0>";
  echo "<td><input type=number name=Cost0>";
  echo "<td><input type=number name=Amps0>";
  echo "</table></div>\n";
  echo "<input type=submit name=Update value=Update>\n";
  echo "</form></div>";

  dotail();

?>
