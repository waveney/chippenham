<?php
  include_once("fest.php");

  include_once("ProgLib.php");
  include_once("MapLib.php");
  include_once("DanceLib.php");
  include_once("MusicLib.php");
  include_once("DispLib.php");
  
  global $YEAR;

  dostaffhead("QR Codes",["js/qrcode.js","css/festconstyle.css"]);
  
  A_Check('Staff');
  
  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
    
      case 'Code':
      echo "Keep this square - it is best at a multiple of 19 pixels.<p>";
      echo "This is for: " . $_REQUEST['URL'] . "<p>";
      echo "<div id=qrcode></div>";
      echo "<script type='text/javascript'>
        var qrcode = new QRCode(document.getElementById('qrcode'), {
          text: '" . $_REQUEST['URL'] . "',
          width: 190,
          height: 190,
        });
        </script>";
      echo "<br clear=all>\n";   
      
      dotail();
    }
  }
  
  $_REQUEST['URL'] = 'Https://' . Feature('HostURL');
  echo "<form method=post action=QRMake?ACTION=Code>";
  echo fm_text("URL to encode",$_REQUEST,'URL',3);
  echo "<input type=submit value='Make Code'>";
  echo "</form>";
  dotail();
?>
