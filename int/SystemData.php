<?php
  include_once("fest.php");
  A_Check('SysAdmin');
  global $FESTSYS,$VERSION;
  dostaffhead("System Data Settings");
  
  $FESTSYS = Gen_Get('SystemData',1);

  echo "<h2>System Data Settings and Global Actions</h2>\n";

//var_dump($_REQUEST);

  if (isset($_POST['Update'])) Update_db_post('SystemData',$FESTSYS);
  echo "<form method=post>\n";
  Register_AutoUpdate('SystemData',1);
  echo "<div class=tablecont><table>";
  echo "<tr>" . fm_textarea("Features",$FESTSYS,'Features',6,40);
  if (Access('Internal')) echo "<tr>" . fm_textarea("Capabilities",$FESTSYS,'Capabilities',6,10);
  echo "<tr>" . fm_text('Update Version #',$FESTSYS,'CurVersion');
  echo "<tr>" . fm_textarea("Analytics code",$FESTSYS,'Analytics',3,3);
  echo "</table></div>\n";

  echo "</form>\n";

/*  $feet = $FESTSYS['Features'];
  
  $Dat = parse_ini_string($feet);
  
  var_dump($Dat);*/
  
  echo "Features: are in php ini format.<br>; Comments start with ';', as in php.ini<p>";
  dotail();

?>
