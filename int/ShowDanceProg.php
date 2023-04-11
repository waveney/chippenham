<?php
  include_once("fest.php");
//  A_Check('Steward');

  global $DAY;
  include_once("NewProgramLib.php");
  include_once("MapLib.php");
  include_once("ProgLib.php");
  global $Event_Types;

  $Cond = 1;
  if (isset($_GET['Cond'])) $Cond = $_GET['Cond'];
  if (isset($_POST['Cond'])) $Cond = $_POST['Cond'];

  $day = "All";
  if (isset($_GET['Day'])) $day = $_GET['Day'];

  $head = 1;
  if (isset($_GET['Head'])) $head = $_GET['Head'];

  $Public=1;
  if (isset($_GET['Pub'])) $Public=$_GET['Pub'];

//  var_dump($day);
  Prog_Headers($Public,$head);
  echo "<h2 class='DanceMap Fakelink' onclick=$('.DanceMap').toggle()>Show Dance Locations</h2>";
  echo "<h2 class='DanceMap FakeLink' onclick=$('.DanceMap').toggle() hidden>Hide Dance Locations</h2>";
  echo "<div class=DanceMap hidden><div id=MapWrap>";
  echo "<div id=DirPaneWrap><div id=DirPane><div id=DirPaneTop></div><div id=Directions></div></div></div>";
  echo "<div id=map></div></div>";
  Init_Map(-1,0,17,3);
  echo "</div>";
  
  if ($day == 'All') {
    echo "<h2>Jump to: <a href=#Sunday>Sunday</a> <a href=#Monday>Monday</a></h2>";
  }
  
  if ($day == 'All' || $day == 'Sat') {
    Grab_Data('Sat');
    Scan_Data($Cond);
    if ($Public) echo "<p id=Saturday><h2>Saturday " . $Event_Types[1]['Plural'] . "</h2><p>\n";
//    echo "This will be easier to use on a small screen soon.<p>";
    echo "Click on a team to learn more about them, click on a venue to find out where it is.<p>";
    Create_Grid($Cond);
    Print_Grid(0,0,$Cond,$Public);
  }
  if ($day == 'All' || $day == 'Sun') {
    if ($Public) {
      Grab_Data("Sun");
      Scan_Data($Cond);
      echo "<p id=Sunday><h2>Sunday " . $Event_Types[1]['Plural'] . "</h2></p>\n";
//      echo "This will be easier to use on a small screen soon.<p>";
      echo "Click on a team to learn more about them, click on a venue to find out where it is.<p>";
      Create_Grid($Cond);
      Print_Grid(0,0,$Cond,$Public);
    } else {
      Controls(0,$Cond);
      ErrorPane(0);
      echo "</body></html>\n";
    }
  }
  if ($day == 'All' || $day == 'Mon') {
    if ($Public) {
      Grab_Data("Mon");
      Scan_Data($Cond);
      echo "<p id=Monday><h2>Monday " . $Event_Types[1]['Plural'] . "</h2></p>\n";
//      echo "This will be easier to use on a small screen soon.<p>";
      echo "Click on a team to learn more about them, click on a venue to find out where it is.<p>";
      Create_Grid($Cond);
      Print_Grid(0,0,$Cond,$Public);
    }
    dotail();
  } else {
    if ($Public && $head) {
      dotail();
    } else {
      echo "</body></html>\n";
    }
  }

?>

