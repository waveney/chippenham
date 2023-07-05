<?php

// Update festival system
// push database Skema changes
// Call Special Update if needed

  include_once("fest.php");
  global $FESTSYS,$VERSION,$db;
 
// Change the year field from int to text - Skeema does not like it. 
function PreUpdate420() {
  global $db;
  $db->query("ALTER TABLE `VolCatYear` MODIFY COLUMN `Year` text COLLATE latin1_general_ci NOT NULL");
  $db->query("ALTER TABLE `PerfChanges` MODIFY COLUMN `Year` text COLLATE latin1_general_ci NOT NULL");
  $db->query("ALTER TABLE `EventChanges` MODIFY COLUMN `Year` text COLLATE latin1_general_ci NOT NULL");
  $db->query("ALTER TABLE `VolCatYear` MODIFY COLUMN `Year` text COLLATE latin1_general_ci NOT NULL");
  $db->query("ALTER TABLE `Venues` MODIFY COLUMN `SponsorYear` text COLLATE latin1_general_ci NOT NULL");
  $db->query("ALTER TABLE `PerformerTypes` MODIFY COLUMN `Year` text COLLATE latin1_general_ci NOT NULL");
  $db->query("ALTER TABLE `Sponsorship` MODIFY COLUMN `Year` text COLLATE latin1_general_ci NOT NULL");
}

function PostUpdate429() {
  // Copy files
  if (!file_exists("../favicon.ico")) {
    if (copy("../images/icons/favicon.ico","../favicon.ico")) {
      echo "Copied the default favicon<br>";
    } else {
      echo "Failed to copy default favicon - aborting for now - you can retry once corrected<p>";  
      exit;
    }
  }
  
  foreach (glob("../images/icons/apple-touch-icon*") as $fn) {
    $dfn = preg_replace('/images\/icons\//','',$fn);
    if (!file_exists($dfn)) {
      if (copy($fn,$dfn)) {
        echo "Copied $fn to $dfn<br>";     
      } else {
        echo "Failed to copy $fn to $dfn - aborting for now - you can retry once corrected<p>";
        exit;    
      }
    }
  }
}

  dostaffhead("Update System");  
  preg_match('/(\d*)\.(\d*)/',$VERSION,$Match);
  $pfx = $Match[1];
  $Version = $Match[2];

  if (($FESTSYS['CurVersion'] ?? 0) == $Version) {
    echo "The System is up to date - no actions taken<p>";
    dotail();
  }
// Pre Database changes

  for ($Ver = ($FESTSYS['CurVersion'] ?? 0); $Ver <= $Version; $Ver++) {
    if (function_exists("PreUpdate$Ver")) {
      echo "Doing Pre update to Verion $pfx.$Ver<br>";
      ("PreUpdate$Ver")();
    }
  }

  
  chdir('../Schema');
  $skema = system('skeema push');
  echo $skema . "\n\n";
  chdir('../int');

// Post Database changes
 
  for ($Ver = ($FESTSYS['CurVersion'] ?? 0); $Ver <= $Version; $Ver++) {
    if (function_exists("PostUpdate$Ver")) {
      echo "Doing Post update to Verion $pfx.$Ver<br>";
      ("PostUpdate$Ver")();
    }
  }
  
  echo "Updated to Version $VERSION<p>";
  $FESTSYS['CurVersion'] = $Version;
  Gen_Put('SystemData',$FESTSYS);
  dotail();
?>
