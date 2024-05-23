<?php
  include_once("fest.php");
  include_once("ChangeLib.php");

  dominimalhead("Event changes since the programme went to print",["files/Newheader.php","festcon.php",'files/Newstyle.css','css/PrintPage.css'],1);
  echo "<div class=PaperL>";
  echo "<h1>Event Changes since the programme went to print</h1>";
  EventChangePrint(2);
  echo "<h1 class=pagebreak>Performer Changes since the programme went to print</h1>";
  PerfChangePrint(2);
  exit;


