<?php
  include_once("fest.php");

//  A_Check('Staff');
  
  include_once("ProgLib.php");
  include_once("DispLib.php");
  include_once("DanceLib.php");
  include_once("MusicLib.php");
  dominimalhead("All Event by Time", ['css/PrintPage.css']);
  include_once("files/Newheader.php");
//  include_once("festcon.php");

function PaperDayTable($d,$Types,$xtr='',$xtra2='',$xtra3='',$ForceNew=0,$PageBreak=0) {
  global $DayLongList,$YEAR,$YEARDATA;
  static $lastday = -99;
  if (($Mismatch = ($d != $lastday)) || $ForceNew) {
    $Class = Feature('PaperColouredEvents',DayList($d) . "tab");
    if ($lastday != -99) echo "</table></div><p>\n";
    $lastday = $d;
    if ($PageBreak) {
      if ($Mismatch) {
        echo "<div class=tablecont><table class=$Class $xtra3>";
      } else {
        echo "<div class=pagebreak></div><div class=tablecont><table class=$Class $xtra3>";
      }
    } else {
      echo "<div class=tablecont><table class=$Class $xtra3>";
    }
    if ($Mismatch || ($ForceNew<2)) {
      echo "<tr><th colspan=99 $xtra2>$Types on " . FestDate($d,'L') . " $xtr</th>\n";
      return 1;
    }
  }
  return 0;
}


  set_ShowYear();
 
  global $db,$YEAR,$PLANYEAR,$YEARDATA,$SHOWYEAR,$DayList,$DayLongList,$Event_Types ;

  echo "<script>document.getElementsByTagName('body')[0].style.background = 'none';</script><div class=PaperL>";
  
  $Splits = explode(',',TnC('PaperSplits'));

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
  $xtr = (isset($_REQUEST['Mode']) || $YEAR<$PLANYEAR)?'':"AND ( e.Public=1 OR (e.Type=t.ETypeNo AND t.State>1 AND e.Public<2 ))";

  $Count = 0;
  $Page = 0;
  $TimeWidth = 65;
  $LastDay = -99;
  $res = $db->query("SELECT DISTINCT e.* FROM Events e, EventTypes t WHERE e.Year='$YEAR' AND (e.SubEvent<=0 OR e.LongEvent=1) AND t.Public=1 $xtr ORDER BY Day, Start");

    while( $e = $res->fetch_assoc()) {
      $eid = $e['EventId'];

      if ($Count >= $Splits[$Page]) {
        $dname = $DayLongList[$e['Day']];
        if (PaperDayTable($e['Day'],"Events",'','class=DayHead','style=max-width:99%',(1 + ($Page+1)%2),1)) {
          echo "<tr class=Day$dname ><td style='max-width:$TimeWidth;width:$TimeWidth;'>Time<td >What<td>Where<td>With and/or Description<td>Price";
        }
        $LastDay = $e['Day'];
        $Page++;
        $Count = 1;

      } if ($LastDay != $e['Day']) {
        $LastDay = $e['Day'];
        $dname = $DayLongList[$e['Day']];
        if (PaperDayTable($e['Day'],"Events",'','class=DayHead','style=max-width:99%',(1 + ($Page+1)%2))) {
          if ($Page == 0) echo "<tr class=Day$dname ><td style='max-width:$TimeWidth;width:$TimeWidth;'>Time<td >What<td>Where<td>With and/or Description<td>Price";
        }      
        $Count++;
      } else {
        $Count++;
      }
      
              
      Get_Imps($e,$imps,1,(Access('Staff')?1:0));
      echo "<tr class=Day$dname ><td style='max-width:$TimeWidth;width:$TimeWidth;'>" . timecolon($e['Start']) . " - " . timecolon($e['End']); 
      echo "<td>" . $e['SN'];

      if (empty($e['VenuePaper'])) {
        if (isset($Vens[$e['Venue']]['SN'])) {
          echo "<td>" . Venue_Parents($Vens,$e['Venue']) . $Vens[$e['Venue']]['SN'];
        } else {
          echo "<td>Unknown";
        }
      } else {
        echo "<td>" . $e['VenuePaper'];
      }
      
      if ($e['BigEvent']) {
        $Others = Get_Other_Things_For($eid);
        $PerfC = 0;
        foreach ($Others as $i=>$o) {
          if (empty($e['VenuePaper']) && (($o['Type'] == 'Venue') && ($o['Identifier']>0) )) 
            echo ", " . Venue_Parents($Vens,$o['Identifier']) . $Vens[$o['Identifier']]['SN'];
          if ($o['Type'] == 'Perf') $PerfC++;
        }
      }
      echo "<td><span style='font-size:12'>";
      if ($e['Description']) {
        $Desc = $e['Description'];
        $Desc = preg_replace('/<a href=(.*?)[ >].*?<\/a>/i','$1',$Desc);
        echo "$Desc ";
      }
      if ($e['BigEvent']) {
        if ($e['NoPerfsPaper']) {
          echo "<span style='font-size:14'>For the full list of $PerfC teams see the website<span>";
          
        } else {
          echo Get_Other_Participants($Others,0,-1,12,1,'',$e);
        }
      } elseif ($e['NoPerfsPaper'] || $e['SN'] == $Event_Types[1]['Plural']) {
        echo "<span style='font-size:14'><br>See the Dance Grid for details</span>";
      } else {
        echo Get_Event_Participants($eid,0,-1,12);
      }

      echo "</span><td><span style='font-size:12'>" . Price_Show($e,1) . "</span>";   
    }
    echo "</table></div>\n";
  
  echo "</div></div>";  
  exit;
