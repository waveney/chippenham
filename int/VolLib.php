<?php
  include_once("fest.php");

//  dostaffhead("Steward / Volunteer Application", ["/js/Volunteers.js"]);

  include_once("Email.php");
//  include_once("SignupLib.php");
  global $USER,$USERID,$db,$PLANYEAR,$StewClasses,$Relations,$AgeCats,$CampType,$CampStatus;
  
$yesno = array('','Yes','No');
$Relations = array('','Husband','Wife','Partner','Son','Daughter','Mother','Father','Brother','Sister','Grandchild','Grandparent','Guardian','Uncle','Aunty',
                'Son/Daughter in law', 'Friend','Other');
$YearStatus = ['Not Submitted','Submitted','Withdrawn','Confirmed','Rejected'];
$YearColour = ['white','Yellow','white','lightgreen','Pink'];
$CatStatus = ['No','Applied','Withdrawn','Confirmed','Rejected'];
$Cat_Status_Short = ['','?','','Y',''];
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
      if (!empty($VCY['id']) && ($VCY['Status'] > 0)) {
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
  
  if (isset($VY['id']) && $VY['id']) $Body .= "<p>Status: " . $YearStatus[$VY['Status']];
  if (isset($VY['id']) && $VY['id']>0 && $VY['Status'] == 1 && $VY['SubmitDate']) $Body .=  " On " . date('d/n/Y',$VY['SubmitDate']);
  if (isset($VY['id']) && $VY['id']>0 && $VY['Status'] == 1 && $VY['SubmitDate'] != $VY['LastUpdate'] && $VY['LastUpdate']) 
    $Body .=  " Last updated on " . date('d/n/Y',$VY['LastUpdate']);
  $Body .= "<p>\n";
  
  if (!empty($VY['Children'])) $Body .= "Children: " . $VY['Children'] . "<p>\n";
  if (!empty($VY['Youth'])) $Body .= "Youth: " . $VY['Youth'] . "<p>\n";
  
  $camps = Get_Campsites(1);
  
  if (Feature('Vol_Camping')) {
    $Body .= "Camping: " . $camps[$VY['CampNeed']] . "<br>\n";
    if ($VY['CampNeed'] < 10) { }
    elseif ($VY['CampNeed'] < 20) $Body .= "Space for: " . $CampType[$VY['CampType']] . "<p>\n";
    elseif ($VY['CampNeed'] < 30) $Body .= "Space for: " . $VY['CampText'] . "<p>\n";
  }

  return $Body;
}

function Vol_Details($key,&$vol) {
  global $FESTSYS,$VolCats,$CatStatus;
  switch ($key) {
  case 'WHO': return firstword($vol['SN']);
  case 'DETAILS': return Get_Vol_Details($vol);
  case 'LINK' : 
    if (empty($vol['AccessKey'])) {
      $vol['AccessKey'] = rand_string(40);
      Put_Volunteer($vol);
    }
    return "<a href='https://" . $_SERVER['HTTP_HOST'] . "/int/Access?t=v&i=" . $vol['id'] . "&k=" . $vol['AccessKey'] . "'><b>link</b></a>";
  case 'FESTLINK' :
  case 'WMFFLINK' : return "<a href='https://" . $_SERVER['HTTP_HOST'] . "/int/Volunteers?A=View&id=" . $vol['id'] . "'><b>link</b></a>";
  case 'VOLTEAM_ACCEPT' :
    $Accept = '';
    foreach ($VolCats as $Cat) {
      $VCY = Get_Vol_Cat_Year($vol['id'],$Cat['id']);
      if ($VCY['Status'] > 0) $Accept .= $Cat['Name'] . " - " . $CatStatus[$VCY['Status']] . "<br>\n";
    }
    return $Accept; 
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
  return ['Volid'=>$Volid,'CatId'=>$CatId,'Year'=>$Year,'id'=>0, 'Status'=>0];
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
  return ['Volid'=>$Volid,'Year'=>$Year,'id'=>0, 'Status'=>0];
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

function OtherVols(&$Vol) {
  return Gen_Get_Cond('Volunteers',"Email='" . $Vol['Email'] . "' AND id!=" . $Vol['id']);
}

function VolForm(&$Vol,$Err='',$View=0) {
  global $VolCats,$YEARDATA,$PLANYEAR,$YEAR,$Relations,$YearStatus,$AgeCats,$CampType,$CatStatus;
  $Volid = $Vol['id'];
// var_dump($Vol);

  $VolMgr = Access('Committee','Volunteers') && !isset($_REQUEST['FORCE']);
    
  echo "<form method=post action=Volunteers?M>";
    foreach ($_REQUEST as $K=>$V) if ($K != 'M') echo fm_hidden($K,$V);
    echo "<input type=submit style='font-size:12pt' value='Switch to: Mobile Friendly Version'>";
  echo "</form>";
  
  $OVols = OtherVols($Vol);
  if ($OVols) {
    echo "<h2>This page is for " . $Vol['SN'] . "</h2>\n";
    $Ovlst = [];
    foreach ($OVols as $V) $Ovlst[$V['id']] = $V['SN'];
    
    echo "<form method=post action=Volunteers>";
    echo fm_hidden('ACTION',($View ?'View':'Show'));
    echo fm_radio("Switch to",$Ovlst, $_REQUEST,'id','onchange=this.form.submit()',-3);
    echo "</form>";
  }
  
  echo "<h2 class=subtitle>Steward / Volunteer Application Form</h2>\n";
  if (!empty($Err)) echo "<p class=Err>$Err<p>";
  echo "<form method=post action=Volunteers>";  
  Register_AutoUpdate('Volunteers',$Volid);
  Register_Onload('CampingVolSet',"'CampNeed::$PLANYEAR'",0);

  echo fm_hidden('id',$Volid);  


    if ($VolMgr) echo "If you change any of the team statuses on this page you must click <b>Send Updates</b>, to notify the volunteer.<p>";

    echo "This is in 4 parts:";
    echo "<li><b>Who you are</b>.  This will normally be kept year to year, so you should only need to fill this in once.\n";
    echo "<li>Which <b>team(s)</B>. you would like to be part of, along with any likes, dislikes and team related details.\n";
    echo "<li>Your <b>availability</b> this year.\n";
    echo "<li>Anything special this year and the <b>submit</b> button.<p>";
    echo "<div class=tablecont><table border style='table-layout:fixed'>\n";
    echo "<tr><td colspan=5><h3><center>Part 1: The Volunteer</center></h3>";
    if (Access('SysAdmin')) echo "<tr><td>id: $Volid";
//  echo "<tr><td style='max-width:100;width:100'>Name:" . fm_text1('',$Vol,'SN',2,'');
    echo "<tr>" . fm_text('Name',$Vol,'SN',4,'');
    echo "<tr>" . fm_text('Email',$Vol,'Email',4);
    echo "<tr>" . fm_text('Phone(s)',$Vol,'Phone',4);
    echo "<tr>" . fm_textarea("Address", $Vol,'Address',3,3);
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
  
  echo "<tr><td colspan=5><h2><center>Volunteering in $PLANYEAR</center></h2>";


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

      $SetShow = ($VCY['Status'] > 0);
      $Ctxt = "";
      $rows = 1;
      $cp = $Cat['Props'];
      $SName = preg_replace('/ /','',$Cat['Name']);
      $cls = "SC_$SName";
      if (($cp & VOL_USE) == 0) continue;
      $Colour = (empty($Cat['Colour'])?'' : " style='background:" . $Cat['Colour'] . "' ");
      $Hide = (($VCY['Status'] == 0) ?"hidden ":'');

      $SplitWhen = explode(',', $Cat['Listofwhen']);
      foreach($SplitWhen as $wh) {
        if (isset($DayTeams[$wh])) {
          $DayTeams[$wh] .= "<span class=$cls $Hide $Colour> " . $Cat['Name'] . ", </span>";
          $DayClasses[$wh] .= " Cat_$Catid";
        } else {
          $DayTeams[$wh] = "<span class=$cls $Hide $Colour>" . $Cat['Name'] . ", </span>";
          $DayClasses[$wh] = "Cat_$Catid"; 
        }
        if ($SetShow) $DayShow[$wh] = 1;
      }
    
      if ($cp & VOL_Likes)   { 
        $rows++; 
        $Ctxt .= "\n<tr $Colour>" . fm_text1("Preferred " . $Cat['Name'] . " Tasks", $VCY,'Likes',4,"colspan=4 class=$cls $Hide $Colour",'',"Likes:$Catid:$PLANYEAR") . 
                             $Cat['LExtra']; 
      };
      if ($cp & VOL_Dislikes){ 
        $rows++; 
        $Ctxt .= "\n<tr $Colour>" . fm_text1("Disliked " . $Cat['Name'] . " Tasks", $VCY,'Dislikes',4,"colspan=4 class=$cls $Hide $Colour",'',"Dislikes:$Catid:$PLANYEAR") .
                             $Cat['DExtra']; 
      };

      if ($cp & VOL_Exp){ 
        $rows++; 
        $Ctxt .= "\n<tr $Colour>" . fm_textarea("Please outline your experience with us or other festivals", $VCY,'Experience',3,3," class=$cls $Hide $Colour",'',
                            "Experience:$Catid:$PLANYEAR"); 
      };
      for ($i=1; $i<5; $i++) {
        if ($cp & (VOL_Other1 << ($i-1))) {
          $rows++; 
          if ($cp & (VOL_Other1 << ($i+3))) {
            $Ctxt .= "\n<tr>" . fm_textarea($Cat["OtherQ$i"] . "<br>" . $Cat["Q$i" . "Extra"], $VCY,"Other$i",3,3,"class=$cls $Hide $Colour",'',"Other$i:$Catid:$PLANYEAR");
          
          } else {
            $Ctxt .= "\n<tr>" . fm_text1($Cat["OtherQ$i"], $VCY,"Other$i",4,"colspan=4 class=$cls $Hide $Colour",'',"Other$i:$Catid:$PLANYEAR") .
                              $Cat["Q$i" . "Extra"] ; 
          }
        }
      }       

// tabs 0=none, 1 normal, 2 lines between, 3 box before txt
// function fm_radio($Desc,&$defn,&$data,$field,$extra='',$tabs=1,$extra2='',$field2='',$colours=0,$multi=0,$extra3='',$extra4='') {
      
      if ($VolMgr) {
        $Ctxt = "\n<tr $Colour><td rowspan=$rows $Colour>" . fm_radio("<b>" . $Cat['Name'] . "</b>",$CatStatus,$VCY,'Status',
                "onchange=Update_VolMgrCats(event,'$cls',$Catid,$PLANYEAR) data-name='" . $Cat['Name'] . "' data-props=$cp ",-3,'',
                "Status:$Catid:$PLANYEAR") . "<td  colspan=4 $Colour>" . $Cat['Description'] . $Ctxt;

      } else {
        $Ctxt = "\n<tr $Colour><td rowspan=$rows $Colour>" .  fm_checkbox($Cat['Name'],$VCY,'Status',
                "onchange=Update_VolCats('$cls',$Catid,$PLANYEAR) data-name='" . $Cat['Name'] . "' data-props=$cp ",
                "Status:$Catid:$PLANYEAR",0,'') . "<td  colspan=4 $Colour>" . $Cat['Description'] . $Ctxt;
      }
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
      echo "<tr>" . fm_text("Free Youth tickets (11 to 17 - please give their ages)",$VYear,'Youth',4,'','',"Youth::$PLANYEAR");
    }
    if (Feature('Vol_Camping')) {
      $camps = Get_Campsites(1,1);
//var_dump($camps);exit;
      echo "<tr>" . fm_radio("Do you want camping?",$camps,$VYear,'CampNeed','',3,' colspan=4',"CampNeed::$PLANYEAR",0,0,''," onchange=CampingVolSet('CampNeed::$PLANYEAR')");
      echo "<tr id=CampPUB>" . fm_radio("If so for what?" ,$CampType,$VYear,'CampType','',1,' colspan=4',"CampType::$PLANYEAR");
      echo "<tr id=CampREST>" . fm_text('Please describe the footprint you need.<br>For example 1 car one tent /<br>one car one tent and a caravan etc ',
                    $VYear,'CampText',4,'','',"CampText::$PLANYEAR");
    }


    echo "\n<tr><td><h3>Anything Else /Notes:</h3><td colspan=4>" . fm_basictextarea($VYear,'Notes',3,3,'',"Notes::$PLANYEAR");
    $Stat = empty($VYear['Status'])?0:$VYear['Status'];
    echo "\n<tr><td>Application Status:<td colspan=3 " . ($Stat?'style=color:Green;font-weight:bold;>': 'style=color:Red;font-weight:bold;>') . $YearStatus[$Stat];
    if ($VYear['Status'] == 1 && $VYear['SubmitDate']) echo " On " . date('d/n/Y',$VYear['SubmitDate']);
    if ($VYear['Status'] == 1 && $VYear['SubmitDate'] != $VYear['LastUpdate']  && $VYear['LastUpdate']) echo ", Last updated on " . date('d/n/Y',$VYear['LastUpdate']);

    if (Access('SysAdmin')) {
      echo "<tr><td>State: " . fm_select($YearStatus,$VYear,'Status',0,'',"YStatus::$PLANYEAR");
      echo "<tr><td>Link:<td colspan=4>" . htmlspec(Vol_Details('LINK',$Vol)) . "<br>" . Vol_Details('LINK',$Vol);
    }
  echo "</table></div><p>";    
 
    echo "<H3>Actions:</h3>";
    echo "<input type=submit hidden name=ACTION value=View>\n";
    echo "<input type=submit name=ACTION value='Submit/Update Application'>\n"; 
    if ($Vol['id'] > 0) {
      echo "<input type=submit name=ACTION value='Sorry not this Year'>";
      echo "<input class=floatright type=submit name=ACTION value='Remove me from the festival records' " .
         "onClick=\"javascript:return confirm('Please confirm delete?');\">";
    }
    if ($VolMgr) echo "<input type=submit name=ACTION value='Send Updates'>";

  echo "<input type=submit name=ACTION value='Register another volunteer with the same email address'>\n";

  $Mess = TnC("VolTnC");
  Parse_Proforma($Mess);
  echo $Mess;
  echo "</form>\n";

  if (Access('Staff')) echo "<h2><a href=Volunteers?A=List>Back to list of Volunteers</a></h2>";
  
  dotail();
}


function VolFormM(&$Vol,$Err='',$View=0) {
  global $VolCats,$YEARDATA,$PLANYEAR,$YEAR,$Relations,$YearStatus,$AgeCats,$CampType,$CatStatus,$M;
  $Volid = $Vol['id'];
// var_dump($Vol);

  $VolMgr = Access('Committee','Volunteers') && !isset($_REQUEST['FORCE']);

  echo "<form method=post action=Volunteers>";
    foreach ($_REQUEST as $K=>$V) if ($K != 'M') echo fm_hidden($K,$V);
    echo "<input type=submit style='font-size:12pt' value='Switch to: Computer Friendly Version'>";
  echo "</form>";

  $OVols = OtherVols($Vol);
  if ($OVols) {
    echo "<h2>This page is for " . $Vol['SN'] . "</h2>\n";
    $Ovlst = [];
    foreach ($OVols as $V) $Ovlst[$V['id']] = $V['SN'];
    
    echo "<form method=post action=Volunteers?M>";
    echo fm_hidden('ACTION',($View ?'View':'Show'));
    echo fm_radio("Switch to",$Ovlst, $_REQUEST,'id','onchange=this.form.submit()',-3);
    echo "</form>";
  }

  echo "<h2 class=subtitle>Steward / Volunteer Application Form</h2>\n";
  if (!empty($Err)) echo "<p class=Err>$Err<p>";
  echo "<div class=VolWrapper>";
  echo "<form method=post action=Volunteers>";  
  if ($M) echo fm_hidden('M','M');
  Register_AutoUpdate('Volunteers',$Volid);
  Register_Onload('CampingVolSet',"'CampNeed::$PLANYEAR'",0);
//  Register_AfterInput('VolEnables',$Volid,$PLANYEAR);
  echo fm_hidden('id',$Volid);  

  if ($VolMgr) echo "If you change any of the team statuses on this page you must click <b>Send Updates</b>, to notify the volunteer.<p>";

  echo "This is in 4 parts:";
  echo "<ol><li><b>Who you are</b>.  This will normally be kept year to year, so you should only need to fill this in once.\n";
  echo "<li>Which <b>team(s)</B>. you would like to be part of, along with any likes, dislikes and team related details.\n";
  echo "<li>Your <b>availability</b> this year.\n";
  echo "<li>Anything special this year and the <b>submit</b> button.<p>";
  echo "</ol><div><table border>\n";
  echo "<tr><td><h3><center>Part 1: The Volunteer</center></h3>";
  
  if (Access('SysAdmin')) echo "<tr><td>id: $Volid";

    echo "<tr>" . fm_text('Name',$Vol,'SN',-2);
    echo "<tr>" . fm_text('Email',$Vol,'Email',-2);
    echo "<tr>" . fm_text('Phone(s)',$Vol,'Phone',-2);
    echo "<tr>" . fm_textarea("Address", $Vol,'Address',3,-3); //fm_text('Address',$Vol,'Address',-2);

    echo "<tr><td>" . fm_radio("Age range",$AgeCats,$Vol,'Over18','',-1) . "<br>All volunteers need to be over 18, a few roles need over 21.";
    $Photo = Feature('VolPhoto');
    if ($Photo) {
      echo "<tr><td>" . ($Photo == 1 ? 'Photo, not essential yet' : 'Photo');
      echo fm_DragonDrop(1,'MobPhoto','Volunteer',$Volid,$Vol,1,'',1,'','Photo');
      }
    echo "<tr><td>" . fm_checkbox("Are you happy to handle Money",$Vol,'Money') . "<br>Needed for some teams";
    echo "<tr><td>" . fm_checkbox("Keep my records",$Vol,'KeepMe') . 
         "<br><span style='font-size:10pt;'>Please uncheck this box if you do not wish the festival to contact you about our future events.<br>" .
         "If you are happy for us to save your details, they will be available to you when you apply next time!</span>"; 

    echo "<tr>" . fm_text('Do you have any medical conditions, disabilities or accessibility requirements that we need to be aware of? ' .
                          'Please give any details to enable us to support you',$Vol,'Disabilities',-2);
    if (Feature('VolDBS')) {
      echo "<tr>" . fm_text("Do you have a current DBS certificate? if so please give details (needed for some volunteering roles)",$Vol,'DBS',-2);
    }
    if (Feature('VolFirstAid')) {
      echo "<tr>" . fm_text("Do you have current First Aid training? if so please give details (Just plain useful for the unknown)" ,$Vol,'FirstAid',-2);
    }
    echo "<tr><td><h3>Emergency Contact</h3>\n";
    echo "<tr>" . fm_text('Contact Name',$Vol,'ContactName',-2);
    echo "<tr>" . fm_text('Contact Phone',$Vol,'ContactPhone',-2);
    echo "<tr><td>Relationship:<br>" . fm_select($Relations,$Vol,'Relation');
    if (Access('SysAdmin')) echo "<tr><td class=NotSide>Debug<tr<td class=NotSide><textarea id=Debug></textarea>";  

  echo "</table>";

  echo "<h2><center>Volunteering in $PLANYEAR</center></h2>";
  echo "<table border>\n";    

    if (isset($Vol['Year']) && $YEAR != $Vol['Year']) {
      echo "<center>This shows what you filled in for " . $Vol['Year'] . " please update as appropriate</center>";
//    $Vol['VYid'] = -1;
    }
    echo "<tr><td><h3><center>Part 2: Which Team(s) would you like to volunteer for?</center></h3>\n";

    $DayTeams = [];
    $DayClasses = [];
    $DayShow = [];
    $Day4All = [];
    foreach ($VolCats as $Cat) {
      $Catid = $Cat['id'];
      $VCY = Get_Vol_Cat_Year($Volid,$Catid);

      $SetShow = ($VCY['Status'] > 0);
      $Ctxt = "";
      $rows = 1;
      $cp = $Cat['Props'];
      $SName = preg_replace('/ /','',$Cat['Name']);
      $cls = "SC_$SName";
      $Colour = (empty($Cat['Colour'])?'' : " style='background:" . $Cat['Colour'] . "' ");
      if (($cp & VOL_USE) == 0) continue;

      $Hide = (($VCY['Status'] == 0) ?"hidden ":'');

      $SplitWhen = explode(',', $Cat['Listofwhen']);
      foreach($SplitWhen as $wh) {
        if (isset($DayTeams[$wh])) {
          $DayTeams[$wh] .= "<span class=$cls $Hide $Colour> " . $Cat['Name'] . ", </span>";
          $DayClasses[$wh] .= " Cat_$Catid";
        } else {
          $DayTeams[$wh] = "<span class=$cls $Hide $Colour>" . $Cat['Name'] . ", </span>";
          $DayClasses[$wh] = "Cat_$Catid"; 
        }
        if ($SetShow) $DayShow[$wh] = 1;
      }
    
      if ($cp & VOL_Likes)   { 
        $rows++; 
        $Ctxt .= "\n<tr class=$cls $Hide $Colour>" . fm_text("Preferred " . $Cat['Name'] . " Tasks", $VCY,'Likes',-2,"class=$cls $Hide $Colour",'',
                 "Likes:$Catid:$PLANYEAR") . "<br>" . $Cat['LExtra']; 
      };
      if ($cp & VOL_Dislikes){ 
        $rows++; 
        $Ctxt .= "\n<tr class=$cls $Hide $Colour>" . fm_text("Disliked " . $Cat['Name'] . " Tasks", $VCY,'Dislikes',-2,"class=$cls $Hide $Colour",'',
                 "Dislikes:$Catid:$PLANYEAR") . "<br>" . $Cat['DExtra']; 
      };

      if ($cp & VOL_Exp){ 
        $rows++; 
        $Ctxt .= "\n<tr class=$cls $Hide $Colour>" . fm_textarea("Please outline your experience with us or other festivals", $VCY,'Experience',3,-3,
                    " class=$cls $Hide $Colour",'',"Experience:$Catid:$PLANYEAR"); 
      };
      for ($i=1; $i<5; $i++) {
        if ($cp & (VOL_Other1 << ($i-1))) {
          $rows++; 
          if ($cp & (VOL_Other1 << ($i+3))) {
            $Ctxt .= "\n<tr class=$cls $Hide $Colour>" . fm_textarea($Cat["OtherQ$i"] . "<br>" . $Cat["Q$i" . "Extra"], $VCY,"Other$i"
                     ,3,-3,"class=$cls $Hide $Colour",'',"Other$i:$Catid:$PLANYEAR");
          
          } else {
            $Ctxt .= "\n<tr class=$cls $Hide $Colour>" . fm_text($Cat["OtherQ$i"], $VCY,"Other$i",-2,"colspan=4 class=$cls $Hide $Colour",
                     '',"Other$i:$Catid:$PLANYEAR") . $Cat["Q$i" . "Extra"] ; 
          }
        }
      }       

      if ($VolMgr) {
        $Ctxt = "\n<tr $Colour><td $Colour>" . fm_radio("<b>" . $Cat['Name'] . "</b>",$CatStatus,$VCY,'Status',
                "onchange=Update_VolMgrCats(event,'$cls',$Catid,$PLANYEAR) data-name='" . $Cat['Name'] . "' data-props=$cp ",-3,'',
                "Status:$Catid:$PLANYEAR") . "<br>" . $Cat['Description'] . $Ctxt;

      } else {
        $Ctxt = "\n<tr $Colour $Colour><td>" .  fm_checkbox($Cat['Name'],$VCY,'Status',
                "onchange=Update_VolCats('$cls',$Catid,$PLANYEAR) data-name='" . $Cat['Name'] . "' data-props=$cp ",
                "Status:$Catid:$PLANYEAR",0,'') . "<br>" . $Cat['Description'] . $Ctxt;
      }
      echo $Ctxt. "\n"; 
    }


    $VYear = Get_Vol_Year($Volid);

  echo "</table>";

  echo "<table border>\n";    

    
    echo "\n<tr><td><h3><center>Part 3: Availability in $PLANYEAR</center></h3>" .
         "If you could help on the days below, please give the times you would be available\n";
    if (isset($DayTeams['Before'])) echo "\n<tr id=TRAvailBefore>" .
        fm_text("Months before the festival",$VYear,"AvailBefore",-2,'','',"AvailBefore::$PLANYEAR") . 
        "<br><div id=TeamsBefore class=Inline>For: " . $DayTeams['Before'] . "</div>"; // " . (empty($DayShow['Before'])?" hidden " : "") . "
    if (isset($DayTeams['Week'])) echo "\n<tr id=TRAvailWeek>" . 
        fm_text("Week before the festival",$VYear,"AvailWeek",-2,'','',"AvailWeek::$PLANYEAR")  . 
        "<br><div id=TeamsWeek class=Inline>For: " . $DayTeams['Week'] . "</div>";//  " . (empty($DayShow['Week'])?" hidden " : "") . "
    for ($day = $YEARDATA['FirstDay']-1; $day<=$YEARDATA['LastDay']+1; $day++) {
      $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
      $rs = (($day<$YEARDATA['FirstDay'] || $day> $YEARDATA['LastDay']));
      echo "\n<tr id=TR$av>" . 
           fm_text("On " . FestDate($day,'M'), $VYear,$av,-2,'','',"$av::$PLANYEAR") . "<br><div id=TeamsWeek class=Inline>For: " . $DayTeams[$day] . "</div>";
    }// " . (empty($DayShow[$day])?" hidden " : "") . "


  echo "</table>";

  echo "<table border>\n";    

     echo "\n<tr><td><h3><center>Part 4: Anything else for $PLANYEAR</center></h3>";
    if (Feature('Vol_Children')) {
      echo "<tr>" . fm_text("Free Childrens tickets (under 10 - please give their ages)",$VYear,'Children',-2,'','',"Children::$PLANYEAR");
      echo "<tr>" . fm_text("Free Youth tickets (11 to 15 - please give their ages)",$VYear,'Youth',4,'','',"Youth::$PLANYEAR");
    }
    if (Feature('Vol_Camping')) {
      $camps = Get_Campsites(1,1);
//var_dump($camps);exit;
      echo "<tr><td>" . fm_radio("Do you want camping?",$camps,$VYear,'CampNeed','',-3,'',"CampNeed::$PLANYEAR",0,0,''," onchange=CampingVolSet('CampNeed::$PLANYEAR')");
      echo "<tr id=CampPUB><td>" . fm_radio("If so for what?" ,$CampType,$VYear,'CampType','',-3,'',"CampType::$PLANYEAR");
      echo "<tr id=CampREST>" . fm_text('Please describe the footprint you need.<br>For example 1 car one tent /<br>one car one tent and a caravan etc ',
                    $VYear,'CampText',-2,'','',"CampText::$PLANYEAR");
    }


    echo "\n<tr><td><h3>Anything Else /Notes:</h3>" . fm_basictextarea($VYear,'Notes',3,3,'',"Notes::$PLANYEAR");
    $Stat = empty($VYear['Status'])?0:$VYear['Status'];
    echo "\n<tr><td>Application Status:<br><span " . ($Stat?'style=color:Green;font-weight:bold;>': 'style=color:Red;font-weight:bold;>') . $YearStatus[$Stat];
    if ($VYear['Status'] == 1 && $VYear['SubmitDate']) echo " On " . date('d/n/Y',$VYear['SubmitDate']);
    if ($VYear['Status'] == 1 && $VYear['SubmitDate'] != $VYear['LastUpdate']  && $VYear['LastUpdate']) echo ", Last updated on " . date('d/n/Y',$VYear['LastUpdate']);
    echo "</span>";

  echo "</table></div><p></div>";
    
    echo "<H3>Actions:</h3>";
    echo "<input type=submit hidden name=ACTION value=View>\n";
    echo "<input type=submit name=ACTION value='Submit/Update Application'>\n"; 
    if ($Vol['id'] > 0) {
      echo "<input type=submit name=ACTION value='Sorry not this Year'>";
      echo "<input class=floatright type=submit name=ACTION value='Remove me from the festival records' " .
         "onClick=\"javascript:return confirm('Please confirm delete?');\">";
    }
    if ($VolMgr) echo "<input type=submit name=ACTION value='Send Updates'>";

  echo "<input type=submit name=ACTION value='Register another volunteer with the same email address'>\n";

  $Mess = TnC("VolTnC");
  Parse_Proforma($Mess);
  echo $Mess;
  echo "</form>\n";

  if (Access('Staff')) echo "<h2><a href=Volunteers?A=List>Back to list of Volunteers</a></h2>";
  
  dotail();
}


function Vol_Validate(&$Vol) {
  global $YEARDATA,$VolCats,$YEAR;

  if (($l = strlen($Vol['SN'])) < 2 || $l > 40) return "Please give your name";
  if (($l = strlen($Vol['Email'])) < 6 || $l > 40) return "Please give your Email";
  if (($l = strlen($Vol['Phone'])) < 6 || $l > 40) return "Please give your Phone number(s)";
  if (($l = strlen($Vol['Address'])) < 10 || $l > 100) return "Please give your Address";
  if (!isset($Vol['Over18']) || !$Vol['Over18']) return "Please confirm you are over 18";
//  if (strlen($Vol['Birthday']) < 2) return "Please give your age";

  $Clss=0;
  $VCYs = Gen_Get_Cond('VolCatYear',"Volid=" . $Vol['id'] . " AND Year=$YEAR");
  foreach ($VCYs as $VCY) if (isset($VCY['Status']) && $VCY['Status']) {
    $Clss++;
    if ($VCY['CatId'] == 0) { /* var_dump($VCY);*/ continue; };
    if (($VolCats[$VCY['CatId']]['Props'] & VOL_NeedDBS) && empty($Vol['DBS'])) return $VolCats[$VCY['CatId']]['Name'] . " requires DBS";
    if (($VolCats[$VCY['CatId']]['Props'] & VOL_Over21) && $Vol['Over18'] <2) return $VolCats[$VCY['CatId']]['Name'] . " requires you to be over 21";
    if (($VolCats[$VCY['CatId']]['Props'] & VOL_Money) && $Vol['Money'] != 1) return $VolCats[$VCY['CatId']]['Name'] . " requires you to be handle money";
  }
  if (Feature('VolPhoto') == 2 && empty($Vol['Photo'])) return "Please supply a photo of yourself so we can print personal volunteer badges.";

  if ($Clss == 0) return "Please select at least one team";

  $Avail=0;
  $VY = Get_Vol_Year($Vol['id']);
  if (isset($VY["AvailBefore"]) && strlen($VY["AvailBefore"]) > 1 && !preg_match('/^\s*no/i',$VY["AvailBefore"],$mtch)) $Avail++;
  if (isset($VY["AvailWeek"]) && strlen($VY["AvailWeek"]) > 1 && !preg_match('/^\s*no/i',$VY["AvailWeek"],$mtch)) $Avail++;
  for ($day =$YEARDATA['FirstDay']-1; $day<=$YEARDATA['LastDay']+1; $day++) {
    $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
    if (isset($VY[$av]) && strlen($VY[$av]) > 0 && !preg_match('/^\s*no/i',$VY[$av],$mtch)) $Avail++;
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


function CSV_Vols() {
  global $YEAR,$db,$VolCats,$YEARDATA,$PLANYEAR,$YearStatus,$Cat_Status_Short,$YearColour,$CatStatus;
  
  $output = fopen('php://output', 'w');
  $heads = ['Name','Email','Phone(s)','Status'];
  foreach ($VolCats as &$Cat) {
    if ($Cat['Props'] & VOL_USE) $heads[] = $Cat['Name'];
  }
  $heads[] = 'Months Before';
  $heads[] = 'Week Before';
  for ($day = $YEARDATA['FirstDay']-1; $day<= $YEARDATA['LastDay']+1; $day++) $heads[] = FestDate($day,'s');
  fputcsv($output, $heads,',','"');

  $res=$db->query("SELECT * FROM Volunteers WHERE Status=0 ORDER BY SN");
  
  if ($res) while ($Vol = $res->fetch_assoc()) {
    $id = $Vol['id'];
    if (empty($id) || empty($Vol['SN']) || empty($Vol['Email']) ) continue;
    $VY = Get_Vol_Year($id);

    if (empty($VY['id'])) continue;
    $csvdat = [$Vol['SN'], $Vol['Email'], $Vol['Phone'], $YearStatus[$VY['Status']]];
    
    foreach ($VolCats as &$Cat) {
      if ($Cat['Props'] & VOL_USE) {
        $VCY = Get_Vol_Cat_Year($Vol['id'],$Cat['id']);
        $csvdat[] = $Cat_Status_Short[$VCY['Status']]; 
      }
    }
    $csvdat[] = $VY['AvailBefore'];
    $csvdat[] =  $VY['AvailWeek'];
    for ($day = $YEARDATA['FirstDay']-1; $day<= $YEARDATA['LastDay']+1; $day++) {    
      $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
      $csvdat[] = $VY[$av];
    }
    fputcsv($output,$csvdat);    
  }
  fclose($output);
  exit;
}


function List_Vols() {
  global $YEAR,$db,$VolCats,$YEARDATA,$PLANYEAR,$YearStatus,$Cat_Status_Short,$YearColour,$CatStatus;
  echo "<button class='floatright FullD' onclick=\"($('.FullD').toggle())\">All Applications</button>" .
       "<button class='floatright FullD' hidden onclick=\"($('.FullD').toggle())\">Curent Aplications</button> ";
  echo "<button class='floatright AvailD' onclick=\"($('.AvailD').toggle())\">Hide Availability</button>" .
       "<button class='floatright AvailD' hidden onclick=\"($('.AvailD').toggle())\">Show Availability</button> ";

//var_dump($VolCats);
    // create a file pointer connected to the output stream

  $ShowCats = ['All'];
  $ShowCols = ['white'];
  foreach ($VolCats as &$Cat) {
    if ($Cat['Props'] & VOL_USE) {
      $ShowCats[$Cat['id']] = $Cat['Name'];
      $ShowCols[$Cat['id']] = $Cat['Colour'];
    }
  }

  foreach ($VolCats as &$Cat) $Cat['Total'] = 0;
  $VolMgr = Access('Committee','Volunteers');
  echo "Click on name for full info<p>";
  echo "A <b>?</b> for a team, means they have volunteered for this team, but not yet been accepted.<p>";
  echo "Where it says EXPAND under availability, means there is a longer entry - click on the persons name or the expand button to see more info on their availabilty<p>";
  
  if ($VolMgr ) echo "To reject an application or do a partial acceptance, first click on their name.<p>";

  $Show['ThingShow'] = 0;
  echo "<div class=floatright ><b>" . fm_radio("Show",$ShowCats,$Show,'ThingShow',' onchange=VolListFilter()',1,'','',$ShowCols)  . "</b></div>";
  
  $coln = 0;
// var_dump($VolCats);  
  echo "<form method=post>";
  echo "<div class=tablecont><table id=indextable border>\n";
  echo "<thead><tr>";

  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Email</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Phone</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Status</a>\n";
  echo "<th class=FullD hidden><a href=javascript:SortTable(" . $coln++ . ",'N')>Year</a>\n";
  foreach ($VolCats as &$Cat) {
// var_dump($Cat);
    if ($Cat['Props'] & VOL_USE) {
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>" . ($Cat['ShortName']?$Cat['ShortName']:$Cat['Name']) . "</a>\n";
    }
  }
  echo "<th class=AvailD><a href=javascript:SortTable(" . $coln++ . ",'T')>Months Before</a>\n";
  echo "<th class=AvailD><a href=javascript:SortTable(" . $coln++ . ",'T')>Week Before</a>\n";
  for ($day = $YEARDATA['FirstDay']-1; $day<= $YEARDATA['LastDay']+1; $day++) {
    echo "<th class=AvailD><a href=javascript:SortTable(" . $coln++ . ",'T')>" . FestDate($day,'s') . "</a>\n";
  }
  if (Access('Committee','Volunteers')) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Actions</a>\n";
  echo "</thead><tbody>";

  $res=$db->query("SELECT * FROM Volunteers WHERE Status=0 ORDER BY SN");
  
  if ($res) while ($Vol = $res->fetch_assoc()) {
    $id = $Vol['id'];
    if (empty($id) || empty($Vol['SN']) || empty($Vol['Email']) ) continue;
    $VY = Get_Vol_Year($id);

    $Accepted = 0;
    $Form = 0;
    $str = '';
    $VClass = 'Volunteer ';

    if (isset($VY['id']) && $VY['id']>0) {
      $year = $PLANYEAR;
    } else {
      for ($year=$PLANYEAR-1; $year>($PLANYEAR-6); $year--) {
        $VY = Get_Vol_Year($id);
        if (!empty($VY['id'])) break;
      }
    }

    foreach ($VolCats as &$Cat) {
      if ($Cat['Props'] & VOL_USE) {
        $VCY = Get_Vol_Cat_Year($Vol['id'],$Cat['id'],$year);
        $str .= "<td>" . $Cat_Status_Short[$VCY['Status']]; 
        if (($CatStatus[$VCY['Status']] == 'Applied') && $VolMgr) $Form = 1;
        if ( $CatStatus[$VCY['Status']] == 'Confirmed') {
          $Cat['Total']++;
          $Accepted++;
        }
        
        if ($Cat_Status_Short[$VCY['Status']]) $VClass .= " VolCat" . $Cat['id'];
      }
    }

//    var_dump($VY);
    $link = "<a href=Volunteers?A=" . ($VolMgr? "Show":"View") . "&id=$id>";
    echo "<tr class='$VClass " . ((($VY['Year'] != $PLANYEAR) || empty($VY['id']) || ($VY['Status'] == 2) || ($VY['Status'] == 4))?" FullD' hidden" : "'" ) . ">";
    echo "<td>$id";
    echo "<td>$link" . $Vol['SN'] . "</a>";
    echo "<td>" . $Vol['Email'];
    echo "<td>" . $Vol['Phone'];
    
    if ($Accepted && ($YearStatus[$VY['Status']] != 'Confirmed')) {
      $VY['Status'] = 3;
      Put_Vol_Year($VY);
    }
    
    echo "<td>" . ((isset($VY['id']) && $VY['id']>0)?("<span style='background:" . $YearColour[$VY['Status']] . ";'>" . $YearStatus[$VY['Status']] . "</span>"):'');
      if (isset($VY['id']) && $VY['id']>0 && $VY['Status'] == 1 && $VY['SubmitDate']) echo "<br>" . date('d/n/Y',$VY['SubmitDate']);
    echo "<td class=FullD hidden>$year";
    echo $str;
    
    echo "<td class=AvailD>" . (isset($VY['AvailBefore'])? ((strlen($VY['AvailBefore'])<12)? $VY['AvailBefore'] : ($link . "Expand</a>")):"");
    echo "<td class=AvailD>" . (isset($VY['AvailWeek'])? ((strlen($VY['AvailWeek'])<12)? $VY['AvailWeek'] : ($link . "Expand</a>")):"");
    for ($day = $YEARDATA['FirstDay']-1; $day<= $YEARDATA['LastDay']+1; $day++) {
      $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
      echo "<td class=AvailD>";
      if (isset($VY[$av])) echo ((strlen($VY[$av])<12)? $VY[$av] : ($link . "Expand</a>")) . "\n";
    }
    
    if ($VolMgr) {
      echo "<td>";
      if ($Form) {
        echo "<b><a href=Volunteers?ACTION=Accept&id=$id>Accept</a></b>\n";
//        echo "<input type=submit name=Accept value=Accept></form>\n";
      }
    }
  }

  echo "<tr><td><td>Total confirmed<td><td><td>";  
    foreach ($VolCats as &$Cat) if ($Cat['Props'] & VOL_USE) echo "<td>" . $Cat['Total'];
  
  
  
  echo "</tbody></table></div>\n";

  echo "<h2><a href=Volunteers?A=New>Add a Volunteer</a></h2>";
  echo "<h2><a href=Volunteers?A=CSV&F=CSV>Volunteer list as a CSV</a></h2>";  
  dotail();
}


function Email_Form_Only($Vol,$mess='',$xtra='') {
  $coln = 0;
  echo "<h2>Stage 1 - Who are you?</h2>";
  if ($mess) echo "<h2 class=Err>$mess</h2>";
  echo "<form method=post>";
  echo "<div class=tablecont><table border>";
  if ($xtra) { 
    echo fm_hidden('Second',$xtra);
    echo fm_hidden('Address',$Vol['Address']);
  }
  echo "<tr>" . fm_text('Name',$Vol,'SN',2);
  echo "<tr>" . fm_text('Email',$Vol,'Email',2);
  echo fm_hidden('A','NewStage2');
  echo "</table></div><p><input type=Submit>\n";
  dotail();
}

function Check_Unique() { // Is email Email already registered - if so send new email back with link to update
  global $M;
  $adr = Sanitise($_REQUEST['Email'],40,'email');
  if (!filter_var($adr,FILTER_VALIDATE_EMAIL)) Email_Form_Only($_POST,"Please give an email address");
  $EVols = Gen_Get_Cond('Volunteers',"Email LIKE '%$adr%'");

  if (!$EVols) return;
  
  $Name = Sanitise($_REQUEST['SN']);
  
  foreach($EVols as $Vol) {
    if ($Name == $Vol['SN']) {
      Email_Volunteer($Vol,"Vol_Link_Message",$Vol['Email']);
      echo "<h2>You are already recorded as a Volunteer</h2>";
      echo "An email has been sent to you with a link to your record, only information about this years volunteering is now needed.<p>";
      dotail();
    }
  }
  
  echo "<form method=post action=Volunteers?ACTION=Select>";
  echo "<H2>Are you?</h2>";
  echo fm_hidden('Email',$adr);
  $lst = $sel = [];
  foreach($EVols as $Vol) {  
    $lst[$Vol['id']] = $Vol['SN'];
  }
  $lst[-$Vol['id']] = 'New Volunteer on same email address';

// tabs 0=none, 1 normal, 2 lines between, 3 box before txt
  echo fm_radio('',$lst,$sel,'id',' onchange=this.form.submit()',-3);
  echo "</form>";
  dotail();
  
/*
  if ($res && $res->num_rows) {
    $Vol = $res->fetch_assoc();
    if (1 ||!Access('Staff')) {
      Email_Volunteer($Vol,"Vol_Link_Message",$Vol['Email']);
      echo "<h2>You are already recorded as a Volunteer</h2>";
      echo "An email has been sent to you with a link to your record, only information about this years volunteering is now needed.<p>";
      dotail();
    }
//    echo "<h2>" . $Vol['SN'] . " Is already a volunteer</h2>";
//    $id = $Vol['id'];
//    $Vol = array_merge($Vol, Get_Vol_Year($id));
    $M($Vol);
  } // else new - full through*/
}

function SendCatsToBrowser() {
  global $VolCats;
  echo fm_hidden('VolCatsRaw',base64_encode(json_encode($VolCats)));
}

function Send_Accepts($Vol) {
  global $VolCats;
    
  $Accepted = $Rejected = $Pending = 0;
  $VY = Get_Vol_Year($Vol['id']);
  foreach($VolCats as $Cat) {
    $VCY = Get_Vol_Cat_Year($Vol['id'],$Cat['id']);
    if ($VCY['Status'] == 3) { $Accepted++; }
    elseif ($VCY['Status'] == 4) { $Rejected++; }
    elseif ($VCY['Status'] == 1) { $Pending++; }
  }
  
  if ($Accepted) {
    $VY['Status'] = 3;
    Put_Vol_Year($VY);
    Email_Volunteer($Vol,"Vol_Accept",$Vol['Email']);
  } elseif ($Pending) { // no Action
  } elseif ($Rejected) {
    $VY['Status'] = 4;
    Put_Vol_Year($VY);
    Email_Volunteer($Vol,"Vol_Reject",$Vol['Email']);  
  }
}

function VolAction($Action,$csv=0) {
  global $PLANYEAR,$VolCats,$M;

  if ($csv == 0) {
    dostaffhead("Steward / Volunteer Application", ["/js/Volunteers.js","js/dropzone.js","css/dropzone.css" ]);
    SendCatsToBrowser();
  }
//var_dump($Action);
//var_dump($_REQUEST);

  $M = (isset($_REQUEST['M'])?'VolFormM':'VolForm');

  switch ($Action) {
  case 'New': // New Volunteer
  default:
    $Vol = ['id'=>-1, 'Year'=>$PLANYEAR,'KeepMe'=>1];
    Email_Form_Only($Vol);
    break;

  case 'NewStage2': 
    if (isset($_REQUEST['Second'])) {
      $Os = ['Email'=>$_POST['Email'], 'id'=> -1 ];
      $OVs = OtherVols($Os);
      $Name = Sanitise($_POST['SN']);
      if ($OVs) foreach($OVs as $OV) if ($OV['SN'] == $Name) {
        echo "<span class=Err>$Name is already in the system</span><br>";
        $Vol = $OV;
        $M($Vol);     
      }
      $Vol = ['Year'=>$PLANYEAR, 'SN'=>$Name, 'Email'=>$_POST['Email'], 'KeepMe'=>1, 'AccessKey' => rand_string(40), 'Address'=>$_POST['Address'] ];
      $Volid = Gen_Put('Volunteers',$Vol);
      $M($Vol);
    }
    Check_Unique(); // Deliberate drop through

  case 'Form': // New stage 2
    $Vol = ['Year'=>$PLANYEAR, 'SN'=>$_POST['SN'], 'Email'=>$_POST['Email'], 'KeepMe'=>1, 'AccessKey' => rand_string(40)];
    $Volid = Gen_Put('Volunteers',$Vol);
    $M($Vol);
    break;
    
  case 'List': // List Volunteers
    List_Vols();
    break;
  
  case 'CSV': // List Volunteers as CSV
    CSV_Vols();
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
      $M($Vol,$res);
    } else {
      $VY = Get_Vol_Year($Vol['id']);
      $VY['Status'] = 1;
      Put_Vol_Year($VY);
    }
    
    if (empty($Vol['AccessKey']) || !isset($_REQUEST['id']) || $_REQUEST['id'] < 0) { // New
      $Vol['AccessKey'] = rand_string(40);
      Put_Volunteer($Vol);
    }
    
    $VY['LastUpdate'] = time();

    if ($VY['SubmitDate']) {
      Put_Vol_Year($VY);
      Vol_Emails($Vol,'Update');
    } else {
      $VY['SubmitDate'] = time();
      Put_Vol_Year($VY);
      Vol_Emails($Vol,'Submit');
    }

    break;
  
  case 'View':
    $Vol = Get_Volunteer($_REQUEST['id']);
    $Volid = $Vol['id'];

    VolView($Vol);
    break;
     
  case 'Show':
    $Vol = Get_Volunteer($_REQUEST['id']);
    $Volid = $Vol['id'];

    $M($Vol);
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
//    Put_Volunteer($Vol);
    $VY = Get_Vol_Year($Vol['id'],$PLANYEAR);
    if (!empty($VY['id'])) Vol_Staff_Emails($Vol);

//??    if ($Vol['Year'] == $PLANYEAR) Vol_Staff_Emails($Vol);

    echo "<h2>Thankyou for Volunteering in the past, you are no longer recorded</h2>";
    db_delete('Volunteers',$id);  
    break;

  case 'Accept':
    $Vol = Get_Volunteer($id = $_REQUEST['id']);
    
    $Accepted = 0;
//    $AccList = [];
    $VY = Get_Vol_Year($Vol['id'],$PLANYEAR);
    foreach($VolCats as $Cat) {
      $VCY = Get_Vol_Cat_Year($Vol['id'],$Cat['id'],$PLANYEAR);
      if ($VCY['Status'] == 1) {
        $VCY['Status'] = 3; // Accepted
        Put_Vol_Cat_Year($VCY);
//        $AccList[] = $Cat['id'];
        $Accepted++;
      }
    }
    if ($Accepted) {
      $VY['Status'] = 3; // Accepted at least once
      Put_Vol_Year($VY);
      Send_Accepts($Vol);
    }
    List_Vols();
    break;


  case 'Send Updates':
    $Vol = Get_Volunteer($id = $_REQUEST['id']);
    Send_Accepts($Vol);
    List_Vols();
    break;

  case 'Register another volunteer with the same email address':
    $OldVol = Get_Volunteer($id = $_REQUEST['id']);
    $Vol = ['Email'=>$OldVol['Email'], 'Address'=>$OldVol['Address'],'id'=>-1, 'Year'=>$PLANYEAR,'KeepMe'=>1,'Over18'=>0,];
    Email_Form_Only($Vol,'',1); // WRONG
    
  case 'Select':
    $id = $_REQUEST['id'];
    if ($id > 0) {
      $Vol = Get_Volunteer($id);
      if (1 ||!Access('Staff')) {
        Email_Volunteer($Vol,"Vol_Link_Message",$Vol['Email']);
//        echo "<h2>You are already recorded as a Volunteer</h2>";
        echo "An email has been sent to you with a link to your record, only information about this years volunteering is now needed.<p>";
        dotail();
      }
      $M($Vol);
    }
    if (1 ||!Access('Staff')) {
      $Vol = Get_Volunteer(-$id);
      Email_Volunteer($Vol,"Vol_Link_Message",$Vol['Email']);
        echo "An email has been sent to you with a link to the existing record, scroll to the bottom and then click on " .
             "<b>Register another volunteer with the same email address</b>.<p>";
        dotail();
      }
    $M($Vol);

  }  
  if (Access('Staff')) List_Vols();
  echo "<h2>Something has gone wrong...</h2>";
  dotail();
}


/*
  TODO
  1) DBS upload
  6) Email all/subsets
  7) Form validation - hack prevention

*/

?>
