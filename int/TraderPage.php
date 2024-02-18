<?php
  include_once("fest.php");

  dostaffhead("Trader Application", ["/js/Trade.js","js/dropzone.js","css/dropzone.css"]);

  include_once("TradeLib.php");
  include_once("DateTime.php"); 

  $TTTid = 0;
  
  if (isset($_REQUEST['id'])) $TTTid = $_REQUEST['id'];
  Trade_Main(0,'TraderPage',$TTTid);

  dotail();
?>
