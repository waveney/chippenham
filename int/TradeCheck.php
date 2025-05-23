<?php
include_once("fest.php");
A_Check('Committee','Trade');

dostaffhead("Check Trade Finances");
global $PLANYEAR,$TradeTypeStates,$YEAR,$Trade_State,$Trade_States,$Trade_State_Colours,$db;

include_once("TradeLib.php");
include_once("InvoiceLib.php");

$Trade_Types = Get_Trade_Types(1);

$TradePowers = Gen_Get_All('TradePower');
$qry = "SELECT t.*, y.* FROM Trade AS t, TradeYear AS y WHERE (t.Status!=2 || y.ShowAnyway) AND t.Tid = y.Tid AND y.Year='$YEAR' AND y.BookingState>" .
    $Trade_State['Submitted'] . " ORDER BY SN";
    $res = $db->query($qry);
    
TableStart();
TableHead('id','N');
TableHead('Name');
TableHead('Type');
TableHead('State');
TableHead('Total Fees','N');
TableHead('TotalPaid','N');
TableTop();

while ($Tr = $res->fetch_assoc()) {
  $Tid = $Tr['Tid'];
  $stat = $Tr['BookingState'];
  echo "<tr><td>$Tid<td><a href=Trade?id=$Tid>" . $Tr['SN'] ."</a>" .
    "<td style='background:" . $Trade_Types[$Tr['TradeType']]['Colour'] . ";'>" . $Trade_Types[$Tr['TradeType']]['SN'] .
    "<td style='background:" . $Trade_State_Colours[$stat] . "'>" . $Trade_States[$stat];
  $TotPowerCost = PowerCost($Tr);
  $TableCost = TableCost($Tr);
  if (($Tr['Fee']??0) < 0) $TotPowerCost = $TableCost = 0;
  $TotalFee = (($Tr['Fee'] ?? 0) +  ($Tr['ExtraPowerCost']??0) + $TotPowerCost + $TableCost);
  
  echo "<td>$TotalFee<td>" . $Tr['TotalPaid'];
}

TableEnd();

dotail();

  