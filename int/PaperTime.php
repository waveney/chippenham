<?php
  include_once("fest.php");

  A_Check('Staff');
  
  include_once("ProgLib.php");
  include_once("DispLib.php");
  include_once("DanceLib.php");
  include_once("MusicLib.php");
  dominimalhead("All Event by Time", ['css/PrintPage.css']);
  include_once("files/Newheader.php");
//  include_once("festcon.php");
    
  set_ShowYear();
 
  global $db,$YEAR,$PLANYEAR,$YEARDATA,$SHOWYEAR,$DayList,$DayLongList,$Event_Types ;

  echo "<div style='background:white'><div class=PaperP>";

  $Vens = Get_Venues(1);

  /* Get all events that are public, sort by day, time
     opening display is each day - click to expand 
     sub events not shown - click to expand
     More to come from event states and general
  */
  $More = 0; 

  if ($YEAR != $SHOWYEAR) echo "<h2>What is on When in " . substr($YEAR,0,4) . "?</h2>";
  echo "<div class='FullWidth WhenTable'>";
//  echo "<script src=/js/WhatsWhen.js></script>";
  $xtr = (isset($_GET['Mode']) || $YEAR<$PLANYEAR)?'':"AND ( e.Public=1 OR (e.Type=t.ETypeNo AND t.State>1 AND e.Public<2 ))";

  $res = $db->query("SELECT DISTINCT e.* FROM Events e, EventTypes t WHERE e.Year='$YEAR' AND (e.SubEvent<=0 OR e.LongEvent=1) AND t.Public=1 $xtr ORDER BY Day, Start");

    while( $e = $res->fetch_assoc()) {
      $eid = $e['EventId'];

      $dname = $DayLongList[$e['Day']];

      if (DayTable($e['Day'],"Events",'','class=DayHead','style=max-width:99%')) {
        echo "<tr class=Day$dname ><td>Time<td >What<td>Where<td>With and/or Description<td>Price";
      }
        
      Get_Imps($e,$imps,1,(Access('Staff')?1:0));
      echo "<tr class=Day$dname ><td>" . timecolon($e['Start']) . " - " . timecolon($e['End']); 
      echo "<td>" . $e['SN'] ;

      if (isset($Vens[$e['Venue']]['SN'])) {
        echo "<td>" . Venue_Parents($Vens,$e['Venue']) . $Vens[$e['Venue']]['SN'];
      } else {
        echo "<td>Unknown";
      }
      if ($e['BigEvent']) {
        $Others = Get_Other_Things_For($eid);
        foreach ($Others as $i=>$o) {
          if ($o['Type'] == 'Venue') echo ", " . Venue_Parents($Vens,$o['Identifier']) . $Vens[$o['Identifier']]['SN'];
        }
      }
      echo "<td><span style='font-size:12'>";
      if ($e['Description']) {
        $Desc = $e['Description'];
        $Desc = preg_replace('/<a href=(.*?)>.*?<\/a>/i','$1',$Desc);
        echo "$Desc<br>";
      }
      echo  ($e['BigEvent'] ? Get_Other_Participants($Others,0,-1,15,1,'',$e) : Get_Event_Participants($eid,0,-1,15));
      echo "</span><td>" . Price_Show($e,1);   
    }
    echo "</table></div>\n";
  
  echo "</div></div>";  
  exit;
?>
