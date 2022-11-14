<?php
  include_once("fest.php");
  A_Check('Committee','Users');
  include_once("Email.php");

  dostaffhead("Email Test");
  $letter = "Test message 1";
  NewSendEmail(0,0,"richardjproctor42@gmail.com","Test Email",$letter);

  dotail();
?>
