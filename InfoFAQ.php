<?php
  include_once("int/fest.php");

  $Subject = $_REQUEST['D'] ?? 'FAQ';
  dohead($Subject);
  echo TnC($Subject);
  dotail();
?>
