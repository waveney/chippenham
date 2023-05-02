<?php
  include_once("fest.php");
//  A_Check('Steward');

  global $DAY;
  include_once("NewProgramLib.php");
  include_once("MapLib.php");
  include_once("ProgLib.php");
  global $Event_Types;

  $Cond = 1;
  if (isset($_REQUEST['Cond'])) $Cond = $_REQUEST['Cond'];

  $day = "All";
  if (isset($_REQUEST['Day'])) $day = $_REQUEST['Day'];

  $head = 1;
  if (isset($_REQUEST['Head'])) $head = $_REQUEST['Head'];

  $Public=1;
  if (isset($_REQUEST['Pub'])) $Public=$_REQUEST['Pub'];

  $Links=1;
  if (isset($_REQUEST['Links'])) $Links=$_REQUEST['Links'];

// var_dump($Cond,$day,$head,$Public,$Links);
//  var_dump($day);
  Prog_Headers($Public,$head);
  
  if ($Public && $Links) {
    echo "<h2 class='DanceMap Fakelink' onclick=$('.DanceMap').toggle()>Show Dance Locations</h2>";
    echo "<h2 class='DanceMap FakeLink' onclick=$('.DanceMap').toggle() hidden>Hide Dance Locations</h2>"; 
    echo "<div class=DanceMap hidden><div id=MapWrap>";
    echo "<div id=DirPaneWrap><div id=DirPane><div id=DirPaneTop></div><div id=Directions></div></div></div>";
    echo "<div id=map></div></div>";
    Init_Map(-1,0,17,3);
    echo "</div>";
  }
  
  if ($Links && $day == 'All') {
    echo "<h2>Jump to: <a href=#Sunday>Sunday</a>, <a href=#Monday>Monday</a></h2>";
  }

  if ($day == 'All' || $day == 'Sat') {
    Grab_Data('Sat');
    Scan_Data($Cond);
    if ($Public) echo "<p id=Saturday><h2>Saturday " . $Event_Types[1]['Plural'] . "</h2><p>\n";
//    echo "This will be easier to use on a small screen soon.<p>";
    if ($Links) echo "Click on a team to learn more about them, click on a venue to find out where it is.<p>";
    Create_Grid($Cond);
    Print_Grid(0,0,$Cond,$Links);
  }
  if ($day == 'All' || $day == 'Sun') {
    if ($Public) {
      Grab_Data("Sun");
      Scan_Data($Cond);
      echo "<p id=Sunday><h2>Sunday " . $Event_Types[1]['Plural'] . "</h2></p>\n";
//      echo "This will be easier to use on a small screen soon.<p>";
      if ($Links) echo "Click on a team to learn more about them, click on a venue to find out where it is.<p>";
      Create_Grid($Cond);
      Print_Grid(0,0,$Cond,$Links);
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
      if ($Links) echo "Click on a team to learn more about them, click on a venue to find out where it is.<p>";
      Create_Grid($Cond);
      Print_Grid(0,0,$Cond,$Links);
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

