<?php
  global $VERSION;
  echo "</div></div><br clear=all  style='height=0'><div class=footer style='Background:" . 
    Feature('FooterBack','url(/images/icons/Flora.png) repeat-x bottom left') . "'>";

  echo "<div class=widthLim>";
  echo "<div class=VersionBy>";
  echo "Website supported by <a href=http://wavwebs.com style=color:white;>Waveney Web Services</a> - Version " . $VERSION;
  echo " <a href=/int/Login style='color:white; float:right;'>Staff Login</a><p>\n";

  echo "</div><div class=copyright style='text-decoration:none;'>";

  $ft = TnC('Footer_Text') ?? '';
  $ft = preg_replace('/\$CALYEAR/',gmdate('Y'),$ft);
  echo $ft;

  echo "</div></div></div><div id=LastDiv></div>";
?>
