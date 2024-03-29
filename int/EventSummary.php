<?php
  include_once("fest.php");
  A_Check('Staff');

  dostaffhead("Event Summary");
  include_once("ProgLib.php");
  global $Event_Types;

  echo "<div class=content><h2>Event Summary $YEAR</h2>\n";

  echo "<div class=Scrolltable><table class=TueTab><tr><td>Event type<td>Number";

  $tot = $sp = $fam = 0;
  foreach ($Event_Types as $t) {
    $c = 0;
    $Ett = $t['ETypeNo'];
    if ($Event_Types[$Ett]['DontList']) continue;
    $ans = $db->query("SELECT * FROM Events WHERE Year='$YEAR' AND Type=$Ett");
    if ($ans) while ($e = $ans->fetch_assoc()) { 
      $Evs[] = $e; 
      $c++;
      if ($e['Family']) $fam++;
      if ($e['Special']) $sp++;
    };
    echo "<tr><td>" . $t['SN'] . "<td>" . $c;
    $tot += $c;
  }
  echo "<tr><td>Family<td>$fam";
  echo "<tr><td>Special<td>$sp";
  echo "<tr><td>Total<td>$tot";
    
  echo "</table></div>\n";
  dotail();

?>
