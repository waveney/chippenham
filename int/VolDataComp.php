<?php
  include_once("fest.php");
  A_Check('SysAdmin');
  dostaffhead('Fix Volunteer Data');
  global $YEAR,$db;
  include_once("VolLib.php");
  include_once("festsaveddb.php");

  dostaffhead("Fix Data Stage 1");
  
  // Fix ContactName/Number
  
  $Vs = Gen_Get_All('Volunteers');
  
  foreach($Vs as $Vid=>$V);
    $OV = Gen2_Get('Volunteers',$Vid);
    if (!$OV) {
      echo "No old data for <a href=Volunteers?A=Show&id=$Vid>$Vid - " . $V['SN'] . "</a><br>";
    }  else {
      $V['ContactName'] = $OV['ContactName'];
      Put_Volunteer($V);
      echo "Updated $Vid - " . $V['SN'] . "<br>";
    }
          

  // Display changes

  echo "<h2>All Done</h2>";
  dotail();

?>
