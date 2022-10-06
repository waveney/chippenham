<?php
  include_once('int/fest.php');
  include_once("int/GetPut.php");
  
  dohead("Donate",[],1);
  
  echo "<div class=donateCont>";
  $Dons = Gen_Get_All('Donations','ORDER BY Importance DESC');
  foreach ($Dons as $Don) {
    if (!$Don['InUse']) continue;
    
    echo "<div class=donate><img src='" . $Don['Image'] . "'><p>";
    echo "<h2>" . (is_numeric($Don['Value'])? "&pound;" :'') . $Don['Value'] ."</h2>" . $Don['Text'] . "<p>";

    echo '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="' . $Don['ButtonId'] . '" />
<input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1" />
</form>';
    echo "</div>";
  }
  
  echo "</div>";

  echo "<p class=smaller>Donations handled through Paypal, all major credit cards accepted<p>";
  dotail();
?>
