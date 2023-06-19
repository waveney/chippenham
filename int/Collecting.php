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
  echo "<form method=Post Action=Collecting?ACTION=ListTinsUpdate id=ColForm>\n";
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

  echo "<form method=Post Action=Collecting?ACTION=TinTypesUpdate id=ColForm>\n";
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
  global $YEAR,$TinTypes;
  
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
      echo "<h2>" . $TinTypes[$Tin['Type']]['Name'] . " " . $Tin['Name'] . " is assigned to $TName</h2>";
      echo "<form method=post action=Collecting id=ColForm>\n";
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
  echo "<h2>" . $TinTypes[$Tin['Type']]['Name'] . " " . $Tin['Name'] . " has been assigned to $TName</h2>";
}

function TinIO($Another=0) { // 0 - Normal, 1 - select another tin, 2 - have tin select collector
  global $YEAR,$TinTypes,$TinStatus,$VolCats,$CatStatus,$WhoCats,$Thresh;
  
  $Dance_Sides = Select_Come();
  $Collectors = [];
  if ($Another==1) {
    $WhoCat = $_REQUEST['WhoCat'];
  } else if ($Another==2) {
    $WhoCats []= 'Unknown';
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

  if ($Another!=2) echo "<h1>Assign Tins</h1>";
  echo "<div class=CollectDiv><form method=post action=Collecting id=ColForm>\n";
  echo "<h3>Select Who</h3>";
  echo fm_radio('Category',$WhoCats,$_REQUEST,'WhoCat','class=CollectWho1 oninput=SelectWhoCat(event)',0);
  
  
  echo "<div class=CollectDance id=CollectDance " . (($Another==1 && ($WhoCat==0))?'>': "hidden>");
    if (count($Dance_Sides) > $Thresh) {
      echo fm_select($Dance_Sides,$_REQUEST,'SideId',0,' oninput=SelectDanceSide(event)');
    } else {
      echo fm_radio('',$Dance_Sides,$_REQUEST,'SideId',' oninput=SelectDanceSide(event)',-1);
    }
  echo "</div>";
  
  if ($VolTeams) {
    echo "<div class=CollectVol id=CollectVol " . (($Another==1 && $WhoCat==1)?'>': "hidden>");
    echo fm_radio('Team',$VolTeams,$_REQUEST,'VolTeam','class=CollectTeam1  oninput=SelectTeam(event)',0);
    foreach($VolCats as $Ci=>$VC) if (!empty($Collectors[$Ci])) {

      echo "<div class=CollectTeam id=Collect$Ci " . (($Another==1 && ($WhoCat==1) && ($_REQUEST['VolTeam'] == $Ci))?'>': "hidden>");
      if (count($Collectors[$Ci]) > $Thresh) {
        echo fm_select($Collectors[$Ci],$_REQUEST,'VolMemb',0,' oninput=SelectVolunteer(event)');
      } else {
        echo fm_radio('',$Collectors[$Ci],$_REQUEST,'VolMemb',' oninput=SelectVolunteer(event)',-1);
      }
      echo "</div>\n";
    }
    echo "</div>\n";
  }
    
  echo "<div class=CollectOther id=CollectOther " . (($Another==1 && $WhoCat==2)?'>': "hidden>");
    echo "<p>" . fm_text('Name',$_REQUEST,'OtherName',2,'',' oninput=SelectOther(event)');
    echo "</div>\n";
  
  if ($Another != 2) {
    echo "<h3>Select Tin/Bucket/Reader</h3>";
  
    $Tins = Gen_Get_Cond('CollectingUnit', "Status=0 ORDER BY Name");
    $TinNames = [];
    foreach($Tins as $i=>$T) $TinNames[$i] = $T['Name'];
    if (count($TinNames) > $Thresh) {
      echo fm_select($TinNames,$_REQUEST,'TinIdOut',0,' oninput=EnableAssign(event)'); // Consider Type then name in the future
    } else {
      echo fm_radio('',$TinNames,$_REQUEST,'TinIdOut',' oninput=EnableAssign(event)',-1);
    }

    echo "<p><input id=TinTake class=TinNotYet disabled type=submit name=ACTION value='Assign'>";
  } else {
    echo fm_hidden('TinIdIn',$_REQUEST['TinIdIn']);
    echo "<p><input id=TinTake class=TinNotYet disabled type=submit name=ACTION value='Returned'>";
    return;
  }
  
  echo "<hr><h1>Return Tins</h1>\n";
  echo "If it is being returned unused please tick here: " . fm_checkbox('Not Used',$_REQUEST,'NotUsed') . "<p>";
  if (count($TinNames) > $Thresh) {
    echo fm_select($TinNames,$_REQUEST,'TinIdIn',0,' oninput=EnableReturn(event)'); // Consider Type then name in the future
  } else {
    echo fm_radio('',$TinNames,$_REQUEST,'TinIdIn',' oninput=EnableReturn(event)',-1);
  }

  echo fm_text('<p>Add a note:',$_REQUEST,'Note',2) . " - only for weird cases please";
  echo "<p><input id=TinReturn class=TinReturnNotYet disabled type=submit name=ACTION value='Returned'>";
  
  
  echo "</form></div>\n";
}

function ReturnTin() {
  global $TinTypes,$USER;
  $TinNumb = $_REQUEST['TinIdIn'];
  $Tin = Gen_Get('CollectingUnit',$TinNumb);
  $Rec = Gen_Get_Cond1('CollectingUse',"CollectionUnitId=$TinNumb AND Value=0");
  if (!$Rec) {
    echo "<h2>" . $TinTypes[$Tin['Type']]['Name'] . " " . $Tin['Name'] . " is not booked out</h2>";
    TinIO(2); 
    return;
  }
  $Rec['TimeIn'] = time();
  if (!empty($_REQUEST['NotUsed'])) $Rec['Value'] = -1;
  if (!empty($_REQUEST['Note'])) $Rec['Notes'] .= $_REQUEST['Note'];// . " - " . $USER['SN'] . "\n";
  Gen_Put('CollectingUse',$Rec);
  echo "<h1>This has been recorded - please put " . $TinTypes[$Tin['Type']]['Name'] . ": " . $Tin['Name'] . 
       (!empty($Rec['Value'])?' back in the pile to be reused' : ' to be counted') . "</h1><hr>";
  TinIO(0);
}

function ShowAllRecords() {
  global $TinTypes,$YEAR,$WhoCats;
  
  $Tins = Gen_Get_All('CollectingUnit');
  $Records = Gen_Get_Cond('CollectingUse',"Year='$YEAR'");
  $Dance_Sides = Select_Come();
  $Vols = Gen_Get_Cond("Volunteers","Status=0 AND Money>0 ORDER BY SN");
  $OtherNames = [];
  $Finished = 1;
  $TotalValue = 0;
  $ColCount = [];

  echo "To edit a record click on the id number<p>";
  $coln = 0;
  echo "<div class=Scrolltable><table id=indextable border class=altcolours>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>User Type</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>User Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'D')>Time Out</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'D')>Time In</a>\n"; 
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Value</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Device Type</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Device Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Notes</a>\n";
  echo "</thead><tbody>\n";
  
  foreach($Records as $id=>$R) {
    echo "<tr><td><a href=Collecting?ACTION=Edit&i=$id>$id</a><td>" . $WhoCats[$R['AssignType']] . "<td>";
    switch ($R['AssignType']) {
      case 0: echo "<a href=AddPerf?i=" . $R['AssignTo'] . ">" . ($CName = $Dance_Sides[$R['AssignTo']]) . "</a>"; break;
      case 1: echo "<a href=Volunteers?A=Show&id=" . $R['AssignTo'] . ">" . ($CName = $Vols[$R['AssignTo']]['SN']) . "</a>"; break;
      case 2: echo ($CName = $R['AssignName']); break;
    }
    echo "<td>" . date('D H:i:s',$R['TimeOut']) . "<td>";
      if ( $R['TimeIn']) {
        echo date('D H:i:s',$R['TimeIn']);
      } else if ($R['Value']) {
        echo "Unknown";
      } else {
        echo "<b>Out</b> (<a href=Collecting?ACTION=Returned&TinIdIn=" . $R['CollectionUnitId'] . ">Return</a>)";
        $Finished = 0;
      }
    echo "<td align=right>";
      if (($R['TimeIn']==0) && ($R['Value'] == 0)) {
        echo '?';
        $Finished = 0;
      } else if ($R['Value']<0) {
        echo '£0.00';
      } else if ($R['Value'] == 0) {
        echo "Not Counted (<a href=Collecting?ACTION=Count&i=" . $R['CollectionUnitId'] . ">Count</a>)";
        $Finished = 0;
      } else {
        $val = $R['Value']/100;
        echo sprintf('£%0.2f',$val);
        $TotalValue += $val;
        if (isset($ColCount[$CName])) {
          $ColCount[$CName] += $val;
        } else {
          $ColCount[$CName] = $val;        
        }
      }
//var_dump($R,$Tins[$R['CollectionUnitId']]);
    echo "<td>" . $TinTypes[$Tins[$R['CollectionUnitId']]['Type']]['Name'] . "<td>" . $Tins[$R['CollectionUnitId']]['Name'];
    echo "<td>";
    if (strlen($R['Notes'])< 20) {
      echo $R['Notes'];
    } else {
      echo "<a href=Collecting?ACTION=ShowComment&i=$id>Show Note</a>\n";
    }

//    echo "<td style='width:200;max-height:24;overflow:auto;'>" . $R['Notes'];
  }
  echo "</table></div>\n";
  
  if ($Finished) {
    echo "<h2>All tins are counted.  The totals are:</h2>";
  } else {
    echo "<h2>The interim totals are:</h2>";
  }
  
  arsort($ColCount);
  echo "<div class=Scrolltable><table border><tr><th>Name<th>Value\n"; // Needs to have keys and counter info to be useful 
  
  foreach ($ColCount as $Name=>$Value) echo "<tr><td>$Name<td align=right>" . sprintf('£%0.2f',$Value);
  echo "</table>\n";

}

function TinCount() {
  global $TinTypes,$YEAR,$WhoCats,$Thresh;

  $TinId = ($_REQUEST['i'] ?? 0);
  echo "<form method=post action=Collecting id=ColForm>";

  if (empty($TinId)) {  
    $Tins = Gen_Get_All('CollectingUnit');
    $Records = Gen_Get_Cond('CollectingUse',"Year='$YEAR' AND TimeIn!=0 AND Value=0");
    $ToBeCounted = [];
    foreach ($Records as $R) $ToBeCounted[$R['CollectionUnitId']] = 1;
  
    echo "<H1>Select Device:</h1>";
    echo "If it is not here, it is not recorded properly... (Go to the <a href=Collecting?ACTION=Records>Records</a>)<p>";

  
    $Tins = Gen_Get_Cond('CollectingUnit', "Status=0 ORDER BY Name");
    $TinNames = [];
    foreach($Tins as $i=>$T) if (isset($ToBeCounted[$i])) $TinNames[$i] = $T['Name'];

    if (count($TinNames) > $Thresh) {
      echo fm_select($TinNames,$_REQUEST,'TinIdIn',0,' oninput=EnableCount(event)'); // Consider Type then name in the future
    } else {
      echo fm_radio('',$TinNames,$_REQUEST,'TinIdIn',' oninput=EnableCount(event)',-1);
    }
  } else {
    echo fm_hidden('TinIdIn', $TinId);
  }

  echo  fm_text0("<p><H2>Value (in pounds)</h2>",$_REQUEST,'Value',3,'',' oninput=EnableCount(event)');
  
  echo fm_text('<p>Add a note:',$_REQUEST,'Note',2) . " - only for weird cases please<p>";
  echo "<input id=TinCount class=TinCountNotYet disabled type=submit name=ACTION value=Counted>\n";
}

function TinCounted() {
  global $TinTypes,$YEAR,$WhoCats,$Thresh,$USER;

  $TinId = $_REQUEST['TinIdIn'];
  $Value = $_REQUEST['Value'];
  $Tin = Gen_Get('CollectingUnit',$TinId);
  $Dance_Sides = Select_Come();
  $Vols = Gen_Get_Cond("Volunteers","Status=0 AND Money>0 ORDER BY SN");

  $R = Gen_Get_Cond1('CollectingUse',"Year='$YEAR' AND CollectionUnitId=$TinId AND Value=0");
  if (!$R) {
    echo "<h2 class=Err>Something went wrong, start again please (if you get this twice call Richard)</h2>";
    return;
  }
//var_dump($Value);exit;
  $R['Value'] = ($Value? $Value*100: -1); // Stored in pennies
  if (!empty($_REQUEST['Note'])) $Rec['Notes'] .= $_REQUEST['Note']; // . " - " . $USER['SN'] . "\n";
  Gen_Put('CollectingUse',$R);
  
  echo "<h2>That was collected by: "; 
    switch ($R['AssignType']) {
      case 0: echo "<a href=AddPerf?i=" . $R['AssignTo'] . ">" . $Dance_Sides[$R['AssignTo']] . "</a>"; break;
      case 1: echo "<a href=Volunteers?A=Show&id=" . $R['AssignTo'] . ">" . $Vols[$R['AssignTo']]['SN'] . "</a>"; break;
      case 2: echo $R['AssignName']; break;
    }
  echo "</h2>\n";
  
  echo "<h2>Please put " . $TinTypes[$Tin['Type']]['Name'] . " " . $Tin['Name'] . " to be reused</h2>\n";
} 

function EditRecord() {
  global $TinTypes,$YEAR,$WhoCats,$Thresh;  
  $Rid = $_REQUEST['i'];
  $R = Gen_Get('CollectingUse',$Rid);
  
  echo "<form method=post action=Collecting id=ColForm>\n";
  Register_AutoUpdate('CollectingUse',$Rid);
  
  echo "<table border><tr><td>Id: $Rid" . fm_text('Year',$R,'Year');
  echo "<tr>" . fm_number('Assign Type',$R,'AssignType');
  echo "<tr>" . fm_number('Assign To',$R,'AssignTo');
  echo "<tr>" . fm_text('Assign Name',$R,'AssignName',2);
  echo "<tr>" . fm_number('Value in pennies',$R,'Value');
  echo "<tr>" . fm_number('Time Out (unix)',$R,'TimeOut');
  echo "<tr>" . fm_number('Time In (unix)',$R,'TimeIn');
  echo "<tr>" . fm_number('Collecting Unit Id',$R,'CollectingUnitId');
  echo "<tr>" . fm_textarea('Notes',$R,'Notes',5,3);
  echo "</table>";
}

function ShowComment() {
  global $TinTypes,$YEAR,$WhoCats,$Thresh;  
  $Rid = $_REQUEST['i'];
  $R = Gen_Get('CollectingUse',$Rid);

  $Tin = Gen_Get('CollectingUnit',$R['CollectionUnitId']);

  echo $TinTypes[$Tin['Type']]['Name'] . " " . $Tin['Name'] . "<br>";
  echo "Booked to: ";
    switch ($R['AssignType']) {
      case 0: $Side = Get_Side($R['AssignTo']); echo "<a href=AddPerf?i=" . $R['AssignTo'] . ">" . $Side['SN'] . "</a>"; break;
      case 1: $Vol = Get_Volunteer($R['AssignTo']); echo "<a href=Volunteers?A=Show&id=" . $R['AssignTo'] . ">" . $Vol['SN'] . "</a>"; break;
      case 2: echo ($CName = $R['AssignName']); break;
    }
  echo "<br>Issued: " . date('D H:i:s',$R['TimeOut']) . " Returned: " .  ($R['TimeIn'] ? date('D H:i:s',$R['TimeIn']) : " Not yet") . "<br>";
  echo ($R['Value'] < 0? '£0.00' : ($R['Value']==0? 'Not Counted' : "£" . $R['Value']/100)) . "<br>";
  echo "Notes: " . $R['Notes'] . "<p>";
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

      case 'Returned': // Return Tin
        ReturnTin();
        break;
        
      case 'Count': // Show totals
        TinCount();
        break;

      case 'Counted': // Show totals
        TinCounted();
        break;

      case 'TinTypesUpdate': // Manage Tin Types
        if (UpdateMany('TinTypes',0,$TinTypes)) $TinTypes = Gen_Get_All('TinTypes'); // Drop Through
      case 'TinTypes': // Manage Tin Types
        TinTypes();
        break;
      
      case 'Edit':
        EditRecord();
        break;
 
       case 'ShowComment':
        ShowComment();
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
