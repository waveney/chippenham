<?php
  include_once("fest.php");
  include_once("MailListLib.php");
  include_once("Email.php");

  $csv = 0;
  if (isset($_GET['F'])) $csv = $_GET['F'];

  dostaffhead("Mailing List Management",["/js/Volunteers.js","js/dropzone.js","css/dropzone.css",'/js/InviteThings.js' ]);
 
  global $USER,$USERID,$db,$PLANYEAR,$StewClasses,$Relations,$Days;
//echo "HERE";

  if (isset($_REQUEST['ACTION'])) {
    Mail_List_Action($_REQUEST['ACTION'],$csv);
  } else if (isset($_REQUEST['A'])) {
    Mail_List_Action($_REQUEST['A'],$csv);
  } else {
    Mail_List_Action('New');
  }
  
  dotail();
?>
