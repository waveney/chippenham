<?php
  include_once("fest.php");
  A_Check('Committee');

  dostaffhead("Camping Types");
  
  function Put_CampType(&$now) {
    Gen_Put('Camptypes',$now);
  }

  $Types = Gen_Get_All('Camptypes');
  if (UpdateMany('Camptypes','Put_CampType',$Types,1,'','','Name')) $Types = Gen_Get_All('Camptypes');
  $coln = 0;
  
  echo "<form method=post action=CampTypes>";
  echo "<div class=Scrolltable><table id=indextable border>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Comments</a>\n";
  echo "</thead><tbody>";
  foreach($Types as $t) {
    $i = $t['id'];
    echo "<tr><td>$i" . fm_text1("",$t,'Name',1,'','',"Name$i");
    echo          fm_text1("",$t,'Comments',1,'','',"Comments$i");
    echo "\n";
  }
  echo "<tr><td><td><input type=text name=Name0 >";
  echo "<td><input type=text name=Comments0 >";
  echo "</table></div>\n";
  echo "<input type=submit name=Update value=Update >\n";
  echo "</form></div>";

  dotail();

?>

