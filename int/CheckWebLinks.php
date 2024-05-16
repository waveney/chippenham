<?php

include_once("fest.php");
include_once("DanceLib.php");
include_once("ProgLib.php");
include_once("TradeLib.php");

A_Check('Staff');
global $StartAt, $BatchSize, $WCount;

function CheckLink(&$Data,$Category,$Editor,$id,$Updater) {
  global $CONF;
  $links = $Data['Website']??0;
  if (!$links) return;
  
  $sites = explode(' ',trim($links));
  $Simple = (count($sites) == 1);
  foreach($sites as $si=>$site) {
    $AddedHttp = 0;
    $url = stripslashes($site);
    if (!preg_match("/^https?/i",$url,$mtch)) {
      $url = 'http://' . $url;
      $AddedHttp = 1;
    }
    if ($CONF['testing']??0) {
      echo "$Category - " . ($Data['SN']??$Data['Name']??'Unknown') . "<p>";
      continue;
    }
// exit;
    $headers = @ get_headers($url);
    if ($headers[0]??0) {
      $Code = substr($headers[0], 9, 3) +0;
    } else {
      $Code =999;
      if ($AddedHttp) {
        $url = 'https://' . stripslashes($site);
        $headers = @ get_headers($url);
        if ($headers[0]??0) {
          $Code = substr($headers[0], 9, 3) +0;
          if ($Code < 400 && $Simple) {
            $Data['Website'] = $url;
            $Updater($Data);
            echo "Updated website to $url<p>";
          }
        } else {
          $Code =998;
        }
      }
    } 
    if ($Code >= 400) {
      echo "$Category - <a href=$Editor?id=$id>" . ($Data['SN']??$Data['Name']??'Unknown') . "</a> has an faulty website - $site - failed $Code<p>";
    } else {
      echo "$Category - " . ($Data['SN']??$Data['Name']??'Unknown') . " website ok <br>";
      if ($Code != 200) {
        for($i=0;$i<10;$i++) echo $headers[$i] . "<br>";
      }
    }
    
    if ($Code == 301 && $Simple) { // Update the link
      for ($i=0; $i<10; $i++) if (preg_match('/^Location: ?(ht.*)$/',$header[$i],$mtch)) {
        $url = trim($mtch[1],' /');
        $Data['Website'] = $url;
        $Updater($Data);
        echo "Updated wesite to $url<p>";
        break;
      }
    }
  }
}


function CheckAll() {
  global $StartAt, $BatchSize, $WCount, $Coming_Type,$YEAR,  $Book_State, $Trade_State,$db;

// Check all Dancers performers in PLANYEAR


  $SideQ = $db->query("SELECT s.*, y.* " . 
           "FROM Sides AS s, SideYear AS y WHERE s.IsASide=1 AND s.SideId=y.SideId AND y.year='$YEAR' AND y.Coming=" . $Coming_Type['Y'] . 
           " AND s.IsASide=1 AND s.NotPerformer=0 AND y.NoDanceEvents=0 AND s.Website!=''");

  if ($SideQ) while($side = $SideQ->fetch_assoc()) { 
//    echo "$WCount<p>";
    if ($WCount >= $StartAt + $BatchSize) return 1;
    if ($WCount++ < $StartAt) continue; 
    CheckLink($side,'Dance Side','AddPerf',$side['SideId'],'Put_Side');
  }
  echo "<br>Checked Dance Sides<p>";


  // Check all Other performers in PLANYEAR

  $PerfQ = $db->query("SELECT s.*, y.*  FROM Sides AS s, SideYear AS y " .
         "WHERE (s.IsAnAct+s.IsOther+s.IsFunny+s.IsFamily+s.IsCeilidh)>0 AND s.SideId=y.SideId AND y.year='$YEAR' " .
         " AND y.YearState>=" . $Book_State['Booking'] . " AND s.NotPerformer=0 AND s.Website!=''");
  if ($PerfQ) while($side = $PerfQ->fetch_assoc()) { 
//    echo "$WCount<p>";
    if ($WCount >= $StartAt + $BatchSize) return 2;
    if ($WCount++ < $StartAt) continue; 
    CheckLink($side,'Performer','AddPerf',$side['SideId'],'Put_Side');
  }
  echo "<br>Checked All other performers<p>";

  // Check all Traders in PLANYEAR

  $TradeQ = $db->query("SELECT t.*, y.* FROM Trade AS t, TradeYear AS y WHERE t.Status!=2 AND t.Tid = y.Tid AND Website!='' AND y.Year='$YEAR'" .
            " AND y.BookingState>" . $Trade_State['Submitted']);
  if ($TradeQ) while($t = $TradeQ->fetch_assoc()) { 
//    echo "$WCount<p>";    
    if ($WCount >= $StartAt + $BatchSize) return 3;
    if ($WCount++ < $StartAt) continue; 
    CheckLink($t,'Trader','Trade',$t['Tid'],'Put_Trader');
    }
  echo "<br>Checked All Traders<p>";


  // Check all Sponsors - TODO

  // Check all Events - TODO
  return 0;
}

$StartAt = $_REQUEST['StartAt']??0;
$BatchSize = 20;
$WCount = 0;

dostaffhead("Check Web Links");
$R = CheckAll();

//if ($WCount >= $StartAt + $BatchSize) {
if ($R) {
  echo "<h2><a href=CheckWebLinks?StartAt=$WCount>Next $BatchSize links - $R</a></h2>";
} else { 
  echo "<h2>Finished</h2>";
}

dotail();