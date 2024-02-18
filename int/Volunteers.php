<?php
  include_once("fest.php");
  include_once("VolLib.php");

  $csv = 0;
  if (isset($_REQUEST['F'])) $csv = $_REQUEST['F'];

  if ($csv) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Volunteers.csv');

  } else {
    dostaffhead("Steward / Volunteer Application",["/js/Volunteers.js","js/dropzone.js","css/dropzone.css",'/js/InviteThings.js' ]);
  }
 
  global $USER,$USERID,$db,$PLANYEAR,$StewClasses,$Relations,$Days;
//echo "HERE";
  if (isset($_REQUEST['NotThisYear'])) {
    VolAction('NotThisYear');
  } else if (isset($_REQUEST['Delete'])) {
    VolAction('Delete');
  } else if (isset($_REQUEST['ACTION'])) {
    VolAction($_REQUEST['ACTION'],$csv);
  } else if (isset($_REQUEST['A'])) {
    VolAction($_REQUEST['A'],$csv);
  } else if (isset($_REQUEST['SELECT'])) {
    VolAction('Select',$csv);
  } else {
    VolAction('New');
  }
  
  dotail();
?>
