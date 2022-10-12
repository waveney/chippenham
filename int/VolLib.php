<?php
  include_once("fest.php");
  include_once("GetPut.php");
//  dostaffhead("Steward / Volunteer Application", ["/js/Volunteers.js"]);

  include_once("Email.php");
//  include_once("SignupLib.php");
  global $USER,$USERID,$db,$PLANYEAR,$StewClasses,$Relations;
  
$yesno = array('','Yes','No');
$Relations = array('','Husband','Wife','Partner','Son','Daughter','Mother','Father','Brother','Sister','Grandchild','Grandparent','Guardian','Uncle','Aunty',
                'Son/Daughter in law', 'Friend','Other');
$YearStatus = ['Not Submitted','Submitted','Withdrawn'];

define('VOL_USE',1);
define('VOL_Likes',2);
define('VOL_Dislikes',4);
define('VOL_Other1',8);
define('VOL_Other2',16);
define('VOL_Other3',32);

$VolCats = Gen_Get_All('VolCats','ORDER BY Importance DESC');

function Get_Vol_Details(&$vol) {
  global $VolCats,$Relations,$YEARDATA,$YEAR,$PLANYEAR,$YearStatus;
  $Volid = $vol['id'];
  $Body = "\nName: " . $vol['SN'] . "<br>\n";
  $Body .= "Email: <a href=mailto:" . $vol['Email'] . ">" . $vol['Email'] . "</a><br>\n";
  if ($vol['Phone']) $Body .= "Phone: " . $vol['Phone'] . "<br>\n";
  $Body .= "Address: " . $vol['Address'] . "<br>\n";
  if (isset($vol['PostCode'])) $Body .= "PostCode: " . $vol['PostCode'] . "<br>\n\n";
  $Body .= "<p>Disabilities: " . (empty($vol['Disabilities']) ? 'None' : $vol['Disabilities'] ) . "<p>\n\n";
  if (Feature('VolDBS')) {
    $Body .= "<p>DBS: " . (empty($vol['DBS']) ? 'No' : $vol['DBS'] ) . "<p>\n\n";
  }

  if (Feature('VolFirstAid')) {
    $Body .= "<p>First Aid: " . (empty($vol['FirstAid']) ? 'No' : $vol['FirstAid'] ) . "<p>\n\n";
  }

  $Body .= "Emergency Contact<br>\nName: " . $vol['ContactName'] . "<br>\n";
  $Body .= "Phone: " . $vol['ContactPhone'] . "<br>\n";
  $Body .= "Relationship: " . $Relations[$vol['Relation']] . "<br><p>\n";

//  $Body .= "Birthday: " . $vol['Birthday'] . "<br>\n";
  $Body .= "\n\n";

  foreach ($VolCats as $Cat) {
    $Catid = $Cat['id'];
    if (!($Cat['Props'] & VOL_USE )) continue;
      $cp = $Cat['Props'];
      $VCY = Get_Vol_Cat_Year($Volid,$Catid,$PLANYEAR);
      if (!empty($VCY['id']) && ($VCY['Props'] & VOL_USE)) {
        $Body .= "<p>Team: " . $Cat['Name'] . "<br>\n";
        if (($cp & VOL_Likes)  && !empty($VCY['Likes'])) $Body .= "Like: " . $VCY['Likes'] . "<br>\n";
        if (($cp & VOL_Dislikes)  && !empty($VCY['Dislikes'])) $Body .= "Dislike: " . $VCY['Dislikes'] . "<br>\n";
        if (($cp & VOL_Other1)  && !empty($VCY['Other1'])) $Body .= $Cat['OtherQ1'] . ": " . $VCY['Other1'] . "<br>\n";
        if (($cp & VOL_Other2)  && !empty($VCY['Other2'])) $Body .= $Cat['OtherQ2'] . ": " . $VCY['Other2'] . "<br>\n";
        if (($cp & VOL_Other3)  && !empty($VCY['Other3'])) $Body .= $Cat['OtherQ3'] . ": " . $VCY['Other3'] . "<br>\n";
      }
    }
  $Body .= "<p>\n";
  $Body . "Available:<p>\n";
  $VY = Get_Vol_Year($Volid);
  
  if (isset($VY['AvailBefore']) && $VY['AvailBefore'])  $Body .= "Before Festival: " . $VY["AvailBefore"] . "<br>\n";
  for ($day = $YEARDATA['FirstDay']-1; $day<= $YEARDATA['LastDay']+1; $day++) {
    $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
    if (isset($VY[$av]) && $VY[$av]) $Body .= FestDate($day,'M') . ": " . $VY[$av] . "<br>\n";
  }
  
  if (isset($VY['Notes']) && $VY['Notes']) $Body .= "<p>Notes: " . $VY['Notes'] . "<p>\n";
  
  if (isset($VY['id']) && $VY['id']) $Body .= "<p>Status: " . $YearStatus[$VY['Status']] . "<p>\n";

  return $Body;
}

function Vol_Details($key,&$vol) {
  global $FESTSYS;
  switch ($key) {
  case 'WHO': return firstword($vol['SN']);
  case 'DETAILS': return Get_Vol_Details($vol);
  case 'LINK' : return "<a href='https://" . $_SERVER['HTTP_HOST'] . "/int/Access?t=v&i=" . $vol['id'] . "&k=" . $vol['AccessKey'] . "'><b>link</b></a>";
  case 'FESTLINK' :
  case 'WMFFLINK' : return "<a href='https://" . $_SERVER['HTTP_HOST'] . "/int/Volunteers?A=View&id=" . $vol['id'] . "'><b>link</b></a>";
  }
}

function Email_Volunteer(&$vol,$messcat,$whoto) {
  global $PLANYEAR,$USER,$FESTSYS;
  Email_Proforma(5,$vol['id'],$whoto,$messcat,$FESTSYS['FestName'] . " $PLANYEAR and " . $vol['SN'],'Vol_Details',$vol,'Volunteer.txt');
}

function Get_Volunteer($id) { return Gen_Get('Volunteers',$id); };

function Put_Volunteer(&$now) { return Gen_Put('Volunteers',$now); };

function Get_Vol_Cat_Year($Volid,$CatId,$Year=0) {
  global $PLANYEAR;
  if ($Year == 0) $Year = $PLANYEAR;
  $VCY = Gen_Get_Cond1('VolCatYear'," Volid=$Volid AND CatId=$CatId AND Year=$Year ");
  if (isset($VCY['id'])) return $VCY;
  return ['Volid'=>$Volid,'CatId'=>$CatId,'Year'=>$Year,'id'=>0, 'Props'=>0];
}

function Put_Vol_Cat_Year(&$VCY) {
  Gen_Put('VolCatYear',$VCY);
}

function Get_Vol_Year($Volid,$Year=0) {
  global $PLANYEAR;
  if ($Year == 0) $Year = $PLANYEAR;
  $VY = Gen_Get_Cond1('VolYear'," Volid=$Volid AND Year=$Year ");

//var_dump($VY);
  if (isset($VY['id'])) return $VY;
  return ['Volid'=>$Volid,'Year'=>$Year,'id'=>0, 'Props'=>0];
}

function Put_Vol_Year(&$VY) {
  Gen_Put('VolYear',$VY);
}

function BeforeTeams() {
  global $VolCats;
  static $txt = '';
  if ($txt) return $txt;
  $teams = [];
  foreach ($VolCats as $Cat) if ((( $Cat['Props'] & VOL_USE) != 0) && (preg_match('/Before/',$Cat['Listofwhen'],$mtch))) $teams[] = $Cat['Name'];
  $txt = " Teams: " . implode(", ",$teams);
  return $txt;
}

function VolView(&$Vol) {
  echo Get_Vol_Details($Vol);
  if (Access('Committee','Volunteers'))  echo "<P><h2><a href=Volunteers?ACTION=Show&id=" . $Vol['id'] . ">Edit</a>";
  dotail();
}


function VolForm(&$Vol,$Err='',$View=0) {
  global $VolCats,$YEARDATA,$PLANYEAR,$YEAR,$Relations,$YearStatus;
  $Volid = $Vol['id'];
// var_dump($Vol);

  echo "<h2 class=subtitle>Steward / Volunteer Application Form</h2>\n";
  if (!empty($Err)) echo "<p class=Err>$Err<p>";
  echo "<form method=post action=Volunteers>";  
  Register_AutoUpdate('Volunteers',$Volid);
  echo fm_hidden('id',$Volid);  


    echo "This is in 3 parts.  The first records who you are.  This will be kept year to year, so you should only need to fill this in once.<p>\n";
    echo "The second part records which team(s) you would like to be part of, along with any likes and dislikes.<p>\n";
    echo "The last part records your avaibility this year.<p>\n";
    echo "<div class=tablecont><table border style='table-layout:fixed'>\n";
    echo "<tr><td colspan=4><h3><center>Part 1: The Volunteer</center></h3>";
    if (Access('SysAdmin')) echo "<tr><td>id: $Volid";
//  echo "<tr><td style='max-width:100;width:100'>Name:" . fm_text1('',$Vol,'SN',2,'');
    echo "<tr>" . fm_text('Name',$Vol,'SN',2,'');
    echo "<tr>" . fm_text('Email',$Vol,'Email',2);
    echo "<tr>" . fm_text('Phone(s)',$Vol,'Phone',2);
    echo "<tr>" . fm_text('Address',$Vol,'Address',3);
    echo "<tr><td>" . fm_checkbox("I am over 18",$Vol,'Over18',"","",1);
    echo "<tr><td>" . fm_checkbox("Keep my records",$Vol,'KeepMe',"","",1). "<td>So you don't have to type this in again next year";
//  echo "<tr>" . fm_text('Date of Birth',$Vol,'Birthday');
    echo "<tr>" . fm_text('Disabilities',$Vol,'Disabilities',2);
    if (Feature('VolDBS')) {
      echo "<tr><td colspan=4>"; // <h3>Legal</h3>\n";
      echo "Do you have a current DBS certificate? if so please give details (needed for some volunteering roles)<br>" . 
           fm_textinput('DBS',(isset($Vol['DBS'])?$Vol['DBS']:''),'size=100');
    }
    if (Feature('VolFirstAid')) {
      echo "<tr><td colspan=4>"; // <h3>Legal</h3>\n";
      echo "Do you have current First Aid training? if so please give details (Just plain useful for the unknown)<br>" . 
           fm_textinput('FirstAid',(isset($Vol['FirstAid'])?$Vol['FirstAid']:''),'size=100');
    }
    echo "<tr><td colspan=4><h3>Emergency Contact</h3>\n";
    echo "<tr>" . fm_text('Contact Name',$Vol,'ContactName',2);
    echo "<tr>" . fm_text('Contact Phone',$Vol,'ContactPhone',2);
    echo "<tr><td>Relationship:<td>" . fm_select($Relations,$Vol,'Relation');
    if (Access('SysAdmin')) echo "<tr><td class=NotSide>Debug<td colspan=3 class=NotSide><textarea id=Debug></textarea>";  
  
  echo "<tr><td colspan=4><h3><center>Volunteering in $PLANYEAR</center></h3>";


    if (isset($Vol['Year']) && $YEAR != $Vol['Year']) {
      echo "<center>This shows what you filled in for " . $Vol['Year'] . " please update as appropriate</center>";
//    $Vol['VYid'] = -1;
    }
    echo "<tr><td colspan=4><h3><center>Part 2: Which Team(s) would you like to volunteer for?</center></h3>\n";

    $DayTeams = [];
    $Day4All = [];
    foreach ($VolCats as $Cat) {
      $Ctxt = "";
      $rows = 1;
      $cp = $Cat['Props'];
      $SName = preg_replace('/ /','',$Cat['Name']);
      $cls = "SC_$SName";
      if (($cp & VOL_USE) == 0) continue;
      $SplitWhen = explode(',', $Cat['Listofwhen']);
      foreach($SplitWhen as $wh) {
        if (isset($DayTeams[$wh])) {
          $DayTeams[$wh] .= ", " . $Cat['Name'];
        } else {
          $DayTeams[$wh] = $Cat['Name'];      
        }
      }
    
      $Catid = $Cat['id'];

      $VCY = Get_Vol_Cat_Year($Volid,$Catid);
      $Hide = (($VCY['Props'] & VOL_USE) ?"":"hidden ");

      if ($cp & VOL_Likes)   { 
        $rows++; 
        $Ctxt .= "\n<tr>" . fm_text1("Prefered " . $Cat['Name'] . " Tasks", $VCY,'Likes',3,"colspan=3 class=$cls $Hide",'',"Likes:$Catid:$PLANYEAR") . 
                             $Cat['LExtra']; 
      };
      if ($cp & VOL_Dislikes){ 
        $rows++; 
        $Ctxt .= "\n<tr>" . fm_text1("Disliked " . $Cat['Name'] . " Tasks", $VCY,'Dislikes',3,"colspan=3 class=$cls $Hide",'',"Dislikes:$Catid:$PLANYEAR") .
                             $Cat['DExtra']; 
      };
      if ($cp & VOL_Other1)  { 
        $rows++; 
        $Ctxt .= "\n<tr>" . fm_text1($Cat['OtherQ1'], $VCY,'Other1',3,"colspan=3 class=$cls $Hide",'',"Other1:$Catid:$PLANYEAR") .
                             $Cat['Q1Extra']; 
      };   
      if ($cp & VOL_Other2)  { 
        $rows++; 
        $Ctxt .= "\n<tr>" . fm_text1($Cat['OtherQ2'], $VCY,'Other2',3,"colspan=3 class=$cls $Hide",'',"Other2:$Catid:$PLANYEAR") .
                             $Cat['Q2Extra']; 
      };      
      if ($cp & VOL_Other3)  { 
        $rows++; 
        $Ctxt .= "\n<tr>" . fm_text1($Cat['OtherQ3'], $VCY,'Other3',3,"colspan=3 class=$cls $Hide",'',"Other3:$Catid:$PLANYEAR") .
                             $Cat['Q3Extra']; 
      };   
      $Ctxt = "\n<tr><td rowspan=$rows>" .  fm_checkbox($Cat['Name'],$VCY,'Props',"onchange=Update_VolCats('$cls')","Props:$Catid:$PLANYEAR",1,' colspan=3') . " " .
              $Cat['Description'] . $Ctxt;
      echo $Ctxt. "\n"; // $Desc,&$data,$field,$extra='',$field2='',$split=0,$extra2='

    }

//echo "<tr><td colspan=4>"; var_dump($DayTeams);

    $VYear = Get_Vol_Year($Volid);
    echo "\n<tr><td colspan=4><h3><center>Part 3: Availability in $PLANYEAR</center></h3>If you could help on the days below, please give the times you would be available\n";
    if (isset($DayTeams['Before'])) echo "\n<tr>" . fm_text("Months before the festival",$Vol,"AvailBefore",3) . $DayTeams['Before'];

    for ($day = $YEARDATA['FirstDay']-1; $day<=$YEARDATA['LastDay']+1; $day++) {
      $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
      $rs = (($day<$YEARDATA['FirstDay'] || $day> $YEARDATA['LastDay']));
      echo "\n<tr>" . fm_text("On " . FestDate($day,'M'), $VYear,$av,3,'','',"$av::$PLANYEAR") . $DayTeams[$day];
//             ($rs? BeforeTeams(): " <span class=SC_Days> All Teams</span>");
    }


    echo "\n<tr><td><h3>Anything Else /Notes:</h3><td colspan=3>" . fm_basictextarea($VYear,'Notes',3,3,'',"Notes::$PLANYEAR");
    $Stat = empty($VYear['Status'])?0:$VYear['Status'];
    echo "\n<tr><td>Application Status:<td " . ($Stat?'style=color:Green;font-weight:bold;>': 'style=color:Red;font-weight:bold;>') . $YearStatus[$Stat];
    
    echo "\n<tr><td><b>Part 4: Actions:</b><td colspan=3><div class=tablecont><table border=0><tr><td width=33%>";
    echo "<input type=submit hidden name=ACTION value=View>\n";
    echo "<input type=submit name=ACTION value='Submit/Update Application'>\n"; 
    if ($Vol['id'] > 0) {
      echo "<td width=33%><input type=submit name=ACTION value='Sorry not this Year'>";
      echo "<td><input class=floatright type=submit name=ACTION value='Remove me from the festival records' " .
         "onClick=\"javascript:return confirm('Please confirm delete?');\">";
    }
  echo "</table></div>";

  echo "</table></div><p>";
  $Mess = TnC("VolTnC");
  Parse_Proforma($Mess);
  echo $Mess;
  echo "</form>\n";

  if (Access('Staff')) echo "<h2><a href=Volunteers?A=List>Back to list of Volunteers</a></h2>";
/*
  if ($Vol['id'] <= 0) {
    echo "<input type=submit name=Submit value='Submit Application'><p>\n"; 
  } else {
    echo "<input type=submit name=Submit value='Update Application'><p>\n"; 
  }  
*/
  
  dotail();
}

function Vol_Validate(&$Vol) {
  global $YEARDATA,$VolCats,$YEAR;

  if (strlen($Vol['SN']) < 2) return "Please give your name";
  if (strlen($Vol['Email']) < 6) return "Please give your Email";
  if (strlen($Vol['Phone']) < 6) return "Please give your Phone number(s)";
  if (strlen($Vol['Address']) < 10) return "Please give your Address";
  if (!isset($Vol['Over18']) || !$Vol['Over18']) return "Please confirm you are over 18";
//  if (strlen($Vol['Birthday']) < 2) return "Please give your age";

  $Clss=0;
  $VCYs = Gen_Get_Cond('VolCatYear',"Volid=" . $Vol['id'] . " AND Year=$YEAR");
  foreach ($VCYs as $VCY) if (isset($VCY['Props']) && $VCY['Props']) $Clss++;
  if ($Clss == 0) return "Please select at least one team";

  $Avail=0;
  $VY = Get_Vol_Year($Vol['id']);
  if (isset($VY["AvailBefore"]) && strlen($VY["AvailBefore"]) > 1) $Avail++;
  for ($day =$YEARDATA['FirstDay']-1; $day<=$YEARDATA['LastDay']+1; $day++) {
    $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
    if (isset($VY[$av]) && strlen($VY[$av]) > 1) $Avail++;
  }

  if ($Avail == 0) return "Please give your availabilty";
  if (strlen($Vol['ContactName']) < 2) return "Please give an emergency contact";
  if (strlen($Vol['ContactPhone']) < 6) return ">Please give emergency Phone number(s)";
  if (!isset($Vol['Relation']) || !$Vol['Relation']) return "Please give your emergency contact relationship to you";

  Clean_Email($Vol['Email']);  
  return 0;
}

function Vol_Emails(&$Vol,$reason='Submit') {// Allow diff message on reason=update
  global $FESTSYS,$VolCats,$PLANYEAR;
  $Leaders = [];
  Email_Volunteer($Vol,"Vol_Application_$reason",$Vol['Email']);
  $VCYs = Gen_Get_Cond('VolCatYear',"Volid=" . $Vol['id'] . " AND Year=$PLANYEAR");
  foreach($VolCats as $Cat) {
    foreach ($VCYs as $VCY) {
      if ($VCY['CatId'] == $Cat['id']) {
        if (isset($Leaders[$Cat['Email']])) continue 2;
        $Leaders[$Cat['Email']] = 1;
        Email_Volunteer($Vol,"Vol_Staff_$reason",$Cat['Email']);
        continue 2;
      }
    }
  }
  echo "<h2 class=subtitle>Thankyou for your application</h2>";
  if (Access('Staff')) echo "<h2><a href=Volunteers?A=List>Back to list of Volunteers</a></h2>";
  dotail();
}

function Vol_Staff_Emails(&$Vol,$reason='NotThisYear') {// Allow diff message on reason=update
  global $FESTSYS,$VolCats,$PLANYEAR;

  $VCYs = Gen_Get_Cond('VolCatYear',"Volid=" . $Vol['id'] . " AND Year=$PLANYEAR");
  foreach($VolCats as $Cat) {
    foreach ($VCYs as $VCY) {
      if ($VCY['CatId'] == $Cat['id']) {
        if (isset($Leaders[$Cat['Email']])) continue 2;
        $Leaders[$Cat['Email']] = 1;
        Email_Volunteer($Vol,"Vol_Staff_$reason",$Cat['Email']);
        continue 2;
      }
    }
  }
}


function List_Vols() {
  global $YEAR,$db,$VolCats,$YEARDATA,$PLANYEAR,$YearStatus;
  echo "<button class='floatright FullD' onclick=\"($('.FullD').toggle())\">All Applications</button>" .
       "<button class='floatright FullD' hidden onclick=\"($('.FullD').toggle())\">Curent Aplications</button> ";

  echo "Click on name for full info<p>";
  
  echo "Where it says EXPAND under availability, means there is a longer entry - click on the persons name to see more info on their availabilty<p>";
  
  $coln = 0;  
  echo "<form method=post>";
  echo "<div class=tablecont><table id=indextable border>\n";
  echo "<thead><tr>";

  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Email</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Phone</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Status</a>\n";
  echo "<th class=FullD hidden><a href=javascript:SortTable(" . $coln++ . ",'N')>Year</a>\n";
  foreach ($VolCats as $Cat) {
    if ($Cat['Props'] & VOL_USE) {
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>" . ($Cat['ShortName']?$Cat['ShortName']:$Cat['Name']) . "</a>\n";
    }
  }
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Before</a>\n";
  for ($day = $YEARDATA['FirstDay']-1; $day<= $YEARDATA['LastDay']+1; $day++) {
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>" . FestDate($day,'s') . "</a>\n";
  }
  echo "</thead><tbody>";

  $res=$db->query("SELECT * FROM Volunteers WHERE Status=0 ORDER BY SN");
  
  if ($res) while ($Vol = $res->fetch_assoc()) {
    $id = $Vol['id'];
    if (empty($id) || empty($Vol['SN']) || empty($Vol['Email']) ) continue;
    $VY = Get_Vol_Year($id);
//    var_dump($VY);
    $link = "<a href=Volunteers?A=View&id=$id>";
    echo "<tr" . ((($VY['Year'] != $PLANYEAR) || empty($VY['id']))?" class=FullD hidden" : "" ) . ">";
    echo "<td>$id";
    echo "<td>$link" . $Vol['SN'] . "</a>";
    echo "<td>" . $Vol['Email'];
    echo "<td>" . $Vol['Phone'];
    echo "<td>" . ((isset($VY['id']) && $VY['id']>0)?$YearStatus[$VY['Status']]:'');
    echo "<td class=FullD hidden>";
    if (isset($VY['id']) && $VY['id']>0) {
      echo $VY['Year'];
      $year = $PLANYEAR;
    } else {
      for ($year=$PLANYEAR-1; $year>($PLANYEAR-6); $year--) {
        $VY = Get_Vol_Year($id);
        if (!empty($VY['id'])) break;
      }
    }
    foreach ($VolCats as $Cat) {
      if ($Cat['Props'] & VOL_USE) {
        $VCY = Get_Vol_Cat_Year($Vol['id'],$Cat['id'],$year);
        echo "<td>" . (($VCY['Props'] & VOL_USE)?'Y':''); 
      }
    }

    echo "<td>" . (isset($VY['AvailBefore'])?$VY['AvailBefore']:"");
    for ($day = $YEARDATA['FirstDay']-1; $day<= $YEARDATA['LastDay']+1; $day++) {
      $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
      echo "<td>";
      if (isset($VY[$av])) echo (strlen($VY[$av])<12? $VY[$av] : $link. "Expand</a>") . "\n";
    }
  }
  echo "</tbody></table></div>\n";

  echo "<h2><a href=Volunteers?A=New>Add a Volunteer</a></h2>";
  dotail();
}

function Email_Form_Only($Vol,$mess='') {
  $coln = 0;
  echo "<h2>Stage 1 - Who are you?</h2>";
  if ($mess) echo "<h2 class=Err>$mess</h2>";
  echo "<form method=post>";
  echo "<div class=tablecont><table border>";
  echo "<tr>" . fm_text('Name',$Vol,'SN',2);
  echo "<tr>" . fm_text('Email',$Vol,'Email',2);
  echo fm_hidden('A','NewStage2');
  echo "</table></div><p><input type=Submit>\n";
  dotail();
}

function Check_Unique() { // Is email Email already registered - if so send new email back with link to update
  global $db;
  $adr = trim($_POST['Email']);
  if (!filter_var($adr,FILTER_VALIDATE_EMAIL)) Email_Form_Only($_POST,"Please give an email address");
  $res = $db->query("SELECT * FROM Volunteers WHERE Email LIKE '%$adr%'");
  if ($res && $res->num_rows) {
    $Vol = $res->fetch_assoc();
    if (!Access('Staff')) {
      Email_Volunteer($Vol,"Vol_Link_Message",$Vol['Email']);
      echo "<h2>You are already recorded as a Volunteer</h2>";
      echo "An email has been sent to you with a link to your record, only information about this years volunteering is now needed.<p>";
      dotail();
    }
//    echo "<h2>" . $Vol['SN'] . " Is already a volunteer</h2>";
//    $id = $Vol['id'];
//    $Vol = array_merge($Vol, Get_Vol_Year($id));
    VolForm($Vol);
  } // else new - full through
}

function VolAction($Action) {
  global $PLANYEAR;

  dostaffhead("Steward / Volunteer Application", ["/js/Volunteers.js"]);
  
//var_dump($Action);
//var_dump($_REQUEST);

  switch ($Action) {
  case 'New': // New Volunteer
  default:
    $Vol = ['id'=>-1, 'Year'=>$PLANYEAR,'KeepMe'=>1];
    Email_Form_Only($Vol);
    break;

  case 'NewStage2': 
    Check_Unique(); // Deliberate drop through

  case 'Form': // New stage 2
    $Vol = ['Year'=>$PLANYEAR, 'SN'=>$_POST['SN'], 'Email'=>$_POST['Email'], 'KeepMe'=>1];
    $Volid = Gen_Put('Volunteers',$Vol);
    VolForm($Vol);
    break;
    
  case 'List': // List Volunteers
    List_Vols();
    break;
    
  case 'Create': // Volunteer hass clicked 'Submit', store and email staff
  case 'Submit':
  case 'Update': // Volunteer/Staff has updated entry - if Volunteer, remail relevant staff
  case 'Submit/Update Application':
    $Volid = ((isset($_REQUEST['id']) ?$_REQUEST['id'] :(isset($_REQUEST['AutoRef'])?$_REQUEST['AutoRef']:0)));
    $Vol = Get_Volunteer($Volid);
    $res = Vol_Validate($Vol);
    if ($res) {
      VolForm($Vol,$res);
    } else {
      $VY = Get_Vol_Year($Vol['id']);
      $VY['Status'] = 1;
      Put_Vol_Year($VY);
    }
    
    if (empty($Vol['AccessKey']) || !isset($_REQUEST['id']) || $_REQUEST['id'] < 0) { // New
      $Vol['AccessKey'] = rand_string(40);
      Put_Volunteer($Vol);
    }
    
    Vol_Emails($Vol,'Submit');
    break;
  
  case 'View':
    $Vol = Get_Volunteer($_REQUEST['id']);
    $Volid = $Vol['id'];

    VolView($Vol);
    break;
     
  case 'Show':
    $Vol = Get_Volunteer($_REQUEST['id']);
    $Volid = $Vol['id'];

    VolForm($Vol);
    break;
     

  case 'Email': // Send Invite email out
//??
    break;
    
  case 'NotThisYear':
  case 'Sorry not this Year':
    $Vol = Get_Volunteer($_REQUEST['id']);
      $VY = Get_Vol_Year($Vol['id']);
      if (isset($VY['id'])) {
        $VY['Status'] = 2;
        Put_Vol_Year($VY);

        $Vol = Get_Volunteer($id = $_REQUEST['id']);
        $VY = Get_Vol_Year($Vol['id'],$PLANYEAR);
        if (!empty($VY['id'])) {
          Vol_Staff_Emails($Vol);
          db_delete('VolYear',$VY['id']);
        }
      }
    
    echo "<h2>Thankyou for letting us know</h2>";
    break;
    
  case 'Delete': // Delete Volunteer
  case 'Remove me from the festival records':
    $Vol = Get_Volunteer($id = $_REQUEST['id']);
    $Vol['Status']=1;
    Put_Volunteer($Vol);
    $VY = Get_Vol_Year($Vol['id'],$PLANYEAR);
    if (!empty($VY['id'])) Vol_Staff_Emails($Vol);

//??    if ($Vol['Year'] == $PLANYEAR) Vol_Staff_Emails($Vol);

    echo "<h2>Thankyou for Volunteering in the past, you are no longer recorded</h2>";
    db_delete('Volunteers',$id);  
    break;
    
  }  
}


/*
  TODO
  1) DBS upload
  6) Email all/subsets
  7) Form validation - hack prevention

*/

?>
