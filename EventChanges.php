<?php
  include_once("int/fest.php");

  include_once("int/ProgLib.php");
  include_once("int/DateTime.php");
  include_once("int/DispLib.php");
  include_once("int/DanceLib.php");
  include_once("int/MusicLib.php");

  global $db,$YEAR,$SHOWYEAR,$YEARDATA,$DayList,$DayLongList,$Event_Types ;

  $Vens = Get_Venues(1);

  /* Get All Event Changes
     Get their events, sort by time/date
     Report changes in that order */
     
  $EChanges = Gen_Get_Cond('EventChanges',"Year='$YEAR' ORDER by EventId");
  
  $Events = [];
  $LastEvent = 0;

  dohead("Events changes since the programme went to print",[],1);

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

  function cmp($a,$b) {
    if ($a['Day'] != $b['Day']) return ($a['Day'] < $b['Day']) ? -1 : 1;
    if ($a['Start'] != $b['Start']) return ($a['Start'] < $b['Start']) ? -1 : 1;
    return 0;
  }
  
  uasort($Events, 'cmp');
  
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
          $Chtxt[4] = '?';
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
  dotail();

?>
