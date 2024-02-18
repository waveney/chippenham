<?php
  include_once("fest.php");
  A_Check('Committee','Dance');

  dostaffhead("View Insurance");

  include_once("DanceLib.php");
  global $YEAR,$USERID;

  echo '<h2>Insurance</h2>';

  if (isset($_REQUEST['sidenum'])) {
    $snum = $_REQUEST['sidenum'];
    $data = Get_Side($snum);
    $type = 'Sides';
  } else if (isset($_REQUEST['othernum'])) {
    $snum = $_REQUEST['othernum'];
    $data = Get_Other($snum);
    $type = 'Others';
  } else if (isset($_REQUEST['actnum'])) {
    $snum = $_REQUEST['actnum'];
    $data = Get_Act($snum);
    $type = 'Acts';
  } else Error_Page("Viewing Insurance of nothing");

  $file = glob("Insurance/$type/$YEAR/$snum.*");

  if ($file) {
    $sfx = pathinfo($file[0],PATHINFO_EXTENSION);
    copy($file[0],"Temp/$USERID.$sfx");
    echo "<img src=Temp/$USERID.$sfx>\n";
  } else {
    echo "<h2>No Insurance Stored for " . $data['SN'] . "</h2>\n";
  }
  dotaikl();
?>

