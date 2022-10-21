<?php
  include_once("fest.php");

  dostaffhead("Donate Types");
 
  global $USER,$USERID,$db,$PLANYEAR;

  A_Check('Staff'); // Will refine gate later
  
function Show_Don($Don,$Act='UPDATE') {
  echo "<table border>";
  if (isset($Don['id'])) {
    echo "<form method=post action=DonateTypes?ACTION=$Act>";
    Register_AutoUpdate('Donations',$Don['id']);
    echo "<td>Id:" . $Don['id'] . "\n";
  } else {
    echo "<form method=post action=DonateTypes>";
    echo fm_hidden('id',0);
  }
  echo "<tr>" . fm_text('Value',$Don,'Value') . "<td>Value of Donation Category\n";
    echo "<td rowspan=6>";
    if (!empty($Don['Image'])) echo "<img src='" . $Don['Image'] . "' width=300>";
  echo "<tr>" . fm_number('In Use',$Don,'InUse');
  echo "<tr>" . fm_number('Relative Importance',$Don,'Importance');
  echo "<tr>" . fm_text('Text',$Don,'Text',2);
  echo "<tr>" . fm_text('Button Id',$Don,'ButtonId',2);
  echo "<tr>" . fm_text('Image',$Don,'Image',2); 
  if (Access('SysAdmin')) echo "<tr><td class=NotSide>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea>";  
  echo "</table><br>\n";
  if (empty($Don['id'])) {
    echo "<h2><input type=submit name=ACTION value=$Act></h2><p>\n";
  }
  echo "<h2><a href=DonateTypes.php?ACTION=LIST>Back to list of Types</a></h2>\n";
}

function ListDons() {
  $Dons = Gen_Get_All('Donations','ORDER BY Importance DESC');
        
  echo "<h1>Donation Buttons</h1>";
  echo "Clicking on the Value takes you to edit access<br>\n";
  
  $coln = 0;
  echo "<div class=tablecont><table id=indextable border width=100% style='min-width:1400px'>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Value</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>InUse</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Importance</a>\n";
  echo "</thead><tbody>";

  foreach($Dons as $D) {
    $Did = $D['id'];
    echo "<tr><td><a href=DonateTypes?ACTION=SHOW&id=Did>$Did</a><td><a href=DonateTypes?ACTION=SHOW&id=$Did>" . $D['Value'] . "</a><td>";
    echo ['No','Yes'][$D['InUse']] . "<td>" . $D['Importance'] . "\n";
  }
  echo "</table></div>\n";
        
  echo "<h2><a href=DonateTypes?ACTION=ADD>Add New Types</a></h2>\n";
}

function doactions($Act = 'LIST') {
  switch ($Act) {
    case 'LIST':
      ListDons();
    break;
  
  
    case 'SHOW':
      $Did = $_REQUEST['id'];
      $Don = Gen_Get('Donations',$Did);
      Show_Don($Don);
    break;
  
    case 'ADD':
      $Don = [];
      Show_Don($Don,'CREATE');
      break;
 
    case 'CREATE':
      if (isset($_REQUEST['Value'])) {
        Insert_db_post('Donations', $Don);
      }
      ListDons();      
      break;
        
    case 'SPARE':
      break;
  
    case 'UPDATE':
      if (isset($_REQUEST['id'])) {
        $Did = $_REQUEST['id'];
        $Don = Gen_Get('Donations',$Did);
        Update_db_post('Donations', $Don);
      } else {
        Insert_db_post('Donations', $Don);
      }      
      Show_Don($Don);
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
