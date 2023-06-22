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
$TinStates = ['Free','Being Used','Needs Counting'];
$Thresh = Feature('RadioSelectThreshold',60);
$AltColours = ['#ff99ff', '#ccffff', '#ccffcc', '#ffffcc', '#ffcccc', '#e6ccff', '#cce6ff', '#ffd9b3', '#ecc6c6', '#ecc6d6', '#d6b3ff', '#d1e0e0', '#d6ff99',
     '#ffb3ff', '#b3b3ff', '#b3ffff', '#b3ffb3', '#ffffb3', '#ffb3b3', '#ecc6c6', '#ffb3cc', '#ffb3d9', '#ecc6d9', '#ffb3ff', '#ecc6ec', '#ecb3ff', '#e0b3ff',
     '#d9b3ff', '#d1d1e0', '#c6c6ec', '#b3b3ff', '#b3ccff', '#c2d1f0', '#c6d9ec', '#b3d9ff', '#d1e0e0', '#c6ecd9', '#d9ffb3', '#e5e5cc', '#e0e0d1', '#ecd9c6',
     '#ffd9b3', '#ffe6b3', '#ffc6b3', '#ffccb3' ];
     
function ShowList($Pfx,&$List, $Fld, $Action='') {
  global $AltColours,$Thresh;
  $LLen = count($List);
  if ($LLen > $Thresh) {
    echo $Pfx . fm_select($List,$_REQUEST,$Fld,0,($Action?" oninput=$Action(event)":'')); 
  } else {
    if ($LLen == 1) $_REQUEST[$Fld] = array_key_first($List);
    echo fm_radio($Pfx,$List,$_REQUEST,$Fld,($Action?" oninput=$Action(event)":''),-1,'','',$AltColours);
  }
}     
     
// 0 = Free, 1=Out, 2=Counting
function Get_Tins_And_States() {
  global $Tins,$YEAR;
  $Tins = Gen_Get_Cond('CollectingUnit','Status=0');
  
  $Records = Gen_Get_Cond('CollectingUse',"Year='$YEAR' AND Value=0");
  foreach ($Tins as &$T) $T['State'] = 0;
  foreach ($Records as $R) $Tins[$R['CollectionUnitId']]['State'] = ($R['TimeIn']?2:1);
}

function ListTins() {
  global $TinTypes,$TinStatus,$Tins,$TinStates;

  Get_Tins_And_States();
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
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>State</a>\n";
  echo "</thead><tbody>\n";

//var_dump($Tins);
  
  foreach ($Tins as $i=>$T) {

    echo "<tr><td>$i<td>" . fm_select($TNames,$T,'Type',0,'',"Type$i") . fm_text1('',$T,'Name',2,'','',"Name$i") . 
         "<td>" . fm_checkbox("Lost",$T,'Status'); // fm_select($TinStatus,$T,'Status',0,'',"Status$i") . 
    echo "<td>" . ($T['Status'] == 0 ? $TinStates[$T['State']] : 'Lost');
    if (($T['Status'] == 0) && ($T['State'] != 0)) {
      echo " (<a href=Collecting?ACTION=" . (($T['State'] == 1)?"Returned&TinIdIn=$i>Return":"Count&i=$i>Count") . "</a>)";
    }
         
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
  global $YEAR,$TinTypes,$TinStatus,$VolCats,$CatStatus,$WhoCats,$Thresh,$Tins,$AltColours;

  Get_Tins_And_States();  
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
  ShowList('',$WhoCats,'WhoCat','SelectWhoCat');
//  echo fm_radio('Category',$WhoCats,$_REQUEST,'WhoCat','class=CollectWho1 oninput=SelectWhoCat(event)',0,'','',$AltColours);

//function fm_radio($Desc,&$defn,&$data,$field,$extra='',$tabs=1,$extra2='',$field2='',$colours=0,$multi=0,$extra3='',$extra4='') {  
  
  echo "<div class=CollectDance id=CollectDance " . (($Another==1 && ($WhoCat==0))?'>': "hidden>");
    ShowList('',$Dance_Sides,'SideId','SelectDanceSide');
//    if (count($Dance_Sides) > $Thresh) {
//      echo fm_select($Dance_Sides,$_REQUEST,'SideId',0,' oninput=SelectDanceSide(event)');
//    } else {
//      echo fm_radio('',$Dance_Sides,$_REQUEST,'SideId',' oninput=SelectDanceSide(event)',-1,'','',$AltColours);
//    }
  echo "</div>";
  
  if ($VolTeams) {
    echo "<div class=CollectVol id=CollectVol " . (($Another==1 && $WhoCat==1)?'>': "hidden>");
    echo "&nbsp;<br>" . fm_radio('Team',$VolTeams,$_REQUEST,'VolTeam','class=CollectTeam1  oninput=SelectTeam(event)',0,'','',$AltColours);
    foreach($VolCats as $Ci=>$VC) if (!empty($Collectors[$Ci])) {

      echo "<div class=CollectTeam id=Collect$Ci " . (($Another==1 && ($WhoCat==1) && ($_REQUEST['VolTeam'] == $Ci))?'>': "hidden>");
      ShowList('',$Collectors[$Ci],'VolMemb','SelectVolunteer');
/*      if (count($Collectors[$Ci]) > $Thresh) {
        echo fm_select($Collectors[$Ci],$_REQUEST,'VolMemb',0,' oninput=SelectVolunteer(event)');
      } else {
        echo fm_radio('',$Collectors[$Ci],$_REQUEST,'VolMemb',' oninput=SelectVolunteer(event)',-1,'','',$AltColours);
      }*/
      echo "</div>\n";
    }
    echo "</div>\n";
  }
    
  echo "<div class=CollectOther id=CollectOther " . (($Another==1 && $WhoCat==2)?'>': "hidden>");
    echo fm_text('Name',$_REQUEST,'OtherName',2,'',' oninput=SelectOther(event)');
    echo "</div>\n";
  
  if ($Another != 2) {
    echo "<h3>Select Tin/Bucket/Reader</h3>";
    echo "If it is not listed here please check the (<a href=Collecting?ACTION=Records>Records</a>)<p>";
    
//    $Tins = Gen_Get_Cond('CollectingUnit', "Status=0 ORDER BY Name");
    $TinNames = [];
    foreach($Tins as $i=>$T) if ($T['State'] == 0) $TinNames[$i] = $TinTypes[$T['Type']]['Name'] . " - " . $T['Name'];
    ShowList('',$TinNames,'TinIdOut','EnableAssign');
/*    if (count($TinNames) > $Thresh) {
      echo fm_select($TinNames,$_REQUEST,'TinIdOut',0,' oninput=EnableAssign(event)'); // Consider Type then name in the future
    } else {
      echo fm_radio('',$TinNames,$_REQUEST,'TinIdOut',' oninput=EnableAssign(event)',-1,'','',$AltColours);
    }*/

    echo "<p><input id=TinTake class=TinNotYet disabled type=submit name=ACTION value='Assign'>";
  } else {
    echo fm_hidden('TinIdIn',$_REQUEST['TinIdIn']);
    echo "<p><input id=TinTake class=TinNotYet disabled type=submit name=ACTION value='Returned'>";
    return;
  }
  
  echo "<hr><h1>Return Tins</h1>\n";
  echo "If it is not listed here please check the (<a href=Collecting?ACTION=Records>Records</a>)<p>";
  echo "If it is being returned unused please tick here: " . fm_checkbox('Not Used',$_REQUEST,'NotUsed') . "<p>";
  
  $TinNames = [];
  foreach($Tins as $i=>$T) if ($T['State'] == 1) $TinNames[$i] = $TinTypes[$T['Type']]['Name'] . " - " . $T['Name'];
  ShowList('',$TinNames,'TinIdIn','EnableReturn');
/*  if (count($TinNames) > $Thresh) {
    echo fm_select($TinNames,$_REQUEST,'TinIdIn',0,' oninput=EnableReturn(event)'); // Consider Type then name in the future
  } else {
    echo fm_radio('',$TinNames,$_REQUEST,'TinIdIn',' oninput=EnableReturn(event)',-1,'','',$AltColours);
  }*/

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

function Get_All_Data() { // Used for Showing records, totals and emails
  global $TinTypes,$YEAR,$WhoCats,$ColCount,$Records,$Finished,$TotalValue;

  $Records = Gen_Get_Cond('CollectingUse',"Year='$YEAR'");
  $Dance_Sides = Select_Come();
  $Vols = Gen_Get_Cond("Volunteers","Status=0 AND Money>0 ORDER BY SN");

  $Finished = 1;
  $TotalValue = 0;
  $ColCount = [];

  foreach($Records as $id=>&$R) {
    switch ($R['AssignType']) {
      case 0: $ColLink = "<a href=AddPerf?i=" . $R['AssignTo'] . ">"; $CName = $Dance_Sides[$R['AssignTo']]; break;
      case 1: $ColLink = "<a href=Volunteers?A=Show&id=" . $R['AssignTo'] . ">"; $CName = $Vols[$R['AssignTo']]['SN']; break;
      case 2: $ColLink = ''; $CName = $R['AssignName']; break;
    }
    
    $R['Name'] = $CName;
    $R['ColLink'] = $ColLink;
    
    if ($R['Value'] == 0) {
      $Finished = 0;
      continue;
    } 
    $val = (($R['Value'] < 0)? 0 : $R['Value']/100);

    $TotalValue += $val;
    if (isset($ColCount[$CName])) {
      $ColCount[$CName]['Value'] += $val;
      $ColCount[$CName]['Tins'][] = $id;                
    } else {
      $ColCount[$CName]['Value'] = $val;
      $ColCount[$CName]['Link'] = $ColLink;
      $ColCount[$CName]['Tins'] = [$id];
      $ColCount[$CName]['Cat'] = $R['AssignType'];
      $ColCount[$CName]['Who'] = $R['AssignTo'];
    }
  }
  return ($Finished?$TotalValue:0) ;
}

function ShowAllRecords() {
  global $TinTypes,$YEAR,$WhoCats,$ColCount,$Records;
  
  Get_All_Data();
  
  $Tins = Gen_Get_All('CollectingUnit');

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
    echo ($R['ColLink'] ? $R['ColLink'] : '') . $R['Name'] . ($R['ColLink'] ? '</a>' : '');
/*    switch ($R['AssignType']) {
      case 0: echo "<a href=AddPerf?i=" . $R['AssignTo'] . ">" . ($CName = $Dance_Sides[$R['AssignTo']]) . "</a>"; break;
      case 1: echo "<a href=Volunteers?A=Show&id=" . $R['AssignTo'] . ">" . ($CName = $Vols[$R['AssignTo']]['SN']) . "</a>"; break;
      case 2: echo ($CName = $R['AssignName']); break;
    }*/
    echo "<td>" . date('D H:i:s',$R['TimeOut']) . "<td>";
      if ( $R['TimeIn']) {
        echo date('D H:i:s',$R['TimeIn']);
      } else if ($R['Value']) {
        echo "Unknown";
      } else {
        echo "<b>Out</b> (<a href=Collecting?ACTION=Returned&TinIdIn=" . $R['CollectionUnitId'] . ">Return</a>)";
      }
    echo "<td align=right>";
      if (($R['TimeIn']==0) && ($R['Value'] == 0)) {
        echo '?';
      } else if ($R['Value']<0) {
        echo '£0.00';
      } else if ($R['Value'] == 0) {
        echo "Not Counted (<a href=Collecting?ACTION=Count&i=" . $R['CollectionUnitId'] . ">Count</a>)";
      } else {
        $val = $R['Value']/100;
        echo sprintf('£%0.2f',$val);
/*        $TotalValue += $val;
        if (isset($ColCount[$CName])) {
          $ColCount[$CName] += $val;
        } else {
          $ColCount[$CName] = $val;        
        }*/
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
    $tid = -1;
    foreach($Tins as $i=>$T) if (isset($ToBeCounted[$i])) {
      $TinNames[$i] = $T['Name'];
      $tid = $i;
    }

    ShowList('',$TinNames,'TinIdIn','EnableCount');
/*    if (count($TinNames) > $Thresh) {
      echo fm_select($TinNames,$_REQUEST,'TinIdIn',0,' oninput=EnableCount(event)'); // Consider Type then name in the future
    } else {
      if (count($TinNames) == 1) $_REQUEST['TinIdIn'] = $tid;
      echo fm_radio('',$TinNames,$_REQUEST,'TinIdIn',' oninput=EnableCount(event)',-1);
    }*/
  } else {
    echo fm_hidden('TinIdIn', $TinId);
  }

  echo  fm_text0("<p><H2>Value (in pounds)</h2>",$_REQUEST,'Value',3,'',' oninput=EnableCount(event)');
  
  echo fm_text('<p>Add a note:',$_REQUEST,'Note',2) . " - only for weird cases please<p>";
  echo "<input id=TinCount class=TinCountNotYet disabled type=submit name=ACTION value=Counted>\n";
}

function TinCounted() {
  global $TinTypes,$YEAR,$WhoCats,$Thresh,$USER;
//var_dump($_REQUEST);
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

function ShowTotals() {
  global $TinTypes,$YEAR,$WhoCats,$ColCount,$Records;
  
  $Finished = Get_All_Data();
  $Tins = Gen_Get_All('CollectingUnit');
    
  if ($Finished) {
    echo "<h2>All tins are counted.  The totals are:</h2>";
  } else {
    echo "<h2>The interim totals are:</h2>";
  }
  
  uasort($ColCount, function ($a,$b) { return $b['Value'] <=> $a['Value'];});

  echo "<div class=Scrolltable><table border class=altcolours>\n";
  echo "<th>User Type<th>User Name<th>Device<th>When<th>Sub Total<th>Total Value<th>Email<th>Phone\n";
  if ($Finished) echo "<th>Send Email";
  echo "</thead><tbody>\n";

  foreach ($ColCount as $Name=>$Col) {
    $Rows = count($Col['Tins']);
    echo "<tr><td rowspan=$Rows>" . $WhoCats[$Col['Cat']] . "<td rowspan=$Rows>";
    echo ($Col['Link'] ? $Col['Link'] : '') . $Name . ($Col['Link'] ? '</a>' : '');
//    echo "<td>"; // Tins
/*      if (count($Col['Tins']) == 1) {
        $R = $Records[$Col['Tins'][0]];
        $Tin = $Tins[$R['CollectionUnitId']];
        echo $TinTypes[$Tin['Type']]['Name'] . ": " . $Tin['Name'];
      } else {
        echo "<table border>";*/
        $TinNum = 0;
        foreach($Col['Tins'] as $Rid) {
          $R = $Records[$Rid];
          $Tin = $Tins[$R['CollectionUnitId']];
          if ($TinNum) echo "<tr>";
          echo "<td>" . $TinTypes[$Tin['Type']]['Name'] . ": " . $Tin['Name'] . "<td>";
          echo "Out: " . date('D H:i:s',$R['TimeOut']) . " In: " . date('D H:i:s',$R['TimeIn']);
          echo "<td align=right>" . ($R['Value'] <0 ? 0 :  sprintf('£%0.2f',$R['Value']/100));
          
          if ($TinNum++ == 0) {
            echo "<td rowspan=$Rows align=right>" . sprintf('£%0.2f',$Col['Value']);
            switch ($Col['Cat']) {
              case 0:
                $Sid = $Col['Who'];
                $Side = Get_Side($Sid); 
                echo "<td class=smalltext style='max-width:180;overflow-x:auto;' rowspan=$Rows>" . $Side['Email'] . "<td rowspan=$Rows>" . ($Side['Phone'] ?? $Side['Mobile']); 
                if ($Finished) echo "<td rowspan=$Rows><button type=button class=ProfButton onclick=ProformaSend('Dance_Collect_Info',$Sid,'CollectInfo','SendProfEmail')" . 
                     Proforma_Background('FinalInfo') . ">Send Thanks</button>";

                break;
              case 1: 
                $Vol = Get_Volunteer($Col['Who']); 
                echo "<td class=smalltext style='max-width:180;overflow-x:auto;' rowspan=$Rows>" . $Vol['Email'] . "<td rowspan=$Rows>" . $Vol['Phone']; 
                if ($Finished) echo "<td rowspan=$Rows>EMAIL";
                break;
              case 2: 
                echo "<td rowspan=$Rows><td rowspan=$Rows><td rowspan=$Rows>\n";
            }
          }
        }
//        echo "</table>";
//      }
    
  }
  echo "</table></div>\n";
}

function CollectInfo(&$Data,$type=0) { // 0 =Dance, 1=Vol
  global $TinTypes,$YEAR,$WhoCats,$ColCount,$Records;
  
  $Finished = Get_All_Data();
  $Tins = Gen_Get_All('CollectingUnit');
    
  if ($Finished) {
    $Str = "The details are:\n";
  } else {
    $Str = "The interim details are:\n";
  }
  
  uasort($ColCount, function ($a,$b) { return $b['Value'] <=> $a['Value'];});

  $Posn = 0;
  foreach ($ColCount as $CName=>$Col) {
    $Posn++;
    if ((($type == 0) && ($Data['SideId'] == $Col['Who'])) ||
        (($type == 1) && ($Data['id'] == $Col['Who']))) {
      
      $Str .= "You collected ";
      // Tins
      
      // Total
      
      // if (Posn < N state your position
    
      return $Str;
    }
  }
}

function CollectActions() {
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
        ShowTotals();
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
    echo "<li><a href=Collecting?ACTION=ListTins>Manage Tins</a>";
    echo "<li><a href=Collecting?ACTION=CurrentTins>Current Tins</a>";
    echo "<li><a href=Collecting?ACTION=Records>List this year records</a>";  
    echo "<li><a href=Collecting?ACTION=Count>Count Tins</a>";  
    echo "<li><a href=Collecting?ACTION=Totals>Show Totals</a>";  
    echo "<li><a href=Collecting?ACTION=Email>Email Teams and Collectors their results</a>";  
    echo "<li><a href=Collecting?ACTION=TinTypes>Manage Tin Types</a>";  
  }
  echo "<li><a href=Collecting?ACTION=IO>Tins in and out</a></ul>";    

  dotail();
}

?>
