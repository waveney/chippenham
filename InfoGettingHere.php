<?php
  include_once("int/fest.php");

  dohead("Travel: Road, Buses, Trains and Taxis",[],1);

  include_once("int/MapLib.php");
  include_once("int/ProgLib.php");

  echo "<div class=venueimg>";
  echo "<button onclick=ShowDirect(1000001)>Directions</button>\n";
  echo "<div id=MapWrap>";
  echo "<div id=DirPaneWrap><div id=DirPane><div id=DirPaneTop></div><div id=Directions></div></div></div>";
  echo "<p><div id=map></div></div>";
  echo "</div>\n";
  Init_Map(-1,0,10,1);
//  echo "</div></div>";
?>
<script language=Javascript defer>
function ShowBus(Route) {
  $('#TimeTab').load("/files/BusRoute" +Route );
}
</script>

<h2>Getting To Chippenham</h2>
Chippenham Folk Festival takes place in the historic town of Chippenham in Wiltshire.<p>

Jump to: <a href=#Buses class='DaySkipTo sattab'>Public Transport</a> <a href=#Taxis class='DaySkipTo suntab'>Taxis</a><p>
<div class=BorderBox>
<h2>By Road</h2>

Chippenham has good road connections with easy access from the M4, A4 and A350.<p>

<a href=InfoParking>Information on Parking - Car parks and bicycle parking</a>.<p>

</div><div class=BorderBox>
<h2>By Public Transport</h2>

Details to follow.<p>

<div class=tablecont>
<table class=InfoTable>
<tr><td>Route<td>Operator<td colspan=3>To Chippenham<td colspan=3>From Chippenham

</table></div><p>

<div id=TimeTab></div>
</div><div class=BorderBox>
<h2><a name=Taxis></a>By Taxi</h2>
This is a list of Taxi firms.<p>
<div class=tablecont><table class=InfoTable>
<tr><td>Authority<td>Name<td>Phone
<?php
  include_once("int/TradeLib.php");
  global $TaxiAuthorities;
  $Taxis = Get_Taxis();
  foreach($Taxis as $t) echo "<tr><td>" . $TaxiAuthorities[$t['Authority']] . "<td>" . $t['SN'] . "<td>" . $t['Phone'];
?>
</table></div><p>
</div>
<?php 
  dotail();
