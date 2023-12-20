<?php
  include_once("fest.php");
  A_Check('SysAdmin');
  dostaffhead('Import Old Trade Data');
  global $YEAR,$db;
  include_once("TradeLib.php");

  dostaffhead("Fix Access Keys");
  $Trads = Gen_Get_All('Trade','','Tid');
  
  foreach ($Trads as $T) {
    if (empty($T['AccessKey'])) {
      $T['AccessKey'] = rand_string(40);
      Put_Trader($T);
      echo "Fixed " . $T['Tid'] . "<br>";
    }
  }
  
  echo "Done<p>";
  dotail();
  
?>

