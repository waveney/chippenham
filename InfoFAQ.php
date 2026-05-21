<?php
  include_once("int/fest.php");
  include_once("int/Email.php");

  $Subject = Sanitise($_REQUEST['D']);
  if (!$Subject) $Subject='FAQ';
  dohead($Subject);
  $msg = TnC($Subject);  
  Parse_Proforma($msg);
  echo $msg;
  dotail();

