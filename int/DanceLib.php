<?php
// Common Dance Library

global $Noise_Levels, $Noise_Colours, $Coming_States, $Coming_Colours, $Coming_idx, $Coming_Type, $Invite_States, $Invite_Type, $Dance_Comp;
global $Surfaces, $Surface_Colours, $Side_Statuses, $Share_Spots, $Share_Type, $Proforma_Colours, $TickBoxes;
global $PayTypes, $Dance_Comp_Colours, $Dance_TimeFeilds, $OlapTypes, $OlapDays, $OlapCats ;

$Noise_Levels = array("Middling","Quiet","Noisy");
$Noise_Colours = ['lightgreen','yellow','Orange'];
$Coming_States = array('','Received','Coming','Not coming','Possibly','Not coming, please ask next year');
$Coming_Colours = ['white','Yellow','Lime','salmon','lightblue','Orange'];
$Coming_idx = array('','R','Y','N','P','NY');
$Coming_Type = array_flip($Coming_idx);
$Invite_States = array('','Yes','YES!','No','Maybe');
$Invite_Type = array_flip($Invite_States);
$Dance_Comp = ['Don\'t Know','Yes','No'];
$Dance_Comp_Colours = ['white','lime','salmon'];
$Surfaces = ['','Tarmac','Flagstones','Grass','Stage','Brick','Wood','Carpet','Astroturf'];// Last 3 Sysadmin only
$Surface_Colours = ['','grey','Khaki','lightgreen','Peru','salmon','Peru','Teal','lime'];
$Side_Statuses = array("Alive","Folded",'Banned');
$Share_Spots = array('Prefered','Always','Never','Sometimes');
$Share_Type = array_flip($Share_Spots);
$PayTypes = ['BACS','Cheque'];

$Dance_TimeFeilds = array('SatArrive','SatDepart','SunArrive','SunDepart');
$OlapTypes = array('Dancer','Musician','Avoid','Also is','Part of','Includes');
$OlapDays = array('All','Sat Only','Sun Only','None');
$OlapCats = array('Side','Act','Comedy','Family','Other');
$Proforma_Colours = ['Decide'=>'DarkOrange','Details'=>'Magenta','Program'=>'Yellow','ProgChk'=>'lightsalmon','NewProg'=>'yellow',
  'FinalInfo'=>'LawnGreen', 'FinalInfo2'=>'MediumSeaGreen', 'Invite'=>'Beige','Remind'=>'khaki', 'Change'=>'DarkOrange', 'Reinvite'=>'Beige',
  'Cancel'=>'lightgrey', 'SpecInvite'=>'Beige','SpecPoss'=>'Khaki','MorrisTickets' =>'Beige'];
$TickBoxes = [['Seen Programme','Invited','YHAS','Program:','D',2]]; 
$PerfListStates = ['Not Open','Open'];


function Proforma_Background($name) {
  global $Proforma_Colours;
  if (isset($Proforma_Colours[$name])) return " Style=Background:" . $Proforma_Colours[$name] . " ";
  return "";
}

function Sides_Name_List() {
  global $db;
  $Sides = array();
  $res = $db->query("SELECT SideId, SN FROM Sides WHERE SideStatus=0 ORDER BY SN");
  if ($res) while ($row = $res->fetch_assoc()) $Sides[$row['SideId']] = $row['SN'];
  return $Sides;
}

function Sides_All($Except=-1,$All=1,$Include1=0,$Include2=0,$Include3=0,$Include4=0) {
  global $db;
  static $Sides_All = array();
  static $Sides_Loaded = 0;
  if ($All) {
    if ($Sides_Loaded == $Except) return $Sides_All;
    $Sides_All = array();
    $slist = $db->query("SELECT SideId, SN FROM Sides WHERE SideStatus=0 AND IsASide=1 ORDER BY SN");
  } else {
    $Blist = Select_Come(1);
    if ($Except) unset($Blist[$Except]);
    if ($Include1 || $Include2 || $Include3 || $Include4) {
      $LongList = Sides_All();
      if ($Include1) $Blist[$Include1] = $LongList[$Include1];
      if ($Include2) $Blist[$Include2] = $LongList[$Include2];
      if ($Include3) $Blist[$Include3] = $LongList[$Include3];
      if ($Include4) $Blist[$Include4] = $LongList[$Include4];
    }
    return $Blist;
  }
  if ($slist) while ($row = $slist->fetch_assoc()) {
    if ($row['SideId'] != $Except) $Sides_All[$row['SideId']] = $row['SN'];
  }
  $Sides_Loaded = $Except;
  return $Sides_All;
}

function Select_Come($type=0,$extra='') {
  global $db,$YEAR,$Coming_Type;
  static $Come_Loaded = 0;
  static $Coming = array('');
  if ($Come_Loaded) return $Coming;
  $qry = "SELECT s.SideId, s.SN, s.Type FROM Sides s, SideYear y WHERE s.SideId=y.SideId AND y.Year='$YEAR' AND y.Coming=" . 
        $Coming_Type['Y'] . " AND s.IsASide=1 " . $extra . " ORDER BY s.SN";
//  echo "<!-- " . var_dump($qry) . " -->\n";
  $res = $db->query($qry);
  if ($res) {
    while ($row = $res->fetch_assoc()) {
      $x = '';
      if ($type == 0 && $row['Type']) $x = " ( " . $row['Type'] . " ) "; 
      $Coming[$row['SideId']] = $row['SN'] . $x;
    }
  }
  $Come_Loaded = 1;
  return $Coming;
}

function Select_Come_Day($Day,$xtr='') {
  global $db,$YEAR,$Coming_Type;
  $Coming = [];
  $qry = "SELECT s.*, y.* FROM Sides s, SideYear y " .
         "WHERE s.SideId=y.SideId AND y.Year='$YEAR' AND y.Coming=" . $Coming_Type['Y'] . " AND y.$Day=1 AND s.IsASide=1 $xtr ORDER BY s.SN";
  $res = $db->query($qry);
  if ($res) {
    while ($row = $res->fetch_assoc()) {
      $Coming[$row['SideId']] = $row;
    }
    return $Coming;
  }
}

function &Select_Come_All($extra='') {
  global $db,$YEAR,$Coming_Type;
  static $Coming;
  if ($Coming) return $Coming;
  $qry = "SELECT s.*, y.* FROM Sides s, SideYear y WHERE s.SideId=y.SideId AND y.Year='$YEAR' AND y.Coming=" . $Coming_Type['Y'] .
        " $extra ORDER BY s.SN";
  $res = $db->query($qry);
  if ($res) while ($row = $res->fetch_assoc()) $Coming[$row['SideId']] = $row;

  return $Coming;
}

function &Part_Come_All() {
  global $db,$YEAR,$Coming_Type;
  $Coming = [];

  $qry = "SELECT s.*, y.* FROM Sides s, SideYear y WHERE s.SideStatus=0 AND s.SideId=y.SideId AND y.Year='$YEAR' AND ( y.Coming=" . 
         $Coming_Type['Y'] . " OR y.YearState>1 ) ORDER BY s.SN" ;
  $res = $db->query($qry);
  if ($res) while ($row = $res->fetch_assoc()) $Coming[$row['SideId']] = $row; // All Sides, now acts
  return $Coming;  
}

function Get_SideAndYear($snum) {
  global $db,$YEAR;
//  echo "SELECT s.*, y.* FROM Sides s, SideYear y WHERE s.SideId=$snum AND y.SideId=$snum AND y.Year='$YEAR'";
  $res = $db->query("SELECT s.*, y.* FROM Sides s, SideYear y WHERE s.SideId=$snum AND y.SideId=$snum AND y.Year='$YEAR'") ;
  if ($res) return $res->fetch_assoc();
  return 0;  
}

function Show_Side($snum,$Message='',$price=0,$Pcat='') {
  include_once("ProgLib.php");
  include_once("SideOverLib.php");
  global $YEAR, $Coming_Type,$db,$PerfTypes,$OlapTypes;
  if (is_numeric($snum) && ($side = Get_Side($snum))) {
    $syear = Get_SideYear($snum,$YEAR);
    Expand_PerfTypes();

    $HasOverlay = ($side['HasOverlays'] ?? 0);
    $Isa = 0;
    if ($HasOverlay && $Pcat) {
      $is = $PerfTypes[$Pcat][0];
      if ($side[$is]) $Isa = $Pcat;
    }
    
    $AlsoIs = Get_Active_Overlaps_For($snum,"AND OType>=3");

    $Banner = 1;
    if (Feature('PerformerBanners') && OvPhoto($side,$Isa)) $Banner = OvPhoto($side,$Isa);
    dohead($side['SN'],[],$Banner);
    if ($Message) echo "<h2 class=ERR>$Message</h2>"; 

    $txt = '';
    $BlobNum = 0;

//    if ($side['IsASide'] && $side['ShortName']) echo "( Appearing in the Dance grids as:" . $side['ShortName'] . " )<br>";

    echo "<div class=TwoCols><script>Register_Onload(Set_ColBlobs,'Blob',4)</script>";
    echo "<div class=OneCol id=TwoCols1>";
    

    
    $txt .= "<div id=Blob" . ($BlobNum++) . ">";
    $txt .=  "<h2>" . OvName($side,$Isa) . "</h2>";
    if (OvDesc($side,$Isa)) {
//      if ($side['OneBlurb']==0 || 
      if (strlen(OvDesc($side,$Isa)) > strlen(OvBlurb($side,$Isa))) $txt .=  OvDesc($side,$Isa) . "<p>";
    }

    if (isset($syear) && isset($syear['Coming'])) {
      switch ($syear['Coming']) {
        case $Coming_Type['N']:
        case $Coming_Type['NY']:
          $txt .=  "Not Coming this year";
          break;
        case $Coming_Type['Y']:
          $txt .=  "Coming";
          if ($syear['Fri'] || $syear['Sat'] || $syear['Sun']) {
            $txt .=  " on ";
            $lst = array();
            if ($syear['Tue']) $lst[] = 'Tuesday';
            if ($syear['Wed']) $lst[] = 'Wednesday';
            if ($syear['Thur']) $lst[] = 'Thursday';
            if ($syear['Fri']) $lst[] = 'Friday';
            if ($syear['Sat']) $lst[] = 'Saturday';
            if ($syear['Sun']) $lst[] = 'Sunday';
            if ($syear['Mon']) $lst[] = 'Monday';
            $txt .=  FormatList($lst);
          }
          break;
        case $Coming_Type['P']:
          $txt .=  "Probably coming";
          if ($syear['Fri'] || $syear['Sat'] || $syear['Sun']) {
            $txt .=  " on ";
            $lst = array();
            if ($syear['Tue']) $lst[] = 'Tuesday';
            if ($syear['Wed']) $lst[] = 'Wednesday';
            if ($syear['Thur']) $lst[] = 'Thursday';
            if ($syear['Fri']) $lst[] = 'Friday';
            if ($syear['Sat']) $lst[] = 'Saturday';
            if ($syear['Sun']) $lst[] = 'Sunday';
            if ($syear['Mon']) $lst[] = 'Monday';
            $txt .=  FormatList($lst);
          }
          break;
        case $Coming_Type['R']:
        case $Coming_Type['']:
        default:
//          echo "Invited";
      }
      $txt .=  "<p>";
    }
    if (OvBlurb($side,$Isa)) $txt .=  $side['Blurb'];
    $txt .=  "</div>";
    
    if (OvPhoto($side,$Isa)) $txt .=  "<div id=Blob" . ($BlobNum++) . "><img src=" . OvPhoto($side,$Isa) . " width=100%></div>\n";
    
    if ($syear['SponsoredBy'] ?? 0) {
      $txt .=  "<div id=Blob" . ($BlobNum++) . ">";
      SponsoredBy($syear,OvName($side,$Isa),3,$snum);
      $txt .= "</div>";
    }
    
    if ( OvVideo($side,$Isa)) $txt .=  "<div id=Blob" . ($BlobNum++) . "  style='max-width:100%; object-fit:contain;overflow:hidden'>" . 
      embedvideo(OvVideo($side,$Isa)) . "</div>";

    if (OvWebsite($side,$Isa) || OvFacebook($side,$Isa) || $side['Twitter'] || $side['Instagram']) {
      $txt .=  "<div id=Blob" . ($BlobNum++) . ">";
      if ( $side['Website'] ) $txt .=  "<img src=/images/icons/web.svg width=24 class=Limited> " . weblink(OvWebsite($side,$Isa),"<b>" . 
        $side['SN'] . "'s website</b>") . "<br>";
      $follow = "Follow " . $side['SN'] . " on ";
      $txt .=   Social_Link(OvFacebook($side,$Isa),'Facebook',1,$follow);
      $txt .=   Social_Link(OvTwitter($side,$Isa),'Twitter',1,$follow);
      $txt .=   Social_Link(OvInstagram($side,$Isa),'Instagram',1,$follow);
  //    $txt .=   Social_Link(OvSpotify($side,$Isa),'Spotify',1,$follow);
      $txt .=  "</div>";
    }
    
    if ($AlsoIs) {
 //       var_dump($AlsoIs);
//$OlapTypes = array('Dancer','Musician','Avoid','Also is','Part of','Includes');

      $AlsoList = [];
      foreach($AlsoIs as $Also) {
        if ($Also['OType']>2) {
          $Aid = (($Also['Sid1'] == $snum)?$Also['Sid2']:$Also['Sid1'])+0;
          
          switch ($Also['OType']) {
            case 4:
              if ($Also['Sid1'] == $snum) $Also['OType'] = 5;
              break;
            case 5:
              if ($Also['Sid1'] != $snum) $Also['OType'] = 4;
              break;
            default:
              break;
          }
          
          $OLap_Strings = ['','','','is also appearing as','is part of','includes'];
                
          $Aside = Get_SideAndYear($Aid);
          if ($Aside && (($Aside['IsASide'] && ($Aside['Coming'] == 2)) || ($Aside['YearState'] >= 2))) {
  //      $AYear = Get_SideYear($Aid);
            if ($Aside) $AlsoList []= "<b>" . $side['SN'] . "</b> " .
              $OLap_Strings[$Also['OType']] . " <a href=ShowPerf?id=" . $Aside['SideId'] . ">" . $Aside['SN'] . "</a>";
          } else {
  //          var_dump($Aid, $Also, $Aside);
          }
        }
      }
      if ($AlsoList) {
        $txt .= "<div id=Blob" . ($BlobNum++) . ">" . implode('<br>', $AlsoList) . "</b></div>";
      }
    }

    $txt .=  "</div>";
    
    if ($HasOverlay) {
      // Not written yet
    }
    
    echo "<div class=TwoCols><script>Register_Onload(Set_ColBlobs,'Blob'," . $BlobNum . ")</script>";
    echo "<div class=OneCol id=TwoCols1>$txt";
    echo "<div class=OneCol id=TwoCols2></div></div>";

    $prog = Show_Prog('Side',$snum,0,$price);
  //  $Exted_prog = Extended_Prog('Side',$snum,0);
    
    if ($prog) {
      if ($prog) echo $prog;
  //    if ($Exted_prog) echo $Exted_prog;
    } else {
      echo "<h2>The programme has not yet been published.</h2>\n";
      echo "When it is, the programme for <b>" . $side['SN'] . "</b> will appear here.<p>";
    }

  } else {
    echo "<h2 class=ERR>Sorry side $snum has an error: " . $db->error . "</h2>\n";
  }

}

function Get_Side_Name($id) {
  global $db;
  $res = $db->query("SELECT * FROM Sides WHERE SideId='$id'");
  if (!$res || $res->num_rows == 0) return '';
  $data = $res->fetch_assoc();
  return SName($data);
}

$Save_Sides = array('');

function Get_Side($who) {
  global $db;
  global $Save_Sides;
  if (isset($Save_Sides[$who])) return $Save_Sides[$who];
  $res = $db->query("SELECT * FROM Sides WHERE SideId='$who'");
  if (!$res || $res->num_rows == 0) return 0;
  $data = $res->fetch_assoc();
  $Save_Sides[$who] = $data;
  return $data;
}

function Put_Side(&$data) {
  global $Save_Sides;
  
  if (!isset($Save_Sides[$data['SideId']])) Get_Side($data['SideId']);
  $Save = &$Save_Sides[$data['SideId']];
  return Update_db('Sides',$Save,$data);
}

$Save_SideYears = array('');

function Get_SideYear($snum,$year=0) {
  global $db;
  global $Save_SideYears,$YEAR;
  if (!$year) $year=$YEAR;
  if (isset($Save_SideYears[$snum][$year])) return $Save_SideYears[$snum][$year];
  $res = $db->query("SELECT * FROM SideYear WHERE SideId='" . $snum . "' AND Year='" . $year . "'");
  if (!$res || $res->num_rows == 0) return Default_SY($snum);
  $data = $res->fetch_assoc();
  $Save_SideYears[$snum][$year] = $data;
  return $data;
}

function Get_SideYears($snum) {
  global $db;
  global $Save_SideYears;
  if (isset($Save_SideYears[$snum]['ALL'])) return $Save_SideYears[$snum];
  $res = $db->query("SELECT * FROM SideYear WHERE SideId='$snum'");
  if (!$res) return 0;
  while ($yr = $res->fetch_assoc()) {
    $y = $yr['Year'];
    $Save_SideYears[$snum][$y] = $yr;
  }
  $Save_SideYears[$snum]['ALL'] = 1;
  return $Save_SideYears[$snum];
}

function RecordPerfChanges(&$now,&$Cur,$Up) {
  global $PLANYEAR,$USERID;
  $Fields = ['Coming','Sat','Sun','Mon','YearState'];

  if (!isset($now['NoEvents']) || $now['NoEvents'] || $now['Year'] != $PLANYEAR) return;
//var_dump("HERE");
  foreach ($Fields as $f) if (isset($Cur[$f]) && $now[$f] != $Cur[$f]) {
    if (($f == 'YearState') && ($now['YearState'] >= 2) && ($Cur['YearState'] > 0)) continue;
    if (preg_match('/(Sat)|(Sun)|(Mon)/',$f)) {
      $ff = ($now[$f]?'+':'-') . $f;
    } else {
      $ff = $f;
    }
    $Rec = Gen_Get_Cond1('PerfChanges',"( SideId=" . $now['SideId'] . " AND Field='$ff' )");
    if (isset($Rec['id'])) {
      $Rec['Changes'] = $now[$f];
      $Rec['Who'] = $USERID;
      Gen_Put('PerfChanges',$Rec);
    } else {
      $Rec = ['SideId'=>$now['SideId'], 'Year'=>$PLANYEAR, 'Changes'=>$now[$f], 'Field'=>$ff, 'Who'=>$USERID ];
      Gen_Put('PerfChanges',$Rec);
    }
    
  }
}

function Put_SideYear(&$data,$Force=0) {
  global $Save_SideYears,$YEAR;
  if (!$data) return;
  if ($Force) {
    $Save = Get_SideYear($data['SideId']);
    $Up = 1;    
   } else {
     if (!isset($Save_SideYears[$data['SideId']][$data['Year']])) {
       $Save = &$Save_SideYears[$data['SideId']][$YEAR];
       $Save = Default_SY($data['SideId']);
       $data = array_merge($Save,$data);
       $Up = 0;
     } else { 
       $Save = &$Save_SideYears[$data['SideId']][$data['Year']];
       $Up = 1;
     }
  }
  
  if (Feature('RecordPerfChanges')) RecordPerfChanges($data,$Save,$Up);
  if ($Up) {
    return Update_db('SideYear',$Save,$data);
  } else {
    return Insert_db('SideYear',$data);
  }
 
}

function isknown($snum,$yr) {
  global $Save_SideYears;
  return isset($Save_SideYears[$snum][$yr]);
}

function Get_Perf_Types($tup=0,$Cond='') {
  global $db;
  $full = $short = [];
  $res = $db->query("SELECT * FROM PerformerTypes $Cond ORDER BY SN ");
  if ($res) {
    while ($typ = $res->fetch_assoc()) {
      $short[$typ['id']] = $typ['SN'];
      $full[$typ['id']] = $typ;
    }
  }
  if ($tup) return $full;
  return $short;
}

function Get_Perf_Type($id) {
  global $db;
  $res=$db->query("SELECT * FROM PerformerTypes WHERE id=$id");
  if ($res) {
    $ans = $res->fetch_assoc();
    return $ans;
  }
  return 0; 
}

function Put_Perf_Type(&$now) {
  $e=$now['id'];
  $Cur = Get_Perf_Type($e);
  return Update_db('PerformerTypes',$Cur,$now);
}

function Set_Side_Help() {
  $t = array(
        'SN'=>'To appear on website and in the programme',
        'ShortName'=>'IF the name is more than 20 characters, give a short form to appear on the Grids.',
        'Type'=>'For example North West, Border, Folk, Jazz',
        'Importance'=>'Only raise the importance for those that really need it.  They get front billing and bigger fonts in publicity.  ' .
                      'Under normal circumstances at most 3 should be Very High. Higher values are for the late addition of surprise headline ' .
                      'acts and can only be set by Richard.',
        'OverlapsD'=>'Sides that share Dancers - Where possible, there will be a 30 minute gap between any spot by any of these sides',
        'OverlapsM'=>'Sides that share Musicians - These can perform at the same spot at the same time, or consecutive times',
        'Blurb'=>'Longer description, for the webpage on the festival website about the side/act/performer, only seen when a user clicks a link ' .
                 'for more info on them - OPTIONAL',
        'CostumeDesc'=>'Short description of costume and where in the country they are from, for the programme book',
        'Description'=>'The entry in the programme book will be based on this',
        'Website'=>'If more than one seperate with spaces (mainly for music acts)',
        'Facebook'=>'If more than one seperate with spaces (mainly for music acts)',
        'Twitter'=>'If more than one seperate with spaces (mainly for music acts)',
        'Instagram'=>'If more than one seperate with spaces (mainly for music acts)',
        'Spotify'=>'If more than one seperate with spaces (mainly for music acts)',
        'Video'=>'You can use a YouTube embed or share link',
        'Likes'=>'Venues prefered, sides like to share with',
        'Dislikes'=>'Venues disliked, sides do not want to share with - not in use',
        'Pre2017'=>'Previous Festivals/Invites etc',
        'AccessKey'=>'Allows user editing of many fields.  When you use the Email links here it is always appended to the message',
        'Photo'=>'Give URL of photo to use or upload one',
        'Mobile'=>'As an emergency contact number, this is important',
        'NoiseLevel'=>'Loud PAs are noisy, a single violin or flute is quiet',
        'Surfaces'=>'What surfaces can be danced on, if none are set all is assumed.',
        'SideStatus'=>'If the act/side/performer is disbanded mark as dead.  Mark as banned to prevent from automatic acceptence of known sides.',
        'StagePA'=>'Give PA Requirments (if any) as simple text, or upload a file',
        'DataCheck'=>'Not yet working',
        'MorrisAnimal'=>'If the side has a morris animal - what kind is it',
        'Workshops'=>'That the side could run',
        'Overlaps'=>('Do you overlap with any dance sides, musicians or other performers who might be at' . Feature('FestName') . 
                    ', if so please describe in detail and we will try and prevent clashes'),
        'OverlapRules'=>'Dancer - must have break between spots, Musician allowed to play at same spot for two periods - then must break, ' .
                    'Avoid - Dont put these together, Also is - Same performer different profile. Major - major error, minor avoid if you can',
        'Contact'=>'Main Contact',
        'AgentName'=>'Main Contact',
        'DirContact'=>'Direct Performer Contact',
        'Address'=>'Where to send performers wristbands and any tickets',
        'AltContact'=>'Alternative Contact',
        'Location'=>'Where in the country they are from',
        'PublicInfo'=>'Anything here may appear on the festival website where appropriate',
        'PrivateInfo'=>'Anything here is ONLY visible to you and the relevant members of the festival',
        'NeedBank'=>'Set this to enable bank details for dance sides (for payments)',
        'Bank'=>'If you expect to be paid, please fill your bank details in',
        'RelOrder'=>'To give finer control than Importance, can be negative',
        'ManageFiles'=>'Use this to upload, download, view and delete as manay files as you wish about this performer',
        'Testing'=>'Testing Only',
        'PerfTypes'=>'You MUST Save changes after any changes to Performer Types, to refresh the page. ' .
                     'IF you wish to remove a performer type tell Richard - there are many small changes ' .
                     'that may be needed that are not yet automated',
        'OneBlurb'=>'Select this to surpress showing the Short Blurb and the Long Blurb at the same time',
        'DiffImportance'=>'IF needs to have different Importances for performer types, select this and SAVE CHANGES',
        'EmailLog'=>'View the system email log to (and from) this performer - if there is one',
        'BookDirect'=>'Tick this to bypass the agent and email the performer drectly',
        'NotPerformer'=>'People in the database that are not performing and should not appear in lists of performers',
        'HasOverlays'=>'Enables separate public info for performers in multiple categories - ask Richard',
        'NoDanceEvents'=>'If the performer is not doing a dancing event, tick to surpress errors',
  );
  Set_Help_Table($t);
}

function Set_Side_Year_Help() {
  $t = array(
        'Performers'=>'Number of Dancers and Musicians that will want wristbands, put -1 if none are wanted',
        'FriEve'=>'Would you like to have some dancing on Friday Evening?',
        'SatEve'=>'Would you like to have some dancing on Saturday Evening?',
        'FriDance'=>'Number of Dance spots requested on Friday, the default assumption is 0',
        'SatDance'=>'How many Dance spots would you like on Saturday, the minimum for a performers wristband is 3 shared spots plus the ' .
           'procession or 4 shared spots or 3 solo spots',
        'SunDance'=>'How many Dance spots would you like on Sunday, the minimum for a performers wristband is 4 shared spots or 3 solo spots',
        'Share'=>'Do you like shared or dedicated dance spots?', 
        'CarPark'=>'Number of free car park tickets for parking at QE school (10 minute walk to square)',
        'SatArrive'=>'The earliest time (eg 1000), if blank no restrictions are assumed',
        'SatDepart'=>'The end of the last spot (eg 1700).  If blank no restictions are assumed.',
        'SunArrive'=>'The earliest time (eg 1000), if blank no restrictions are assumed',
        'SunDepart'=>'The end of the last spot (eg 1700).  If blank no restictions are assumed.',
        'BudgetArea0'=>'In MOST cases nothing needs setting here as Music acts will default to Music and Dance to Dance.  
                * IF you need to assign to a different budget change the area
                * IF you need part of the fee to come under a different budget, you set up to 2 areas to have parts of the Fee and the amount ' .
           'to assign',
        'OtherPayment' => 'Eg A bottle of Rum',
        'OtherPayCost' => 'Cost of the other payment, eg the bottle of Rum',
        'ReleaseDate' => 'If set, do not show to public until after this date/time',
        'YearState'=>'This is generally set by your and the Acts actions.  
Declined - Will leave this state after any change that would affect the contract.
Booking - negotiations in place. 
Contract Ready - For the Act to confirm it.
Contract Signed - Enables listing to public.',
        'Rider'=>'Additional text to be added to the Contract',
        'EnableCamp' => 'Note this will be added to the fee as part of your budget',
        'GreenRoom' => 'If ticked, their contract will inform them of the Green Room',
        'ReportTo' => 'For the arrival statement in contract.  Most will report to the Infomation Point, None means no statement in contract, ' .
           'Green Room will say report to Green Room',
        'Coming' => 'Please indicate you have got the invite and then update when you have made a decision',
        'Messages' => 'To Edit ask Richard (for now)',
        'ContractAnyway' => 'Forces contract even if none are needed',
        'NoEvents' => 'Set this if you are issuing a contract even without any events',
        'SponsoredBy' => 'This is setup from the Sponsor, just displayed here for info',

  );
  Set_Help_Table($t);
}

function Default_SY($id=0) { 
  global $YEAR,$USERID;
  $numprocs = intval(Feature('ProcessDays'));
  $ans = array('SatDance'=>4,'SunDance'=>4,'MonDance'=>4,'Year'=>$YEAR,'Invited'=>'','BookedBy'=>$USERID,'YearState'=>0,'Coming'=>0);
  if ($id) $ans['SideId'] = $id;
  switch ($numprocs) {
  case 0:
    break;
  case 2:
    $ans['ProcessionSat'] = 1; 
    $ans['SatDance'] = 3;
    break;
  case 4:
    $ans['ProcessionSun'] = 1; 
    $ans['SunDance'] = 3;
    break;
  
  case 8:
    $ans['ProcessionMon'] = 1; 
    $ans['MonDance'] = 3;
    break;
  
  case 10:
    if (rand() < 0.5) {
      $ans['ProcessionSat'] = 1; 
      $ans['SatDance'] = 3;
    } else {
      $ans['ProcessionMon'] = 1; 
      $ans['MonDance'] = 3;
    }  
  default:
  }

  return $ans;
}

function Get_Dance_Types($tup) {
  global $db;
  $res = $db->query("SELECT * FROM DanceTypes ORDER BY Importance DESC");
  $short = $full = [];
  if ($res) {
    while ($typ = $res->fetch_assoc()) {
      $short[$typ['TypeId']] = $typ['SN'];
      $full[$typ['TypeId']] = $typ;
    }
  }
  if ($tup) return $full;
  return $short;
}

function Get_Dance_Type($id) {
  global $db;
  static $Types;
  if (isset($Types[$id])) return $Types[$id];
  $res=$db->query("SELECT * FROM DanceTypes WHERE TypeId=$id");
  if ($res) {
    $ans = $res->fetch_assoc();
    $Types[$id] = $ans;
    return $ans;
  }
  return 0; 
}

function Put_Dance_Type(&$now) {
  $e=$now['TypeId'];
  $Cur = Get_Dance_Type($e);
  Update_db('DanceTypes',$Cur,$now);
}

function Has_Info(&$data) {
  $checkfor = array( 'StagePA', 'Likes', 'Notes', 'YNotes', 'PrivNotes', 'NoiseLevel');
  foreach ($checkfor as $c) if (isset($data[$c]) && $data[$c] && ($data[$c] != 'None')) return 1;
//  if (Get_Overlaps_For($data['SideId'],1)) return 1;
  return 0;
} 

function Get_Overlaps_For($id,$act=0,$xtra='') { // if act only active
  global $db;
  $Os = [];
  $res = $db->query("SELECT * FROM Overlaps WHERE (Sid1=$id OR Sid2=$id)" . ($act?' AND Active=1':'') . $xtra);
  if ($res) while ($o = $res->fetch_assoc()) $Os[] = $o;
  return $Os;
}

function Get_Active_Overlaps_For($id,$xtra='') { // if act only active
  global $db;
  $Os = [];
  $res = $db->query("SELECT * FROM Overlaps WHERE (Sid1=$id OR Sid2=$id) AND Sid1!=0 AND Sid2!=0 AND Active=1 $xtra");
  if ($res) while ($o = $res->fetch_assoc()) $Os[] = $o;
  return $Os;
}

function Get_Overlap($id) {
  global $db;
  $res = $db->query("SELECT * FROM Overlaps WHERE id=$id");
  if ($res) while ($o = $res->fetch_assoc()) return $o;
}

function Put_Overlap($now) {
  $e=$now['id'];
  $Cur = Get_Overlap($e);
  Update_db('Overlaps',$Cur,$now);
}

function Put_Overlaps(&$Ovs) {
  foreach($Ovs as $o) {
    if ($o['id']) {
      Put_Overlap($o);
    } else {
      Insert_db('Overlaps', $o);
    }
  }
}
  
function UpdateOverlaps($snum) {
  $Exist = Get_Overlaps_For($snum);

//  for($i=1; $i<5; $i++) {
//    $_REQUEST["Side$i"] = $_REQUEST["Perf" . $_REQUEST["PerfType$i"] . "_Side$i"];
//  }  

// Scan each existing and any added rules
  $Rule = 0;
  while (1) {
    $r = $Rule++;
    if (!isset($_REQUEST["Olap$r" . "Cat"])) break;
    $cat = $_REQUEST["Olap$r" . "Cat"];
    $sid = $_REQUEST["Perf$cat" . "_Side$r"];
  
    if (!$sid || !isset($_REQUEST["OlapActive$r"]) || !isset($_REQUEST["OlapMajor$r"])) continue;
    $O = $StO = (isset($Exist[$r]) ? $Exist[$r] : ['Sid1'=>$snum,'Cat2'=>0]);
    $Other = ($O['Sid1'] == $snum)?'Sid2':'Sid1'; 
    $OtherCat = ($O['Sid1'] == $snum)?'Cat2':'Cat1';
    $O['OType'] = $_REQUEST["OlapType$r"];
    $O['Major'] = (isset($_REQUEST["OlapMajor$r"]) ? $_REQUEST["OlapMajor$r"] :0);
    $O['Days'] = $_REQUEST["OlapDays$r"];
    $O['Active'] = (isset($_REQUEST["OlapActive$r"]) ? $_REQUEST["OlapActive$r"] :0);
    $O[$OtherCat] = $cat;
    $O[$Other] = $sid;

    if ((isset($O['id'])) && $O['id']) {
      Update_db('Overlaps',$StO,$O); 
    } else if ($O[$Other]) {
      Insert_db('Overlaps',$O); 
    }
  }
}
      
function Side_ShortName($si) {
  $side = Get_Side($si);
  return $side[($side['ShortName']?'ShortName':'SN')];
}

// Ignore case and -> &, ommit | add 'The'
function Find_Perf_Similar($name,$isa='') {
  global $db;
  $name = strtolower(trim($name));
  $name = preg_replace('/^the /','',$name);
  $name = preg_replace('/ morris/',' ',$name);
  $name = preg_replace('/ band/',' ',$name);
  $name = preg_replace('/ and /',' ',$name);
  $name = preg_replace('/ & /',' ',$name);
  $name = preg_replace('/[,.!]/',' ',$name);
  $name = trim($name);

  $res = $db->query("SELECT * FROM Sides WHERE SN LIKE '%$name%' $isa");
  if (!$res) return [];
  $sims = [];
  while ($rec = $res->fetch_assoc()) $sims[] = $rec;
  return $sims;
}

function EventCmp($a,$b) {
  if ($a['Day'] != $b['Day'] ) return (($a['Day'] < $b['Day']) ? -1 : 1);
  if ($a['Start'] == $b['Start']) return 0;
  return (($a['Start'] < $b['Start']) ? -1 : 1);
}

/* Get Overlaps - if none return empty string, if not public return,
/  otherwise get programmes for all overlaps and merge together and list as a timetable
/ */
function Extended_Prog($type,$id,$all=0) { 
    global $OlapCats;
    $Olaps = Get_Active_Overlaps_For($id,"AND OType>=1");
    if (!$Olaps) return "";

    include_once("ProgLib.php");
    $str = '';
    $Evs = Get_All_Events_For($id,$all);
    if (!$Evs) return "";
    $ETs = Get_Event_Types(1);
//echo "Type: $type, $id<p>";
//var_dump($Evs);
    $evc=0;
    $Worst= 99;
    $EventLink = ($all?'EventAdd':'EventShow');
    $VenueLink = ($all?'AddVenue':'VenueShow');
    $host = "https://" . $_SERVER['HTTP_HOST'];

    foreach ($Evs as $ei=>$e) $Evs[$ei]['ActAs'] = $id;
    $Found = 0;
    // Go through each Olap and add events
    foreach($Olaps as $O) {
      $Oid = ($O['Sid1'] == $id ? $O['Sid2'] : $O['Sid1']);
      $Oct = ($O['Sid1'] == $id ? $O['Cat2'] : $O['Cat1']);
      $OEvs = Get_All_Events_For($Oid,$all);
      if (!$OEvs) continue;
      foreach ($OEvs as $oe=>$e) $OEvs[$oe]['ActAs'] = $Oid;
      $Evs = array_merge($Evs,$OEvs);
      $Found = 1;
    }
    if (!$Found) return ""; // No new events found

    usort($Evs,"EventCmp"); 
    
//var_dump($Evs); exit;
    $Venues = Get_Real_Venues(1);
    if ($Evs) { // Show IF all or EType state > 1 or (==1 && participant)
      $With = 0;
      foreach ($Evs as $e) {
        if ($e["BigEvent"]) { $With = 1; break; }
        for ($i = 1; $i<5;$i++) if ($e["Side$i"] && $e["Side$i"] != $id) { $With = 1; break 2; }
      }
        
      $UsedNotPub = 0;
      foreach ($Evs as $e) {
        $cls = ($e['Public']<2?'':' class=NotCSide ');
        if ($all || $ETs[$e['Type']]['State'] > 1 || ($ETs[$e['Type']]['State'] == 1 && Access('Participant',$type,$id))) {
          $evc++;
           $Worst = min($ETs[$e['Type']]['State'],$Worst);
          if ($e['BigEvent']) { // Big Event
            $Others = Get_Other_Things_For($e['EventId']);
            $VenC=0;
            $PrevI=0;
            $NextI=0;
            $PrevT=0;
            $NextT=0;
            $Found=0;
            $Position=1;
            foreach ($Others as $O) {
              if ($O['Identifier'] == 0) continue;
              switch ($O['Type']) {
              case 'Side':
              case 'Act':
              case 'Other':
                if ($O['Identifier'] == $e['ActAs']) { 
                  $Found = 1; 
                } else {
                  if ($Found && $NextI==0) { $NextI=$O['Identifier']; $NextT=$O['Type']; }
                  if (!$Found) { $PrevI=$O['Identifier']; $PrevT=$O['Type']; $Position++; }
                }
                break;
              case 'Venue':
                $VenC++;
              default:
                break;
              }
            }
            $str .= "<tr><td $cls>" . DayList($e['Day']) . 
              "<td $cls>" . timecolon($e['Start']) . "-" . timecolon(($e['SubEvent'] < 0 ? $e['SlotEnd'] : $e['End'] )) .
                        "<td>" . SAO_Report($e['ActAs']) .
                        "<td $cls><a href=$host/int/$EventLink?e=" . $e['EventId'] . ">" . $e['SN'] . "</a><td $cls>";
            if ($VenC) $str .= " starting from ";
            $str .= "<a href=$host/int/$VenueLink?v=" . $e['Venue'] . ">" . VenName($Venues[$e['Venue']]) ;
            $str .= "</a><td $cls>";
            if ($PrevI || $NextI) $str .= "In position $Position";
            if ($PrevI) { $str .= ", After " . SAO_Report($PrevI); }
            if ($NextI) { $str .= ", Before " . SAO_Report($NextI); }
            $str .= "\n";
          } else { // Normal Event
            $str .= "<tr><td $cls>" . DayList($e['Day']) . "<td $cls>" . timecolon($e['Start']) . "-" . 
              timecolon(($e['SubEvent'] < 0 ? $e['SlotEnd'] : $e['End'] )) .
                        "<td>" . SAO_Report($e['ActAs']) .
                        "<td $cls><a href=$host/int/$EventLink?e=" . $e['EventId'] . ">" . $e['SN'] . 
                        "</a><td $cls><a href=$host/int/$VenueLink?v=" . $e['Venue'] . ">" . VenName($Venues[$e['Venue']]) . "</a>";
            if ($With) {
              $str .= "<td $cls>";
              $withc=0;
              for ($i=1;$i<5;$i++) {
                if ($e["Side$i"] > 0 && $e["Side$i"] != $id && $type == 'Side') { 
                  if ($withc++) $str .= ", "; 
                  $str .= SAO_Report($e["Side$i"],$e["Roll$i"],$e['SubEvent']);
                }
              }
            }
            $str .= "\n";
          }
        } else { // Debug Code
//          echo "State: " . $ETs[$e['Type']]['State'] ."<p>";
        }
        if ($cls) $UsedNotPub = 1;
      }
      if ($evc) {
        $Thing = Get_Side($id);
        $Desc = ($Worst > 2)?"":'Current ';
        if ($With) $str = "<td>With\n" . $str;
        $str = "<h2>$Desc Programme for " . $Thing['SN'] . " including overlaps:</h2>\n" . 
                ($UsedNotPub?"<span class=NotCSide>These are not currently public<p>\n</span>":"") .
                "<div class=Scrolltable><table border class=PerfProg><tr><td>Day<td>time<td>As<td>Event<td>Venue" . $str;
      }
    }
    if ($evc) {
      $str .= "</table></div>\n";    
    }

//var_dump($str);

  return $str;
}


function Dance_Email_Details($key,&$data,&$att=0) {
  global $YEAR,$PLANYEAR,$Book_State;
  include_once("ProgLib.php");
  $Side = &$data[0];
  if (isset($data[1])) $Sidey = &$data[1];
  $snum = $Side['SideId'];
  $str = '';
  $host = "https://" . $_SERVER['HTTP_HOST'];
  switch ($key) {
  case 'WHO':  return $Side['Contact']? firstword($Side['Contact']) : $Side['SN'];
  case 'LINK': return "<a href='$host/int/Direct?t=Perf&id=$snum&key=" . $Side['AccessKey'] . "&Y=$YEAR'><b>this link</b></a>  " ;
  case 'PROG': return Show_Prog('Perf',$snum,1);
  case 'MISSING': $str = "Please could you <span style='background:pink'><B>click</B> on the *LINK*</span> and add the following:<ol>\n"; 
    $count = 0;
    if ($Sidey['Sat'] == 0 && $Sidey['Sun'] == 0 && $Sidey['Mon'] == 0) {
      $str .= '<li><b>Days</b> What days you will be dancing.  It is also very helpful if you tell us: ' .
              'your earliest start and latest finish times, the defaults are 10am to 5pm.<p>';
      $count++;
      }
    if (!$Side['Mobile']) {
      $str .= '<li><b>Mobile phone number</b> so we can contact you in an emergency.<p>';
      $count++;
      }        
    if (Feature('PublicLiability') && !$Sidey['Insurance']) {
      $str .= '<Li>Upload your <b>insurance</b> for *PLANYEAR*.<p>';
      $count++;
      }
    if (Feature('PerformerTickets') && $Sidey['Performers'] == 0) {
      $str .= '<li><b>Performer Numbers</b> which is the number of performers wristbands you require.  If none of your team want to go to ' .
        'any of the paid events, then put -1 (which means none are required).<p>';
      $count++;
      }
    if (Feature('DanceNeedAddress') && $Sidey['Performers']>=0 && !$Side['Address']) {
      $str .= '<li>An <b>Address</b> so we can post your performer wristbands - not needed if you do not require any wristbands.<p>';
      $count++;
      }

    return ($count? "$str</ol><p>\n" : "");
  case 'PERF': return $Side['SN'];
  case 'SIDE': return $Side['SN'];
  case 'DANCEORG': return Feature('DanceOrg','Richard Proctor');
  case (preg_match('/TICKBOX(.*)/',$key,$mtch)?true:false):
    $bits = preg_split('/:/',$mtch[1],3);
    $box = 1;
    $txt = 'Click This';
    if (isset($bits[1])) $box = $bits[1];
    if (isset($bits[2])) { $txt = $bits[2]; $txt = preg_replace('/_/',' ',$txt); }
    return "<a href='$host/int/Access?t=s&i=$snum&TB=$box&k=" . $Side['AccessKey'] . "&Y=$PLANYEAR'><b>$txt</b></a>\n";

  case 'CONTRACT': 
    if (isset($Sidey['YearState']) && $Sidey['YearState']) {
      if ($Sidey['YearState'] == $Book_State['Contract Signed']) { 
        $p = 1; 
        $AddC = 1;
      } else {
        $ConAns = Contract_Check($snum,1,1);
        switch ($ConAns) {
          case 0: // Ready
          case 1: /// No fee - acceptable sometimes
            $str = '<b>Please confirm your contract by following *LINK* and clicking on the "Green Confirm" button near the ' .
              'bottom of the page.</b><p>';
            $p = 0;
            $AddC = 1;
            break;
          case 3: // Ok apart from bank account
            $str = 'Please follow *LINK*, fill in your bank account details (so we can pay you).<p> ' .
                  'Then you will be able to view and confirm your contract, ' .
                  'by clicking on the "Green Confirm" button. (The button will only appear once you have input your bank account details ).<p>';
            $p = 0;
            $AddC = 2;
            break;
          case 4: // No Cont
            break;
          default: // Add draft for info
            $AddC = 2;
        }
      }
      
    if (is_array($att) && $AddC) {
      $att[] = Contract_Save($Side,$Sidey,($Sidey['YearState'] == $Book_State['Contract Ready']?-1:1),1);
    } else {
    }
    return $str;
    }
  return '';
  
  case 'COLLECTINFO':
    include_once("CollectLib.php");
    return CollectInfo($Side);  
  }
}

function Dance_Email_Details_Callback($mescat,$data) {
  global $Book_State;
// $str = "In Callback - $mescat<p>";
  switch ($mescat) {
  case 'Music_Contract':
//    $str .= " Got to contract ";
    $Side = &$data[0];
    if (isset($data[1])) $Sidey = &$data[1];
    $snum = $Side['SideId'];
    if (isset($Sidey)) {
      $Sidey['YearState'] = $Book_State['Contract Sent'];
//      $str .= var_export($Sidey);
      Put_SideYear($Sidey);
//      $str .= "Updated State to " . $Sidey['YearState'];
    }
//echo $str;  
    return;
  default:
//echo $str;  
    return;
  }
}


function Dance_Record_Change($id,$prefix) { 
  global $YEAR,$PLANYEAR,$YEARDATA;
  
//  echo "Called DRC<p>";
  
// var_dump($YEAR,$PLANYEAR);
  
//  exit;
  
  if ($YEAR == $PLANYEAR) {
    $SideLY = Get_SideYear($id,$YEARDATA['PrevFest']);
    if (!strstr($SideLY['Invited'],$prefix)) {
      if (strlen($SideLY['Invited'])) {
        $SideLY['Invited'] = $prefix . ", " . $SideLY['Invited'];
      } else {
        $SideLY['Invited'] = $prefix;  
      }
      Put_SideYear($SideLY);
    }
    
    $Sidey = Get_SideYear($id);
    $Sidey['Invite'] = $SideLY['Invite'];
    if (!strstr($Sidey['Invited'],$prefix)) {
      if (strlen($Sidey['Invited'])) {
        $Sidey['Invited'] = $prefix . ", " . $Sidey['Invited'];
      } else {
        $Sidey['Invited'] = $prefix;  
      }
    }
    Put_SideYear($Sidey);
  } else {
    $Sidey = Get_SideYear($id);
    if (!strstr($Sidey['Invited'],$prefix)) {
      if (strlen($Sidey['Invited'])) {
        $Sidey['Invited'] = $prefix . ", " . $Sidey['Invited'];
      } else {
        $Sidey['Invited'] = $prefix;  
      }
      Put_SideYear($Sidey);
    }
    
    $SideNY = Get_SideYear($id,$YEARDATA['NextFest']);
    $SideNY['Invite'] = $Sidey['Invite'];
    if (!strstr($SideNY['Invited'],$prefix)) {
      if (strlen($SideNY['Invited'])) {
        $SideNY['Invited'] = $prefix . ", " . $SideNY['Invited'];
      } else {
        $SideNY['Invited'] = $prefix;  
      }
    }
    Put_SideYear($SideNY);
  }
}
