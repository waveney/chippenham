<?php
include_once("fest.php");
include_once("VolLib.php");
global $YEARDATA,$PLANYEAR,$Months;


dostaffhead("Volunteer signup Rates");
//$Months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

$Years = [];
//Scan data build up table of Month/number

$When = [];
$Vols = Gen_Get_Cond('VolYear',"SubmitDate>0");
foreach ($Vols as $V) {
  $mon = date('n', $V['SubmitDate']);
  $Yr = $V['Year'];
  
  $Years[$Yr]=1;
//  var_dump($mon);
  if($When[$Yr][$mon]??0) {
    $When[$Yr][$mon]++;
  } else {
    $When[$Yr][$mon]=1;
  }
}

//var_dump($When);

$Fests = Gen_Get_Cond("General","Year<='$PLANYEAR'");

echo "<table border><tr><th>YEAR";
for($mon = 0; $mon<12; $mon++) {
  $M = ($mon + $YEARDATA['MonthFri'])%12;
  echo "<th>" . $Months[$M+1];
}
echo "<th>Total";

foreach($Fests as $F) {
  $Total = 0;
  $Yr = $F['Year'];
  if (!($Years[$Yr]??0)) continue;

  echo "<tr><td>$Yr";
  for($mon = 0; $mon<12; $mon++) {
    $M = ($mon + $YEARDATA['MonthFri'])%12;
    $Num = ($When[$Yr][$M+1]??0);
    $Total += $Num;
    echo "<td>$Num";
  }
    echo "<td>$Total";
}

echo "</table>";

dotail();

