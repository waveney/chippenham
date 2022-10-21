<?php
  include_once("fest.php");
  include_once("GetPut.php");
  A_Check('Committee');

  dostaffhead("Manage Campsites");

  function Get_Campsites() {
    return Gen_Get_All('Campsites');
  }

  function Get_Campsite($id) {
    return Gen_Get('Campsites',$id);
  }

  function Put_Campsite(&$now) {
    return Gen_Put('Campsites',$now);
  }

function ListSites() {
  $Sites = Gen_Get_All('Campsites','ORDER BY Importance DESC');
        
  echo "<h1>Campsites</h1>";
  echo "Clicking on the name takes you to edit access<br>\n";
  
  $coln = 0;
  echo "<div class=tablecont><table id=indextable border width=100% style='min-width:1400px'>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Importance</a>\n";
  echo "</thead><tbody>";

  foreach($Sites as $S) {
    $Sid = $S['id'];
    echo "<tr><td><a href=Campsites?ACTION=SHOW&id=$Sid>$Sid</a><td><a href=Campsites?ACTION=SHOW&id=$Sid>" . $S['Name'] . "</a><td>";
    echo  $S['Importance'] . "\n";
  }
  echo "</table></div>\n";
        
  echo "<h2><a href=Campsites?ACTION=ADD>Add New Site</a></h2>\n";
}

function Show_Site(&$Site,$Act='UPDATE') {
  echo "Properties: 1=In use, 2=Restricted Use <br>";

  echo "<table border>";
  if (isset($Site['id'])) {
    echo "<form method=post action=Campsites?ACTION=$Act>";
    Register_AutoUpdate('Campsites',$Site['id']);
    echo "<td>Id:" . $Site['id'] . "\n";
  } else {
    echo "<form method=post action=Campsites?ACTION=CREATE>";
    echo fm_hidden('id',0);
  }
  echo "<tr>" . fm_text('Name',$Site,'Name') . "<td>Name of Campsite\n";
  echo "<tr>" . fm_text('Comment',$Site,'Comment'). "<td>eg Tents only\n";;
  echo "<tr>" . fm_text('Postcode',$Site,'Postcode') . "<td>Used for Directions\n";
  echo "<tr>" . fm_text('Address',$Site,'Address',3) . "<td>Used for Directions\n";
  echo "<tr>" . fm_number('Properties',$Site,'Props');
  echo "<tr>" . fm_number('Map Point',$Site,'MapPoint');
  echo "<tr>" . fm_number('Relative Importance',$Site,'Importance');
  echo "<tr>" . fm_textarea('Short Description',$Site,'ShortDesc',3,3);
  echo "<tr>" . fm_textarea('Long Description',$Site,'LongDesc',3,3);
  echo "<tr>" . fm_text('Image',$Cat,'Image',2); 
  echo "<tr>" . fm_text('If Restricted, to who',$Site,'Restriction',3) . "<td>eg just for Task Force\n";
  if (Access('SysAdmin')) echo "<tr><td class=NotSide>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea>";  
  echo "</table><br>\n";
  if (empty($Site['id'])) {
    echo "<h2><input type=submit name=ACTION value=$Act></h2><p>\n";
  } else {
    echo "<h2 hidden><input type=submit name=ACTION value=Update></h2>\n";
    if (Access('SysAdmin')) echo fm_hidden('id',$Site['id']) . "<h2><input type=submit name=ACTION value=DELETE></h2><p>\n";
  }
  echo "<h2><a href=Campsites.php?ACTION=LIST>Back to list of Categories</a></h2>\n";


}

// START HERE

  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
    case 'SHOW':
      $Sid = $_REQUEST['id'];
      $Site = Gen_Get('Campsites',$Sid);
      Show_Site($Site);
    break;
  
    case 'ADD':
      $Site = [];
      Show_Site($Site,'CREATE');
      break;
 
    case 'CREATE':
      if (isset($_REQUEST['Name'])) {
        Insert_db_post('Campsites', $Site);
      }
      ListSites();      
      break;
    
    case 'DELETE':
      $Sid = $_REQUEST['id'];      
      db_delete('Campsites',$Sid);
      echo "<h2>Deleted</h2>";
      ListSites();
      break;
    
    case 'UPDATE':
      if (isset($_REQUEST['id'])) {
        $Sid = $_REQUEST['id'];
        $Site = Gen_Get('Campsites',$Sid);
        Update_db_post('Campsites', $Site);
      } else {
        Insert_db_post('Campsites', $Site);
      }      
      Show_Site($Site,'CREATE');
      break;
    
    case 'LIST':
    default:
      ListSites();    
    
    }
    
  } else {
    ListSites();
  }
  dotail();
  
?>
