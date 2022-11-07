<?php
  include_once("fest.php");
  include_once("DispLib.php");
  
  $Gals = Gen_Get_All('Galleries'," ORDER BY MenuBarOrder DESC");
  
  dohead('All Galleries', ['/files/gallery.css'],1);
  
  foreach($Gals as $G) {
    if ($G['MenuBarOrder'] < 0 ) continue;
    echo "<h2><a href=ShowGallery?g=" . $G['id'] .">" . $G['SN'] . "</a></h2>";
  }
  
  dotail();
?>
