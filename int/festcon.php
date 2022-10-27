<?php
  global $FESTSYS;
  include_once("fest.php"); 
  $V = $FESTSYS['V'];
  echo "<link href=/css/festconstyle.css?V=$V type=text/css rel=stylesheet />\n";
  echo "<script src=/js/tablesort.js?V=$V></script>\n";
  echo "<script src=/js/Tools.js?V=$V></script>\n";
?>
