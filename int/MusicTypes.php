<?php
  include_once("fest.php");
  A_Check('SysAdmin');

  dostaffhead("Music Types");
  include_once("MusicLib.php");

  $Types=Get_Music_Types(1);

  if (isset($_REQUEST['Update'])) {
    foreach($Types as $i=>$t) {
      if ($_REQUEST["SN$i"] != $t['SN'] || $_REQUEST["Imp$i"] != $t['Importance'] ) {
        if ($_REQUEST["SN$i"] == '') {
          db_delete('MusicTypes',$t['TypeId']);
        } else {
          $t['SN'] = $_REQUEST["SN$i"];
            $t['Importance'] = $_REQUEST["Imp$i"];
          Put_Music_Type($t);
        }
      }
    }
    if ($_REQUEST["SN0"]) {
      $t = array('SN'=> $_REQUEST['SN0'], 'Importance' => $_REQUEST['Imp0']);
      Insert_db('MusicTypes',$t);
    }
    $Types=Get_Music_Types(1);
  } 

  echo "<h2>Music Types</h2><p>";
  echo "You do not have to have all Music Types here, these are just the categories brought out in the summary page.<p>";
  echo "<form method=post action=MusicTypes>";
  echo "<div class=Scrolltable><table border><tr><td>id<td>Name<td>Importance\n";
  foreach($Types as $i=>$t) {
    echo "<tr><td>$i<td><input type=text name=SN$i value='" . $t['SN'] . "'>";
    echo "<td><input text name=Imp$i value='" . $t['Importance'] . "'>\n";
  }
  echo "<tr><td><td><input type=text name=SN0 >";
  echo "<td><input text name=Imp0>\n";
  echo "</table></div>";
  echo "<input type=submit name=Update value=Update>\n";
  echo "</form>";
  dotail();
?>

