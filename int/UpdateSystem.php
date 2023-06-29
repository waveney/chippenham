<?php

// Update festival system
// push database Skema changes
// Call Special Update if needed

  include_once("fest.php");
  global $FESTSYS,$VERSION;
  


  

  dostaffhead("Update System");  
  preg_match('/(\d*)\.(\d*)/',$VERSION,$Match);
  $pfx = $Match[1];
  $Version = $Match[2];

  if (($FESTSYS['CurVersion'] ?? 0) == $Version) {
    echo "The System is up to date - no actions taken<p>";
    dotail();
  }
  
  chdir('../Schema');
  $skema = `skeema push`;
  echo $skema . "\n\n";
  chdir('../int');
  
  for ($Ver = $FESTSYS['CurVersion']; $Ver <= $Version; $Ver++) {
    if (function_exists("Update$Ver")) {
      echo "Doing update to Verion $pfx.$Ver<br>";
      ("Update$Ver")();
    }
  }
  
  echo "Updated to Version $VERSION<p>";
  $FESTSYS['CurVersion'] = $Version;
  Gen_Put('SystemData',$FESTSYS);
  dotail();
?>
