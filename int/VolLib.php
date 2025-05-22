<?php
  include_once("fest.php");

  include_once("Email.php");
//  include_once("SignupLib.php");
  global $USER,$USERID,$db,$YEAR,$StewClasses,$Relations,$AgeCats,$CampType,$CampStatus;

$yesno = array('','Yes','No');
$Relations = array('','Husband','Wife','Partner','Son','Daughter','Mother','Father','Brother','Sister','Grandchild','Grandparent','Guardian','Uncle','Aunty',
                'Son/Daughter in law', 'Friend','Other');
$YearStatus = ['Not Submitted','Submitted','Withdrawn','Confirmed','Rejected'];
$YearColour = ['white','Yellow','white','lightgreen','Pink'];
$CatStatus = ['No','Applied','Withdrawn','Confirmed','Rejected'];
$Cat_Status_Short = ['','?','','<b>Y</b>',''];
$AgeCats = ['Under 18','Over 18','Over 21'];
$CampStatus = ['No','Yes, I am in a group of volunteers, another person is booking the space. e.g. Two people in a tent/van only need one tent/van space'];
$VolOrders = ['','1st','2nd','3rd'];
// $CampType = ['','Small Tent','Large Tent','Campervan','Caravan'];
$CampType = Gen_Get_Names('Camptypes');
$ADTimes = [0=>'No idea',9=>'9am',10=>'10am',11=>'11am',12=>'Midday',13=>'1pm',14=>'2pm',15=>'3pm',16=>'4pm',17=>'5pm',18=>'6pm',
  19=>'7pm',20=>'8pm',21=>'9pm',22=>'10pm',23=>'11pm',24=>'Midnight',25=>'Later'];
$FDays = ['Fri','Sat','Sun','Mon'];

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
define('VOL_NoList',0x20000);
define('VOL_Tins',0x40000); // Not Used
// define('VOL_TeamFull',0x80000); Don't Use
define('VOL_FullAvail',0x100000);
define('VOL_GROUPQS',0x200000);
define('VOL_HEADER',0x400000);
define('VOL_GRP1',0x1000000);
define('VOL_GRPS',0xf000000);

define('VOL_OMIT_SUBMIT',1); // Props 2
define('VOL_OMIT_CANCEL',2); // Props 2
define('VOL_CAT_FULL',4); //Props 2

// Button Name, Vol_Button
$EmailMsgs = [''=>'','U'=>'NotSub','E' => Feature('Vol_Special_Mess'),'G' => Feature('Vol_Special_Mess3'),
  'S'=>'Stew1','M'=>'Note2','F' => Feature('Vol_Special_Mess2'),'T' => 'Vol_Post_Fest1', 'O' => 'Vol_October',
  'N'=>'Vol_November', 'D'=>'Vol_December', 'J'=>'Vol_January', 'R' => 'Vol_March', 'A'=>'Vol_April', 'r'=>'Vol_March2',
];

$VolCats = Gen_Get_All('VolCats','ORDER BY Importance DESC');
//$VolGroups = Gen_Get_All('VolGroups','ORDER BY Importance DESC');

function Get_Campsites($Restrict='',$Comments=1) {
  global $CampStatus;
  $CList = $CampStatus;
  $Camps = Gen_Get_All('Campsites','ORDER BY Importance DESC');
  foreach ($Camps as $C) {
    $N = $C['Name'];
    if ($Comments && !empty($C['Comment'])) $N .= " (" . $C['Comment'] . ")";
    if ($C['Props'] & 2) {
      if (!$Restrict) continue;
      if ($Restrict == 'All' || strstr($C['Restriction'],$Restrict)) {
        $CList[$C['id']] = $N . " - " . $C['Restriction'];
      } else {
        continue;
      }
    } else {
      $CList[$C['id']] = $N;
    }
  }
  return $CList;
}

function Get_Vol_Details(&$vol) {
  global $VolCats,$Relations,$YEARDATA,$YEAR,$YearStatus,$AgeCats,$CampType,$VolOrders,$ADTimes;
// var_dump($vol);
  $Volid = $vol['id'];
  $Body = "\nName: " . $vol['SN'] . "<br>\n";
  $Body .= "Email: <a href=mailto:" . $vol['Email'] . ">" . $vol['Email'] . "</a><br>\n";
  if ($vol['Phone']) $Body .= "Phone: " . $vol['Phone'] . "<br>\n";
  $Body .= "Address: " . $vol['Address'] . "<br>\n";
  if (isset($vol['PostCode'])) $Body .= "PostCode: " . $vol['PostCode'] . "<br>\n\n";
  $Body .= "Age: " . $AgeCats[$vol['Over18']] . "<br>\n";
  if (Feature('VolMoney')) $Body .= "Handle Money:" . ($vol['Money']?'Yes':'No') . "<br>\n";
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

  $NeedAv = $NeedDp = 0;

  foreach ($VolCats as $Cat) {
    $Catid = $Cat['id'];
    if (!($Cat['Props'] & VOL_USE )) continue;
      $cp = $Cat['Props'];
      $VCY = Get_Vol_Cat_Year($Volid,$Catid,$YEAR);
      if (!empty($VCY['id']) && ($VCY['Status'] > 0)) {
        if (($cp & VOL_GROUPQS) == 0) $Body .= "<p>Team: " . $Cat['Name'] . "<br>\n";
        if ($VCY['VolOrder']??0) $Body .= "Team Preference: " . $VolOrders[$VCY['VolOrder']] . "<br>\n";
        if (($cp & VOL_Likes)  && !empty($VCY['Likes'])) $Body .= "Like: " . $VCY['Likes'] . "<br>\n";
        if (($cp & VOL_Dislikes)  && !empty($VCY['Dislikes'])) $Body .= "Dislike: " . $VCY['Dislikes'] . "<br>\n";
        if (($cp & VOL_Exp)  && !empty($VCY['Experience'])) $Body .= "Experience: " . $VCY['Experience'] . "<br>\n";
        if (($cp & VOL_Other1)  && !empty($VCY['Other1'])) $Body .= $Cat['OtherQ1'] . ": " . $VCY['Other1'] . "<br>\n";
        if (($cp & VOL_Other2)  && !empty($VCY['Other2'])) $Body .= $Cat['OtherQ2'] . ": " . $VCY['Other2'] . "<br>\n";
        if (($cp & VOL_Other3)  && !empty($VCY['Other3'])) $Body .= $Cat['OtherQ3'] . ": " . $VCY['Other3'] . "<br>\n";
        if (($cp & VOL_Other4)  && !empty($VCY['Other4'])) $Body .= $Cat['OtherQ4'] . ": " . $VCY['Other4'] . "<br>\n";

        if ($cp & VOL_FullAvail) {
          $NeedAv = 1;
        } else {
          $NeedDp = 1;
        }
      }
    }
  $Body .= "<p>\n";
  $Body . "Available:<p>\n";
  $VY = Get_Vol_Year($Volid);

  if ($NeedDp || $VY["Arrival"] || $VY["Depart"] || $VY["ArriveTime"] || $VY["DepartTime"]) {
    $Body .= "Arrive: " . FestDate($VY['Arrival'],'V') . " " . $ADTimes[$VY['ArriveTime']] . "<br>\n";
    $Body .= "Depart: " . FestDate($VY['Depart'],'V') . " " . $ADTimes[$VY['DepartTime']] . "<p>\n";
  }

  if (isset($VY['AvailBefore']) && $VY['AvailBefore'])  $Body .= "Months Before Festival: " . $VY["AvailBefore"] . "<br>\n";
  if (isset($VY['AvailWeek']) && $VY['AvailWeek'])  $Body .= "Week Before Festival: " . $VY["AvailWeek"] . "<br>\n";
  for ($day = $YEARDATA['FirstDay']-1; $day<= $YEARDATA['LastDay']+1; $day++) {
    $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
    if (isset($VY[$av]) && $VY[$av]) $Body .= FestDate($day,'M') . ": " . $VY[$av] . "<br>\n";
  }

  if (isset($VY['Notes']) && $VY['Notes']) $Body .= "<p>Notes: " . $VY['Notes'] . "<p>\n";

  if (isset($VY['Commitments']) && $VY['Commitments']) $Body .= "<p>Commitments: " . $VY['Commitments'] . "<p>\n";

  if (isset($VY['id']) && $VY['id']) $Body .= "<p>Status: " . $YearStatus[$VY['Status']];
  if (isset($VY['id']) && $VY['id']>0 && $VY['Status'] == 1 && $VY['SubmitDate']) $Body .=  " On " . date('d/n/Y',$VY['SubmitDate']);
  if (isset($VY['id']) && $VY['id']>0 && $VY['Status'] == 1 && $VY['SubmitDate'] != $VY['LastUpdate'] && $VY['LastUpdate'])
    $Body .=  " Last updated on " . date('d/n/Y',$VY['LastUpdate']);
  $Body .= "<p>\n";

  if (!empty($VY['Children'])) $Body .= "Children: " . $VY['Children'] . "<p>\n";
  if (!empty($VY['Youth'])) $Body .= "Youth: " . $VY['Youth'] . "<p>\n";

  $camps = Get_Campsites('All');

  if (Feature('Vol_Camping') && !empty($VY['CampNeed'])) {
    $Body .= "Camping: " . $camps[$VY['CampNeed']] . "<br>\n";
    if ($VY['CampNeed'] < 10) { }
    elseif ($VY['CampNeed'] < 20) $Body .= "Space for: " . ($CampType[$VY['CampType']] ?? 0) . "<p>\n";
    elseif ($VY['CampNeed'] < 30) $Body .= "Space for: " . $VY['CampText'] . "<p>\n";
  }

  return $Body;
}

function Vol_Details($key,&$vol) {
  global $VolCats,$CatStatus,$VolGroups,$ADTimes;
  switch ($key) {
  case 'WHO': return firstword($vol['SN']);
  case 'DETAILS': return Get_Vol_Details($vol);
  case 'LINK' :
    if (empty($vol['AccessKey'])) {
      $vol['AccessKey'] = rand_string(40);
      Put_Volunteer($vol);
    }
    return "<a href='https://" . $_SERVER['HTTP_HOST'] . "/int/Access?t=v&i=" . $vol['id'] . "&k=" . $vol['AccessKey'] . "'><b>link</b></a>";
  case 'INNERLINK': return "https://" . $_SERVER['HTTP_HOST'] . "/int/Access?t=v&i=" . $vol['id'] . "&k=" . $vol['AccessKey'];

  case 'FESTLINK' : return "<a href='https://" . $_SERVER['HTTP_HOST'] . "/int/Volunteers?A=View&id=" . $vol['id'] . "'><b>link</b></a>";
  case 'VOLTEAM_ACCEPT' :
    $Accept = '';
    foreach ($VolCats as $Cat) {
      $VCY = Get_Vol_Cat_Year($vol['id'],$Cat['id']);
      if ($VCY['Status'] > 0) $Accept .= $Cat['Name'] . " - " . $CatStatus[$VCY['Status']] . "<br>\n";
    }
    return $Accept;

  case 'VOLTEAM_TIMES' :
    $Times = '';
    foreach ($VolCats as $Cat) {
      $VCY = Get_Vol_Cat_Year($vol['id'],$Cat['id']);
      if ($VCY['Status'] == 3) {
        if ($Cat['Name'] == 'Task Force') {
          $Times .= $Cat['Name'] . " - your working hours will be between *DAY-4* and *DAY4*<br>\n";
        } else {
          $Times .= $Cat['Name'] . " - your working hours will be between *DAY0* and *DAY3*<br>\n";
        }
      }
    }
    return $Times;


  case 'COLLECTINFO':
    include_once("CollectLib.php");
    return CollectInfo($vol,1);
  }
}

function Email_Volunteer(&$vol,$messcat,$whoto) {
  global $YEAR;
  Email_Proforma(EMAIL_VOL,$vol['id'],$whoto,$messcat,Feature('FestName') . " $YEAR and " . $vol['SN'],'Vol_Details',$vol,'Volunteer.txt');
}

function Get_Volunteer($id) { return Gen_Get('Volunteers',$id); }

function Put_Volunteer(&$now) { return Gen_Put('Volunteers',$now); }

function Get_Vol_Cat_Year($Volid,$CatId,$Year=0) {
  global $YEAR;
  if ($Year == 0) $Year = $YEAR;
  $VCY = Gen_Get_Cond1('VolCatYear'," Volid=$Volid AND CatId=$CatId AND Year=$Year ");
  if (isset($VCY['id'])) return $VCY;
  return ['Volid'=>$Volid,'CatId'=>$CatId,'Year'=>$YEAR,'id'=>0, 'Status'=>0];
}

function Put_Vol_Cat_Year(&$VCY) {
  return Gen_Put('VolCatYear',$VCY);
}

function Get_Vol_Year($Volid,$Year=0) {
  global $YEAR;
  if ($Year == 0) $Year = $YEAR;
  $VY = Gen_Get_Cond1('VolYear'," Volid=$Volid AND Year=$Year ");

  if (!isset($VY['History'])) $VY['History'] = "";
  if (isset($VY['id'])) return $VY;
  return ['Volid'=>$Volid,'Year'=>$Year,'id'=>0, 'Status'=>0, 'CampNeed'=>0, 'History' => ""];
}

function Put_Vol_Year(&$VY) {
  Gen_Put('VolYear',$VY);
}

function BeforeTeams($term='Before') {
  global $VolCats;
  static $txt = '';
  if ($txt) return $txt;
  $mtch = [];
  $teams = [];
  foreach ($VolCats as $Cat)
    if ((( $Cat['Props'] & VOL_USE) != 0) && (preg_match('/' . $term . '/',$Cat['Listofwhen'],$mtch)))
      $teams[] = $Cat['Name'];
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


function CatsInGroups() {
  global $VolCats,$YEARDATA,$YEAR,$YEAR,$Relations,$YearStatus,$AgeCats,$CampType,$CatStatus,$VolOrders,$VolGroups; //M



}

function VolForm(&$Vol,$Err='',$View=0) {
  global $VolCats,$YEARDATA,$YEAR,$YEAR,$Relations,$YearStatus,$AgeCats,$CampType,$CatStatus,$VolOrders,$VolGroups,$ADTimes; //M
  $Volid = $Vol['id'];
  $AgeColours = ['Orange','Yellow','lightGreen'];
// var_dump($Vol);

  $M = $_REQUEST['M']??0;
  $VolMgr = Access('Committee','Volunteers') && !isset($_REQUEST['FORCE']);

  $CopyList = ['t','i','ACTION','k','id','A','FORCE'];
  echo "<form method=post action=Volunteers?M>";
  foreach ($CopyList as $F) if (isset($_REQUEST[$F])) echo fm_hidden($F,$_REQUEST[$F]);
  echo fm_hidden('M',(1-$M));
  echo "<input type=submit style='font-size:12pt' value='Switch to: " . ['Mobile','Computer'][$M] . " Friendly Version'>";
  echo "</form>";

  $MM = ($M?'':'&M=1');
  if ($VolMgr) {
    echo "<h2><a class=floatright href=Volunteers?A=Show&id=$Volid&FORCE$MM>As Seen by the Volunteer</a></h2>";
  }

  $OVols = OtherVols($Vol);
  if ($OVols) {
    echo "<h2>This page is for " . $Vol['SN'] . "</h2>\n";
    $Ovlst = [];
    foreach ($OVols as $V) $Ovlst[$V['id']] = $V['SN'];

    echo "<form method=post action=Volunteers?$MM>";
    echo fm_hidden('ACTION',($View ?'View':'Show'));
    echo fm_radio("Switch to",$Ovlst, $_REQUEST,'id','onchange=this.form.submit()',-3);
    echo "</form>";
  }

  echo "<h2 class=subtitle>Steward / Volunteer Application Form</h2>\n";
  if (!empty($Err)) echo "<p class=Err>$Err<p>";
  echo "<form method=post action=Volunteers>";
  if ($M) {
    echo fm_hidden('M',$M);
    echo "<div>"; // class=VolWrapper>";
    $Col1 = -1;
    $Col2 = -2;
    $Col3 = -3;
    $Col4 = -2; // Not an error
    $Col5 = -2;
    $Csp4 = $Csp5 = '';
    $td = '<td>';
    $td3 = '<br>';
  } else {
    $Col1 = 1;
    $Col2 = 2;
    $Col3 = 3;
    $Col4 = 4;
    $Col5 = 5;
    $Csp4 = 'colspan=4 ';
    $Csp5 = 'colspan=5 ';
    $td = '';
    $td3 = '<td colspan=3>';

  }
  Register_AutoUpdate('Volunteers',$Volid);
  Register_Onload('CampingVolSet',"'CampNeed::$YEAR'",0);

  echo fm_hidden('id',$Volid) . fm_hidden('kvk', substr($Vol['AccessKey'],0,6)) . fm_hidden('VolManager',$VolMgr);


    if ($VolMgr) echo "If you change any of the team statuses on this page you must click <b>Send Updates</b>, to notify the volunteer.<p>";

    echo "This is in 4 parts:";
    echo "<ol><li><b>Who you are</b>.  This will normally be kept year to year, so you should only need to fill this in once.\n";
    echo "<li>Which <b>team(s)</B>. you would like to be part of, along with any likes, dislikes and team related details.\n";
    echo "<li>Your <b>availability</b> this year.\n";
    echo "<li>Anything special this year and the <b>submit</b> button.<p>";

    echo "</ol>";
    if ($M) {
      echo "</div>";
      echo "<table border>\n";
    } else {
      echo "<div class=tablecont><table border style='table-layout:fixed'>\n";
    }


    echo "<tr><td colspan=5><h3><center>Part 1: The Volunteer</center></h3>";
    if (Access('SysAdmin')) echo "<tr><td>id: $Volid";
    echo "<tr>" . fm_text('Name',$Vol,'SN',$Col4);
    echo "<tr>" . fm_text('Email',$Vol,'Email',$Col4);
    echo "<tr>" . fm_text('Phone(s)',$Vol,'Phone',$Col4);

    echo "<tr>" . ($M?'<td>':'') . fm_textarea("Address", $Vol,'Address',$Col4,$Col3);

    echo "<tr>$td" . fm_Radio("Age range",$AgeCats,$Vol,'Over18'," style='margin-bottom:10'",$Col1,'','',$AgeColours). $td3  . "All volunteers need to be over 18, a few roles need over 21.";
    if ($VolMgr) echo " <span class=NotSide>" . fm_checkbox("Allow Underage",$Vol,'AllowUnder') . "</span>";
    $Photo = Feature('VolPhoto');
    if ($Photo) echo "<tr rowspan=4 colspan=4 height=80><td>" . ($Photo == 1 ? 'Photo, not essential yet' : 'Photo') .
        fm_DragonDrop(1,'Photo','Volunteer',$Volid,$Vol,1,'',1,'','Photo');
    if (Feature('VolMoney')) echo "<tr><td>" . fm_checkbox("Are you happy to handle Money",$Vol,'Money',"","",0). $td3 . "Needed for some teams";
    echo "<tr><td>" . fm_checkbox("Keep my records",$Vol,'KeepMe',"","",($M?0:1),'colspan=4') .
         "Please uncheck this box if you do not wish the festival to contact you about our future events.<br>" .
         "If you are happy for us to save your details, they will be available to you when you apply next time!";

    echo "<tr>" . fm_text('Do you have any medical conditions, disabilities or accessibility requirements that we need to be aware of? ' .
                          'Please give any details to enable us to support you',$Vol,'Disabilities',$Col4);
    if (Feature('VolDBS')) {
      echo "<tr><td" . ($M?'':' colspan=5') . ">";
      echo "Do you have a current DBS certificate? if so please give details (needed for some volunteering roles)<br>" .
           fm_textinput('DBS',(isset($Vol['DBS'])?$Vol['DBS']:''),'size=100');
    }
    if (Feature('VolFirstAid')) {
      echo "<tr><td" . ($M?'':' colspan=5') . ">";
      echo "Do you have current First Aid training? if so please give details (Just plain useful for the unknown)<br>" .
           fm_textinput('FirstAid',(isset($Vol['FirstAid'])?$Vol['FirstAid']:''),'size=100');
    }
    echo "<tr><td colspan=5><h3>Emergency Contact</h3>\n";
    echo "<tr>" . fm_text('Contact Name',$Vol,'ContactName',$Col4);
    echo "<tr>" . fm_text('Contact Phone',$Vol,'ContactPhone',$Col4);
    echo "<tr><td>Relationship:$td3" . fm_select($Relations,$Vol,'Relation');
    if (Access('SysAdmin')) echo "<tr><td class=NotSide colspan=5>Debug:<br><textarea id=Debug></textarea>";

    if ($M) {
      echo "</table>";
      echo "<h2><center>Volunteering in $YEAR</center></h2>";
      echo "<table border>\n";

    } else {
      echo "<tr><td colspan=5><h2><center>Volunteering in $YEAR</center></h2>";

    }

  $VYear = Get_Vol_Year($Volid);//!

    if ($VYear['id'] == 0) {
      $VYear1 = Get_Vol_Year($Volid,$YEAR-1);
      if ($VYear1['id'] != 0) {
//        $VYear = $VYear1;
//        $VYear['Status'] = 0;
        echo "<center>This shows what you filled in for " . $VYear1['Year'] . " please update as appropriate</center>";
      }
    }
    echo "<tr><td" . ($M?'':' colspan=5') . "><h3><center>Part 2: Which Team(s) would you like to volunteer for?</center></h3>\n";
    echo "<center>If you select more than one team, please indicate your preference (1st, 2nd, 3rd)</center>";

    $DayTeams = [];
    $DayClasses = [];
    $DayShow = [];
    $NeedAD = $NeedAV = 0;
    $LastGroup = $ShowGroup = 0;

    foreach ($VolCats as $Cat) {
      $Catid = $Cat['id'];
      $VCY = Get_Vol_Cat_Year($Volid,$Catid);

//      echo "<tr><td colspan=5>"; var_dump($Vol,$Cat,$VCY);

      if ($VCY['id'] == 0) {
        if (!empty($VYear1['id'])) {
          $VCY = Get_Vol_Cat_Year($Volid,$Catid,$VYear1['Year']);
          $VCY['Year'] = $YEAR;
          unset($VCY['id']);
          if ($VCY['Status'] > 1) $VCY['Status'] = 1;
          Put_Vol_Cat_Year($VCY);
        } else if (($Vol['Cat']??0) == $Catid) {
          $VCY['Status'] = 1;
          Put_Vol_Cat_Year($VCY);
          if (empty($VYear['id'])) {
            Put_Vol_Year($VYear);
          }
        }
      }

      $cp = $Cat['Props'];
      $cp2 = $Cat['Props2'];
      if ((!$VolMgr) && ($cp & VOL_NoList) && ($VCY['Status'] == 0)) continue; // Skip if team not listed
//      if ((!$VolMgr) && ($cp & VOL_TeamFull) && ($VCY['Status'] == 0)) continue; // Skip if team full Do differently if needed again

      $SetShow = ($VCY['Status'] > 0);
      $Ctxt = "";
      $rows = 1;

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

      if (($cp & VOL_GROUPQS) == 0) {
        if ($VolMgr) {
          echo "\n<tr $Colour><td $Colour>" .
            fm_radio("<b>" . $Cat['Name'] . "</b> " . fm_select($VolOrders,$VCY,'VolOrder',1,'',"VolOrder:$Catid:$YEAR") ,$CatStatus,$VCY,'Status',
            "onchange=Update_VolCats(event,'$cls',$Catid,$YEAR) data-name='" . $Cat['Name'] . "' data-props=$cp ",-3,'',
            "Status:$Catid:$YEAR") . "<br>" .
            ($M?' &nbsp; ':"<td $Csp4 $Colour>") . $Cat['Description'] ;
          
            if ($cp2 & VOL_CAT_FULL) echo "<br>Note this category is full"; 

        } else {
          if ((($cp2 & VOL_CAT_FULL) == 0) || $VCY['Status'] ) {
            echo "\n<tr $Colour><td $Colour>" .  fm_checkbox("<b>" . $Cat['Name'] . "</b>",$VCY,'Status',
              "onchange=Update_VolCats(event,'$cls',$Catid,$YEAR) data-name='" . $Cat['Name'] . "' data-props=$cp ",
              "Status:$Catid:$YEAR",0,'') .
              "<span  class=$cls $Hide><br>Team Preference: " . fm_select($VolOrders,$VCY,'VolOrder',1,'',"VolOrder:$Catid:$YEAR") . "</span>" .
              ($M?' &nbsp; ':"<td $Csp4 $Colour>") . $Cat['Description'] ;
          } else {
            echo "\n<tr $Colour><td $Colour>" . $Cat['Name'] . "</b> is full please select another team.";
          }
        }
        if ($VCY['Status'] > 0) {
          $ShowGroup = 1;
          if ($cp & VOL_FullAvail) {
            $NeedAV = 1;
          } else {
            $NeedAD = 1;
          }
        }
      } else if ($cp & VOL_HEADER) {
        echo "\n<tr $Colour class='CatGroup" . $Cat['FormGroup'] . "'" . ($ShowGroup?'':' hidden') .
          "><td $Colour colspan=5><b>" . $Cat['Description'] . "</b>";
      }

//      echo "<tr><td>$ShowGroup<td>"; var_dump($Cat);

      $ShowAnyway = (($VCY['Likes']??0) || ($VCY['Dislikes']??0) || ($VCY['Experience']??0) );
      if (!$ShowAnyway) {
        for ($i=1; $i<5; $i++) {
          if ($cp & (VOL_Other1 << ($i-1))) {
            if (($cp & (VOL_Other1 << ($i+3))) && ($Cat["OtherQ$i"]??0)) {
              $ShowAnyWay = 1;
              break;
            }
          }
        }
      }

      $Override = (($Cat['FormGroup'] != 0) && (($cp & VOL_GROUPQS) == 0) && (($cp & VOL_GRPS )!=0));
      $BaseShow = (($Cat['FormGroup'] == 0) || (($Cat['FormGroup'] != 0) && (($cp & VOL_GROUPQS) != 0)) || $ShowAnyway);

//      echo "<tr><td colspan=5>"; var_dump($Override,$BaseShow,$VCY);

      if ($BaseShow || $Override) {
        $Xtr = (($Cat['FormGroup'] && ($cp & VOL_GROUPQS) != 0)?("class='$cls CatGroup" . $Cat['FormGroup'] . "'"):"class=$cls");
        $QHide = ((((($cp & VOL_GROUPQS) != 0) && $ShowGroup) || ((($cp & VOL_GROUPQS) == 0) || $ShowAnyway) && ($VCY['Status']>0))?'':' hidden');

        if ($BaseShow && ($cp & VOL_Likes))  {
          echo "\n<tr $Xtr $QHide $Colour>" . fm_text1("Preferred " . $Cat['Name'] . " Tasks", $VCY,'Likes',$Col5,"$Csp4 class=$cls $Colour",'',
                   "Likes:$Catid:$YEAR") . $Cat['LExtra'];
        }
        if ($BaseShow && ($cp & VOL_Dislikes)) {
          echo "\n<tr $Xtr $QHide $Colour>" . fm_text1("Disliked " . $Cat['Name'] . " Tasks", $VCY,'Dislikes',$Col5,"$Csp4 class=$cls $Colour",'',
                   "Dislikes:$Catid:$YEAR") . $Cat['DExtra'];
        }

        if ($BaseShow && ($cp & VOL_Exp)) {
          echo "\n<tr $Xtr $QHide $Colour>" . ($M?"<td $Colour>":'') .
            fm_textarea("Please outline your relevant experience", $VCY,'Experience',$Col5-1,$Col3,
            " class=$cls $QHide $Colour",'', "Experience:$Catid:$YEAR");
        }
        for ($i=1; $i<5; $i++) {
          if ($cp & (VOL_Other1 << ($i-1))) {
            if ($BaseShow || ($cp & (VOL_GRP1 << ($i-1)) )) {
              $HidQ = (($cp & (VOL_GRP1 << ($i-1)) && ($VCY['Status']>0))?'':$QHide);
              if ($cp & (VOL_Other1 << ($i+3))) {
                echo "\n<tr $Xtr $HidQ $Colour>" . ($M?"<td $Colour>":'') .
                      fm_textarea($Cat["OtherQ$i"] .($Cat["Q$i" . "Extra"]?"<br>" . $Cat["Q$i" . "Extra"]:''),
                      $VCY,"Other$i",$Col4,$Col3,"class=$cls $HidQ $Colour",'',"Other$i:$Catid:$YEAR");

              } else {
                echo "\n<tr $Xtr $HidQ $Colour>" . fm_text1($Cat["OtherQ$i"], $VCY,"Other$i",$Col5,"$Csp4 class=$cls $Colour",'',"Other$i:$Catid:$YEAR") .
                                  $Cat["Q$i" . "Extra"] ;
              }
            }
          }
        }
        if (!$ShowAnyway || $Cat['FormGroup']==0) $ShowGroup = 0;
      }
    }
// tabs 0=none, 1 normal, 2 lines between, 3 box before txt
// function fm_radio($Desc,&$defn,&$data,$field,$extra='',$tabs=1,$extra2='',$field2='',$colours=0,$multi=0,$extra3='',$extra4='') {

    if ($M) {
      echo "</table>";
      echo "<table border>\n";
    } else {

    }

    $Days = [];
    for ($d = $YEARDATA['FirstDay']; $d <= $YEARDATA['LastDay']; $d++) $Days[$d] = FestDate($d,'V');
    $Day_Colours = [0=>'slategray', 1=>'seagreen',2=>'darkcyan',3=>'peru'];

    echo "\n<tr><td $Csp5><h3><center>Part 3: Availability in $YEAR</center></h3></tr>";
//    function fm_select(&$Options,$data,$field,$blank=0,$selopt='',$field2='',$Max=0) {

    echo "<tr class=NoTeams" . (($NeedAD || $NeedAV)?' hidden':'') . "><td $Csp5><b>Please Select a Team first</b>";

      echo "<tr class=NeedDept " . ($NeedAD?'':' hidden') . "><td $Csp5><h3>Please give your arrival, departure day/time and any commitments you have:</h3>\n";
      echo "<tr class=NeedDept " . ($NeedAD?'':' hidden') . "><td colspan=2>" .
        fm_radio('<b>Arrival</b>',$Days,$VYear,'Arrival',"style='margin-bottom:10'",0,'',"Arrival::$YEAR",$Day_Colours) . ($M?' ':'<td>') .
          " Time: " . fm_select($ADTimes,$VYear,'ArriveTime',0,'',"ArriveTime::$YEAR");
  //      fm_text0('Time',$VYear,'ArriveTime',1,'','',"ArriveTime::$YEAR");
      echo "<tr class=NeedDept " . ($NeedAD?'':' hidden') . "><td colspan=2>" .
        fm_radio('<b>Depart</b>',$Days,$VYear,'Depart',"style='margin-bottom:10'",0,'',"Depart::$YEAR",$Day_Colours) .
        ($M?' ':'<td>') . " Time: " . fm_select($ADTimes,$VYear,'DepartTime',0,'',"DepartTime::$YEAR");

//      ($M?' ':'<td>') . fm_text0('Time',$VYear,'DepartTime',1,'','',"DepartTime::$YEAR");

    echo "<tr class=NeedAvail " . ($NeedAV?'':' hidden') . "><td $Csp5><h3>Please give the details as to when you would be available:</h3>\n";
      if (isset($DayTeams['Before'])) echo "<tr class=NeedAvail " . ($NeedAV?'':' hidden') . ">" .
        fm_text("Months before the festival",$VYear,"AvailBefore",$Col4,'','',"AvailBefore::$YEAR");
        "<div id=TeamsBefore class=Inline>" . $DayTeams['Before']; // " . (empty($DayShow['Before'])?" hidden " : "") . "
        if (isset($DayTeams['Week'])) echo "<tr class=NeedAvail " . ($NeedAV?'':' hidden') . ">" .
          fm_text("Week before the festival",$VYear,"AvailWeek",$Col4,'','',"AvailWeek::$YEAR");
//          "<div id=TeamsWeek class=Inline>" . $DayTeams['Week'];//  " . (empty($DayShow['Week'])?" hidden " : "") . "
      for ($day = $YEARDATA['FirstDay']-1; $day<=$YEARDATA['LastDay']+1; $day++) {
        $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
   //     $rs = (($day<$YEARDATA['FirstDay'] || $day> $YEARDATA['LastDay']));
        echo "\n<tr class=NeedAvail " . ($NeedAV?'':' hidden') . ">" .
             fm_text("On " . FestDate($day,'M'), $VYear,$av,$Col4,'','',"$av::$YEAR"); // . "<div id=TeamsWeek class=Inline>" . $DayTeams[$day] . "</div>";
      }// " . (empty($DayShow[$day])?" hidden " : "") . "
    echo "</div>";


    echo "<tr>" . fm_text('Do you have any commitments which mean you are unavailable for part of the festival',$VYear,'Commitments',$Col4);

    if ($M) {
      echo "</table>";
      echo "<table border>\n";
    } else {

    }

     echo "\n<tr><td colspan=5><h3><center>Part 4: Anything else for $YEAR</center></h3>";
    if (Feature('Vol_Children')) {
      echo "<tr>" . fm_text("Free Childrens tickets (" . Feature('ChildAges') . " - please give their names and ages)",
           $VYear,'Children',$Col4,'','',"Children::$YEAR");
      echo "<tr>" . fm_text("Free Youth tickets (" . Feature('YouthAges') . " - please give their names and ages)",
           $VYear,'Youth',$Col4,'','',"Youth::$YEAR");
      if (Access('SysAdmin') || (isset($VYear['Adults']) && $VYear['Adults'] > 1)) {
        echo "<tr>" . fm_text("Adults",$VYear,'Adults',$Col4,'','',"Adults::$YEAR");
      }
    }
    if (Feature('Vol_Camping')) {
      $camps = Get_Campsites('Task',1);
//var_dump($camps);exit;
      echo "<tr>$td" . fm_radio("Do you want camping?",$camps,$VYear,'CampNeed','',$Col3," $Csp4 ","CampNeed::$YEAR",
        0,0,''," onchange=CampingVolSet('CampNeed::$YEAR')");
      echo "<tr id=CampPUB>$td" . fm_radio("If so for what?" ,$CampType,$VYear,'CampType','',$Col1," $Csp4 ","CampType::$YEAR");
      echo "<tr id=CampREST>" . ($M?'<td>':'') .
                    fm_text('Please describe the footprint you need.<br>For example 1 car one tent /<br>one car one tent and a caravan etc ',
                    $VYear,'CampText',$Col4,'','',"CampText::$YEAR");
    }


    echo "\n<tr><td><h3>Anything Else /Notes:</h3>" . ($M?'<br>':'<td colspan=4>'). fm_basictextarea($VYear,'Notes',3,$Col3,'',"Notes::$YEAR");
    $Stat = empty($VYear['Status'])?0:$VYear['Status'];
    echo "\n<tr><td>Application Status:" . ($M?'<br><span ':"<td $Csp4 ") .
      ($Stat?'style=color:Green;font-weight:bold;>': 'style=color:Red;font-weight:bold;>') . $YearStatus[$Stat];
    if ($VYear['Status'] == 1 && $VYear['SubmitDate']) echo " On " . date('d/n/Y',$VYear['SubmitDate']);
    if ($VYear['Status'] == 1 && $VYear['SubmitDate'] != $VYear['LastUpdate']  && $VYear['LastUpdate'])
      echo ", Last updated on " . date('d/n/Y',$VYear['LastUpdate']);

    if (Access('Staff') && ($VYear['TicketsCollected'] ?? 0)) {
      $User = Get_User($VYear['CollectedBy']);
      echo fm_text1("Tickets Collected", $VYear,'TicketsCollected') . " from " . ($User['SN'] ?? 'Unknown') . "</span>";
    }

    if (Access('Internal')) {
      echo "</table><table border>";
      echo "<tr><td>State: " . fm_select($YearStatus,$VYear,'Status',0,'',"YStatus::$YEAR");
      echo fm_text('Messages', $VYear,'MessMap',1,'','',"MessMap::$YEAR");
      echo "<tr><td>Link:<td colspan=4>" . htmlspec(Vol_Details('INNERLINK',$Vol)) . "<br>" . Vol_Details('LINK',$Vol);
      echo "<tr>" . fm_textarea('History',$VYear,'History',4,3);
    }
  echo "</table><p>";

//  if ($M) echo "</div></div>";

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
  global $YEARDATA,$VolCats,$YEAR,$VolGroups;
  $mtch = [];

  $Num1st = $Num2nd = $Num3rd = 0;

  if (!isset($_REQUEST['kvk']) || ($_REQUEST['kvk'] != substr($Vol['AccessKey'],0,6))) {
    Error_Page("No Hacking");
  }

  if (strlen(Sanitise($Vol['SN'])) < 2) return "Please give your name";
  if ((strlen(Sanitise($Vol['Email'],40,'email')) < 6) || (strpos($Vol['Email'],'@')==false)) return "Please give your Email";
  if (strlen(Sanitise($Vol['Phone'])) < 6) return "Please give your Phone number(s)";
  if (strlen(Sanitise($Vol['Address'],100)) < 10) return "Please give your Address";
  if (!isset($Vol['AllowUnder'])) if (!isset($Vol['Over18']) || !$Vol['Over18']) return "Please confirm you are over 18";
//  if (strlen($Vol['Birthday']) < 2) return "Please give your age";

  $Clss=0;
  $VCYs = Gen_Get_Cond('VolCatYear',"Volid=" . $Vol['id'] . " AND Year=$YEAR");
  foreach ($VCYs as $VCY) if (isset($VCY['Status']) && $VCY['Status']) {
    if (($VCY['CatId'] == 0) || ($VCY['Status']==0)) { /* var_dump($VCY);*/ continue; }
    $Clss++;
    if (($VolCats[$VCY['CatId']]['Props'] & VOL_NeedDBS) && empty($Vol['DBS'])) return $VolCats[$VCY['CatId']]['Name'] . " requires DBS";
    if (!isset($Vol['AllowUnder']) && ($VolCats[$VCY['CatId']]['Props'] & VOL_Over21) && $Vol['Over18'] <2)
      return $VolCats[$VCY['CatId']]['Name'] . " requires you to be over 21";
    if (($VolCats[$VCY['CatId']]['Props'] & VOL_Money) && $Vol['Money'] != 1) return $VolCats[$VCY['CatId']]['Name'] . " requires you to be handle money";
    if ($VCY['VolOrder']) {
      if ($VCY['VolOrder'] == 1) {
        if ($Num1st == 0) {
          $Num1st = 1;
        } else {
          return "You have more than 1 First preferences.  (You may put more than one 3rd)";
        }
      } else if ($VCY['VolOrder'] == 2) {
        if ($Num2nd == 0) {
          $Num2nd = 1;
        } else {
          return "You have more than 1 Second preferences.  (You may put more than one 3rd)";
        }
      } if ($VCY['VolOrder'] == 3) {
        $Num3rd++;
      }
    }
  }
  if (Feature('VolPhoto') == 2 && empty($Vol['Photo'])) return "Please supply a photo of yourself so we can print personal volunteer badges.";

  if ($Clss == 0) return "Please select at least one team";
  if (($Clss > 1) && (($Num1st+$Num2nd+$Num3rd) < $Clss)) return "Please prioritise your teams (You may put more than one 3rd).";

  $Avail=0;
  $VY = Get_Vol_Year($Vol['id']);

  if (isset($VY["Arrival"]) || isset($VY["Depart"]) || isset($VY["ArriveTime"]) || isset($VY["DepartTime"])) $Avail++;

  if ((($VY["Arrival"]??0) > ($VY["Depart"]??0)) ||
      ((($VY["Arrival"]??0) == ($VY["Depart"]??0)) && (($VY["ArriveTime"]??0) > ($VY["DepartTime"]??0)) ))
    return "You are departing before you are arriving...";


  if (isset($VY["AvailBefore"]) && strlen($VY["AvailBefore"]) > 1 && !preg_match('/^\s*no/i',$VY["AvailBefore"],$mtch)) $Avail++;
  if (isset($VY["AvailWeek"]) && strlen($VY["AvailWeek"]) > 1 && !preg_match('/^\s*no/i',$VY["AvailWeek"],$mtch)) $Avail++;
  for ($day =$YEARDATA['FirstDay']-1; $day<=$YEARDATA['LastDay']+1; $day++) {
    $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
    if (isset($VY[$av]) && strlen($VY[$av]) > 0 && !preg_match('/^\s*no/i',$VY[$av],$mtch)) $Avail++;
  }

  if ($Avail == 0) return "Please give your availabilty";
  if (strlen(Sanitise($Vol['ContactName'])) < 2) return "Please give an emergency contact";
  if (strlen(Sanitise($Vol['ContactPhone'])) < 6) return ">Please give emergency Phone number(s)";
  if (!isset($Vol['Relation']) || !$Vol['Relation']) return "Please give your emergency contact relationship to you";



  Clean_Email($Vol['Email']);
  return 0;
}

function Vol_Emails(&$Vol,$reason='Submit') {// Allow diff message on reason=update
  global $VolCats,$YEAR,$VolGroups;
  $Leaders = [];
  Email_Volunteer($Vol,"Vol_Application_$reason",$Vol['Email']);
  $VCYs = Gen_Get_Cond('VolCatYear',"Volid=" . $Vol['id'] . " AND Year=$YEAR");
  foreach($VolCats as $Cat) {
    if ($Cat['Props2'] && VOL_OMIT_SUBMIT) continue;
    $em = strtolower($Cat['Email']);
    foreach ($VCYs as $VCY) {
      if (($VCY['CatId'] == $Cat['id']) && ($VCY['Status']>0)) {
        if (empty($em) || isset($Leaders[$em])) continue 2;
        $Leaders[$em] = 1;
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
  global $VolCats,$YEAR,$VolGroups;
  $Leaders = [];

  $VCYs = Gen_Get_Cond('VolCatYear',"Volid=" . $Vol['id'] . " AND Year=$YEAR");
  foreach($VolCats as $Cat) {
    $em = strtolower($Cat['Email']);
    if ($Cat['Props2'] && VOL_OMIT_CANCEL) continue;
    foreach ($VCYs as $VCY) {
      
      if (($VCY['CatId'] == $Cat['id']) && ($VCY['Status']>0)) {
        if (empty($em) || isset($Leaders[$em])) continue 2;
        $Leaders[$em] = 1;
        Email_Volunteer($Vol,"Vol_Staff_$reason",$Cat['Email']);
        continue 2;
      }
    }
  }
}


function CSV_Vols() {
  global $db,$VolCats,$YEARDATA,$YearStatus,$Cat_Status_Short,$VolGroups;

  $output = fopen('php://output', 'w');
  $heads = ['Name','Email','Phone(s)','Status','DBS'];
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
    $csvdat = [$Vol['SN'], $Vol['Email'], $Vol['Phone'], $YearStatus[$VY['Status']],$Vol['DBS'] ];

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


function List_Vols($AllVols='') {
  global $db,$VolCats,$YEARDATA,$YEAR,$YearStatus,$Cat_Status_Short,$YearColour,$CatStatus,$VolOrders,$EmailMsgs,$VolGroups,$ADTimes,$FDays;

  echo "<div class=floatright><form method=post>";
  $Avail = 0;
  if ($AllVols) {
    echo "<button type=button class= FullD onclick=\"($('.FullD').toggle())\">Curent Aplications</button>" .
         "<button type=button class= FullD hidden onclick=\"($('.FullD').toggle())\">All Applications</button>";
    $fdh = "class=FullD ";
    $hide = 'hidden';
  } else {
    echo "<button class='floatright' type=submit formaction='Volunteers?A=ListAll'>All Applications</button>";
    $hide = $fdh = '';
  }
  echo "</form></div>";
  echo "<div class=floatright><b>Availability:</b><button type=button class='AvailD AvSelect' id=Avail0 onclick=AvailDisp(0)>Hide</button>" .
    "<button type=button class=AvailD id=Avail1 onclick=AvailDisp(1)>Arrive/Depart</button>" .
    "<button type=button class=AvailD id=Avail2 onclick=AvailDisp(2)>Extended</button>" .
    "<button type=button class=AvailD id=Avail3 onclick=AvailDisp(3)>All</button></div>";


//var_dump($VolCats);
    // create a file pointer connected to the output stream

  $ShowCats = ['All'];
  $ShowCols = ['white'];
//  $AllApps = ($_REQUEST['ALL'] ?? 0);

  foreach ($VolCats as &$Cat) {
    if (($Cat['Props'] & VOL_USE) && (($Cat['Props'] & VOL_NoList) ==0) && ($Cat['Props'] & VOL_GROUPQS)==0 ) {
      $ShowCats[$Cat['id']] = $Cat['Name'];
      $ShowCols[$Cat['id']] = $Cat['Colour'];
    }
  }

  $Ch = $Ad = $AdC = $Yth = $YthC = 0;

  foreach ($VolCats as &$Cat) $Cat['Total'] = 0;

  $VolMgr = Access('Committee','Volunteers');
  echo "Click on name for full info<p>";
  echo "A <b>?</b> for a team, means they have volunteered for this team, but not yet been accepted.<p>";
  echo "Where it says EXPAND under availability, means there is a longer entry " .
       "- click on the persons name or the expand button to see more info on their availabilty<p>";

  if ($VolMgr ) echo "To Accept for just one team click on the <button class=AcceptButton >A</button> in the list.<p>" .
                     "To reject an application or accept for many teams, first click on their name.<p>";

  $Show['ThingShow'] = 0;
  echo "<div class=floatright ><b>" . fm_radio("Show",$ShowCats,$Show,'ThingShow',' onchange=VolListFilter()',1,'','',$ShowCols)  . "</b></div>";

  $coln = 0;
// var_dump($VolCats);
  echo "<form method=post>";
  echo "<div class=Scrolltable><table id=indextable border class=altcolours>\n";
  echo "<thead><tr>";


  echo "<th><a href=javascript:SortTable(" . $coln . ",'T')>Name</a> (<a href=javascript:SortTable(" . $coln++ . ",'L')>Lastname</a>)\n";
  if (Access('SysAdmin')) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Email</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Phone</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Status</a>\n";
  echo "<th $fdh><a href=javascript:SortTable(" . $coln++ . ",'T')>Last Year</a>\n";
  echo "<th $fdh><a href=javascript:SortTable(" . $coln++ . ",'N')>Year</a>\n";
  foreach ($VolCats as $i=>&$Cat) {
// var_dump($Cat);
    if (($Cat['Props'] & VOL_USE) && (($Cat['Props'] & VOL_NoList) ==0) && ($Cat['Props'] & VOL_GROUPQS)==0 ) {
      echo "<th class=Cat$i><a href=javascript:SortTable(" . $coln++ . ",'T')>" . ($Cat['ShortName']?$Cat['ShortName']:$Cat['Name']) . "</a>\n";
    }
  }
  echo "<th class=AvailD1 hidden><a href=javascript:SortTable(" . $coln++ . ",'T')>Ariv</a>\n";
  echo "<th class=AvailD1 hidden><a href=javascript:SortTable(" . $coln++ . ",'T')>Time</a>\n";
  echo "<th class=AvailD1 hidden><a href=javascript:SortTable(" . $coln++ . ",'T')>Dept</a>\n";
  echo "<th class=AvailD1 hidden><a href=javascript:SortTable(" . $coln++ . ",'T')>Time</a>\n";

  echo "<th class=AvailD2 hidden><a href=javascript:SortTable(" . $coln++ . ",'T')>Months Before</a>\n";
  echo "<th class=AvailD2 hidden><a href=javascript:SortTable(" . $coln++ . ",'T')>Week Before</a>\n";
  for ($day = $YEARDATA['FirstDay']-1; $day<= $YEARDATA['LastDay']+1; $day++) {
    echo "<th class=AvailD2 hidden><a href=javascript:SortTable(" . $coln++ . ",'T')>" . FestDate($day,'s') . "</a>\n";
  }
  echo "<th class=AvailD3 hidden><a href=javascript:SortTable(" . $coln++ . ",'T')>Commit?</a>\n";

  if (Access('SysAdmin')) {
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Actions</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Msgs</a>\n";
  }
  echo "</thead><tbody>";

  $res=$db->query("SELECT * FROM Volunteers WHERE Status=0 ORDER BY SN");

  if ($res) while ($Vol = $res->fetch_assoc()) {
    $id = $Vol['id'];
    if (empty($id) || empty($Vol['SN']) || empty($Vol['Email']) ) continue;

    $VY = Get_Vol_Year($id);
    if (!$AllVols && empty($VY['id'])) continue;
    $VLY = Get_Vol_Year($id,$YEAR-1);

    $Accepted = 0;
    $Form = 0;
    $str = '';
    $VClass = 'Volunteer ';
    $Stew = 0;
    $CatTot = 0;

    if (isset($VY['id']) && $VY['id']>0) {
      $year = $YEAR;
    } else {
      for ($year=$YEAR-1; $year>($YEAR-6); $year--) {
        $VY = Get_Vol_Year($id,$year);
        if (!empty($VY['id'])) break;
      }
    }

    foreach ($VolCats as $catid=>&$Cat) {
      if (($Cat['Props'] & VOL_USE) && (($Cat['Props'] & VOL_NoList) ==0) && ($Cat['Props'] & VOL_GROUPQS)==0 ) {
        $VCY = Get_Vol_Cat_Year($Vol['id'],$Cat['id'],$year);
        $str .= "<td class=Cat$catid id='Wanted$id" . "CAT$catid'>" . $Cat_Status_Short[$VCY['Status']];
        if (($Cat['Name'] == 'Stewarding') && $VCY['Status']) $Stew = 1;
        if ($VCY['VolOrder'] ?? 0) $str .= " " . $VolOrders[$VCY['VolOrder']][0];
        if (($VY['Status'] == 1) && ($CatStatus[$VCY['Status']] == 'Applied') && $VolMgr) {
          $Form = 1;

          $str .= " <button type=button id='Accept:$id:$catid' class='AcceptButton Accept$id' onclick=AcceptTeam($id,$catid)>A</button>";
        }
        if ( $CatStatus[$VCY['Status']] == 'Confirmed') {

          $Cat['Total']++;
          $Accepted++;
        }
        if ($VCY['Status']) $CatTot++;
        if ($Cat_Status_Short[$VCY['Status']]) $VClass .= " VolCat" . $Cat['id'];
      }
    }

//var_dump($VY);
//  $Ch = $Ad = $AdC = $Yth = $YthC = 0;

    if ($Accepted) {
      if (!empty($VY['Children'])) $Ch +=  NumbersOf($VY['Children']);
      if (!empty($VY['CampNeed'])) {
        $AdC++;
        if (!empty($VY['Youth'])) $YthC += NumbersOf($VY['Youth']);
      } else {
        $Ad++;
        if (!empty($VY['Youth'])) $Yth += NumbersOf($VY['Youth']);
      }
    }

//    var_dump($VY);
    $link = "<a href=Volunteers?A=" . ($VolMgr? "Show":"View") . "&id=$id>";
    echo "<tr class='altcolours $VClass " . ((($VY['Year'] != $YEAR) || empty($VY['id']) || ($VY['Status'] == 2) ||
          ($VY['Status'] == 4) || ($CatTot==0) )?" FullD' " : "'" ) . ">";

    echo "<td>$link" . $Vol['SN'] . "</a>";
    if (Access('SysAdmin')) echo "<td>$id";
    echo "<td class=smalltext style='max-width:200;overflow-x:auto;'>" . $Vol['Email'];
    echo "<td>" . $Vol['Phone'];

    if ($Accepted && ($YearStatus[$VY['Status']] != 'Confirmed')) {
      $VY['Status'] = 3;
      Put_Vol_Year($VY);
    }

    echo "<td class=smalltext id=YearStatus$id>" .
      (isset($VY['id']) && ($VY['id']>0) && ($VY['Year'] == $YEAR)?("<span style='background:" . $YearColour[$VY['Status']] . ";'>" .
      $YearStatus[$VY['Status']] . "</span>"):'');
      if (isset($VY['id']) && ($VY['Year'] == $YEAR) && ($VY['id']>0) && ($VY['Status'] == 1) && ($VY['SubmitDate']??0))
        echo "<br>" . date('d/n/Y',$VY['SubmitDate']);

    echo "<td class='smalltext FullD'>" . ((isset($VLY['id']) && $VLY['id']>0)?("<span style='background:" . $YearColour[$VLY['Status']] . ";'>" .
      $YearStatus[$VLY['Status']] . "</span>"):'');
      if (isset($VLY['id']) && $VLY['id']>0 && $VLY['Status'] == 1 && ($VY['SubmitDate']??0)) echo "<br>" . date('d/n/Y',$VLY['SubmitDate']);

    echo "<td $fdh>$year";
    echo $str;

    // Availability

    echo "<td class=AvailD1 hidden>" . (($VY['Arrival']??0)<-10?'':$FDays[$VY['Arrival']??0]) . "<td class=AvailD1 hidden>" .
        $ADTimes[$VY['ArriveTime']??0];
    echo "<td class=AvailD1 hidden>" . (($VY['Depart']??0)<-10?'':$FDays[$VY['Depart']??0]) . "<td class=AvailD1 hidden>" .
        $ADTimes[$VY['DepartTime']??0];

    echo "<td class=AvailD2 hidden>" . (isset($VY['AvailBefore'])? ((strlen($VY['AvailBefore'])<12)? $VY['AvailBefore'] : ($link . "Expand</a>")):"");
    echo "<td class=AvailD2 hidden>" . (isset($VY['AvailWeek'])? ((strlen($VY['AvailWeek'])<12)? $VY['AvailWeek'] : ($link . "Expand</a>")):"");
    $HasSetAvail = 0;
    for ($day = $YEARDATA['FirstDay']-1; $day<= $YEARDATA['LastDay']+1; $day++) {
      $av = "Avail" . ($day <0 ? "_" . (-$day) : $day);
      echo "<td class=AvailD2 hidden>";
      if (isset($VY[$av])) {
        echo ((strlen($VY[$av])<12)? $VY[$av] : ($link . "Expand</a>")) . "\n";
        $HasSetAvail = 1;
      }
    }

    echo "<td class=AvailD3 hidden>" . (($VY['Commitments']??0)?($link . "Yes</a>"):'');

    if (Access('SysAdmin')) {
      echo "<td>";
      if ($Form) {
        echo "<b><a href=Volunteers?ACTION=Accept&id=$id class=Accept$id'>Accept All</a></b>\n";
      }
        $Mmap = $VY['MessMap'] ?? '';
        if (($year == $YEAR) && ($VY['Status'] == 0) && (!strstr($Mmap,'U') && $HasSetAvail)) {
          $Msg = $EmailMsgs['U'];
          echo  " <button type=button id=VolSendEmailU$id class=ProfButton onclick=ProformaVolSend('Vol_$Msg',$id,'U')>$Msg</button>";
        }

        $Agn = Feature('VolAgain');
        if ($Agn) {
          [$ALet,$AMsg] = explode(',',($Agn??''));
          if ($ALet && (!strstr($Mmap,$ALet)) && (($year != $YEAR)  ||
                        (($year == $YEAR) && ($VY['Status'] == 0) && (!strstr($Mmap,'U') && ($HasSetAvail == 0))))) {

              echo  " <button type=button id=VolSendEmailN$id class=ProfButton onclick=ProformaVolSend('$AMsg',$id,'$ALet')>$AMsg</button>";
          }
        }

        $Msg = $EmailMsgs['E'];
        if ($Msg && ($VY['Status'] == 0) && !strstr($Mmap,'E') && ($HasSetAvail == 0)) {
          echo  " <button type=button id=VolSendEmailE$id class=ProfButton onclick=ProformaVolSend('Vol_$Msg',$id,'E')>$Msg</button>";
        }
        $Msg = $EmailMsgs['F'];
        if ($Msg && ($VY['Status'] == 3) && !strstr($Mmap,'F')) {
          echo  " <button type=button id=VolSendEmailF$id class=ProfButton onclick=ProformaVolSend('Vol_$Msg',$id,'F')>$Msg</button>";
        }
        $Msg = $EmailMsgs['G'];
        if ($Msg && ($VY['Status'] == 3) && !strstr($Mmap,'G')) {
          echo  " <button type=button id=VolSendEmailF$id class=ProfButton onclick=ProformaVolSend('Vol_$Msg',$id,'G')>$Msg</button>";
        }
        echo "<td id=MessMap$id>" . ($VY['MessMap'] ?? '');
      if ($VY['Status'] == 0) echo "<td><b><a href=Volunteers?ACTION=Del&id=$id>Del</a></b>\n";
    }
  }

  echo "<tr><td>";
  if (Access('SysAdmin')) echo "<td>";
  echo "<td>Ad:$Ad Yth:$Yth Ch: $Ch<td>Ad+C:$AdC Yth+C:$YthC<td class=smalltext>Total confirmed<td $fdh><td $fdh>";
    foreach ($VolCats as $i=>&$Cat) if ($Cat['Props'] & VOL_USE) echo "<td class=Cat$i>" . $Cat['Total'];


    if (Access('SysAdmin')) {
      echo "<tr><td class=NotSide>Debug<td colspan=20 class=NotSide><textarea id=Debug></textarea>";
    }

  echo "</tbody></table></div>\n";

  echo "<h2><a href=Volunteers?A=New>Add a Volunteer</a></h2>";
  echo "<h2><a href=Volunteers?A=CSV&F=CSV>Volunteer list as a CSV</a></h2>";
  
  dotail();
}

function List_Team($Team) {
  global $YEAR,$VolCats,$CatStatus,$yesno,$VolGroups;
  $Cat = $VolCats[$Team];
  $CatP = $Cat['Props'];
  $SplitWhen = explode(',', $Cat['Listofwhen']);

  dostaffhead("Details for:" . $Cat['Name']);

  echo "<h2><a href=Volunteers?ACTION=TeamListCSV&Cat=$Team&F=CSV>Output as CSV</a></h2>";

  $VolMgr = Access('Committee','Volunteers');

  $coln = 0;
// var_dump($VolCats);
  echo "<form method=post>";
  echo "<div class=Scrolltable><table id=indextable border class='altcolours TinyText'>\n";
  echo "<thead><tr>";

  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
//  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Email</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Phone</a>\n";
//  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Status</a>\n";
  if (Feature('VolMoney')) if ($CatP & VOL_Money) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Money</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Mobility</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Notes</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Child</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Youth</a>\n";

  if ($CatP & VOL_Likes) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Likes</a>\n";
  if ($CatP & VOL_Dislikes) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Dislikes</a>\n";
  if ($CatP & VOL_Exp) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Experience</a>\n";
  if ($CatP & VOL_Other1) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>" . $Cat['OtherQ1'] . "</a>\n";
  if ($CatP & VOL_Other2) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>" . $Cat['OtherQ2'] . "</a>\n";
  if ($CatP & VOL_Other3) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>" . $Cat['OtherQ3'] . "</a>\n";
  if ($CatP & VOL_Other4) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>" . $Cat['OtherQ4'] . "</a>\n";

  foreach ($SplitWhen as $W) {
    if ($W == 'Before') {
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Months Before</a>\n";
    } else if ($W == 'Week') {
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Week Before</a>\n";
    } else {
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>" . FestDate($W,'s') . "</a>\n";
    }
  }
  echo "</thead><tbody>";

  $Vols = Gen_Get_Cond('Volunteers',"Status=0 ORDER BY SN");
  foreach ($Vols as $Vol) {
    $vid = $Vol['id'];
    $VY = Get_Vol_Year($vid);
    $VCY = Get_Vol_Cat_Year($vid,$Team,$YEAR);
    if ( $CatStatus[$VCY['Status']] != 'Confirmed') continue;

    $link = "<a href=Volunteers?A=" . ($VolMgr? "Show":"View") . "&id=$vid>";
    echo "<tr><td>$vid<td>$link" . $Vol['SN'] . "</a>";
//    echo "<td>" . $Vol['Email'];
    echo "<td>" . $Vol['Phone'];

    if (Feature('VolMoney')) if ($CatP & VOL_Money)  echo "<td>" . $yesno[$Vol['Money']];
    echo "<td>" . $Vol['Disabilities'];
    echo "<td>" . (empty($VY['Notes'])?'':$link . "Yes</a>");
    echo "<td>" . ($VY['Children']??0);
    echo "<td>" . ($VY['Youth']??0);

    if ($CatP & VOL_Likes) echo "<td>" . $VCY['Likes'] ;
    if ($CatP & VOL_Dislikes) echo "<td>" . $VCY['Dislikes'] ;
    if ($CatP & VOL_Exp) echo "<td>" . $VCY['Experience'] ;
    if ($CatP & VOL_Other1) echo "<td>" . $VCY['Other1'] ;
    if ($CatP & VOL_Other2) echo "<td>" . $VCY['Other2'] ;
    if ($CatP & VOL_Other3) echo "<td>" . $VCY['Other3'] ;
    if ($CatP & VOL_Other4) echo "<td>" . $VCY['Other4'] ;

    foreach ($SplitWhen as $W) {
      if ($W == 'Before') {
        echo "<td>" . $VY['AvailBefore'];
      } else if ($W == 'Week') {
        echo "<td>" . $VY['AvailWeek'];
      } else {
        $av = "Avail" . ($W <0 ? "_" . (-$W) : $W);
        echo "<td>" . (isset($VY[$av])?$VY[$av]:'');
      }
    }
    echo "\n";
  }
  echo "</table></div>";
  dotail();
}

function List_Team_CSV($Team) {
  global $YEAR,$VolCats,$CatStatus,$yesno,$VolGroups;

  $Cat = $VolCats[$Team];
  $CatP = $Cat['Props'];
  $SplitWhen = explode(',', $Cat['Listofwhen']);

  $output = fopen('php://output', 'w');
  $heads = ['Id','Name','Phone'];
  if ($CatP & VOL_Money) $heads[]= 'Money';
  $heads[]= 'Mobility';
  $heads[]= 'Notes';
  $heads[]= 'Child';
  $heads[]= 'Youth';

  if ($CatP & VOL_Likes) $heads[]= 'Likes';
  if ($CatP & VOL_Dislikes) $heads[]= 'Dislikes';
  if ($CatP & VOL_Exp) $heads[]= 'Experience';
  if ($CatP & VOL_Other1) $heads[]= $Cat['OtherQ1'];
  if ($CatP & VOL_Other2) $heads[]= $Cat['OtherQ2'];
  if ($CatP & VOL_Other3) $heads[]= $Cat['OtherQ3'];
  if ($CatP & VOL_Other4) $heads[]= $Cat['OtherQ4'];

  foreach ($SplitWhen as $W) {
    if ($W == 'Before') {
      $heads[]= 'Months Before';
    } else if ($W == 'Week') {
      $heads[]= 'Week Before';
    } else {
      $heads[]= FestDate($W,'s');
    }
  }

  fputcsv($output, $heads,',','"');

  $Vols = Gen_Get_Cond('Volunteers',"Status=0 ORDER BY SN");
  foreach ($Vols as $Vol) {
    $vid = $Vol['id'];
    $VY = Get_Vol_Year($vid);
    $VCY = Get_Vol_Cat_Year($vid,$Team,$YEAR);
    if ( $CatStatus[$VCY['Status']] != 'Confirmed') continue;

    $csv = [$vid,$Vol['SN'],$Vol['Phone']];

    if ($CatP & VOL_Money) $csv[] = $yesno[$Vol['Money']];
    $csv[] =  $Vol['Disabilities'];
    $csv[] =  (empty($VY['Notes'])?'':"Yes");
    $csv[] = isset($VY['Children'])?$VY['Children']:'';
    $csv[] = isset($VY['Youth'])?$VY['Youth']:'';

    if ($CatP & VOL_Likes) $csv[] =  $VCY['Likes'] ;
    if ($CatP & VOL_Dislikes) $csv[] = $VCY['Dislikes'] ;
    if ($CatP & VOL_Exp) $csv[] = $VCY['Experience'] ;
    if ($CatP & VOL_Other1) $csv[] = $VCY['Other1'] ;
    if ($CatP & VOL_Other2) $csv[] = $VCY['Other2'] ;
    if ($CatP & VOL_Other3) $csv[] = $VCY['Other3'] ;
    if ($CatP & VOL_Other4) $csv[] = $VCY['Other4'] ;

    foreach ($SplitWhen as $W) {
      if ($W == 'Before') {
        $csv[] = $VY['AvailBefore'];
      } else if ($W == 'Week') {
        $csv[] = $VY['AvailWeek'];
      } else {
        $av = "Avail" . ($W <0 ? "_" . (-$W) : $W);
        $csv[] = (isset($VY[$av])?$VY[$av]:'');
      }
    }
    fputcsv($output,$csv);
  }
  fclose($output);
  exit;
}

function Email_Form_Only($Vol,$mess='',$xtra='') {
  echo "<h2>Stage 1 - Who are you?</h2>";
  if ($mess) echo "<h2 class=Err>$mess</h2>";
  echo "<form method=post>";
  echo "<div class=tablecont><table border>";
  if ($xtra) {
    echo fm_hidden('Second',$xtra);
    echo fm_hidden('Address',$Vol['Address']);
  }
  if (isset($_REQUEST['C'])) echo fm_hidden('C',$_REQUEST['C']);
  echo "<tr>" . fm_text('Name',$Vol,'SN',2);
  echo "<tr>" . fm_text('Email',$Vol,'Email',2);
  echo fm_hidden('A','NewStage2');
  echo "</table></div><p><input type=Submit>\n";
  dotail();
}

function Check_Unique() { // Is email Email already registered - if so send new email back with link to update
  $adr = Sanitise($_REQUEST['Email'],40,'email');
  if (!filter_var($adr,FILTER_VALIDATE_EMAIL)) Email_Form_Only($_REQUEST,"Please give an email address");
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

function TicketList($Cat) {
  global $YEAR,$CatStatus;
  include_once('DocLib.php');

  $VolMgr = Access('Committee','Volunteers');

  $Users = Get_AllUsers(2);

  echo "All you should need to do is click the Collect button to the right of each volunteer.<p>" .
       "If you click one in error you have 15 seconds to click the Oops button to revert it.<p>" .
       "In the event of problems call Richard.<p>\n";

  $coln = 0;
// var_dump($VolCats);
  echo "<form method=post>";
  echo "<div class=Scrolltable><table id=indextable border class=altcolours>\n";
  echo "<thead><tr>";

  if (Access('SysAdmin')) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Tickets</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Collect</a>\n";

  echo "</thead><tbody>";

  $Vols = Gen_Get_Cond('Volunteers',"Status=0 ORDER BY SN");
  foreach ($Vols as $Vol) {
    $vid = $Vol['id'];
    $VY = Get_Vol_Year($vid);
    if ( $CatStatus[$VY['Status']] != 'Confirmed') continue;
    if ($Cat > 0) {
      $VCY = Get_Vol_Cat_Year($vid,$Cat,$YEAR);
      if ( $CatStatus[$VCY['Status']] != 'Confirmed') continue;
    }

    $link = "<a href=Volunteers?A=" . ($VolMgr? "Show":"View") . "&id=$vid>";
    echo "<tr>";
    if (Access('SysAdmin')) echo "<td>$vid";
    echo "<td>$link" . $Vol['SN'] . "</a>";
    $Camp = ($VY['CampNeed'] != 0);
    echo "<td>" . $VY['Adults'] . Plural($VY['Adults']," Adults",' Adult',' Adults') . ($Camp?'+Camp ':'');
    if ($VY['Youth']) {
      $Yn = NumbersOf($VY['Youth']);
      if ($Yn) echo ", $Yn" . Plural($Yn,'',' Youth',' Youths') . ($Camp?'+Camp ':'');
    }
    if ($VY['Children']) {
      $Yn = NumbersOf($VY['Children']);
      echo ", $Yn" . Plural($Yn,'',' Child',' Children');
    }
    echo "<td id=Collect$vid>" . ($VY['TicketsCollected']
        ? "Collected " . date("D M j G:i:s",$VY['TicketsCollected']) . " from " . ($Users[$VY['CollectedBy']]['SN'] ?? 'Unknown')
        : "<button type=button class=FakeButton onclick='VTicketsCollected($vid)'>Collect</button>");

    echo "\n";
  }
  echo "</table></div>";
  dotail();

}


function SendCatsToBrowser() {
  global $VolCats;
  echo fm_hidden('VolCatsRaw',base64_encode(json_encode($VolCats)));
}

function Send_Accepts($Vol) {
  global $VolCats,$VolGroups;

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
  global $YEAR,$VolCats,$M,$YearColour,$YearStatus,$USER;

  if ($csv == 0) {
    dostaffhead("Steward / Volunteer Application", ["/js/Volunteers.js","js/dropzone.js","css/dropzone.css",'/js/InviteThings.js' ]);
    SendCatsToBrowser();
  }
//var_dump($Action);
// var_dump($_REQUEST);

  $M = 'VolForm'; //(isset($_REQUEST['M'])?'VolFormM':'VolForm');

  switch (Sanitise($Action,60)) {
  case 'New': // New Volunteer
  default:
    $Vol = ['id'=>-1, 'Year'=>$YEAR,'KeepMe'=>1,'Cat'=>($_REQUEST['C']??0)];
    Email_Form_Only($Vol);
    break;

  case 'NS2': // Old import code - no need to keep up to date
    $mindata = json_decode(base64_decode($_REQUEST['data']),true);
    $Name = $mindata[0] . " " . $mindata[1];
    $Vol = ['Year'=>$YEAR, 'SN'=>$Name, 'Email'=>$mindata[2], 'KeepMe'=>1, 'AccessKey' => rand_string(40) ];
//var_dump($Vol);exit;
    $Volid = Gen_Put('Volunteers',$Vol);
    $M($Vol);
    break;

  case 'NewStage2':
    if (isset($_REQUEST['Second'])) {
      $Os = ['Email'=>$_REQUEST['Email'], 'id'=> -1 ];
      $OVs = OtherVols($Os);
      $Name = Sanitise($_REQUEST['SN']);
      if ($OVs) foreach($OVs as $OV) if ($OV['SN'] == $Name) {
        echo "<span class=Err>$Name is already in the system</span><br>";
        $Vol = $OV;
        $M($Vol);
      }
      $Vol = ['Year'=>$YEAR, 'SN'=>$Name, 'Email'=>$_REQUEST['Email'], 'KeepMe'=>1, 'AccessKey' => rand_string(40), 'Address'=>$_REQUEST['Address'],
              'Cat'=>($_REQUEST['C']??0)];

      $Volid = Gen_Put('Volunteers',$Vol);
      $M($Vol);
    }
    Check_Unique(); // Deliberate drop through

  case 'Form': // New stage 2
    $Vol = ['Year'=>$YEAR, 'SN'=>$_REQUEST['SN'], 'Email'=>$_REQUEST['Email'], 'KeepMe'=>1, 'AccessKey' => rand_string(40), 'Cat'=>($_REQUEST['C']??0)];
    $Volid = Gen_Put('Volunteers',$Vol);
    $M($Vol);
    break;

  case 'List': // List Volunteers
    List_Vols();
    break;

  case 'ListAll': // List Volunteers
    List_Vols('All');
    break;

  case 'CSV': // List Volunteers as CSV
    CSV_Vols();
    break;

  case 'Create': // Volunteer hass clicked 'Submit', store and email staff
  case 'Submit':
  case 'Update': // Volunteer/Staff has updated entry - if Volunteer, remail relevant staff
  case 'Submit/Update Application':
  case 'SubmitUpdate Application':
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
    $VY['History'] .= "$Action by " . (isset($USER['Login'])?$USER['Login']:'Volunteer') . " on " . date('d/n/Y') . "\n";

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
      $VY['History'] .= "$Action by " . (isset($USER['Login'])?$USER['Login']:'Volunteer') . " on " . date('d/n/Y') . "\n";

      Put_Vol_Year($VY);

      $Vol = Get_Volunteer($id = $_REQUEST['id']);
      $VY = Get_Vol_Year($Vol['id'],$YEAR);
      if (!empty($VY['id'])) {
        Vol_Staff_Emails($Vol);
//        db_delete('VolYear',$VY['id']);
      }
    }

    echo "<h2>Thankyou for letting us know</h2>";
    if (!Access('Staff')) dotail();
    break;

  case 'Delete': // Delete Volunteer
  case 'Remove me from the festival records':
    $Vol = Get_Volunteer($id = $_REQUEST['id']);
    $Vol['Status']=1;
//    Put_Volunteer($Vol);
    $VY = Get_Vol_Year($Vol['id'],$YEAR);
    if (!empty($VY['id'])) Vol_Staff_Emails($Vol);

//??    if ($Vol['Year'] == $YEAR) Vol_Staff_Emails($Vol);

    echo "<h2>Thankyou for Volunteering in the past, you are no longer recorded</h2>";
    db_delete('Volunteers',$id);
    if ($Action != 'Delete') dotail();
    break;

  case 'Accept': // All
    $Vol = Get_Volunteer($id = $_REQUEST['id']);

    $Accepted = 0;
//    $AccList = [];
    $VY = Get_Vol_Year($Vol['id'],$YEAR);
    foreach($VolCats as $Cat) {
      $VCY = Get_Vol_Cat_Year($Vol['id'],$Cat['id'],$YEAR);
      if ($VCY['Status'] == 1) {
        $VCY['Status'] = 3; // Accepted
        Put_Vol_Cat_Year($VCY);
//        $AccList[] = $Cat['id'];
        $Accepted++;
      }
    }
    $VY['History'] .= "$Action by " . (isset($USER['Login'])?$USER['Login']:'Volunteer') . " on " . date('d/n/Y') . "\n";

    if ($Accepted) {
      $VY['Status'] = 3; // Accepted at least once
      Put_Vol_Year($VY);
      Send_Accepts($Vol);
    }
    List_Vols();
    break;

  case 'Accept1':
    $Vol = Get_Volunteer($id = $_REQUEST['id']);
    $Catid = $_REQUEST['Catid'];
    $VY = Get_Vol_Year($Vol['id'],$YEAR);
    $VCY = Get_Vol_Cat_Year($Vol['id'],$Catid,$YEAR);
    $VCY['Status'] = 3; // Accepted
    Put_Vol_Cat_Year($VCY);
    $VY['Status'] = 3; // Accepted at least once
    $VY['History'] .= "$Action by " . (isset($USER['Login'])?$USER['Login']:'Volunteer') . " on " . date('d/n/Y') . "\n";

    Put_Vol_Year($VY);
    Send_Accepts($Vol);
    echo "<span style='background:" . $YearColour[$VY['Status']] . ";'>" . $YearStatus[$VY['Status']] . "</span>" .
         "<br>" . date('d/n/Y',$VY['SubmitDate']);
    return;

  case 'Send Updates':
    $Vol = Get_Volunteer($id = $_REQUEST['id']);
    Send_Accepts($Vol);
    List_Vols();
    break;

  case 'Register another volunteer with the same email address':
    $OldVol = Get_Volunteer($id = $_REQUEST['id']);
    $Vol = ['Email'=>$OldVol['Email'], 'Address'=>$OldVol['Address'],'id'=>-1, 'Year'=>$YEAR,'KeepMe'=>1,'Over18'=>0,];
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

  case 'TeamList':
    $Team=$_REQUEST['Cat'];
    if (empty($Team)) {
      echo "No Team requested";
    } else {
      List_Team($Team);
    }
    break;

  case 'TeamListCSV':
    $Team=$_REQUEST['Cat'];
    List_Team_CSV($Team);
    break;

  case 'CompAdd':
    CompAdd($_REQUEST['id']?? -1);
    break;

  case 'Comp Create':
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

  case 'Validate':
    $Vol = Get_Volunteer($id = $_REQUEST['id']);
    break;

  case 'CompList':
    CompList();
    break;

  case 'TicketList':
    TicketList($_REQUEST['Cat'] ?? -1);
    break;
    
  case 'Del':
    $Vol = Get_Volunteer($id = $_REQUEST['id']);
    $Vol['Status'] = -1;
    Put_Volunteer($Vol);
    List_Vols();
    break;

  case 'Email1':


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
