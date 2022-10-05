<?php
  include_once("int/fest.php");
  global $FESTSYS;

  dohead("Thank You",[],1);
  
  echo "<center><h1>Thank you for supporting " . $FESTSYS['FestName'] . ".</h3><h3>This festival would not be possible without the generosity of our supporters</h1>";
  dotail()
?>
