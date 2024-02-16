<?php

  include_once "fest.php";
  include_once "TradeLib.php";
  include_once "Email.php";

  global $db;

function Trade_Type_Table($class='') {
  $tts = Get_Trade_Types(1);

  echo "<div class=Scrolltable><table class=$class>\n";
  echo "<tr><th>Trade Type<th>Description<th>Prices<td>Status\n";

  foreach ($tts as $tt) {
    if ($tt['TOpen'] == 0) continue;
    if ($tt['Addition']) continue;
    echo "<tr><td>" . $tt['SN'];
    echo "<td>" . $tt['Description'];
    echo "<td>";
    if (is_numeric($tt['BasePrice'])) {
      echo "<span style='color:grey'>From: </span>&pound;" . $tt['BasePrice'];
    } else {
      echo $tt['BasePrice'];   
    }
    if ($tt['PerDay']) echo " per day";
    echo "<td>" . (($tt['TOpen'] == 1)?'Open':'Closed');
  }
  echo "</table></div><p>";

  $trail = TnC('TradeTypeTrailer');
  Parse_Proforma($trail);
  echo $trail;
  
}

