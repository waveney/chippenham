<?php
  include_once("fest.php");

  include_once("Email.php");

  global $USER,$YEAR;

  A_Check('Staff');
  
$TinTypes = Gen_Get_All('TinTypes');
$TinStatus = ['','Lost'];

function ListTins() {
  global $TinTypes,$TinStatus;

  $Tins = Gen_Get_All('CollectingUnit');
  $coln = 0;
  
  $TNames = [];
  foreach ($TinTypes as $i=>$T) $TNames[$i] = $T['Name'];
  echo "<form method=Post Action=Collecting?ACTION=ListTinsUpdate>\n";
  echo "<div class=Scrolltable><table id=indextable border class=altcolours>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Type</a>\n";
  echo "<th colspan=2><a href=javascript:SortTable(" . $coln++ . ",'N')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Status</a>\n";
  echo "</thead><tbody>\n";
  
  foreach ($Tins as $i=>$T) {
    echo "<tr><td>$i<td>" . fm_select($TNames,$T,'Type',0,'',"Type$i") . fm_text1('',$T,'Name',2,'','',"Name$i") . 
         "<td>" . fm_select($TinStatus,$T,'Status',0,'',"Status$i") . "\n";
  }
  $T = [];
  echo "<tr><td>Add:<td>" . fm_select($TNames,$T,'Type0') . fm_text1('',$T,'Name0',2) . "<td>" . fm_select($TinStatus,$T,'Status0') . "\n";
  echo "</table></div>";
  echo "<input type=submit name=Update value=Update>";
}


function TinTypes() {
  global $TinTypes,$TinStatus;

  $coln = 0;

  echo "<form method=Post Action=Collecting?ACTION=TinTypesUpdate>\n";
  echo "<div class=Scrolltable><table id=indextable border class=altcolours>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "</thead><tbody>\n";

  foreach ($TinTypes as $i=>$T) {
    echo "<tr><td>$i" . fm_text1('',$T,'Name',2,'','',"Name$i");
  }
  $T = [];
  echo "<tr><td>0" . fm_text1('',$T,'Name',2,'','',"Name0");
  echo "</table></div>";
  echo "<input type=submit name=Update value=Update>";
  
}

// MAIN CODE HERE
  dostaffhead("Collecting",[]);
// var_dump($_REQUEST);
  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
    
      case 'ListTinsUpdate': // Manage Tin Types
        UpdateMany('CollectingUnit',0,$TinTypes); // Drop Through
      case 'ListTins': //Manage Tin pool
        ListTins();
        break;
      
      case 'Records': // Records this year

        break;

      case 'IO': // Tins in and out
      
        break;
        
      case 'Email': // Send out totals
      
        break;
        
      case 'Totals': // Show totals
      
        break;
        
      case 'TinTypesUpdate': // Manage Tin Types
        if (UpdateMany('TinTypes',0,$TinTypes)) $TinTypes = Gen_Get_All('TinTypes'); // Drop Through
      case 'TinTypes': // Manage Tin Types
        TinTypes();
        break;
            
    }
  }
  
  echo "<h2>";
  if (Access('Staff','Finance')) {
    echo "<a href=Collecting?ACTION=ListTins>List Tins</a>, ";
    echo "<a href=Collecting?ACTION=Records>List this year records</a>, ";  
    echo "<a href=Collecting?ACTION=Totals>Show Totals</a>, ";  
    echo "<a href=Collecting?ACTION=Email>Email Teams and Collectors their results</a>, ";  
    echo "<a href=Collecting?ACTION=TinTypes>Manage Tin Types</a>, ";  
  }
  echo "<a href=Collecting?ACTION=IO>Tins in and out</a></h2>";    

  dotail();

?>
