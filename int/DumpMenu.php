<?php
  include_once("fest.php");
  A_Check('SysAdmin');

  dostaffhead("Dump Menu");
  
  $Menus = Gen_Get_All('MainMenu');
  
  file_put_contents('festfiles/DumpMenu.json',json_encode($Menus)); 
  echo "Menu Dumped<p>";
  dotail();
?>
