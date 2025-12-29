<?php
  include_once("int/fest.php");

  dohead("Terms and Conditions");
  $msg = TnC("TicketTnC");
  Parse_Proforma($msg);
  echo $msg;
  dotail();
