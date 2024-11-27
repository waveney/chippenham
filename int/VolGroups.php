<?php
  include_once("fest.php");

  dostaffhead("Volunteer Groups",[ "/js/Volunteers.js"]);

  include_once("VolLib.php");

  global $USER,$USERID,$db,$PLANYEAR,$StewClasses,$Relations,$Days;

  A_Check('Staff'); // Will refine gate later

function Show_Group($Cat,$Act='UPDATE') {
  echo "Properties: 1=In use, 2=Likes, 4=Dislikes, 8=Need Over 21, 10=Upload, 20=Experience, 40=Need Money, 80=Need DBS, " .
       " 100=OtherQ1, 200=OtherQ2, 400=OtherQ3, 800=OtherQ4, " .
       " 1000=LongQ1, 2000=LongQ2, 4000=LongQ3, 8000=LongQ4, " .
       " 10000=Special Camp, 20000=Don't list, 40000=Collecting Tins, 80000=Full This year<br>";
  echo "For List of When: 'Before' - long time before, 'Week' - week before, 0=Friday of festival, Fest=Just Fest, -1 = day before, 1 next day etc.  " .
       "upto -4 and +11<p>\n";

  echo "<table border>";
  if (isset($Cat['id'])) {
    echo "<form method=post action=VolGroups?ACTION=$Act>";
    Register_AutoUpdate('VolGroups',$Cat['id']);
    echo "<td>Id:" . $Cat['id'] . "\n";
  } else {
    echo "<form method=post action=VolGroups>";
    echo fm_hidden('id',0);
  }
  echo "<tr>" . fm_text('Name',$Cat,'Name',2) . "<td>Full name of volunteer Category\n";
  echo "<tr>" . fm_text('ShortName',$Cat,'ShortName',2)  . "<td>Short name of volunteer Category (May be the same)\n";
  echo "<tr>" . fm_hex('Properties',$Cat,'Props');
  echo          fm_text('Background Colour',$Cat,'Colour');
  echo "<tr>" . fm_number('Relative Importance',$Cat,'Importance');
  echo "<tr>" . fm_textarea('Short Description',$Cat,'Description',5,3);

  echo "<tr>" . fm_text('List of When',$Cat,'Listofwhen',4) . "<td>'Before','Week',-2,-1,0,1,2,3,...\n";
  echo "<tr>" . fm_text('Extra Like text',$Cat,'LExtra',4);
    echo "<td rowspan=10>";
  echo "<tr>" . fm_text('Extra Dislike text',$Cat,'DExtra',4);
  echo "<tr>" . fm_text('Other Question 1',$Cat,'OtherQ1',4);
  echo "<tr>" . fm_text('Extra Text 1',$Cat,'Q1Extra',4);
  echo "<tr>" . fm_text('Other Question 2',$Cat,'OtherQ2',4);
  echo "<tr>" . fm_text('Extra Text 2',$Cat,'Q2Extra',4);
  echo "<tr>" . fm_text('Other Question 3',$Cat,'OtherQ3',4);
  echo "<tr>" . fm_text('Extra Text 3',$Cat,'Q3Extra',4);
  echo "<tr>" . fm_text('Other Question 4',$Cat,'OtherQ4',4);
  echo "<tr>" . fm_text('Extra Text 4',$Cat,'Q4Extra',4);
  if (Access('SysAdmin')) echo "<tr><td class=NotSide>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea>";
  echo "</table><br>\n";
  if (empty($Cat['id'])) {
    echo "<h2><input type=submit name=ACTION value=$Act></h2><p>\n";
  }
  echo "<h2><a href=VolGroups.php?ACTION=LIST>Back to list of Groups</a></h2>\n";
}

function ListGroups() {
  $Cats = Gen_Get_All('VolGroups');

  echo "<h1>Volunteer Category Groups</h1>";
  echo "Clicking on the name takes you to edit access<br>\n";
  echo "Groups are: 0-Most, 1=Taskforce<p>";

  $coln = 0;
  echo "<div class=tablecont><table id=indextable border width=100% style='min-width:1400px'>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>InUse</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Properties</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Importance</a>\n";
  echo "</thead><tbody>";

  foreach($Cats as $C) {
    $Cid = $C['id'];
    echo "<tr><td><a href=VolGroups?ACTION=SHOW&id=$Cid>$Cid</a><td>" . $C['Name'] . "</a><td>";
    echo ['No','Yes'][($C['Props'] & VOL_USE)] . "<td>" . $C['Props'];
    echo "<td>" . $C['Importance'];
  }
  echo "</table></div>\n";

  echo "<h2><a href=VolGroups?ACTION=ADD>Add New Group</a></h2>\n";
}

function doactions($Act = 'LIST') {
  switch ($Act) {
    case 'LIST':
      ListGroups();
    break;


    case 'SHOW':
      $Cid = $_REQUEST['id'];
      $Cat = Gen_Get('VolGroups',$Cid);
      Show_Group($Cat);
    break;

    case 'ADD':
      $Cat = [];
      Show_Group($Cat,'CREATE');
      break;

    case 'CREATE':
      if (isset($_REQUEST['Name'])) {
        Insert_db_post('VolGroups', $Cat);
      }
      ListGroups();
      break;

    case 'SPARE':
      break;

    case 'UPDATE':
      if (isset($_REQUEST['id'])) {
        $Cid = $_REQUEST['id'];
        $Cat = Gen_Get('VolGroups',$Cid);
        Update_db_post('VolGroups', $Cat);
      } else {
        Insert_db_post('VolGroups', $Cat);
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
