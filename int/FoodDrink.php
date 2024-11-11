<?php
  include_once("fest.php");
  include_once("MapLib.php");

  A_Check('Committee','?');//Sysadmin only at present

  dostaffhead("Manage Food and Drink",["js/dropzone.js","css/dropzone.css" ]);
  global $PLANYEAR;

  $Types = Get_Map_Point_Types();
  foreach ($Types as $t) $Icons[] = $t['SN'];


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
    echo "<tr><td>$i<td><a href=FoodDrink?ACTION=Edit&i=$i>" . $f['SN'] . "</a><td>" . $f['Year'] .
         "<td>" . ($f['Website']? weblink($f['Website']) : "") ;
    echo "<td>" . $f['Phone'] . "<td>" . $f['Description'] . "<td>" . ($f['Vegetarian']?'Y':'') . "<td>" . ($f['Vegan']?'Y':'') . "<td>" . $f['Importance'];
    echo "\n";
  }
  echo "</table></div>\n";
  echo "<h2><a href=FoodDrink?ACTION=Add>Add an Entry</a></h2>";
  dotail();
}

function Edit_Food($i,$e=1) {
  global $PLANYEAR,$Icons;
  echo "<form method=post action=FoodDrink>";
  if ($e) {
    $f = Gen_Get('FoodAndDrink',$i);
    Register_Autoupdate('FoodAndDrink',$i);
  } else {
    $f = ['Year'=>$PLANYEAR,'MapImp'=>20];
  }

  echo "<div class=tablecont><table border>\n";

  echo "<tr><td>Id:<td>$i";
  echo "<tr>" . fm_text('Buisness Name',$f,'SN',2);
    echo "<td>Icon:<td>" . fm_select($Icons,$f,'Type');
  echo "<tr>" . fm_number('Importance',$f,'Importance') . fm_number('Map Importance (16-20)',$f,'MapImp') . "<td colspan=2>Importance just affects order of list if not alpha";
  echo "<tr>" . fm_text('Website',$f,'Website',2);
  echo "<tr>" . fm_text('Address',$f,'Address',2) . fm_text1('Post Code',$f,'PostCode');
  echo "<tr>" . fm_text('Phone',$f,'Phone', 2);
  echo "<tr>" . fm_text('Lat',$f,'Lat') . fm_text('Long',$f,'Lng');
  echo "<tr>" . fm_textarea('Description',$f,'Description',3,3);
  echo "<tr>" . fm_text('Year',$f,'Year') . "<td colspan=2>Only those with current year are listed";

//  echo "<tr><td colspan=2>" . fm_checkbox('Food',$f,'Food') . fm_checkbox('Drink',$f,'Drink');
  echo "<tr><td colspan=2>" . fm_checkbox('Vegetarian',$f,'Vegetarian') . fm_checkbox('Vegan',$f,'Vegan') .
       "<td colspan=2>Only if the have a proper choice with more than just veg curry";
  echo "<tr>" . fm_textarea('Notes',$f,'Notes',3,3);
  echo "<tr><td>Image:" . fm_DragonDrop(1, 'Photo','FoodAndDrink',$i,$f,1,'',1,'','Photo');
  echo "<tr>" . fm_textarea('Directions',$f,'Directions',3,1) . "<td colspan=2>to follow Google directions if necessary";;
  if (Access('SysAdmin')) {
    echo "<tr><td class=NotStaff>Debug<td colspan=5 class=NotStaff><textarea id=Debug></textarea><p><span id=DebugPane></span>";
  }
  echo "</table></div>\n";
  if (!$e) {
    echo "<input type=submit name=ACTION value=Create>";
  } else {
    echo "<input type=submit name=ACTION value='Update Map'> Click this after you change Lat/Long";
  }
  echo "<h2><a href=FoodDrink>Back to List of Food and Drink</a></h2>\n";
  dotail();
}

  echo "<div class='content'><h2>Manage Food and Drink</h2>\n";

//var_dump($_REQUEST);

  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
      case 'Edit':
        Edit_Food($_REQUEST['i']);
        break;
      case 'Add':
        Edit_Food(0,0);
        break;
      case 'Create':
        if (empty($_REQUEST['SN'])) $_REQUEST['SN'] = 'Nameless';
        $Food = [];
        Insert_db_post('FoodAndDrink', $Food);
        Update_MapPoints();
        break;
      case 'Update Map':
        Update_MapPoints();
        Edit_Food($_REQUEST['AutoRef']);
      default:
        break;
    }
  }

  List_All();

  dotail();

?>
