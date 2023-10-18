<?php

// Update festival system
// push database Skema changes
// Call Special Update if needed

  include_once("fest.php");
  global $FESTSYS,$VERSION,$db;
 
// Change the year field from int to text - Skeema does not like it. 
function xPreUpdate420() {
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

function PreUpdate436() {  // Corect mediumtext to text
  global $db,$CONF;
  $qry = "SELECT COLUMN_NAME, DATA_TYPE, TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='" . $CONF['dbase'] ."' AND DATA_TYPE='text'";
  $res = $db->query($qry);
  while ($dat=$res->fetch_array()){
    $db->query("ALTER TABLE $dat[2] MODIFY $dat[0] TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci") or die($db->error);
  }
  echo "The collation of your database has been successfully changed!";
}

function FixUpdate436() {  // Corect mediumtext to text
  global $db,$CONF;
  $qry = "SELECT COLUMN_NAME, DATA_TYPE, TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='" . $CONF['dbase'] ."' AND DATA_TYPE='text'";
  $res = $db->query($qry);
  while ($dat=$res->fetch_array()){
    echo "Trying: ALTER TABLE $dat[2] MODIFY $dat[0] TEXT COLLATE utf8mb4_general_ci<p>";
    $db->query("ALTER TABLE $dat[2] MODIFY $dat[0] TEXT COLLATE utf8mb4_general_ci") or die($db->error);
  }
  echo "The collation of your database has been successfully changed!";
}

// ********************** START HERE ***************************************************************


  dostaffhead("Update System");  
  
  FixUpdate436();
  echo "Done";
  exit;
  
  
  preg_match('/(\d*)\.(\d*)/',$VERSION,$Match);
  $pfx = $Match[1];
  $Version = $Match[2];

  if (($FESTSYS['CurVersion'] ?? 0) == $Version) {
    echo "The System is up to date - no actions taken<p>";
    dotail();
  }
// Pre Database changes

  if (!isset($_REQUEST['MarkDone'])) {
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
  }  
  echo "Updated to Version $VERSION<p>";
  $FESTSYS['CurVersion'] = $Version;
  $FESTSYS['VersionDate'] = time();
  Gen_Put('SystemData',$FESTSYS);
  dotail();
?>
