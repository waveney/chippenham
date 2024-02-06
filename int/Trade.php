<?php
  include_once("fest.php");
  
  if (Access('Committee')) {
    if (!Access('Committee','Trade')) {
      fm_addall('disabled readonly');
    }
  } else if (Access('Steward','Trade')) {
      fm_addall('disabled readonly');
  }

  dostaffhead("Trade Stall Booking", ["/js/Trade.js","js/dropzone.js",'js/emailclick.js',"/js/clipboard.min.js","css/dropzone.css"]);

  include_once("TradeLib.php");
  include_once("DateTime.php"); 
  include_once("InvoiceLib.php");

  Trade_Main((isset($_REQUEST['ORGS'])?2:1),'Trade');

  dotail();
?>
