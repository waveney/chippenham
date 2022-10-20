<?php
  include_once("fest.php");

  dostaffhead("Volunteer Categories",[ "/js/Volunteers.js"]);
  include_once("GetPut.php");
  include_once("VolLib.php");
 
  global $USER,$USERID,$db,$PLANYEAR,$StewClasses,$Relations,$Days;

  A_Check('Staff'); // Will refine gate later
  
function Show_Cat($Cat,$Act='UPDATE') {
  echo "Properties: 1=In use, 2=Likes, 4=Dislikes, 8=Need Over 21, 10=Upload, 20=Experience, 40=Need Money, 80=Need DBS, " .
       " 100=OtherQ1, 200=OtherQ2, 400=OtherQ3, 800=OtherQ4, " .
       " 1000=LongQ1, 2000=LongQ2, 4000=LongQ3, 8000=LongQ4<br>";
  echo "For List of When: 'Before' - long time before, 'Week' - week before, 0=Friday of festival, -1 = day before, 1 next day etc.  upto -4 and +11<p>\n";

  echo "<table border>";
  if (isset($Cat['id'])) {
    echo "<form method=post action=VolCats?ACTION=$Act>";
    Register_AutoUpdate('VolCats',$Cat['id']);
    echo "<td>Id:" . $Cat['id'] . "\n";
  } else {
    echo "<form method=post action=VolCats>";
    echo fm_hidden('id',0);
  }
  echo "<tr>" . fm_text('Name',$Cat,'Name') . "<td>Full name of volunteer Category\n";
  echo "<tr>" . fm_text('ShortName',$Cat,'ShortName')  . "<td>Short name of volunteer Category (May be the same)\n";
  echo "<tr>" . fm_text('Email',$Cat,'Email') . "<td>Email address of category leader can by multiple separated by commas\n";
  echo "<tr>" . fm_hex('Properties',$Cat,'Props');
  echo "<tr>" . fm_number('Relative Importance',$Cat,'Importance');
  echo "<tr>" . fm_textarea('Description',$Cat,'Description',3,3);

  echo "<tr>" . fm_text('List of When',$Cat,'Listofwhen',2) . "<td>'Before','Week',-2,-1,0,1,2,3,...\n";
  echo "<tr>" . fm_text('Extra Like text',$Cat,'LExtra',2);
    echo "<td rowspan=10>";
    if (!empty($Cat['Image'])) echo "<img src='" . $Cat['Image'] . "' width=300>";
  echo "<tr>" . fm_text('Extra Dislike text',$Cat,'DExtra',2);
  echo "<tr>" . fm_text('Other Question 1',$Cat,'OtherQ1',2);
  echo "<tr>" . fm_text('Extra Text 1',$Cat,'Q1Extra',2);
  echo "<tr>" . fm_text('Other Question 2',$Cat,'OtherQ2',2);
  echo "<tr>" . fm_text('Extra Text 2',$Cat,'Q2Extra',2);
  echo "<tr>" . fm_text('Other Question 3',$Cat,'OtherQ3',2); 
  echo "<tr>" . fm_text('Extra Text 3',$Cat,'Q3Extra',2); 
  echo "<tr>" . fm_text('Other Question 4',$Cat,'OtherQ4',2); 
  echo "<tr>" . fm_text('Extra Text 4',$Cat,'Q4Extra',2); 
  echo "<tr>" . fm_textarea('Long Description',$Cat,'LongDesc',3,3); 
  echo "<tr>" . fm_text('Image',$Cat,'Image',2); 
  if (Access('SysAdmin')) echo "<tr><td class=NotSide>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea>";  
  echo "</table><br>\n";
  if (empty($Cat['id'])) {
    echo "<h2><input type=submit name=ACTION value=$Act></h2><p>\n";
  }
  echo "<h2><a href=VolCats.php?ACTION=LIST>Back to list of Categories</a></h2>\n";
}

function ListCats() {
  $Cats = Gen_Get_All('VolCats','ORDER BY Importance DESC');
        
  echo "<h1>Volunteer Categories</h1>";
  echo "Clicking on the name takes you to edit access<br>\n";
  
  $coln = 0;
  echo "<div class=tablecont><table id=indextable border width=100% style='min-width:1400px'>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>InUse</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Email</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Importance</a>\n";
  echo "</thead><tbody>";

  foreach($Cats as $C) {
    $Cid = $C['id'];
    echo "<tr><td><a href=VolCats?ACTION=SHOW&id=$Cid>$Cid</a><td><a href=VolCats?ACTION=SHOW&id=$Cid>" . $C['Name'] . "</a><td>";
    echo ['No','Yes'][($C['Props'] & VOL_USE)] . "<td>" . $C['Email'] . "<td>" . $C['Importance'] . "\n";
  }
  echo "</table></div>\n";
        
  echo "<h2><a href=VolCats?ACTION=ADD>Add New Category</a></h2>\n";
}

function doactions($Act = 'LIST') {
  switch ($Act) {
    case 'LIST':
      ListCats();
    break;
  
  
    case 'SHOW':
      $Cid = $_REQUEST['id'];
      $Cat = Gen_Get('VolCats',$Cid);
      Show_Cat($Cat);
    break;
  
    case 'ADD':
      $Cat = [];
      Show_Cat($Cat,'CREATE');
      break;
 
    case 'CREATE':
      if (isset($_REQUEST['Name'])) {
        Insert_db_post('VolCats', $Cat);
      }
      ListCats();      
      break;
        
    case 'SPARE':
      break;
  
    case 'UPDATE':
      if (isset($_REQUEST['id'])) {
        $Cid = $_REQUEST['id'];
        $Cat = Gen_Get('VolCats',$Cid);
        Update_db_post('VolCats', $Cat);
      } else {
        Insert_db_post('VolCats', $Cat);
      }      
      break;
    }

}
/* START HERE */

  if (isset($_REQUEST['ACTION'])) {
    doactions($_REQUEST['ACTION']);
  } else {
    doactions('LIST');
  } 
  
  dotail();
?>
