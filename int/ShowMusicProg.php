<?php
  include_once("fest.php");
//  A_Check('Steward');

  global $DAY;
  include_once("NewProgramLib.php");

  $Cond = 0;
  if (isset($_REQUEST['Cond'])) $Cond = $_REQUEST['Cond'];

  $day = "All";
  if (isset($_REQUEST['Day'])) $day = $_REQUEST['Day'];

  $head = 1;
  if (isset($_REQUEST['Head'])) $head = $_REQUEST['Head'];

  $Public='';
  if (isset($_REQUEST['Pub'])) $Public=1;

  Prog_Headers($Public,$head,'Music');
  if ($day != 'Fri') {
    Grab_Data('Fri','Music');
    Scan_Data($Cond,'Music');
    if ($Public) echo "<p><h2>Friday Music</h2><p>\n";
    Create_Grid($Cond,'Music');
    Print_Grid(0,0,$Cond,$Public,'Music');
  }
  if ($day != 'Sat') {
    Grab_Data("Sat",'Music');
    Scan_Data($Cond,'Music');
    echo "<p><h2>Saturday Music</h2></p>\n";
    Create_Grid($Cond,'Music');
    Print_Grid(0,0,$Cond,$Public,'Music');
  }
  if ($day != 'Sun') {
    Grab_Data("Sun",'Music');
    Scan_Data($Cond,'Music');
    echo "<p><h2>Sunday Music</h2></p>\n";
    Create_Grid($Cond,'Music');
    Print_Grid(0,0,$Cond,$Public,'Music');
  }

  if ($head) {
    dotail();
  } else {
    Notes_Music_Pane();
    Controls(0,$Cond);
    ErrorPane(0);
    echo "</body></html>\n";
  }

?>

