<?php
  include_once("int/fest.php");

  dostaffhead("Whats on By Venue");

  set_ShowYear();
  include_once("int/ProgLib.php");
  include_once("int/DateTime.php");
  global $db,$YEAR,$PLANYEAR,$YEARDATA;

  $Vens = Get_Active_Venues(1); // Code also in Event types
  $Vids = [];
  
  if ($Vens) foreach ($Vens as $Ven) $Vids[] = $Ven['VenueId'];
  
  file_put_contents("../cache/VenueList",json_encode($Vids));

  echo "<h2>Venues Cached</h2>";
  
  dotail();

?>

