<?php

include_once("fest.php");
include_once("TradeLib.php");

$Trad=Get_Trader_All($_REQUEST['I']);
$Trade_Types = Get_Trade_Types(1);
$TradePowers = Gen_Get_All('TradePower');


echo "<b>" . $Trad['SN'] . "</b> - " . "<span style='background:" . $Trade_Types[$Trad['TradeType']]['Colour'] . "'>" . 
     $Trade_Types[$Trad['TradeType']]['SN'] . "</span><br>";
echo "Goods: " . $Trad['GoodsDesc'] . "<br>";
if ($Trad['Notes']) echo "Notes: " . $Trad['Notes'] . "<br>";
echo "Pitches: ";
for ($i=0;$i<3;$i++) {
  if ($Trad["PitchLoc$i"]) {
    echo $Trad["PitchSize$i"] . " at " . $TradeLocData[$Trad["PitchLoc$i"]]['SN'];
    if ($Trad["Power$i"] > 1) echo " with " . $TradePowers[$Trad["Power$i"]]['Name'];
    echo "<br>";
  }
}
if ($Trad['ExtraPowerDesc']) echo "Also: " . $Trad['ExtraPowerDesc'] . "<br>";
