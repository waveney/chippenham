<?php
  include_once("fest.php");
  A_Check('Steward');

  dostaffhead("List Bugs");

  global $db;
  $yn = array('','Y');
  include_once("BugLib.php");
  include_once("DocLib.php");
  global $Bug_Status,$Bug_Type,$Severities;

  $AllU = Get_AllUsers(0);
  $AllA = Get_AllUsers(1);
  $AllActive = array();
  foreach ($AllU as $id=>$name) if ($AllA[$id] >= 2 && $AllA[$id] <= 6) $AllActive[$id]=$name;

  if (isset($_REQUEST['OLD'])) {
    $res = $db->query("SELECT * FROM Bugs ORDER BY Severity DESC");
  } else {
    $res = $db->query("SELECT * FROM Bugs WHERE State<" . $Bug_Type['Finalised'] . " ORDER BY Severity DESC");
  }

  $coln = 0;
  echo "<div class=Scrolltable><table id=indextable border>\n";
  echo "<thead><tr>";

  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Bug Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Who</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'D','dmy')>Created</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Severity</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Status</a>\n";
  echo "</thead><tbody>";

  if ($res) {
    while ($bug = $res->fetch_assoc()) {
      $b = $bug['BugId'];
      echo "<tr><td>$b<td><a href=AddBug?b=$b>" . $bug['SN'] ;
      if (strlen($bug['SN']) < 2) echo " Nameless Bug ";
      echo "</a><td>" . $AllU[$bug['Who']];
      echo "<td>" . date('d/m/y H:i:s',$bug['Created']) . "<td>" . $Severities[$bug['Severity']];
      echo "<td>" . $Bug_Status[$bug['State']];
    }
  }
  echo "</tbody></table></div>\n";

  echo "<h2><a href=AddBug>Add Bug/Feature Request</a>, <a href=ListBugs?OLD>Old Bugs</a></h2>";

  dotail();
?>
