<?php
  include_once("int/fest.php");

  dohead("Festival Map");
  include_once("int/MapLib.php");
  include_once("int/ProgLib.php");

  echo "<h2 class=subtitle>Festival Map</h2>";
  echo "Zoom out to find " . Feature('FestHomeName','Wimborne') . 
       ", Zoom in for more detail.<p>\n";

  echo "<div id=MapWrap>";
  echo "<div id=DirPaneWrap><div id=DirPane><div id=DirPaneTop></div><div id=Directions></div></div></div>";
  echo "<div id=map></div></div>";
  
  $Feat = 0;
  if (isset($_REQUEST['F'])) $Feat = $_REQUEST['F'];
//  echo "<button class=PurpButton onclick=ShowDirect()>Directions</button> (From the " . Feature('DirectionDefault','Square') . " if it does not know your location)\n";
  Init_Map(-1,0,Feature('MapStartZoom',17),$Feat);
  echo "Zoom out to find " . Feature('FestHomeName','Wimborne') . 
       ", Zoom in for more detail.<p>\n";
  
  dotail();