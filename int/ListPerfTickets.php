<?php
  include_once("fest.php");
  A_Check('Staff');

  dostaffhead("List Performer Tickets", ["/js/clipboard.min.js","/js/emailclick.js", "/js/InviteThings.js"] );
  global $YEAR,$PLANYEAR,$Dance_Comp,$Dance_Comp_Colours,$Event_Types,$YEARDATA;
  include_once("DanceLib.php"); 
  include_once("MusicLib.php"); 
  include_once("ProgLib.php");
  include_once("DateTime.php");
  
  $SideQ = $db->query("SELECT * FROM Sides AS s, SideYear as y WHERE s.SideId=y.SideId AND y.Year='$YEAR' AND y.YearState>=2 AND " .
           "( y.FreePerf>0 OR y.FreeYouth>0 OR y.FreeChild>0 ) ORDER BY SN");

  if (!$SideQ || $SideQ->num_rows==0) {
    echo "<h2>No Performers Found</h2>\n";
    dotail();
  } 
  
  $coln = 1; // Start at 1 for select col
  echo "<div class=tablecont><table id=indextable border width=100% style='min-width:1400px'>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Contact</a>\n";
//  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Booked By</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Adults</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Youth</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Child</a>\n";
  echo "</thead><tbody>";

  while ($fetch = $SideQ->fetch_assoc()) {
    echo "<tr><td>" . $fetch['SideId'];
    echo "<td><a href=AddPerf?id=" . $fetch['SideId'] . "&Y=$YEAR>" . (empty($fetch['SN'])?'Nameless':$fetch['SN']) . "</a>";
    echo "<td>" . $fetch['Contact'];
    echo "<td>" . $fetch['FreePerf'];
    echo "<td>" . $fetch['FreeYouth'];
    echo "<td>" . $fetch['FreeChild'];
  }
  echo "</table></div>";
  dotail();
?>
