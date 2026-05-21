<?php

include_once("fest.php");
include_once("DanceLib.php");
include_once("MusicLib.php");
include_once("ProgLib.php");
include_once("VolLib.php");
include_once("ChangeLib.php");
include_once("ViewLib.php");
include_once("CollectLib.php");
include_once("DispLib.php");
include_once("TradeLib.php");

dostaffhead('Embeded testing');

set_error_handler(function($_errno, $errstr) {
  // Convert notice, warning, etc. to error.
  throw new Error($errstr);
});
  
  A_Check('Internal');
  $Result = '';
  $Code = $_REQUEST['CODE']??'';
  
  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
      case 'Run':
        try {
          $Result = eval($Code . ';');
        } catch (Throwable $e) {
          echo $e; // Error: Undefined variable: tw...
        }
        break;
        
        
    }
  }
  
  $Show['CODE'] = $Code;
  echo "<form method=post><table><tr>" . fm_textarea('',$Show,'CODE',5,10);
  echo "<tr><td>" . fm_submit('ACTION','Run');
  echo "</table><hr>" . $Result??'' . "<hr>";
  
  
  dotail();
  
  