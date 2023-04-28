<?php
  include_once("fest.php");
  A_Check('Committee','?');//Sysadmin only at present

  dostaffhead("Manage Food and Drink");
  global $PLANYEAR;

function List_All() {
  $food=Gen_Get_All('FoodAndDrink');
  
  echo "Only those where Year is current will be shown.<p>\n";
  
  
  $coln = 0;
  echo "<form method=post>";
  echo "<div class=tablecont><table id=indextable border>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Index</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Year</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Website</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Phone</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Description</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Veg</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Vg</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Importance</a>\n";
  echo "</thead><tbody>";
  if ($food) foreach($food as $f) {
    $i = $f['id'];
    echo "<tr><td>$i<td><a href=FoodDrink?ACTION=Edit&i=$i>" . $f['Name'] . "</a><td>" . $f['Year'] . 
         "<td>" . ($f['Website']? "<a href=" . website($f['Website']) . ">" . $f['Website'] . "</a>" : "") ;
    echo "<td>" . $f['Phone'] . "<td>" . $f['Description'] . "<td>" . ($f['Vegetarian']?'Y':'') . "<td>" . ($f['Vegan']?'Y':'') . "<td>" . $f['Importance'];
    echo "\n";
  }
  echo "</table></div>\n";
  echo "<h2><a href=FoodDrink?ACTION=Add>Add an Entry</a></h2>";
  dotail();
}

function Edit_Food($i,$e=1) {
  global $PLANYEAR;
  echo "<form method=post action=FoodDrink>";
  if ($e) {
    $f = Gen_Get('FoodAndDrink',$i);
    Register_Autoupdate('FoodAndDrink',$i);
  } else {
    $f = ['Year'=>$PLANYEAR];
  }
  
  echo "<div class=tablecont><table border>\n";
  
  echo "<tr><td>Id:$i" . fm_text('Buisness Name',$f,'Name',2);


  echo "</table></div>\n";
  if (!e) {
    echo "<input type=submit name=ACTION value=Create>";
  }
  echo "<h2><a href=FoodDrink>Back to List of Food and Drink</a></h2>\n";
  dotail();
}

//  include_once("TradeLib.php");

  echo "<div class='content'><h2>Manage Food and Drink</h2>\n";
  
  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
      case 'Edit':
        Edit_Food($_REQUEST['i']);
        break;
      case 'Add':
        break;
      case 'Create':
        break;
      default:
        break;
    }
  }
  
  List_All();
  

  echo "Year is the most recent year they are a refiller.<p>";
// echo  Importance is a relative value (not yet used).<p>\n";
//  echo "Don't use Both - it does not work...<p>\n";

  $coln = 0;
  echo "<form method=post>";
  echo "<div class=tablecont><table id=indextable border>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Index</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Year</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Website</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Image URL</a>\n";
//  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Both</a>\n";
//  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Description</a>\n";
//  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Importance</a>\n";
  echo "</thead><tbody>";
  if ($Spons) foreach($Spons as $t) {
    $i = $t['id'];
    echo "<tr><td>$i" . fm_text1("",$t,'SN',1,'','',"SN$i");
    echo fm_number1('',$t,'Year','','',"Year$i");
    echo fm_text1("",$t,'Web',1,'','',"Web$i");
    echo fm_text1("",$t,'Image',1,'','',"Image$i");
//    echo "<td>" . fm_checkbox('',$t,'IandT','',"IandT$i");
//    echo "<td>" . fm_basictextarea($t,'Description',2,2,'',"Description$i");
//    echo fm_number1('',$t,'Importance','','',"Importance$i");
    echo "\n";
  }
  echo "<tr><td><td><input type=text name=SN0 >";
  echo "<td><input type=number name=Year0 value=$PLANYEAR>";
  echo "<td><input type=text name=Web0>";
  echo "<td><input type=text name=Image0>";
//  echo "<td><input type=checkbox name=IandT0>";
//  echo "<td><textarea name=Description0 rows=2 cols=40></textarea>";
//  echo "<td><input type=number name=Importance0>";
  echo "</table></div>\n";
  echo "<input type=submit name=Update value=Update>\n";
  echo "</form></div>";

  dotail();

?>
