<?php
  global $YEARDATA,$FESTSYS,$VERSION;

  echo "<meta name=description content='" . addslashes(TnC('Page_Description')) . ">\n";
  echo "<meta name=keywords content='" . addslashes(TnC('Page_Keywords')) . ">\n";
  echo "<meta name=viewport content='width=device-width, initial-scale=1.0'>";

  echo "<script>" . $FESTSYS['Analytics'] . "</script>";
  echo "<link href=/files/Newstyle.css?V=$VERSION type=text/css rel=stylesheet />";
  echo "<link href=/files/Newdropdown.css?V=$VERSION type=text/css rel=stylesheet />\n";
  echo "<link rel=icon href=/favicon.ico>";
//  echo "<link rel=apple-touch-icon type=image/png sizes=167x167 href=favicon-167x167.png">

  echo "<script src=/js/jquery-3.2.1.min.js></script>";
  echo "<link href=/files/themes.css?V=$VERSION type=text/css rel=stylesheet>";
  echo "<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Montserrat%3A300%2C400%2C600%2C700' type='text/css' media='all'>";
  echo "<script src=/js/tablesort.js?V=$VERSION></script>\n";
  echo "<script src=/js/Tools.js?V=$VERSION></script>\n";
  echo '<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">';

?>
