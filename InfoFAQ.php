<?php
  include_once("int/fest.php");
  include_once("int/Email.php");

  $Subject = $_REQUEST['D'] ?? 'FAQ';
  dohead($Subject);
  $msg = TnC($Subject);  
  Parse_Proforma($msg);
  echo $msg;
  dotail();

