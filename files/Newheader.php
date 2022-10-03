<?php
  global $YEARDATA,$FESTSYS,$CALYEAR;

  echo "<meta name=description content='Chippenham\'s annual folk festival takes place in the historic market town of Chippenham in Wiltshire on the spring bank holiday weekend.>\n";
  echo "<meta name=keywords content='Chippenham, folk, festival, folk festival, dorset, folkie, fringe, morris, dance, side, music, concerts, camping, 
	      parking, trade, trading, stewards, volunteer, tickets, line up, appalachian, ceildihs, procession, step dance, workshops, craft, sessions'>\n";
  echo "<meta name=viewport content='width=device-width, initial-scale=1.0'>";

  $V = $FESTSYS['V'];
  echo "<script>" . $FESTSYS['Analytics'] . "</script>";
  echo "<link href=/files/Newstyle.css?V=$V type=text/css rel=stylesheet />";
  echo "<link href=/files/Newdropdown.css?V=$V type=text/css rel=stylesheet />\n";  

  echo "<script src=/js/jquery-3.2.1.min.js></script>";
  echo "<link href=/files/themes.css?V=$V type=text/css rel=stylesheet>";
  echo "<script src=/js/lightbox.js?V=$V></script>";
  echo "<link href=/css/lightbox.css?V=$V rel=stylesheet>";
  echo "<script src=/js/responsiveslides.js?V=$V></script>";
  echo "<link href=/css/responsiveslides.css?V=$V rel=stylesheet>";
  echo "<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Montserrat%3A300%2C400%2C600%2C700' type='text/css' media='all'>";
  echo "<script src=/js/tablesort.js?V=$V></script>\n";
  echo "<script src=/js/Tools.js?V=$V></script>\n";
?>
