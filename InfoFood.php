<?php
  include_once("int/fest.php");
  include_once("int/MapLib.php");
  include_once("int/ProgLib.php");
  dohead("Food and Drink",[],1);

/*
?>
<div class=TwoCols><script>Register_Onload(Set_ColBlobs,'Blob',5)</script>
<div class=OneCol id=TwoCols1>

<div id=Blob1><div id=BlobMap> */
  echo "<div id=MapWrap><div id=DirPaneWrap>";
  echo "<div id=DirPane><div id=DirPaneTop></div><div id=Directions></div></div>";
  echo "</div><div id=map  style='min-height:400px; max-height:400px;'></div></div>";

$Center = Feature('FoodCenter',0);
if ($Center) {
  Init_Map(0,$Center,18,11); // Center on the Angel
} else {
  Init_Map(-1,0,18,11); // Center on the Default Loc
}

// </div></div><div id=Blob4>

echo "<h2>Looking for the best Food and Drink in town?</h2>";
echo "These establishments have supported the festival.<p>";

echo "<div class=Scrolltable><table class=InfoTable><tr class=FoodHead><td>Name<td>Address<td>Post Code<td>Phone<td>What's on offer over the festival<td>Directions";
  $Food = Gen_Get_Cond('FoodAndDrink', "Year=$PLANYEAR ORDER By Importance DESC, SN");
  foreach ($Food as $f) {
    echo "<tr><td>" . $f['SN'] . "<td>" . $f['Address'] . "<td>" . $f['PostCode'] . "<td>" . $f['Phone'] .
         "<td>" . $f['Description'] . "<td><button onclick=ShowDirect(" . (2000000 + $f['id']) . ")>Directions</button>\n";
  }

echo "</table></div>";

echo TnC('Food_And_Drink_Tail');
  dotail();
?>
