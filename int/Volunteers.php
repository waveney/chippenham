<?php
  include_once("fest.php");

  $csv = 0;
  if (isset($_GET['F'])) $csv = $_GET['F'];

  if ($csv) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Volunteers.csv');

  } else {
    dostaffhead("Steward / Volunteer Application",["/js/Volunteers.js","js/dropzone.js","css/dropzone.css" ]);
  }

  include_once("VolLib.php");
 
  global $USER,$USERID,$db,$PLANYEAR,$StewClasses,$Relations,$Days;
//echo "HERE";
  if (isset($_REQUEST['NotThisYear'])) {
    VolAction('NotThisYear');
  } else if (isset($_REQUEST['Delete'])) {
    VolAction('Delete');
  } else if (isset($_REQUEST['ACTION'])) {
    VolAction($_REQUEST['ACTION']);
  } else if (isset($_REQUEST['A'])) {
    VolAction($_REQUEST['A'],$csv);
  } else {
    VolAction('New');
  }
  
  dotail();
?>
