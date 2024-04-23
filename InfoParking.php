<?php
  include_once("int/fest.php");

  dohead("Parking",[],1);

  include_once("int/MapLib.php");
?>
<div class=TwoCols><script>Register_Onload(Set_ColBlobs,'Blob',5)</script>
<div class=OneCol id=TwoCols1>

<!--
<div id=Blob0>
<h2>Festival Parking</h2>

Details to follow.<p>

</div>-->
<div id=Blob1><div id=BlobMap>
<div id=MapWrap>
<div id=DirPaneWrap>
<div id=DirPane><div id=DirPaneTop></div><div id=Directions></div></div>
</div><div id=map  style='min-height:300px; max-height:400px'></div>
</div>
<?php    Init_Map(-1,4,16,4); ?>

</div></div><div id=Blob4>

<h2>Long Term Car Parks - Blue Icons</h2>
And large blocks of Chargers.<p>
The principle long term car parks are:<p>

<div class=tablecont><table class=InfoTable>
<?php
  $Things = Get_Map_Points();
  foreach ($Things as $T) {
    if ($T['Type'] != 3 && $T['Type'] != 8) continue;
    echo "<tr><td>" . $T['SN'] . "<td><button onclick=ShowDirect(" . (1000000 + $T['id']) . ")>Directions</button>\n";
  }
?>

</table></div>
</div><div id=Blob2>
<h2>Short Term Car Parks - Red Icons</h2>
The principle short term car parks are:

<div class=tablecont><table class=InfoTable>
<?php
  $Things = Get_Map_Points();
  foreach ($Things as $T) {
    if ($T['Type'] != 10) continue;
    echo "<tr><td>" . $T['SN'] . "<td><button onclick=ShowDirect(" . (1000000 + $T['id']) . ")>Directions</button>\n";
  }
?>

</table></div>

</div>
<!--<div id=Blob3>
<h2 class=subtitle>Bicycle Parking</h2>
There will be extensive Bicycle parking provided in what is normally the Hanham Road car park.<p>

Please don't use the limited cycle racks on the High Street and by the Minster.<p>

<h2>Closed Car Parks</h2>

</div>-->
</div><div class=OneCol id=TwoCols2></div></div>

<?php
  dotail();
?>
