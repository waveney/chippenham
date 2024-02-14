<?php
  include_once("int/fest.php");
  include_once("int/ChangeLib.php");

  dohead("Event changes since the programme went to print",[],1);
  EventChangePrint(1);
  dotail();
