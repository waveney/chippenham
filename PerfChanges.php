<?php
  include_once("int/fest.php");

  include_once("int/ProgLib.php");
  include_once("int/DateTime.php");
  include_once("int/DispLib.php");
  include_once("int/DanceLib.php");
  include_once("int/MusicLib.php");

  global $db,$YEAR,$SHOWYEAR,$YEARDATA,$DayList,$DayLongList,$Event_Types ;

  $Vens = Get_Venues(1);

  /* Get All Perf Changes
     Get the Perf, sort by name
     Report changes in that order */
     
  $PChanges = Gen_Get_Cond('PerfChanges',"Year='$YEAR' ORDER by SideId");
  
  $Events = [];
  $LastPerf = 0;

  dohead("Lineup changes since the programme went to print",[],1);
  
  if (!$PChanges) {
    echo "<h2>No sigificant changes in performers have been recorded</h2>";
    dotail();
  }
  
  echo "Click on the performers name to find out what they are now doing and when.<p>\n";
  
  foreach ($PChanges as $PC) {
    if ($PC['SideId'] == $LastPerf) {
      $Perfs[$LastPerf]['Changes'][] = $PC;
    } else {
      $LastPerf = $PC['SideId'];
      $Res = $Perfs[$LastPerf] = Get_SideAndYear($LastPerf);
      if (!$Res && Access('SysAdmin')) {
        echo "<span class=Err>Error Performer $LastPerf not found</span><br>";
        continue;
      }
      $Perfs[$LastPerf] = $Res;
      $Perfs[$LastPerf]['Changes'] = [$PC];
    }
  }

  function cmp($a,$b) {
    if ($a['SN'] != $b['SN']) return ($a['SN'] < $b['SN']) ? -1 : 1;
    return 0;
  }
  
  uasort($Perfs, 'cmp');
  
  echo "<table border style='min-width:1200'><td>Name<td>Changes\n";
  foreach ($Perfs as $snum=>$p) {
    
    echo "<tr><td><a href=/int/ShowPerf?id=$snum&Y=$YEAR>" . $p['SN'] . "</a><td>";
//var_dump($p['Changes']);
    $Chtxt = [];
    foreach ($p['Changes'] as $Ch) {
      switch ($Ch['Field']) {
      case 'Missed' :
        $Chtxt[1]= "Missed in the programme";
        break;
      case 'New' :
        $Chtxt[0] = 'New Performer';
        break;
      case 'Sat' :
      case 'Sun' :
      case 'Mon' :
        $Chtxt[2] = 'Changed days';
        break;
      case 'Perform' :
        $Chtxt[3] = 'Changed Performances';
        break;
      case 'Coming' :
        if ($p['Coming'] == 2) {
          $Chtxt[4] = 'New Performer';
        } else {
          $Chtxt[4] = 'Cancelled';
        }
        break;
      case 'SN' :
        $Chtxt[5] = 'Changed Name';
        break;
      case 'YearState' :
        if ($p['YearState'] >= 2) {
          $Chtxt[4] = 'New Performer';
        } else {
          $Chtxt[4] = 'Cancelled';
        }
        break;
      }
// var_dump($Ch, $Chtxt) ;
    }
    echo implode(", ",$Chtxt);
      
  }
  echo "</table></div>\n";
  dotail();

?>
