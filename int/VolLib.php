<?php
  include_once("fest.php");

//  dostaffhead("Steward / Volunteer Application", ["/js/Volunteers.js"]);

  include_once("Email.php");
//  include_once("SignupLib.php");
  global $USER,$USERID,$db,$PLANYEAR,$StewClasses,$Relations,$AgeCats,$CampType,$CampStatus;
  
$yesno = array('','Yes','No');
$Relations = array('','Husband','Wife','Partner','Son','Daughter','Mother','Father','Brother','Sister','Grandchild','Grandparent','Guardian','Uncle','Aunty',
                'Son/Daughter in law', 'Friend','Other');
$YearStatus = ['Not Submitted','Submitted','Withdrawn'];
$AgeCats = ['Under 18','Over 18','Over 21'];
$CampStatus = ['No','Yes, I am in a group of volunteers, another person is booking the space. e.g. Two people in a tent/van only need one tent/van space'];
// $CampType = ['','Small Tent','Large Tent','Campervan','Caravan'];
$CampType = Gen_Get_Names('Camptypes');

define('VOL_USE',1);
define('VOL_Likes',2);
define('VOL_Dislikes',4);
define('VOL_Over21',8);
define('VOL_Upload',16);
define('VOL_Exp',0x20);
define('VOL_Money',0x40);
define('VOL_NeedDBS',0x80);
define('VOL_Other1',0x100);
define('VOL_Other2',0x200);
define('VOL_Other3',0x400);
define('VOL_Other4',0x800);

$VolCats = Gen_Get_All('VolCats','ORDER BY Importance DESC');

function Get_Campsites($Restrict=0,$Comments=1) {
  global $USER,$USERID,$db,$PLANYEAR,$StewClasses,$Relations,$AgeCats,$CampType,$CampStatus;
  $CList = $CampStatus;
  $Camps = Gen_Get_All('Campsites','ORDER BY Importance DESC');
  foreach ($Camps as $C) {
    $N = $C['Name'];
    if ($Comments && !empty($C['Comment'])) $N .= " (" . $C['Comment'] . ")";
    if ($C['Props'] & 2) {
      if ($Restrict==0) continue;
      $CList[$C['id']] = $N . " - " . $C['Restriction'];      
    } else {
      $CList[$C['id']] = $N;
    }
  }
  return $CList;
}

function Get_Vol_Details(&$vol) {
  global $VolCats,$Relations,$YEARDATA,$YEAR,$PLANYEAR,$YearStatus,$AgeCats,$CampType;
  $Volid = $vol['id'];
  $Body = "\nName: " . $vol['SN'] . "<br>\n";
  $Body .= "Email: <a href=mailto:" . $vol['Email'] . ">" . $vol['Email'] . "</a><br>\n";
  if ($vol['Phone']) $Body .= "Phone: " . $vol['Phone'] . "<br>\n";
  $Body .= "Address: " . $vol['Address'] . "<br>\n";
  if (isset($vol['PostCode'])) $Body .= "PostCode: " . $vol['PostCode'] . "<br>\n\n";
  $Body .= "Age: " . $AgeCats[$vol['Over18']] . "<br>\n";
  $Body .= "Handle Money:" . ($vol['Money']?'Yes':'No') . "<br>\n";
//  if (Feature('VolPhoto') && !empty($vol['Photo'])) $Body .= "*IMAGE_" . $vol['Photo'] . "*<br>\n\n";
  if (!empty($vol['Disabilities'])) $Body .= "<p>Disabilities: " . $vol['Disabilities'] . "<br>\n\n";
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
        if (($cp & VOL_Exp)  && !empty($VCY['Experience'])) $Body .= "Experience: " . $VCY['Experience'] . "<br>\n";
        if (($cp & VOL_Other1)  && !empty($VCY['Other1'])) $Body .= $Cat['OtherQ1'] . ": " . $VCY['Other1'] . "<br>\n";
        if (($cp & VOL_Other2)  && !empty($VCY['Other2'])) $Body .= $Cat['OtherQ2'] . ": " . $VCY['Other2'] . "<br>\n";
        if (($cp & VOL_Other3)  && !empty($VCY['Other3'])) $Body .= $Cat['OtherQ3'] . ": " . $VCY['Other3'] . "<br>\n";
        if (($cp & VOL_Other4)  && !empty($VCY['Other4'])) $Body .= $Cat['OtherQ4'] . ": " . $VCY['Other4'] . "<br>\n";
      }
    }
  $Body .= "<p>\n";
  $Body . "Available:<p>\n";
  $VY = Get_Vol_Year($Volid);
  
  if (isset($VY['AvailBefore']) && $VY['AvailBefore'])  $Body .= "Months Before Festival: " . $VY["AvailBefore"] . "<br>\n";
  if (isset($VY['AvailWeek']) && $VY['AvailWeek'])  $Body .= "Week Before Festival: " . $VY["AvailWeek"] . "<br>\n"; 
  for ($day = $YEARDATA['FirstDay']-1; $day<= $YEARDATA['LastDay']+1; $day++) {
    $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
    if (isset($VY[$av]) && $VY[$av]) $Body .= FestDate($day,'M') . ": " . $VY[$av] . "<br>\n";
  }
  
  if (isset($VY['Notes']) && $VY['Notes']) $Body .= "<p>Notes: " . $VY['Notes'] . "<p>\n";
  
  if (isset($VY['id']) && $VY['id']) $Body .= "<p>Status: " . $YearStatus[$VY['Status']] . "<p>\n";
  
  if (!empty($VY['Children'])) $Body .= "Children: " . $VY['Children'] . "<p>\n";

  $camps = Get_Campsites(1);
  
  if (Feature('Vol_Camping')) {
    $Body .= "Camping: " . $camps[$VY['CampNeed']] . "<br>\n";
    if ($VY['CampNeed'] > 10) $Body .= "Space for: " . $CampType[$VY['CampType']] . "<p>\n";
  }

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

function BeforeTeams($term='Before') {
  global $VolCats;
  static $txt = '';
  if ($txt) return $txt;
  $teams = [];
  foreach ($VolCats as $Cat) if ((( $Cat['Props'] & VOL_USE) != 0) && (preg_match('/' . $term . '/',$Cat['Listofwhen'],$mtch))) $teams[] = $Cat['Name'];
  $txt = " Teams: " . implode(", ",$teams);
  return $txt;
}

function VolView(&$Vol) {
  echo Get_Vol_Details($Vol);
  if (!empty($Vol['Photo'])) echo "Photo: <img src='" . $Vol['Photo'] . "' width=200><p>";
  if (Access('Committee','Volunteers'))  echo "<P><h2><a href=Volunteers?ACTION=Show&id=" . $Vol['id'] . ">Edit</a>";
  dotail();
}


function VolForm(&$Vol,$Err='',$View=0) {
  global $VolCats,$YEARDATA,$PLANYEAR,$YEAR,$Relations,$YearStatus,$AgeCats,$CampType;
  $Volid = $Vol['id'];
// var_dump($Vol);

  echo "<h2 class=subtitle>Steward / Volunteer Application Form</h2>\n";
  if (!empty($Err)) echo "<p class=Err>$Err<p>";
  echo "<form method=post action=Volunteers>";  
  Register_AutoUpdate('Volunteers',$Volid);
  echo fm_hidden('id',$Volid);  


    echo "This is in 4 parts.  The first records who you are.  This will be kept year to year, so you should only need to fill this in once.<p>\n";
    echo "The second part records which team(s) you would like to be part of, along with any likes, dislikes and team related details.<p>\n";
    echo "The third part records your avaibility this year.<p>\n";
    echo "The last part is anything special this year and the submit button.<p>";
    echo "<div class=tablecont><table border style='table-layout:fixed'>\n";
    echo "<tr><td colspan=5><h3><center>Part 1: The Volunteer</center></h3>";
    if (Access('SysAdmin')) echo "<tr><td>id: $Volid";
//  echo "<tr><td style='max-width:100;width:100'>Name:" . fm_text1('',$Vol,'SN',2,'');
    echo "<tr>" . fm_text('Name',$Vol,'SN',4,'');
    echo "<tr>" . fm_text('Email',$Vol,'Email',4);
    echo "<tr>" . fm_text('Phone(s)',$Vol,'Phone',4);
    echo "<tr>" . fm_text('Address',$Vol,'Address',4);
    echo "<tr>" . fm_Radio("Age range",$AgeCats,$Vol,'Over18',"",1). "<td colspan=3>All volunteers need to be over 18, a few roles need over 21.";
    $Photo = Feature('VolPhoto');
    if ($Photo) echo "<tr rowspan=4 colspan=4 height=80><td>" . ($Photo == 1 ? 'Photo, not essential yet' : 'Photo') .
        fm_DragonDrop(1,'Photo','Volunteer',$Volid,$Vol,1,'',1,'','Photo');
    echo "<tr><td>" . fm_checkbox("Are you happy to handle Money",$Vol,'Money',"","",1). "<td colspan=3>Needed for some teams";
    echo "<tr><td>" . fm_checkbox("Keep my records",$Vol,'KeepMe',"","",1) . 
         "<td colspan=3>Please uncheck this box if you do not wish the festival to contact you about our future events.<br>" .
         "If you are happy for us to save your details, they will be available to you when you apply next time!"; 

    echo "<tr>" . fm_text('Do you have any medical conditions, disabilities or accessibility requirements that we need to be aware of? ' .
                          'Please give any details to enable us to support you',$Vol,'Disabilities',4);
    if (Feature('VolDBS')) {
      echo "<tr><td colspan=5>"; // <h3>Legal</h3>\n";
      echo "Do you have a current DBS certificate? if so please give details (needed for some volunteering roles)<br>" . 
           fm_textinput('DBS',(isset($Vol['DBS'])?$Vol['DBS']:''),'size=100');
    }
    if (Feature('VolFirstAid')) {
      echo "<tr><td colspan=5>"; // <h3>Legal</h3>\n";
      echo "Do you have current First Aid training? if so please give details (Just plain useful for the unknown)<br>" . 
           fm_textinput('FirstAid',(isset($Vol['FirstAid'])?$Vol['FirstAid']:''),'size=100');
    }
    echo "<tr><td colspan=5><h3>Emergency Contact</h3>\n";
    echo "<tr>" . fm_text('Contact Name',$Vol,'ContactName',4);
    echo "<tr>" . fm_text('Contact Phone',$Vol,'ContactPhone',4);
    echo "<tr><td>Relationship:<td>" . fm_select($Relations,$Vol,'Relation');
    if (Access('SysAdmin')) echo "<tr><td class=NotSide>Debug<td colspan=4 class=NotSide><textarea id=Debug></textarea>";  
  
  echo "<tr><td colspan=5><h3><center>Volunteering in $PLANYEAR</center></h3>";


    if (isset($Vol['Year']) && $YEAR != $Vol['Year']) {
      echo "<center>This shows what you filled in for " . $Vol['Year'] . " please update as appropriate</center>";
//    $Vol['VYid'] = -1;
    }
    echo "<tr><td colspan=5><h3><center>Part 2: Which Team(s) would you like to volunteer for?</center></h3>\n";

    $DayTeams = [];
    $DayClasses = [];
    $DayShow = [];
    $Day4All = [];
    foreach ($VolCats as $Cat) {
      $Catid = $Cat['id'];
      $VCY = Get_Vol_Cat_Year($Volid,$Catid);

      $SetShow = ($VCY['Props'] & VOL_USE);
      $Ctxt = "";
      $rows = 1;
      $cp = $Cat['Props'];
      $SName = preg_replace('/ /','',$Cat['Name']);
      $cls = "SC_$SName";
      if (($cp & VOL_USE) == 0) continue;

      $Hide = (($VCY['Props'] & VOL_USE) ?"":"hidden ");

      $SplitWhen = explode(',', $Cat['Listofwhen']);
      foreach($SplitWhen as $wh) {
        if (isset($DayTeams[$wh])) {
          $DayTeams[$wh] .= "<span class=$cls $Hide> " . $Cat['Name'] . ", </span>";
          $DayClasses[$wh] .= " Cat_$Catid";
        } else {
          $DayTeams[$wh] = "<span class=$cls $Hide>" . $Cat['Name'] . ", </span>";
          $DayClasses[$wh] = "Cat_$Catid"; 
        }
        if ($SetShow) $DayShow[$wh] = 1;
      }
    
      if ($cp & VOL_Likes)   { 
        $rows++; 
        $Ctxt .= "\n<tr>" . fm_text1("Prefered " . $Cat['Name'] . " Tasks", $VCY,'Likes',4,"colspan=4 class=$cls $Hide",'',"Likes:$Catid:$PLANYEAR") . 
                             $Cat['LExtra']; 
      };
      if ($cp & VOL_Dislikes){ 
        $rows++; 
        $Ctxt .= "\n<tr>" . fm_text1("Disliked " . $Cat['Name'] . " Tasks", $VCY,'Dislikes',4,"colspan=4 class=$cls $Hide",'',"Dislikes:$Catid:$PLANYEAR") .
                             $Cat['DExtra']; 
      };

      if ($cp & VOL_Exp){ 
        $rows++; 
        $Ctxt .= "\n<tr>" . fm_textarea("Please outline your experience with us or other festivals", $VCY,'Experience',3,3," class=$cls $Hide",'',"Experience:$Catid:$PLANYEAR") .
                             $Cat['DExtra']; 
      };
      for ($i=1; $i<5; $i++) {
        if ($cp & (VOL_Other1 << ($i-1))) {
          $rows++; 
          if ($cp & (VOL_Other1 << ($i+3))) {
            $Ctxt .= "\n<tr>" . fm_textarea($Cat["OtherQ$i"] . "<br>" . $Cat["Q$i" . "Extra"], $VCY,"Other$i:$Catid:$PLANYEAR",3,3,"class=$cls $Hide");
          
          } else {
            $Ctxt .= "\n<tr>" . fm_text1($Cat["OtherQ$i"], $VCY,"OtherQ$i",4,"colspan=4 class=$cls $Hide",'',"Other$i:$Catid:$PLANYEAR") .
                              $Cat["Q$i" . "Extra"] ; 
          }
        }
      }       

      $Ctxt = "\n<tr><td rowspan=$rows>" .  fm_checkbox($Cat['Name'],$VCY,'Props',"onchange=Update_VolCats('$cls',$Catid,$PLANYEAR)","Props:$Catid:$PLANYEAR",0
              ,'') . "<td  colspan=4>" . $Cat['Description'] . $Ctxt;
      echo $Ctxt. "\n"; // $Desc,&$data,$field,$extra='',$field2='',$split=0,$extra2='

    }

//echo "<tr><td colspan=4>"; var_dump($DayTeams);

    $VYear = Get_Vol_Year($Volid);
//    $DayCats = ['Before','Week'];
//    for ($day = $YEARDATA['FirstDay']-1; $day<=$YEARDATA['LastDay']+1; $day++) $DayCats[]= $day;
    
    echo "\n<tr><td colspan=5><h3><center>Part 3: Availability in $PLANYEAR</center></h3>" .
         "If you could help on the days below, please give the times you would be available\n";
    if (isset($DayTeams['Before'])) echo "\n<tr id=TRAvailBefore>" .
        fm_text("Months before the festival",$VYear,"AvailBefore",4,'','',"AvailBefore::$PLANYEAR") . 
        "<div id=TeamsBefore class=Inline>" . $DayTeams['Before'] . "</div>"; // " . (empty($DayShow['Before'])?" hidden " : "") . "
    if (isset($DayTeams['Week'])) echo "\n<tr id=TRAvailWeek>" . 
        fm_text("Week before the festival",$VYear,"AvailWeek",4,'','',"AvailWeek::$PLANYEAR")  . 
        "<div id=TeamsWeek class=Inline>" . $DayTeams['Week'] . "</div>";//  " . (empty($DayShow['Week'])?" hidden " : "") . "
    for ($day = $YEARDATA['FirstDay']-1; $day<=$YEARDATA['LastDay']+1; $day++) {
      $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
      $rs = (($day<$YEARDATA['FirstDay'] || $day> $YEARDATA['LastDay']));
      echo "\n<tr id=TR$av>" . 
           fm_text("On " . FestDate($day,'M'), $VYear,$av,4,'','',"$av::$PLANYEAR") . "<div id=TeamsWeek class=Inline>" . $DayTeams[$day] . "</div>";
    }// " . (empty($DayShow[$day])?" hidden " : "") . "

     echo "\n<tr><td colspan=5><h3><center>Part 4: Anything else for $PLANYEAR</center></h3>";
    if (Feature('Vol_Children')) {
      echo "<tr>" . fm_text("Free Childrens tickets (under 10 - please give their ages)",$VYear,'Children',4,'','',"Children::$PLANYEAR");
    }
    if (Feature('Vol_Camping')) {
      $camps = Get_Campsites(1,1);
//var_dump($camps);exit;
      echo "<tr>" . fm_radio("Do you want camping?",$camps,$VYear,'CampNeed','',3,' colspan=4',"CampNeed::$PLANYEAR");
      echo "<tr>" . fm_radio("If so for what?" ,$CampType,$VYear,'CampType','',1,' colspan=4',"CampType::$PLANYEAR");
    }


    echo "\n<tr><td><h3>Anything Else /Notes:</h3><td colspan=4>" . fm_basictextarea($VYear,'Notes',3,3,'',"Notes::$PLANYEAR");
    $Stat = empty($VYear['Status'])?0:$VYear['Status'];
    echo "\n<tr><td>Application Status:<td " . ($Stat?'style=color:Green;font-weight:bold;>': 'style=color:Red;font-weight:bold;>') . $YearStatus[$Stat];
    
    echo "\n<tr><td><b>Actions:</b><td colspan=4><div class=tablecont><table border=0><tr><td width=33%>";
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
  foreach ($VCYs as $VCY) if (isset($VCY['Props']) && $VCY['Props']) {
    $Clss++;
    if ($VCY['CatId'] == 0) { var_dump($VCY); continue; };
    if (($VolCats[$VCY['CatId']]['Props'] & VOL_NeedDBS) && empty($Vol['DBS'])) return $VolCats[$VCY['CatId']]['Name'] . " requires DBS";
    if (($VolCats[$VCY['CatId']]['Props'] & VOL_Over21) && $Vol['Over18'] <2) return $VolCats[$VCY['CatId']]['Name'] . " requires you to be over 21";
  }
  if (Feature('VolPhoto') == 2 && empty($Vol['Photo'])) return "Please supply a photo of yourself so we can print personal volunteer badges.";

  if ($Clss == 0) return "Please select at least one team";

  $Avail=0;
  $VY = Get_Vol_Year($Vol['id']);
  if (isset($VY["AvailBefore"]) && strlen($VY["AvailBefore"]) > 1 && !preg_match('/^\s*no/i',$VY["AvailBefore"],$mtch)) $Avail++;
  if (isset($VY["AvailWeek"]) && strlen($VY["AvailWeek"]) > 1 && !preg_match('/^\s*no/i',$VY["AvailWeek"],$mtch)) $Avail++;
  for ($day =$YEARDATA['FirstDay']-1; $day<=$YEARDATA['LastDay']+1; $day++) {
    $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
    if (isset($VY[$av]) && strlen($VY[$av]) > 1 && !preg_match('/^\s*no/i',$VY[$av],$mtch)) $Avail++;
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
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Months Before</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Week Before</a>\n";
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
    echo "<td>" . (isset($VY['AvailWeek'])?$VY['AvailWeek']:"");
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

  dostaffhead("Steward / Volunteer Application", ["/js/Volunteers.js","js/dropzone.js","css/dropzone.css" ]);
  
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
      $VY['Status'] = 0;
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
