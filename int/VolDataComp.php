<?php
  include_once("fest.php");
  A_Check('SysAdmin');
  dostaffhead('Fix Volunteer Data');
  global $YEAR,$db;
  include_once("VolLib.php");
  include_once("festsaveddb.php");

  dostaffhead("Fix Data Stage 1");
  
  // Fix ContactName/Number

  
  $Vols = Gen_Get_All('Volunteers');
/*  
  foreach($Vols as $Vid=>$V) {
    $OV = Gen2_Get('Volunteers',$Vid);
    if (!$OV) {
      echo "No old data for <a href=Volunteers?A=Show&id=$Vid>$Vid - " . $V['SN'] . "</a><br>";
    }  else {
      $V['ContactName'] = $OV['ContactName'];
      Put_Volunteer($V);
      echo "Updated $Vid - " . $V['SN'] . "<br>";
    }
  }*/

  // check Vol Year 
  
  $VYs = Gen_Get_Cond("VolYear",'Year=2026');
/*  
  foreach($VYs as $Yid=>$VY) {
    if (empty($Vols[$VY['Volid']])) {
      echo "Deleting Year Record for $Yid<br>";
//      db_delete("VolYear",$Yid);
      continue;
    }
    $SVY = Gen2_Get('VolYear',$Yid);
    if (!$SVY) {
      echo "No old record for $Yid<br>";
    } else {
      if ($VY == $SVY) {
        echo "All is fine for $Yid<br>";
      } else {
        echo "Changes for $Yid:<br>";
        var_dump($VY);
        echo "<br>";
        var_dump($SVY);
        echo "<p>";
      }
    }
    
  }
  */
  
  $VCYs = Gen_Get('VolCatYear','Year=2026');
  
  
  
  
  // Display changes

  echo "<h2>All Done</h2>";
  dotail();

?>
