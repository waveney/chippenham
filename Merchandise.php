<?php
  include_once("int/fest.php");
  include_once("int/DateTime.php");

  dohead("Merchandise",[],1);
  global $YEARDATA,$YEAR,$FESTSYS;
  set_ShowYear();
  include_once "int/ProgLib.php";
  
  echo TnC("MerchantHeader");
    
  echo "<div class=TicketFrame style='max-width:1000px;'>";
  echo '<p><iframe src="https://www.mudchutney.co.uk/chippenham" scrolling="no" style="border: 0px; width: 1px; min-width: 100%; max-width: 1100px;" onload="iFrameResize()"></iframe></p>';
//  echo '<p><script type="text/javascript" src="https://theticketsellerslive.blob.core.windows.net/webcontent/embed/iframeResizer.min.js"></script><iframe src="https://ww2.theticketsellers.co.uk/embed/10055506" scrolling="no" style="border: 0px; width: 1px; min-width: 100%; max-width: 1100px;" onload="iFrameResize()"></iframe></p>';
  
  echo "</div>";
  
  dotail();

?>
