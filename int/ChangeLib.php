<?php
  include_once("fest.php");

  include_once("ProgLib.php");
  include_once("DateTime.php");
  include_once("DispLib.php");
  include_once("DanceLib.php");
  include_once("MusicLib.php");


function EventChangePrint($Mode=1) {
  global $db,$YEAR,$SHOWYEAR,$YEARDATA,$DayList,$DayLongList,$Event_Types ;

  $Vens = Get_Venues(1);

  /* Get All Event Changes
     Get their events, sort by time/date
     Report changes in that order */
     
  $EChanges = Gen_Get_Cond('EventChanges',"Year='$YEAR' ORDER by EventId");
  
  $Events = [];
  $LastEvent = 0;

  if (!$EChanges) {
    echo "<h2>No sigificant changes in events have been recorded</h2>";
    dotail();
  }
  
  foreach ($EChanges as $EC) {
    if ($EC['EventId'] == $LastEvent) {
      $Events[$LastEvent]['Changes'][] = $EC;
    } else {
      $LastEvent = $EC['EventId'];
      $Res = $Events[$LastEvent] = Get_Event($LastEvent);
      if (!$Res && Access('SysAdmin')) {
        echo "<span class=Err>Error Event $LastEvent not found</span><br>";
        continue;
      }
      $Events[$LastEvent] = $Res;
      $Events[$LastEvent]['Changes'] = [$EC];
    }
  }

  function Ecmp($a,$b) {
    if ($a['Day'] != $b['Day']) return ($a['Day'] < $b['Day']) ? -1 : 1;
    return ($a['Start'] <=> $b['Start']);
  }
  
  uasort($Events, 'Ecmp');
  
  foreach ($Events as $eid=>$e) {
    $dname = $DayLongList[$e['Day']];
    if (DayTable($e['Day'],"Events that have changed","","",'style=min-width:1200' )) {
      echo "<tr class=Day$dname ><td>Time<td >What<td>Where<td>Change(s)<td>With and/or Description<td>Price";
    }
    
    Get_Imps($e,$imps,1,(Access('Staff')?1:0));
    
    echo "<tr class=Day$dname><td>" . timecolon($e['Start']) . " - " . timecolon($e['End']); 
    echo "<td><a href=/int/EventShow?e=$eid>" . $e['SN'] . "</a>";

    if (isset($Vens[$e['Venue']]['SN'])) {
      echo "<td>" . Venue_Parents($Vens,$e['Venue']) . "<a href=/int/VenueShow?v=" . $e['Venue'] . ">" . $Vens[$e['Venue']]['SN'] . "</a>";
    } else {
      echo "<td>Unknown";
    }
    if ($e['BigEvent']) {
      $Others = Get_Other_Things_For($eid);
      foreach ($Others as $i=>$o) {
        if (($o['Type'] == 'Venue') && ($o['Identifier']>0)) echo ", " . Venue_Parents($Vens,$o['Identifier']) . "<a href=/int/VenueShow?v=" . $o['Identifier'] . ">" . 
          $Vens[$o['Identifier']]['SN'] . "</a>";
      }
    }
    echo "<td>";
    $Chtxt = [];
     
    foreach ($e['Changes'] as $Ch) {
      switch ($Ch['Field']) {
      case 'Missed' :
        $Chtxt[1]= "Missed in the programme";
        break;
      case 'New' :
        $Chtxt[0] = 'New Event';
        break;
      case 'Side1' :
      case 'Side2' :
      case 'Side3' :
      case 'Side4' :
        $Chtxt[2] = 'Changed Performers';
        break;
      case 'Day' :
        $Chtxt[3] = 'Changed Day';
        break;
      case 'Start' :
      case 'Emd' :
      case 'SlotEnd' :
        $Chtxt[3] = 'Changed Times';
        break;
      case 'Status' :
        if ($e['Status'] == 0) {
          $Chtxt[4] = 'Re-booked';
        } else {
          $Chtxt[4] = 'Cancelled';
        }
        break;
      case 'SN' :
        $Chtxt[5] = 'Changed Name';
        break;
      case 'Type' :
        $Chtxt[6] = 'Changed Type';
        break;
      }
    }
    echo implode(", ",$Chtxt);
      
    echo "<td>";
    if ($e['Description']) echo $e['Description'] . "<br>";
    echo  ($e['BigEvent'] ? Get_Other_Participants($Others,0,1,15,1,'',$e) : Get_Event_Participants($eid,0,1,15));
    echo "<td>" . Price_Show($e,1);   
  }
  echo "</table></div>\n";
}

function PerfChangePrint($Mode=1) {
  global $db,$YEAR,$SHOWYEAR,$YEARDATA,$DayList,$DayLongList,$Event_Types ;

  $Vens = Get_Venues(1);

  /* Get All Perf Changes
     Get the Perf, sort by name
     Report changes in that order */
     
  $PChanges = Gen_Get_Cond('PerfChanges',"Year='$YEAR' ORDER by SideId");
  
  $Events = [];
  $LastPerf = 0;

//  dohead("Lineup changes since the programme went to print",[],1);
  
  if (!$PChanges) {
    echo "<h2>No sigificant changes in performers have been recorded</h2>";
    dotail();
  }
  
  if ($Mode == 1) echo "Click on the performers name to find out what they are now doing and when.<p>\n";
  
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

  function Pcmp($a,$b) {
    return ($a['SN'] <=> $b['SN']);
  }
  
  uasort($Perfs, 'Pcmp');
  
  echo "<table border style='min-width:1200'><td>Performer<td>Changes\n";
  foreach ($Perfs as $snum=>$p) {
    

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
      case '+Sat' :
      case '+Sun' :
      case '+Mon' :
        $Chtxt[3] = 'Added days';
        break;
      case '-Sat' :
      case '-Sun' :
      case '-Mon' :
        $Chtxt[4] = 'Cancelled days';
        break;
      case 'Perform' :
        $Chtxt[5] = 'Changed Performances';
        break;
      case 'Coming' :
        if ($p['Coming'] == 2) {
          $Chtxt[6] = 'New Performer';
        } else {
          $Chtxt[6] = 'Cancelled';
        }
        break;
      case 'SN' :
        $Chtxt[7] = 'Changed Name';
        break;
      case 'YearState' :
        if ($p['YearState'] >= 2) {
          $Chtxt[6] = 'New Performer';
        } else {
          $Chtxt[6] = 'Cancelled';
        }
        break;
      }
// var_dump($Ch, $Chtxt) ;
    }
    
    if ($Chtxt) {
      echo "<tr><td><a href=/int/ShowPerf?id=$snum&Y=$YEAR>" . $p['SN'] . "</a><td>";
      echo implode(", ",$Chtxt);
    }  
  }
  echo "</table></div>\n";
//  dotail();
}

?>
