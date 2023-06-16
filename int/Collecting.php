<?php
  include_once("fest.php");
  include_once("DanceLib.php");
  include_once("VolLib.php");
  include_once("Email.php");

  global $USER,$YEAR;

  A_Check('Staff');
  
$TinTypes = Gen_Get_All('TinTypes');
$TinStatus = ['','Lost'];
$WhoCats = ['Dance Side','Volunteer','Other'];
$Thresh = Feature('RadioSelectThreshold',60);

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

function TinIssue($Reassign=0) {
  global $YEAR;
  
  $Tcat = $_REQUEST['WhoCat'];
  switch ($Tcat) {
    case 0: $TIden = $_REQUEST['SideId']; $Side = Get_Side($TIden); $TName = $Side['SN']; break;
    case 1: $TIden = $_REQUEST['VolMemb']; $Vol = Get_Volunteer($TIden); $TName = $Vol['SN']; break;
    case 2: $TIden = $TName = $_REQUEST['OtherName']; break;
  }
  
  $Tno = $_REQUEST['TinIdOut'];
  $Tin = Gen_Get('CollectingUnit', $Tno);
  
  $Rec = Gen_Get_Cond1('CollectingUse',"Year='$YEAR' AND CollectionUnitId=$Tno AND Value=0");
  if ($Rec) {
    if ($Reassign) {
      $Rec['Value'] = -1;
      Gen_Put('CollectingUse',$Rec); // Close old record 
    } else {
      $State = ($Rec['TimeIn'] ? 'Returned not empty': 'Not Returned');
      echo "<h2>" . $Tin['Name'] . " is assigned to $TName</h2>";
      echo "<form method=post action=Collecting>\n";
      echo fm_hidden('WhoCat',$Tcat) . fm_hidden('TinIdOut',$Tno);
      switch ($Tcat) {
        case 0: echo fm_hidden('SideId',$TIden); break;
        case 1: echo fm_hidden('VolMemb',$TIden) . fm_hidden('VolTeam',$_REQUEST['VolTeam']); break;
        case 2: echo fm_hidden('OtherName',$TName); break;
      }
      echo "<input type=submit name=ACTION value=Reassign><input type=submit name=ACTION value='Choose Another'>";
      dotail();
    }
  }
  $Rec = ['Year'=>$YEAR,'AssignType'=>$Tcat,'TimeOut'=>time(),'TimeIn'=>0,'Value'=>0,'CollectionUnitId'=>$Tno];
  if ($Tcat < 2) {
    $Rec['AssignTo'] = $TIden;
  } else {
    $Rec['AssignName'] = $TName;
  }
  Gen_Put('CollectingUse',$Rec);
  echo "<h2>" . $Tin['Name'] . " has been assigned to $TName</h2>";
}

function TinIO($Another=0) {
  global $YEAR,$TinTypes,$TinStatus,$VolCats,$CatStatus,$WhoCats,$Thresh;
  
  $Dance_Sides = Select_Come();
  $Collectors = [];
  if ($Another) {
    $WhoCat = $_REQUEST['WhoCat'];
  }
  foreach($VolCats as $Ci=>$VC) if ($VC['Props'] & VOL_USE) if ($VC['Props'] & VOL_Tins) $Collectors[$Ci] = [];
  
  $Vols = Gen_Get_Cond("Volunteers","Status=0 AND Money>0 ORDER BY SN");  
  foreach($Vols as $Vid=>$Vol) {
    $VY = Get_Vol_Year($Vid,$YEAR);
    if ( $CatStatus[$VY['Status']] == 'Confirmed') {
      foreach($VolCats as $Ci=>$VC) if (isset($Collectors[$Ci])) {
        $VCY = Get_Vol_Cat_Year($Vid,$Ci,$YEAR);
        if ( $CatStatus[$VCY['Status']] == 'Confirmed') $Collectors[$Ci][$Vid] = $Vol['SN'];
      }
    }
  }
  
  $VolTeams = [];
  foreach($VolCats as $Ci=>$VC) if (!empty($Collectors[$Ci]) ) $VolTeams[$Ci] = $VC['Name'];

  echo "<h1>Assign Tins</h1><div class=CollectDiv><form method=post action=Collecting>\n";
  echo "<h3>Select Who</h3>";
  echo fm_radio('Category',$WhoCats,$_REQUEST,'WhoCat','class=CollectWho1 oninput=SelectWhoCat(event)',0);
  
  
  echo "<div class=CollectDance id=CollectDance " . (($Another && ($WhoCat==0))?'>': "hidden>");
    if (count($Dance_Sides) > $Thresh) {
      echo fm_select($Dance_Sides,$_REQUEST,'SideId',0,' oninput=SelectDanceSide(event)');
    } else {
      echo fm_radio('',$Dance_Sides,$_REQUEST,'SideId',' oninput=SelectDanceSide(event)',-1);
    }
  echo "</div>";
  
  if ($VolTeams) {
    echo "<div class=CollectVol id=CollectVol " . (($Another && $WhoCat==1)?'>': "hidden>");
    echo fm_radio('Team',$VolTeams,$_REQUEST,'VolTeam','class=CollectTeam1  oninput=SelectTeam(event)',0);
    foreach($VolCats as $Ci=>$VC) if (!empty($Collectors[$Ci])) {

      echo "<div class=CollectTeam id=Collect$Ci " . (($Another && ($WhoCat==1) && ($_REQUEST['VolTeam'] == $Ci))?'>': "hidden>");
      if (count($Collectors[$Ci]) > $Thresh) {
        echo fm_select($Collectors[$Ci],$_REQUEST,'VolMemb',0,' oninput=SelectVolunteer(event)');
      } else {
        echo fm_radio('',$Collectors[$Ci],$_REQUEST,'VolMemb',' oninput=SelectVolunteer(event)',-1);
      }
      echo "</div>\n";
    }
    echo "</div>\n";
  }
    
  echo "<div class=CollectOther id=CollectOther " . (($Another && $WhoCat==2)?'>': "hidden>");
    echo "<p>" . fm_text('Name',$_REQUEST,'OtherName',2,'',' oninput=SelectOther(event)');
    echo "</div>\n";
    

  echo "<h3>Select Tin/Bucket/Reader</h3>";
  
  $Tins = Gen_Get_Cond('CollectingUnit', "Status=0 ORDER BY Name");
  $TinNames = [];
  foreach($Tins as $i=>$T) $TinNames[$i] = $T['Name'];
  if (count($TinNames) > $Thresh) {
    echo fm_select($TinNames,$_REQUEST,'TinIdOut',0,' oninput=EnableAssign(event)'); // Consider Type then name in the future
  } else {
    echo fm_radio('',$TinNames,$_REQUEST,'TinIdOut',' oninput=EnableAssign(event)',-1);
  }

  echo "<p><input id=TinTake class=TinNotYet readonly type=submit name=ACTION value='Assign'>";
  
  echo "<hr><h1>Return Tins</h1>\n";
  echo "If it is being returned unused please tick here: " . fm_checkbox('Not Used',$_REQUEST,'NotUsed') . "<p>";
  if (count($TinNames) > $Thresh) {
    echo fm_select($TinNames,$_REQUEST,'TinIdIn',0,' oninput=EnableReturn(event)'); // Consider Type then name in the future
  } else {
    echo fm_radio('',$TinNames,$_REQUEST,'TinIdIn',' oninput=EnableReturn(event)',-1);
  }

  echo "<p><input id=TinReturn class=TinReturnNotYet readonly type=submit name=ACTION value='Returned'>";
  
  
  echo "</form></div>\n";
}

function ReturnTin() {
  global $TinTypes;
  $TinNumb = $_REQUEST['TinIdIn'];
  $Tin = Gen_Get('CollectingUnit',$TinNumb);
  $Rec = Gen_Get_Cond1('CollectingUse',"CollectionUnitId=$TinNumb AND Value=0");
  if (!$Rec) {
    echo "<h2>" . $TinTypes[$Tin['Type']] . " " . $Tin['Name'] . " is not booked out</h2>";
    TinIO(2);
  }
  $Rec['TimeIn'] = time();
  if (!empty($_REQUEST['NotUsed'])) $Rec['Value'] = -1;
  Gen_Put('CollectingUse',$Rec);
  echo "<h1>This has been recorded - please put " . $Tin['Type'] . " " . $Tin['Name'] . 
       (!empty($Rec['Value'])?' back in the pile to be reused' : ' to be counted') . "</h1>";
  TinIO(0);
}

// MAIN CODE HERE
  dostaffhead("Collecting",["/css/Collecting.css","/js/Collecting.js"]);
//var_dump($_REQUEST);
  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
    
      case 'ListTinsUpdate': // Manage Tin Types
        UpdateMany('CollectingUnit',0,$TinTypes); // Drop Through
      case 'ListTins': //Manage Tin pool
        ListTins();
        break;
      
      case 'Records': // Records this year
        ShowAllRecords();
        break;

      case 'IO': // Tins in and out
        TinIO(0);
        break;
        
      case 'Assign': // Assign Tins
        TinIssue(0);
        break;
        
      case 'Reassign': // Reassign Tins
        TinIssue(1);
        break;

      case 'Choose Another': // Choose another tin
        unset($_REQUEST['TinIdOut']);
        TinIO(1);      
        break;

      case 'Email': // Send out totals
      
        break;
        
      case 'Totals': // Show totals
      
        break;

      case 'Count': // Show totals
      
        break;

      case 'Returned': // Return Tin
        ReturnTin();
        break;
        
      case 'TinTypesUpdate': // Manage Tin Types
        if (UpdateMany('TinTypes',0,$TinTypes)) $TinTypes = Gen_Get_All('TinTypes'); // Drop Through
      case 'TinTypes': // Manage Tin Types
        TinTypes();
        break;
            
    }
  }
  
  echo "<hr><h2>Other Actions:<ul>";
  if (Access('Staff','Finance')) {
    echo "<li><a href=Collecting?ACTION=ListTins>List Tins</a>";
    echo "<li><a href=Collecting?ACTION=Records>List this year records</a>";  
    echo "<li><a href=Collecting?ACTION=Count>Count Tins</a>";  
    echo "<li><a href=Collecting?ACTION=Totals>Show Totals</a>";  
    echo "<li><a href=Collecting?ACTION=Email>Email Teams and Collectors their results</a>";  
    echo "<li><a href=Collecting?ACTION=TinTypes>Manage Tin Types</a>";  
  }
  echo "<li><a href=Collecting?ACTION=IO>Tins in and out</a></ul>";    

  dotail();

?>
