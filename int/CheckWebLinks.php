<?php

include_once("fest.php");
include_once("DanceLib.php");
include_once("ProgLib.php");
include_once("TradeLib.php");

A_Check('Staff');

function CheckLink(&$Data,$Category,$Editor,$id) {
  
  $links = $Data['Website']??0;
  if (!$links) return;
  
  $sites = explode(' ',trim($links));
  foreach($sites as $si=>$site) {
    $url = stripslashes($site);
    if (!preg_match("/^https?/i",$url,$mtch)) $url = 'http://' . $url;
    echo "Would check $Category - <a href=$Editor?id=$id>" . ($Data['SN']??$Data['Name']??'Unknown') . " has an faulty website - $site<p>";
    continue;
    list($status) = get_headers($url);
    if (strpos($status, '200') !== TRUE) {
      echo "$Category - <a href=$Editor?id=$id>" . ($Data['SN']??$Data['Name']??'Unknown') . " has an faulty website - $site<p>";
    }
     
  }
}

// Check all Dancers performers in PLANYEAR

$SideQ = $db->query("SELECT s.*, y.* " . 
         "FROM Sides AS s, SideYear AS y WHERE s.SideId=y.SideId AND y.year='$YEAR' AND y.Coming=" . $Coming_Type['Y'] . 
         " AND s.IsASide=1 AND s.NotPerformer=0 AND y.NoDanceEvents=0 ");

if ($SideQ) while($side = $SideQ->fetch_assoc()) { 
  CheckLink($side,'Dance Side','AddPerf',$side['SideId']);
}
echo "<br>Checked Dance Sides<p>";


// Check all Other performers in PLANYEAR

$SideQ = $db->query("SELECT s.*, y.*  FROM Sides AS s, SideYear AS y " .
       "WHERE s.SideId=y.SideId AND y.year='$YEAR' AND y.YearState>=" . $Book_State['Booking'] . 
       " AND s.NotPerformer=0 ");
if ($SideQ) while($side = $SideQ->fetch_assoc()) { 
  CheckLink($side,'Performer','AddPerf',$side['SideId']);
}
echo "<br>Checked All other performers<p>";

// Check all Traders in PLANYEAR

$TradeQ = $db->query("SELECT t.*, y.* FROM Trade AS t, TradeYear AS y WHERE t.Status!=2 AND t.Tid = y.Tid AND y.Year='$YEAR' AND y.BookingState>" . 
           $Trade_State['Submitted']);
if ($TradeQ) while($t = $TradeQ->fetch_assoc()) { 
  CheckLink($t,'Trader','Trade',$t['Tid']);
}
echo "<br>Checked All Traders<p>";


// Check all Sponsors - TODO

// Check all Events - TODO
