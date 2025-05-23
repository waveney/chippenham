<?php
// Common Venue/Event/Programming Library

global $Venue_Status,$InfoLevels,$VisParts,$Thing_Types,$Public_Event_Types,$Day_Type,$DayLongList,$Info_Type,
       $Public_Event_Type,$Event_Access_Type,$Event_Access_Type_Day,$Event_Access_Colours;
$Venue_Status = array('In Use','Not in Use');
$DayLongList = array(-4=>'Monday',-3=>'Tuesday',-2=>'Wednesday',-1=>'Thursday',0=>'Friday',1=>'Saturday',2=>'Sunday',3=>'Monday',
                     4=>'Tuesday',5=>'Wednesday',6=>'Thursday',7=>'Friday',8=>'Saturday',9=>'Sunday',10=>'Monday');
$InfoLevels = array('None','Major','Minor','All');
$VisParts = array('All','None'); // Add subcats when needed
$Thing_Types = array('Sides','Acts','Others');
$Public_Event_Types = array('As Global','Yes', 'Not yet','Never');

$Event_Access_Type_Day = ['Open','Weekend or Day tickets Only', 'Weekend, Day or Event tickets Only', 'Event Tickets Only'];
$Event_Access_Type = ['Open','Weekend tickets Only', 'Weekend or Event tickets Only', 'Event Tickets Only'];
$Event_Access_Colours = ['white','lightblue','lightgreen','pink'];

$Day_Type = ['Thur'=>-1,'Fri'=>0,'Sat'=>1,'Sun'=>2,'Mon'=>3,'Tue'=>4];
$Info_Type = array_flip($InfoLevels);
$Public_Event_Type = array_flip($Public_Event_Types);

include_once("DateTime.php");

function DayList($d) {
  $DayList = array(-4=>'Mon',-3=>'Tue',-2=>'Wed',-1=>'Thur',0=>'Fri',1=>'Sat',2=>'Sun',3=>'Mon',4=>'Tue',5=>'Wed',6=>'Thur',7=>'Fri',
    8=>'Sat',9=>'Sun',10=>'Mon');
  global $YEARDATA;

  if ($d < $YEARDATA['FirstDay'] || $d > $YEARDATA['LastDay'] || ($YEARDATA['LastDay']-$YEARDATA['FirstDay']) < 6) return $DayList[$d];
  return FestDate($d,'S');
}

function Set_Venue_Help() {
  static $t = array(
        'ShortName'=>'Short name eg Cornmarket - OPTIONAL',
        'SN'=>'Full name eg Daccombe International Stage',
        'DanceImportance'=>'Higher numbers get listed first',
        'Description'=>'Sent to particpants so they know what to expect and where it is',
        'GoogleMap'=>'Link for partipants to know exactly where to go',
        'DanceRider'=>'Additional text sent to dance sides about this venue',
        'MusicRider'=>'Additional text sent to music acts about this venue',
        'OtherRider'=>'Additional text sent to other participants about this venue',
        'Parking'=>'Is parking provided by/for the Venue',
        'Bar'=>'Does the venue have a bar?',
        'Food'=>'Does the venue serve food?',
        'BarFoodText'=>'Any text that expands on the food and drink available',
        'Website'=>'If the venue has a website put it here',
        'MapImp'=>'Range 0-20, 0 means 16 which is default, 15 is VERY important, 18 very minor, -1 do not display',
        'DirectionsExtra'=>'Extra info to be put at end of directions to venue',
        'IsVirtual'=>'Combined site for display purposes, do not use for real events',
        'PartVirt'=>'What virtual site this is part of (if any)',
        'SupressFree'=>'If the venue has an entry change set this',
        'Minor'=>'Treatment of venue in final dance grid',
        'DisabilityStat'=>'A Statement about disabled access for the venue',
        'SuppressParent'=>'Set to Suppress showing Parent venue',
  );
  Set_Help_Table($t);
}

function Get_Venues($type=0,$extra='') { //0 = short, 1 = full
  global $db;
  static $short,$full;
  if (!$short) {
    $res = $db->query("SELECT * FROM Venues $extra ORDER BY SN");
    if ($res) {
      while ($Ven = $res->fetch_assoc()) {
        $i = $Ven['VenueId'];
        $short[$i] = SName($Ven);
        $full[$i] = $Ven;
      }
    }
  }
  if ($type) return $full;
  return $short;
}

function Get_AVenues($type=0,$extra='') { //0 = short, 1 = full
  global $db;
  static $short,$full;
  if (!$short) {
    $res = $db->query("SELECT * FROM Venues WHERE status=0 $extra ORDER BY SN");
    if ($res) {
      while ($Ven = $res->fetch_assoc()) {
        $i = $Ven['VenueId'];
        $short[$i] = SName($Ven);
        $full[$i] = $Ven;
      }
    }
  }
  if ($type) return $full;
  return $short;
}

function &Report_To() { // List of report to locs
  static $List;
  include_once('MapLib.php');
  if (isset($List)) return $List;
  $List = [Feature('DefaultReportPoint','Information Point'),'None'];
  $Vens = Get_Real_Venues(0);
  $Pts = Get_Map_Points();
  $List = array_merge($List,$Vens);
  if ($Pts) foreach ($Pts as $P) if ($P['Directions']) $List[-$P['id']] = $P['SN'];
  return $List;
}


function Get_Real_Venues($type=0) { // 0 =short, 1 =full
  $Vens = Get_AVenues(1);
  $real = array();
  if ($Vens) foreach ($Vens as $vi=>$v) if (!$v['IsVirtual']) $real[$v['VenueId']] = ($type?$v:SName($v));
  return $real;
}

function Get_Virtual_Venues($type=0) {
  $Vens = Get_Venues(1);
  $virt = array();
  foreach ($Vens as $vi=>$v) if ($v['IsVirtual']) $virt[$v['VenueId']] = ($type?$v:SName($v));
  return $virt;
}

function Get_Venues_For($What) {
  global $db;
  $ids = [];
  $res = $db->query("SELECT VenueId FROM Venues WHERE $What=1 AND Status=0 ORDER BY $What" . "Importance DESC");
  if ($res) {
    while ($Ven = $res->fetch_assoc()) $ids[] = $Ven['VenueId'];
  }
  return $ids;
}

function Get_Venue($vid) {
  static $Venues;
  global $db;
  if (isset($Venues[$vid])) return $Venues[$vid];
  $res = $db->query("SELECT * FROM Venues WHERE VenueId=$vid");
  if ($res) {
    $ans = $res->fetch_assoc();
    $Venues[$vid] = $ans;
    return $ans;
  }
  return 0;
}

function Put_Venue(&$now) {
  $v=$now['VenueId'];
  $Cur = Get_Venue($v);
  Update_db('Venues',$Cur,$now);
}

function Get_VenueYear($vid,$y=0) {
  global $db,$YEAR;
  if (!$y) $y = $YEAR;
  $res = $db->query("SELECT * FROM VenueYear WHERE VenueId=$vid AND Year='$y'");
  if ($res) {
    $vy = $res->fetch_assoc();
    return $vy;
  }
}

function Put_VenueYear(&$now) {
  $Cur = Get_VenueYear($now['VenueId'],$now['Year']);
  Update_db('VenueYear',$Cur,$now);
}

function Get_VenueYears($y=0) {
  global $db,$YEAR;
  if (!$y) $y = $YEAR;
  $res = $db->query("SELECT * FROM VenueYear WHERE Year='$y' ORDER BY VenueId");
  $VenY = [];
  if ($res) {
    while ($vy = $res->fetch_assoc()) $VenY[$vy['VenueId']] = $vy;
  }
  return $VenY;
}

function Set_Event_Help() {
  static $t = array(
        'Start'=>'It is recommended to use 24hr clock for all times eg 1030, 1330.  But it can handle most formats',
        'Sides'=>'Do not use this tool for dance programming use the tool under Dance, once the events have been created',
        'SN'=>'Needed for now, need not be unique',
        'Type'=>'Broad event category, if in doubt ask Richard',
        'Description'=>'Brief description of event for website and programme book, max 150 chars.  Recommended for Workshops and particpartory events.',
        'Blurb'=>'Longer blurb if wanted, that will follow the description when this particular events is being looked at online',
        'Setup'=>'IF the event has setup prior to the start time, set it here in minutes to block out the venue',
        'Duration'=>'Duration in minutes of the event, this will normally be calculated from the End time',
        'BigEvent'=>'For large events needing more than 4 participants eg a procession/parade and/or use more than one venue
Set No Order to prevent the order in the event being meaningful.
Set Use Notes to fmt to use the Big Event programming Notes to describe types of performers',
        'IgnoreClash'=>'Ignore two events at same time and surpress gap checking',
        'Public'=>'Controls public visibility of Event, "Not Yet" and "Never" are handled the same',
        'ExcludeCount'=>'For Big Events - if set exclude this event from Dance Spot counts - eg Procession',
        'Price'=>'Needs to be coordinated between here and Ticketing - Do NOT set these, let Richard do it.
Price is for entire event - there are no prices for sub events - negative prices have special meanings -1 = museum',
        'Venue'=>'If the Venue you need is not here ask Richard.  For Big Events - put the starting Venue here',
        'SlotEnd'=>'If a large event is divided into a number of slots, this is the end of the first slot, not needed otherwise',
        'NonFest'=>'Event not run by the Festival, but included in programme - only for friendly non fesival events',
        'Family'=>'Also list as a family event',
        'Special'=>'Also list as a Special event',
        'LongEvent'=>'Enable event to ran over many days',
        'Owner'=>'Who created the event, editable by this person, the Alt Edit and any with global edit rights',
        'Owner2'=>'This person is also allowed to edit this event',
        'Importance'=>'Affects appearance of event on home page - not used',
        'NoPart'=>'Set if the event has no particpants (Sides, Acts or Other)',
        'Image'=>'These are all for handling weird cases only',
        'Status'=>'Only mark as cancelled to have it appear in online lists and say cancelled, otherwise just delete',
        'Budget'=>'What part of the festival budget this Event comes under',
        'DoorsOpen'=>'If significantly before Start',
        'NeedSteward'=>'Most ticketed events (unless managed by third parties) and a few others will need stewards',
        'InvisiblePart' => 'Not currently used',
        'Bar'=>'Does the venue have a bar?',
        'Food'=>'Does the venue serve food?',
        'BarFoodText'=>'Any text that expands on the food and drink available',
        'StewardTasks'=>'Use this to elaborate on the Stewarding requirements for the event',
        'SetupTasks'=>'Use this to elaborate the Setup requirements for the event',
        'StagePA'=>'IF this event needs extra PA other than identified by the performers, list it here.  E.g. a microphone for the MC',
        'ExcludePA'=>'Exclude participents in this event from PA requirements for the venue - for the procession',
        'IgnoreMultiUse'=>'Set to prevent warning that same performer has been at this location on this day',
        'ShowSubevent'=>'Set this in the rare case when a sub event should be show on top level listings',
        'IsConcert'=>'Select this if it has a ticketed entry to a whole - multi act event - used in formatting event descriptions - Not needed for event type Concert',
        'WeirdStuff'=>'Set this to have events before the start and after the end.  After setting save and reload',
        'Roll'=>'To highlight band/callers for Ceilidhs and Folk Dances and MCs for concerts',
        'SeasonTicketOnly'=>'Event is only open to people with season tickets, no non ticket admission',
        'ShowNameOnGrid'=>'Enable to put event name on the dance grid, normally ommied',
        'ListOffGrid'=>'Set to list the dance event separate from the grid or exclude totally, - weird times/venues',
        'Notes'=>'Anything you want to record - not used externally',
        'AgeRange'=>'Target audience age range - for Family/Youth events'

  );
  Set_Help_Table($t);
}

function Get_Event($eid,$new=0) {
  static $Events;
  global $db;
  if ($new == 0 && isset($Events[$eid])) return $Events[$eid];
  $res=$db->query("SELECT * FROM Events WHERE EventId=$eid");
  if ($res) {
    $ans = $res->fetch_assoc();
    $Events[$eid] = $ans;
    return $ans;
  }
  return 0;
}

function Get_Event_VT($v,$t,$d) {
  global $db,$YEAR;
  $res=$db->query("SELECT * FROM Events WHERE Year='$YEAR' AND Venue=$v AND Start=$t AND Day=$d AND Status=0");
  if ($res) return $res->fetch_assoc();
}

function Get_Event_VTs($v,$t,$d) { // As above returns many
  global $db,$YEAR;
  $res=$db->query("SELECT * FROM Events WHERE Year='$YEAR' AND Venue=$v AND Start=$t AND Day=$d AND Status=0 ORDER BY EventId");

  if (!$res) return 0;
  $evs = [];
  while($ev = $res->fetch_assoc()) $evs[] = $ev;
  return $evs;
}


function Check_4Changes(&$Cur,&$now) {
  $tdchange = 0;
  if (!isset($Cur['Day'])) return;

  if ($Cur['Day'] != $now['Day'] || $Cur['Start'] != $now['Start'] || $Cur['End'] != $now['End'] || $Cur['SlotEnd'] != $now['SlotEnd']) $tdchange = 1;
  if ($Cur['Venue'] != $now['Venue']) $tdchange = 1;

  for ($i=1;$i<=4;$i++) {
    if ($tdchange) {
      if ($Cur["Side$i"] != 0) { Contract_Changed_id($Cur["Side$i"]); }
      else if ($now["Side$i"] != 0) { Contract_Changed_id($now["Side$i"]); }
    } else if ($Cur["Side$i"] != $now["Side$i"]) {
      if ($Cur["Side$i"] != 0) { Contract_Changed_id($Cur["Side$i"]); }
      if ($now["Side$i"] != 0) { Contract_Changed_id($now["Side$i"]); }
    }
  }

// Will Probably need same code for "Other"
}

function RecordPerfEventChange($id,$Type='Perform') {
  global $PLANYEAR,$USERID;
  $Rec = Gen_Get_Cond1('PerfChanges',"SideId=$id AND Year=$PLANYEAR AND Field='Perform'");
  if (!$Rec) {
    $Rec = ['SideId'=>$id, 'Year'=>$PLANYEAR, 'syId'=>-1, 'Year'=>$PLANYEAR, 'Field'=>$Type, 'Changes'=>'','Who'=>$USERID ];
    Gen_Put('PerfChanges',$Rec);
  }
}

function RecordEventChanges(&$now,&$Cur,$new) {
  global $PLANYEAR,$USERID;
  $Fields = ['Start','SlotEnd','End','Day','SN','Side1','Side2','Side3','Side4','Type','Status','Venue','Description'];

  $Check = $TCheck = 0;
  if (isset($Cur['EventId'])) {

    foreach ($Fields as $i=>$f) if ($now[$f] != $Cur[$f]) {
      if ($i<4) $TCheck = 1;
      $Check = 1;
      $Rec = Gen_Get_Cond1('EventChanges',"( EventId=" . $now['EventId'] . " AND Field='$f' )");
      if (isset($Rec['id'])) {
        $Rec['Changes'] = $now[$f];
        $Rec['Who'] = $USERID;
        Gen_Put('EventChanges',$Rec);
      } else {
        $Rec = ['EventId'=>$now['EventId'], 'Year'=>$PLANYEAR, 'Changes'=>$now[$f], 'Field'=>$f ];
        Gen_Put('EventChanges',$Rec);
      }
    }
  } else {
    $Check = 1;
    $Rec = ['EventId'=>$now['EventId'], 'Year'=>$PLANYEAR, 'Changes'=>$now['SN'], 'Field'=>'New', 'Who'=>$USERID ];
    Gen_Put('EventChanges',$Rec);
  }

  if ($Check) {
    for($i=1;$i<5;$i++) {
      if (!isset($Cur["Side$i"]) || $now["Side$i"] != $Cur["Side$i"]) {
        if (!empty($Cur["Side$i"])) RecordPerfEventChange($Cur["Side$i"]);
        if (!empty($now["Side$i"])) RecordPerfEventChange($now["Side$i"]);
      }
    }
  }

  if ($TCheck) {
    for($i=1;$i<5;$i++) {
      if (!isset($Cur["Side$i"])) {
        if (!empty($Cur["Side$i"])) RecordPerfEventChange($Cur["Side$i"],'Times');
      }
    }
  }

}

function Put_Event(&$now,$new=0) {
  $e=$now['EventId'];
  $Cur = Get_Event($e,$new);
  if (isset($Cur['EventId'])) {
    if (Feature('RecordEventChanges')) RecordEventChanges($now,$Cur,$new);
    Update_db('Events',$Cur,$now);
  } else {
    Update_db('Events',$Cur,$now);
    if (Feature('RecordEventChanges')) RecordEventChanges($now,$Cur,$new);
  }
  Check_4Changes($Cur,$now);
}

function Get_Events_For($what,$Day) {
  global $db,$YEAR,$Day_Type;
  $evs = [];
  $xtra = ($what=='Dance'?' OR e.ListDance=1 ':($what=='Music'?' OR e.ListMusic=1':''));
  $res=$db->query("SELECT DISTINCT e.* FROM Events e, EventTypes t WHERE e.Year='$YEAR' AND Status=0 AND (( e.Type=t.ETypeNo AND t.Has$what=1) $xtra ) AND e.Day=" .
                $Day_Type[$Day] );
  if ($res) {
    while($ev = $res->fetch_assoc()) $evs[$ev['EventId']] = $ev;
    return $evs;
  }
}

function Get_All_Events_For($wnum,$All=0) {// what is not used
  global $db,$YEAR;
  $qry="SELECT DISTINCT e.* FROM Events e, BigEvent b WHERE Year='$YEAR' " . ($All?'':"AND Public<2") . " AND ( " .
                "Side1=$wnum OR Side2=$wnum OR Side3=$wnum OR Side4=$wnum" .
                " OR ( BigEvent=1 AND e.EventId=b.Event AND ( b.Type='Side' OR b.Type='Perf') AND b.Identifier=$wnum ) ) " .
                " ORDER BY Day,Start";
  $res = $db->query($qry);
  if ($res) {
    while($ev = $res->fetch_assoc()) $evs[$ev['EventId']] = $ev;
    if (isset($evs)) return $evs;
  }
  return 0;
}

function Get_All_Public_Subevents_For($Eid) {
  global $db,$YEAR,$Event_Types;
  $evs = [];
  $res=$db->query("SELECT * FROM Events WHERE SubEvent=$Eid ORDER BY Day, Start, Type");
  if ($res) {
    while($ev = $res->fetch_assoc()) {
      if (( $Event_Types[$ev['Type']]['Public']) && ($Event_Types[$ev['Type']]['State'] >= 2) && ($ev['Public'] < 2)) {
        $evs[$ev['EventId']] = $ev;
      }
    }
  }
  return $evs;
}

function Get_All_Subevents_For($Eid) {
  global $db,$YEAR,$Event_Types;
  $evs = [];
  $res=$db->query("SELECT * FROM Events WHERE SubEvent=$Eid ORDER BY Day, Start, Type");
  if ($res) {
    while($ev = $res->fetch_assoc()) {
        $evs[$ev['EventId']] = $ev;
    }
  }
  return $evs;
}

function Get_Other_Things_For($what) {
  global $db;
  $evs = array();
  $res = $db->query("SELECT * FROM BigEvent WHERE Event=$what ORDER BY EventOrder");
  if ($res) {
    while($ev = $res->fetch_assoc()) $evs[] = $ev;
  }
  return $evs;
}

function Get_BigEvent($b) {
  static $BigEvent;
  global $db;
  if (isset($BigEvent[$b])) return $BigEvent[$b];
  $res=$db->query("SELECT * FROM BigEvent WHERE BigEid=$b");
  if ($res) {
    $ans = $res->fetch_assoc();
    $BigEvent[$b] = $ans;
    return $BigEvent[$b];
  }
  return 0;
}

function Put_BigEvent($now) {
  $e=$now['BigEid'];
  $Cur = Get_BigEvent($e);
  Update_db('BigEvent',$Cur,$now);
}

function New_BigEvent(&$data) {
  Insert_Db('BigEvent',$data);
}


function &Select_All_Acts() {
  static $dummy=0;
  return $dummy;
}

function &Select_All_Other() {
  static $dummy=0;
  return $dummy;
}

function Get_Event_Type($id) {
  global $Event_Types;
  return $Event_Types[$id];
}

function Put_Event_Type(&$now) {
  $e=$now['ETypeNo'];
  $Cur = Get_Event_Type($e);
  Update_db('EventTypes',$Cur,$now);
}

$Event_Types = Get_Event_Types(1);

function Get_Event_Type_For($nam) {
  global $Event_Types;
  foreach ($Event_Types as $ET) if ($ET['SN'] == $nam) return $ET;
  return null;
}

function Event_Has_Parts($e) {
  for ($i=1;$i<5;$i++) {
    if ($e["Side$i"]) return 1;
  }
  return 0;
}

function ListLinksNew(&$Perfs,$idx,$single,$plural,$size,$mult) {
  global $PerfTypes,$PerfIdx;

  if (!isset($Perfs[$idx])) return "";
//  var_dump($Perfs);
  $ks = array_keys($Perfs[$idx]);
  $think = array();
  sort($ks);
  $things = 0;
  $ans = '';
  foreach ( array_reverse($ks) as $imp) {
    if ($imp) $ans .= "<span style='font-size:" . ($size+$imp*$mult) . "px'>";
    foreach ($Perfs[$idx][$imp] as $thing) {
      $things++;
      $ttxt = "<a href='/int/ShowPerf?id=" . $thing['SideId'] . "'>";
      $ttxt .= NoBreak($thing['SN']);
      if (isset($thing['Type']) && $thing['Type']) $ttxt .= NoBreak(" (" . $thing['Type'] . ")");
      $ttxt .= "</a>";
      $think[] = $ttxt;
    }
  }
  if ($things == 1) return $single . " " . $think[0];
  $ans = $plural . " " . $think[0];
  for ($i = 2; $i<$things; $i++) $ans .= ", " . $think[$i-1];
  return $ans . " and " . $think[$things-1];
}

// Get Participants, Order by Importance/Time, if l>0 give part links as well, if l<0 names in bold
function Get_Event_Participants($Ev,$Mode=0,$l=0,$size=12,$mult=1,$prefix='') {
  global $db,$Event_Types,$YEAR,$PerfTypes,$SHOWYEAR;

  include_once "DanceLib.php";
  include_once "MusicLib.php";
  $ans = "";
  $now = time();
  $MainEv = 0;
  $res = $db->query("SELECT * FROM Events WHERE EventId='$Ev' OR SubEvent='$Ev' ORDER BY Day, Start DESC");
  $found = array();
  $PerfCount = 0;
  if ($res) {
    $imps=[];
    $Perfs=[];
    $PerfRolls=[];
    while ($e = $res->fetch_assoc()) {
      if ($e['EventId'] == $Ev) $MainEv = $e;
      if ($e['BigEvent']) {
// TODO
      } else {


// need perfs[type][imp] and then condense to perf[type] in imporder - will be needed for Big Es as well in time
          for($i=1;$i<5;$i++) {
            if (isset($e["Side$i"])) {
              $ee = $e["Side$i"];
              if ($ee) {

                if (!isset($found[$ee]) || !$found[$ee]) {
                  $s = Get_Side($ee);
                  if (!is_array($s)) continue;
                  $sy = Get_SideYear($ee,$YEAR);
//var_dump($sy); echo "<P>";
                  if (is_array($sy)) {
                    $s = array_merge($s, $sy);
                    $s['NotComing'] = ((($s['Coming'] != 2) && ($s['YearState'] < 2)) );
                  } else $s['NotComing'] = 1;
                  if ($s && (($sy['ReleaseDate']??0) < $now) || ( Access('Committee') && $Mode)) {
                    $Imp2Use = $s['Importance'];
                    if ($s['DiffImportance']) {
                      $Imp2Use = 0;
                      foreach($PerfTypes as $pt=>$pd) if (Capability("Enable" . $pd[2])) if ($s[$pd[0]] && $Imp2Use < $s[$pd[2] . 'Importance'])
                        $Imp2Use = $s[$pd[2] . 'Importance'];
                    }
                    $imps[$Imp2Use][] = $s;
                    $Perfs[$e["PerfType$i"]][$Imp2Use][] = $s;
                    $PerfRolls[$e["Roll$i"]][] = $s;
                    $PerfCount++;
                  }
                  $found[$ee]=1;
                }
              }
            }
          }
      }
    }

    switch ($Event_Types[$MainEv['Type']]['SN']) {
    case 'Ceilidh':

/*      if (($PerfCount < 2) || ($PerfCount == count($PrefRolls[0]))) {
        // Drop through
      } else {
        $ans .= (isset($Perfs[1])?ListLinksNew($Perfs,1,'Music by','Music by',$size,$mult):"Music to be announced");
        if (isset($Perfs[4])) $ans .= "; "   . ListLinksNew($Perfs,4,'Caller','Callers',$size,$mult);
        if (isset($Perfs[0])) $ans .= "<br>" . ListLinksNew($Perfs,0,'Dance spot by','Dance spots by',$size,$mult);
        if (isset($Perfs[2])) $ans .= "<br>" . ListLinksNew($Perfs,2,'Comedy spot by','Comedy spots by',$size,$mult);
        if (isset($Perfs[3])) $ans .= "<br>" . ListLinksNew($Perfs,3,'Entertainment spot by','Entertainment spots by',$size,$mult);
        if ($ans) $ans .= "<p>";
        break;
      }
*/

    default: // Do default treatment below
      $ks = array_keys($imps);
      sort($ks);
      $things = 0;
      foreach ( array_reverse($ks) as $imp) {
        if ($imp) $ans .= "<span style='font-size:" . ($size+$imp*$mult) . "px'>";
        foreach ($imps[$imp] as $thing) {
          if ($things++) $ans .= ", ";
          $link=0;
          if ($thing['NotComing']) {
            $ans .= "<del>" . NoBreak($thing['SN'],2) . "</del>";
          } else {
            if ($l > 0 && ($thing['Photo'] || $thing['Description'] || $thing['Blurb'] || $thing['Website'])) $link=$l;
            if ($link) {
              $ans .= "<a href='/int/ShowPerf?id=" . $thing['SideId'] . "'>";
            }
            $ans .= ($l<0?'<b>':'') . NoBreak($thing['SN'],2) . ($l<0?'</b>':'') ;
            if ($thing['IsASide'] && isset($thing['Type']) && $thing['Type']) $ans .= ' ' . NoBreak("(" . trim($thing['Type']) . ")",2);
            if ($link) $ans .= "</a>";
          }
        }
        if ($imp) $ans .= "</span>";
      }
      break;
    }
    if ($ans) return "$prefix$ans";
  }
  if ($Event_Types[$MainEv['Type']]['NoPart'] == 0 && $MainEv['NoPart']==0) return $prefix . "Details to follow";
  return "";
}

function Get_Other_Participants(&$Others,$Mode=0,$l=0,$size=12,$mult=1,$prefix='',&$Event=0) {
  global $db,$PerfTypes;
  include_once "DanceLib.php";
  if (!is_array($Others)) return;
  $now = time();
  $imps=array();
  $found = array();
  $something = 0;
  $ans = '';
  $pfx = '';
  if ($Others) foreach ($Others as $oi=>$o) {
    if (($o['Identifier']> 0) && ($o['Type'] == 'Side' || $o['Type'] == 'Act' || $o['Type'] == 'Other' || $o['Type'] == 'OtherPerf' || $o['Type'] == 'Perf')) {
      $si = $o['Identifier'];
      if (!isset($found[$si])) {
        $s = Get_Side($si);
        $sy = Get_SideYear($si); // TODO munge/merge?
        if ($pfx) { $s['ZZZZZpfx'] = $pfx; $pfx = ''; };
        if (isset($Event['UseBEnotes']) && $Event['UseBEnotes']) {
          $iimp = 0;
        } elseif (!$s['DiffImportance']) {
          $iimp = $s['Importance'];
        } else {
          $iimp = 0;
          foreach ($PerfTypes as $j=>$pd) {
            if ($s[$pd[0]] && $s[$pd[2] . "Importance"] > $iimp) $iimp = $s[$pd[2] . "Importance"];
          }
        }

        if ($s && ($sy['ReleaseDate'] < $now) || ( Access('Committee') && $Mode)) $imps[$iimp][] = $s;
        $something = 1;
        $found[$si] = 1;
      }
    }
    if ($o['Type'] == 'Note' && $Event['UseBEnotes']) $pfx = "<b>" . $o['Notes'] . "</b>: ";
  }

//var_dump($imps);
  if ($something) {
    $ks = array_keys($imps);
    sort($ks);
    $things = 0;
    foreach ( array_reverse($ks) as $imp) {
      if ($imp) $ans .= "<span style='font-size:" . ($size+$imp*$mult) . "px'>";
      foreach ($imps[$imp] as $thing) {
        $link=0;
        if (isset($thing['ZZZZZpfx'])) {
          if ($things++) $ans .= "<br>";
          $ans .= $thing['ZZZZZpfx'];
        } else {
          if ($things++) $ans .= ", ";
        }
        if ($l > 0 && ($thing['Photo'] || $thing['Description'] || $thing['Blurb'] || $thing['Website'])) $link=$l;
        if ($link) {
          if ($link ==1) {
            $ans .= "<a href='/int/ShowPerf?id=" . $thing['SideId'] . "'>";
          } else if ($Mode)  {
            $ans .= "<a href='/int/AddPerf?sidenum=" . $thing['SideId'] . "'>";
          }
        }
        $ans .= ($l<0?'<b>':'') . NoBreak($thing['SN'], 2) . ($l<0?'</b>':'') ;
        if ($thing['IsASide'] && isset($thing['Type']) && $thing['Type']) $ans .= ' ' . NoBreak("(" . trim($thing['Type']) . ")",2);
        if ($link) $ans .= "</a>";
       }
      if ($imp) $ans .= "</span>";
    }
  }
//  var_dump($prefix,$ans);
  if ($ans) return "$prefix$ans";
  return $prefix . "Details to follow";
}

function Price_Show(&$Ev,$Buy=0) {
  global $YEARDATA,$Event_Access_Type,$Event_Access_Type_Day;

  if ($Ev['Status']) return "<span class=red>Cancelled</span>";
  if ($Ev['SpecPrice']) return $Ev['SpecPrice'];

  $str = '';
  $once = 0;
  $Cpri = $Ev['Price1'];
  if ($Ev['SeasonTicketOnly']) {
    if ($Cpri) $str = " (" . Print_Pound($Cpri) . ") ";
    if (Feature("DayTicket" . $Ev['Day'])) return $Event_Access_Type_Day[$Ev['SeasonTicketOnly']] . $str;
    return $Event_Access_Type[$Ev['SeasonTicketOnly']] . $str;
  }
  if (!$Cpri) return Feature('FreeText','Free');

  if ($Buy) {
    if ($Ev['TicketCode']) {
      $str .= "<a href=" . $Ev['TicketCode'] . " target=_blank>";
    } else if ($Ev['SpecPriceLink']) {
      $str .= "<a href=" . $Ev['SpecPriceLink'] . " target=_blank>";
    } else {
      $Buy = 0;
    }
  }
  if ($YEARDATA['PriceChange1']) {
    $pc = $YEARDATA['PriceChange1'];
    $Npri = $Ev['Price2'];
    if ($Npri != $Cpri && $Npri != 0) {
      if ($pc > time()) {
        $str .= Print_Pound($Cpri) . " until " . date('j M Y',$pc);
        $once = 1;
      }
    $Cpri = $Npri;
    }
  }

  if ($YEARDATA['PriceChange2']) {
    $pc = $YEARDATA['PriceChange2'];
    $Npri = $Ev['Price3'];
    if ($Npri != $Cpri && $Npri != 0) {
      if ($pc > time()) {
        if ($str) $str .= ", then ";
        $str .= Print_Pound($Cpri) . " until " . date('j M Y',$pc);
        $once = 1;
      }
      $Cpri = $Npri;
    }
  }

  if ($Ev['DoorPrice'] && $Ev['DoorPrice'] != $Cpri) {
    if ($once) $str .= ", then ";
    $str .= Print_Pound($Cpri) . " in advance and " . Print_Pound($Ev['DoorPrice']) . " on the door";
  } else {
    if ($once) $str .= ", then ";
    $str .= Print_Pound($Cpri);
  }

  if ($Buy) $str .= "</a>";
  return $str;
}

function VenName(&$V) {
  return (!empty($V['ShortName'])?$V['ShortName']:($V['SN']??'Unknown'));
}

function DayTable($d,$Types,$xtr='',$xtra2='',$xtra3='',$ForceNew=0,$PageBreak=0) {
  global $DayLongList,$YEAR,$YEARDATA;
  static $lastday = -99;
  if (($Mismatch = ($d != $lastday)) || $ForceNew) {

    if ($lastday != -99) echo "</table></div><p>\n";
    $lastday = $d;
    echo '<div class="tablecont' . (($PageBreak && !$Mismatch)?' pagebreak':'') . '"><table class=' . DayList($d) . "tab $xtra3>";
    if ($Mismatch || ($ForceNew<2)) {
      echo "<tr><th colspan=99 $xtra2>$Types on " . FestDate($d,'L') . " $xtr</th>\n";
      return 1;
    }
  }
  return 0;
}

function &Get_Active_Venues($All=0) {
  global $db,$YEAR;

  if ($All) {

  }
  $res = $db->query("SELECT DISTINCT v.* FROM Venues v, Events e, EventTypes t WHERE " .
         "( v.VenueId=e.Venue AND (e.Public=1 OR ( e.Public=0 AND e.Type=t.ETypeNo AND t.State>1 ) AND " .
                    " e.Year='$YEAR' AND v.PartVirt=0)) OR ( v.IsVirtual=1 ) ORDER BY v.SN"); // v.IsVirtual needs to work for virt venues TODO
  if ($res) while($ven = $res->fetch_assoc()) {
    if ($ven['IsVirtual']) {
      $vid = $ven['VenueId'];
      $r2 = $db->query("SELECT t.* FROM Events e, Venues v, EventTypes t WHERE e.Venue=v.VenueId AND v.PartVirt=$vid AND " .
                    "(e.Public=1 OR ( e.Public=0 AND e.Type=t.ETypeNo AND t.State>1 )) AND e.Year='$YEAR'");
      if ($r2->num_rows == 0) continue;
    }
    $ans[] = $ven;
  }
  return $ans;
}


function Show_Prog($type,$id,$all=0,$price=0) { //mode 0 = html, 1 = text for email
    global $db,$Event_Types;
    $str = '';
    include_once("DanceLib.php");
    $Evs = Get_All_Events_For($id,$all);

    $side = Get_Side($id);
//var_dump($Evs);
    $evc=0;
    $Worst= 99;
    $EventLink = ($all?'EventAdd':'EventShow');
    $VenueLink = ($all?'AddVenue':'VenueShow');
    $Venues = Get_Real_Venues(1);
    if ($Evs) { // Show IF all or EType state > 1 or (==1 && participant)
      $With = 0;
      $Price = 0;
      foreach ($Evs as $e) {
        if ($e["BigEvent"] || ($e['IsConcert'] && $e['SubEvent']>0)) { $With = 1;  }
        for ($i = 1; $i<5;$i++) if ($e["Side$i"] && $e["Side$i"] != $id) { $With = 1; break; }
        if ($price && ( $e['Price1'] || ($e['IsConcert'] && $e['SubEvent']>0))) $Price = 1; // Maybe slightly too likely to set Price, but it probably does not matter
      }

      $UsedNotPub = 0;
      foreach ($Evs as $e) {
        $cls = ($e['Public']<2?'':' class=NotCSide ');
        if ($all || $e['Public']==1 || $Event_Types[$e['Type']]['State'] > 1 ||
          ($Event_Types[$e['Type']]['State'] == 1 && Access('Participant',$type,$id))) {
          if (!$all && ($Event_Types[$e['Type']]['Public'] == 0) && ($e['Public']!=1)  ) continue;
          $evc++;
           $Worst = min($Event_Types[$e['Type']]['State'],$Worst);
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
              case 'Perf':
                if ($O['Identifier'] == $id) {
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
            $str .= "<tr><td $cls>" . FestDate($e['Day'],'M') . "<td $cls>" . timecolon($e['Start']) . "-" . timecolon(($e['SubEvent'] < 0 ? $e['SlotEnd'] : $e['End'] )) .
                        "<td $cls><a href=/int/$EventLink?e=" . $e['EventId'] . ">" . $e['SN'] . "</a><td $cls>";
            if ($VenC) $str .= " starting from ";
            $str .= "<a href=/int/$VenueLink?v=" . $e['Venue'] . ">" . VenName($Venues[$e['Venue']]) ;
            $str .= "</a><td $cls>";
            if ($e['NoOrder']==0) {
              if ( $PrevI || $NextI) $str .= "In position $Position";
              if ($PrevI) { $str .= ", After " . SAO_Report($PrevI); };
              if ($NextI) { $str .= ", Before " . SAO_Report($NextI); };
            }
            if ($Price) $str .= "<td>" . Price_Show($e,1);
            $str .= "\n";
          } else if ($e['IsConcert'] && $e['SubEvent'] > 0) {
            // Need all other perfs, concert start & end
            $Parent = $e['SubEvent'];
            $pe = Get_Event($Parent);
            $res=$db->query("SELECT * FROM Events WHERE SubEvent=$Parent ORDER BY Day, Start");
            $with = [];
            while ($ev = $res->fetch_assoc()) {
              for ($i=1;$i<5;$i++) {
                if ($ev["Side$i"] > 0 && $ev["Side$i"] != $id) {
                  $with[] = SAO_Report($ev["Side$i"],$e["Roll$i"],$e['SubEvent']);
                }
              }
            }
            $str .= "<tr><td $cls>" . FestDate($e['Day'],'M') . "<td $cls>" . timecolon($pe['Start']) . "-" . timecolon($pe['End'] ) .
                        "<td $cls><a href=/int/$EventLink?e=$Parent>" . $pe['SN'] .
                        "</a><td $cls><a href=/int/$VenueLink?v=" . $pe['Venue'] . ">" . VenName($Venues[$pe['Venue']]) . "</a>" .
                        "<td>" . implode(', ',$with) . "<br>" . $side['SN'] . " will be performing from " . timecolon($e['Start']) . " to " . timecolon($e['End']);
            if ($Price) $str .= "<td>" . Price_Show($pe,1);

          } else { // Normal Event
            $str .= "<tr><td $cls>" . FestDate($e['Day'],'M') . "<td $cls>" . timecolon($e['Start']) . "-" . timecolon(($e['SubEvent'] < 0 ? $e['SlotEnd'] : $e['End'] )) .
                        "<td $cls><a href=/int/$EventLink?e=" . $e['EventId'] . ">" . $e['SN'] .
                        "</a><td $cls><a href=/int/$VenueLink?v=" . $e['Venue'] . ">" . VenName($Venues[$e['Venue']]) . "</a>";
            if ($With) {
              $str .= "<td $cls>";
              $withc=0;
              for ($i=1;$i<5;$i++) {
                if ($e["Side$i"] > 0 && $e["Side$i"] != $id) {
                  if ($withc++) $str .= ", ";
                  $str .= SAO_Report($e["Side$i"],$e["Roll$i"],$e['SubEvent']);
                }
              }
            }
            if ($Price) $str .= "<td>" . Price_Show($e,1);
            $str .= "\n";
          }
        } else { // Debug Code
//          echo "State: " . $Event_Types[$e['Type']]['State'] ."<p>";
        }
        if ($cls) $UsedNotPub = 1;
      }
      if ($evc) {
        $Thing = Get_Side($id);
        $Desc = ($Worst > 2)?"":'Current ';
        $str = "<h2>$Desc Programme for " . $Thing['SN'] . ":</h2>\n" . ($UsedNotPub?"<span class=NotCSide>These are not currently public<p>\n</span>":"") .
                "<div class=Scrolltable><table border class=PerfProg><tr><td>Day<td>time<td>Event<td>Venue" . ($With?'<td>With':'') . ($Price?'<td>':'') . $str;
      }
    }
    if ($evc) {
      $str .= "</table></div>\n";
    }

//var_dump($str);

  return $str;
}

function Venue_Parents(&$Vens,$vid) {
  if (empty($Vens[$vid]['PartVirt']) || ($Parent = $Vens[$vid]['PartVirt']) == 0 || $Vens[$vid]['SuppressParent']) return '';
  $Pven = Get_Venue($Parent);
  return ($Pven['SN'] . ": ");
}
