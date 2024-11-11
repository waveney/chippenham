<?php

// Handle Businesses - uses same data as Trade, but without the baggage

  include_once("fest.php");
  include_once("TradeLib.php");
  include_once("DanceLib.php");
  include_once("ProgLib.php");
//  A_Check('Staff','Biz');
  global $FESTSYS,$VERSION,$YEAR;
  dostaffhead("Business Admin",["js/dropzone.js"]);

function Show_Biz() {
  global $Trad,$Tid,$YEAR;
  Show_Trader($Tid,$Trad,$Form='Biz',$Mode=3);

  if ($Trad['IsTrader']) {
    echo "<h2>Trader Actions: </h2>";
  }
  if ($Trad['IsSponsor']) {
    echo "<h2>Sponsorships:</h2>";
    List_Spons(2);
    echo "<h2>Sponsor Actions: ";
    //<a href=Biz?T=$Tid&ACTION=SponList&Y=$YEAR>List</a>,
    echo "<a href=Biz?T=$Tid&ACTION=SponAdd>Add</a></h2>";
  }
  if ($Trad['IsSupplier']) {
    echo "<h2>Supplier Actions: </h2>";
  }
  if ($Trad['IsAdvertiser']) {
    echo "<h2>Advertiser Actions: </h2>";
  }
  if ($Trad['IsOther']) {
    echo "<h2>Other Actions: </h2>";
  }

  echo "<h2><a href=Biz?ACTION=AllSponList>Back to list of Sponsors</a></h2>\n";
}

function Spon_Header($Tid) {
  $Spon = Get_Trader($Tid);
  echo "<h1>Sponsorships of: <a href=Biz?T=$Tid&ACTION=Show>" . $Spon['SN'] . "</a></h1>\n";
}


function List_Spons($Mode=0) { //Mode 0 = One sponsor, 1 = all
  global $Trad,$Tid,$YEAR,$SponTypes,$SponStates;

  switch ($Mode) {
    case 0:
      $Spons = Gen_Get_Cond('Sponsorship',"SponsorId=$Tid");
      Spon_Header($Tid);
      break;
    case 1:
      $Spons = Gen_Get_All('Sponsorship');
      break;
    case 2:
      $Spons = Gen_Get_Cond('Sponsorship',"SponsorId=$Tid");
      break;
    }

  if ($Spons) {
    $coln = 0;
    echo "<div class=Scrolltable><table id=indextable border>\n";
    Register_AutoUpdate('Sponsorships', 0);
    echo "<thead><tr>";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
    if ($Mode) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Who</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Type</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>What</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Year</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Value</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>State</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Actions</a>\n";

    echo "</thead><tbody>";
    foreach ($Spons as $Spid=>$S) {
      echo "<tr><td>" . $S['id'];
      if ($Mode) {
        $Who = Get_Trader($S['SponsorId']);
        echo "<td><a href=Biz?ACTION=Show&T=" . $S['SponsorId'] . ">" . $Who['SN'] . "</a>";
      }
      echo "<td>" . $SponTypes[$S['ThingType']];
      switch ($SponTypes[$S['ThingType']]) {
      case 'General':
        echo "<td>General";
        break;
      case 'Venue':
        $Ven = Get_Venue($S['ThingId']);
        echo "<td><a href=AddVenue?v=" . ($S['ThingId'] ??0) . ">" . ($Ven['SN'] ?? '<span class=Err>Unknown</span>') . "</a>";
        break;
      case 'Event':
        $Ev = Get_Event($S['ThingId']);
        if ($Ev) $Ven = Get_Venue($Ev['Venue']);
        echo "<td><a href=EventAdd?e=" . $S['ThingId'] . ">" . ($Ev['SN'] ?? '<span class=Err>Unknown</span>') .
             "</a> at <a href=AddVenue?v=" . ($Ev['Venue'] ??0) . ">" . ($Ven['SN'] ?? '<span class=Err>Unknown</span>') . "</a> on " .
            ($Ev? (FestDate($Ev['Day'],'S') . " at " . timecolon($Ev['Start'])) : "<span class=Err>Unknown</span>");
        break;
      case 'Performer':
        $Perf = Get_Side($S['ThingId']);
        echo "<td><a href=AddPerf?sidenum=" . $S['ThingId'] . ">" . ( $Perf['SN']  ?? '<span class=Err>Unknown</span>') . "</a>";
        break;
      }
      echo fm_text1('',$S, 'Year',1,'','',"Year:$Spid") .
           fm_text1('', $S, 'Importance',1,'','', "Importance:$Spid") .
           "<td>" . $SponStates[$S['Status']] . "<td>";

      echo "<a href=Biz?ACTION=SponAdd&Spid=$Spid>Edit</a>, ";
      echo "<a href=Biz?ACTION=SponDel&Spid=$Spid>Remove</a>, ";
// Actions will go here
    }

    if (Access('SysAdmin')) echo "<tr><td class=NotStaff>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea><p><span id=DebugPane></span>";

    echo "</tbody></table></div>\n";

  } else {
    echo "No sponsorship records found for " . $Trad['SN'] . "<P>";
  }

  if ($Mode ==2) return;

  echo "<h2><a href=Trade?ORGS>Add Sponsor</a>, <a href=ListBiz>Back to list of Businesses</a></h2>"; // Invoice all | selected
  dotail();// TODO That is old code to add sponsor - should work
}


// State 0 = Delete, 1/2= Update, 3=New
function ReHash() {
  global $YEAR;
  $TestMode = 0;
  $Spons = Gen_Get_All('Sponsorship'); // Sponsorship
  $Sponsors = []; // The Orgs themselves
  $Sponsored = []; // Sponsor table

  foreach ($Spons as $S) {
    $Sid = $S['SponsorId'];
    if (!isset($Sponsors[$Sid])) $Sponsors[$Sid] = Get_Trader($Sid);
    if ($S['ThingType']) {
      if (isset($Sponsored[$S['ThingType']][$S['ThingId']])) {
        $Zid = $Sponsored[$S['ThingType']][$S['ThingId']];
        if ($Zid > 0) $Sponsored[$S['ThingType']][$S['ThingId']] = -$Zid;
      } else {
        $Sponsored[$S['ThingType']][$S['ThingId']] = $Sid;
      }
    }
  }

//  var_dump($Sponsored);
//  exit;
  $SpCs = Gen_Get_All('Sponsors');

//  $SpIdx = [];
  foreach($SpCs as &$SPC) {
    $Sid = $SPC['SponsorId'];
    if ($Sid) {
      $Found = isset($Sponsors[$Sid]);
      $SPC['State'] = ($Found?1:0);
      if ($Found) {
        if (isset($Sponsors[$Sid]['Cached'])) {
          $SPC['State'] = 0;
        }
        $Sponsors[$Sid]['Cached'] = 1;
      }
      continue;
    }
    foreach($Sponsors as $i=>$S) {
      if ($SPC['SN'] == $S['SN']) {
        $SPC['State'] = 2;
        $SPC['SponsorId'] = $i;
        $Sponsors[$i]['Cached'] = 1;
        if (empty($S['Logo']) && !empty($SPC['Image'])) {
          $S['Logo'] = $SPC['Image'];
          $S['IandT'] = $SPC['IandT'];
          if ($TestMode) {
            echo "Whould save trader: "; var_dump($S); echo "<p>";
          } else Put_Trader($S);
        }
        continue 2;
      }
    }
    $SPC['State'] = 0;
  }

//  echo "State of Spons..<p>"; var_dump($Spons); echo "<p>";
  foreach($Spons as $Sp=>$S) {
    if (isset($Sponsors[$S['SponsorId']]['Cached'])) continue; // Done
    $Sid = $S['SponsorId'];
    $SpRec = ['SponsorId'=>$Sid, 'SN'=>$Sponsors[$Sid]['SN'], 'Image'=>$Sponsors[$Sid]['Logo'] , 'IandT' =>$Sponsors[$Sid]['IandT'],'State'=>3];
    $SpCs[] = $SpRec;
  }

  // Now re-write the sponsors table

  foreach ($SpCs as $SP) {
    if (($SP['State'] ??0) ==0 ) {
      if ($TestMode) {
        echo "Would delete Sponsor " . $SP['id'] . "<p>";
      } else db_delete('Sponsors', $SP['id']);
    } elseif ($SP['State'] == 3) {
      if ($TestMode) {
        echo "New Sponsor "; var_dump($SP); echo "<p>";
      } else Gen_Put('Sponsors',$SP);
    } else { // The difference between 1 and 2 is handled by db_update
      $Sid = $SP['SponsorId'];
      $SP['Image'] = $Sponsors[$Sid]['Logo'];
      $SP['SN'] = $Sponsors[$Sid]['SN'];
      $SP['IandT'] = $Sponsors[$Sid]['IandT'];
      $SP['Year'] = $YEAR;
      if ($TestMode) {
        echo "Update Sponsor "; var_dump($SP); echo "<p>";
      } else Gen_Put('Sponsors',$SP);

    }
  }

  // Now go through Venues, Events and Performers

  if (isset($Sponsored[1])) { // Venues
    foreach ($Sponsored[1] as $Vid=>$who) {
      $VenY = Gen_Get_Cond1('VenueYear',"Year=$YEAR AND VenueId=$Vid");
      if ($VenY) {
        if ($VenY['SponsoredBy'] == $who) continue;
        $VenY['SponsoredBy'] = $who;
        if ($TestMode) {
          echo "Updating Venue Year: "; var_dump($VenY); echo "<p>";
        } else Gen_Put('VenueYear',$VenY);
      } else {
        $R = ['VenueId'=>$Vid,'Year'=>$YEAR, 'SponsoredBy'=>$who ];
        if ($TestMode) {
          echo "New Venue Year"; var_dump($R); echo "<p>";
        } else Gen_Put('VenueYear',$R);
      }
    }
  }

  $SVens = Gen_Get_Cond('VenueYear',"Year=$YEAR AND SponsoredBy!=0");
  if ($SVens) {
    foreach ($SVens as $VY) {
      if (!isset($Sponsored[1][$VY['VenueId']])) {
        $VY['SponsoredBy'] = 0;
        if ($TestMode) {
          echo "Would remove sponsor data from VY "; var_dump($VY); echo "<P>";
        } else Gen_Put('VenueYear',$VY);
      }
    }
  }

  if (isset($Sponsored[2])) { // Events
    foreach ($Sponsored[2] as $Eid=>$who) {
      $Ev = Gen_Get('Events',$Eid,'EventId');
      if ($Ev) {
        if ($Ev['SponsoredBy'] == $who) continue;
        $Ev['SponsoredBy'] = $who;
        if ($Ev['Year'] != $YEAR) echo "<h2 class=Orange>Warning - There are sponsors of Event <a href=EventAdd?e=$Eid>$Eid</a> - Not current year</h2>";
        if ($TestMode) {
          echo "Updating Event: "; var_dump($Ev); echo "<p>";
        } else Gen_Put('Events',$Ev,'EventId');
      } else {
        echo "<h2 class=Err>ERROR: There are sponsors of Event $Eid - no such event</h2>";
      }
    }
  }

  $Evs = Gen_Get_Cond('Events',"Year=$YEAR AND SponsoredBy!=0",'EventId');
  if ($Evs) {
    foreach ($Evs as $Ev) {
      if (!isset($Sponsored[2][$Ev['EventId']])) {
        $Ev['SponsoredBy'] = 0;
        if ($TestMode) {
          echo "Would remove sponsor data from Ev "; var_dump($Ev); echo "<P>";
        } else Gen_Put('Events',$Ev,'EventId');
      }
    }
  }

//  echo "About to check perfs";
  if (isset($Sponsored[3])) { // Performers

//    var_dump($Sponsored[3]);

    foreach ($Sponsored[3] as $Sid=>$who) {

    echo "Checking $Sid<p>";
      $SideY = Get_SideYear($Sid);
      if ($SideY && ($SideY['Coming'] == 2 || $SideY['YearState'] >=2)) {
//var_dump($who, $SideY);
        if ($SideY['SponsoredBy'] == $who) continue;
        $SideY['SponsoredBy'] = $who;

//echo "<p>Set who<p>";
//var_dump($who, $SideY);
        if ($TestMode) {
          echo "Updating Performer: "; var_dump($SideY); echo "<p>";
        } else {
//echo "<p>Putting...<p>";
          Put_SideYear($SideY);
        }
      } else {
        $Side = Get_Side($Sid);
        echo "<h2 class=Err>ERROR: There are sponsors of Performer <a href=AddPerf?id=$Sid>" . $Side['SN'] . " - not yet/no longer performing...</h2>";
      }
    }
  }

  $SideYs = Gen_Get_Cond('SideYear',"Year=$YEAR AND SponsoredBy!=0",'syId');
  if ($SideYs) {
    foreach ($SideYs as $Sy) {
      if (!isset($Sponsored[3][$Sy['SideId']])) {
        $Sy['SponsoredBy'] = 0;
        if ($TestMode) {
          echo "Would remove sponsor data from Perf "; var_dump($Sy); echo "<P>";
        } else Gen_Put('SideYear',$Sy,'syId');
      }
    }
  }

  echo "Sponsorship data updated<p>";
/*
  Read All Sponsorship data

  go through sponsors - remove unused entires, create new ones

  go through each Venue, Event, Performer that has records - if incorrect correct
  go through each sponsorship that has not yet been checked - update VEP as appropriate

*/
}

function SponConvert() {
  global $YEAR;
  $i = $_REQUEST['id'];
  $Spon = Get_Sponsor($i);
  $R = ['IsSponsor'=>1,'SN'=>$Spon['SN'],'Logo'=>$Spon['Image'],'IandT'=>$Spon['IandT'], 'Website'=>$Spon['Website']];
  $idx = Gen_Put('Trade',$R);
  $R2 = ['SponsorId'=>$idx,'Year'=>$YEAR,'ThingType'=>0];
  Gen_Put('Sponsorship',$R2);

  List_Spons(1);
}

function Add_Spon_Request($Spid=0) {
  global $SponTypes,$YEAR,$db,$PerfTypes;
  echo "<h1>Add/Edit sponsorship record</h1>\n";

  echo "<form method=post action=Biz?ACTION=SponCreate>";
  if ($Spid) {
    $S = Gen_Get('Sponsorship',$Spid);
    Register_AutoUpdate('Sponsorship',$Spid);
    $Tid = $S['SponsorId'];
    $Trad = Get_Trader($Tid);
    echo fm_hidden('Spid',$Spid);
  } else {
    $Tid = $_REQUEST['T'] ?? $_REQUEST['Tid'] ?? 0;
    $S = ['SponsorId'=>$Tid];
    if ($Tid) {
      $Trad = Get_Trader($Tid);
    } else {

      echo "Bug... What Organisation is this for...";
      var_dump($_REQUEST);
      exit;
    }
  }

  $Lists[0] = [''];
  $Venues = Get_Venues();
  $Lists[1] = $Venues;
  $Events = Gen_Get_Cond('Events',"Year=$YEAR AND SubEvent<=0  ORDER BY Day, Start",'EventId');
  $EvList = [];
  foreach($Events as $Eid=>$Ev) {
    $EvList[$Eid] = $Ev['SN'] . " at " . $Venues[$Ev['Venue']] . " starting at " . FestDate($Ev['Day'],'S') . " " . timecolon($Ev['Start']);
  }
  $Lists[2] = $EvList;

  $AllPerfs = $db->query("SELECT s.* FROM Sides s, SideYear y WHERE y.Year='$YEAR' AND s.SideId=y.SideId AND (y.coming=2 OR y.YearState>=2) ORDER BY s.SN");
  $PList = [];
  if ($AllPerfs) while ($P = $AllPerfs->fetch_assoc()) {
    $Cats = [];
    foreach ($PerfTypes as $Pcat=>$pt) if ($P[$pt[0]]) $Cats[] = $Pcat;
    $PList[$P['SideId']] = $P['SN'] . " ( " . implode(', ',$Cats) . " )";
  }

  $Lists[3] = $PList; //['Not yet written'];
//var_dump($Lists);

  echo "<table border>";
  echo "<tr>" . fm_number('Buisness Id',$S,'SponsorId') . "<tr><td>Name:<td>" . ($Trad['SN'] ?? 'Unknown') . fm_hidden('Tid',$Tid);
  echo "<tr>" . fm_text('Year',$S,'Year') . " if not current not used";
  echo "<tr>" . fm_number('Importance',$S,'Importance','',' min=-1 max=5 ') . " if negative not listed, +ve number is boost level";
  echo "<tr><td colspan=2" . fm_radio('Sponsorship Type',$SponTypes,$S,'ThingType',"onclick=PCatSel(event,'ThingType')",0);
  $i=0;
  foreach($Lists as $cat=>$dog) {
    if (!empty($dog)) {
      echo "<td id=MPC_$i " . ($cat == ($S['ThingType']  ?? 0)?'':'hidden') . ">";
      if ($i) echo " Select: " . fm_select($dog,$S,'ThingId',1,'',"Id$i") . "</td>";
    }
    $i++;
  }
 // echo "<tr>" . fm_number('Value',$S,'Importance') . "<td colspan=3>Used to sort sponsors and in Invoices (May be ommitted)";

  if (Access('SysAdmin')) {
    echo "<tr><td class=NotStaff>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea><p><span id=DebugPane></span>";
  }
  echo "</table>";
  if ($Spid == 0) echo "<input type=submit name=ACTION value=Create>";

  echo "<h2><a href=Biz?ACTION=Show&id=$Tid>Back to Sponsor</a>, <a href=Biz?ACTION=AllSponList>Back to list of Sponsors</a></h2>\n";
  dotail(); // change?
}

function Spon_Validate() {
  global $SponTypes;
  if (!isset($_REQUEST['ThingType'])) {
    echo "<h2 class=Err>Please select type of sponsorship</h2>";
    return 0;
  }
  if ($_REQUEST['ThingType'] == 0) return 1;
  if (empty($_REQUEST["Id" . $_REQUEST['ThingType']]) ) {
    echo "<h2 class=Err>Please select the " . $SponTypes[$_REQUEST['ThingType']] . " being sponsored</h2>";
    return 0;
  }
  $_REQUEST['ThingId'] = $_REQUEST["Id" . $_REQUEST['ThingType']];
  return 1;
}


/// START HERE

//  var_dump($_REQUEST);

  $Tid = ($_REQUEST['id']??$_REQUEST['T']??0);
  if ($Tid) $Trad = Get_Trader($Tid);

  if (isset($_REQUEST['ACTION'])) {
    A_Check('Staff','Biz');
    switch ($_REQUEST['ACTION']) {
      case 'Show':
        Show_Biz();
        dotail();

      case 'SponList':
        List_Spons(0);
        break;

      case 'AllSponList':
        List_Spons(1);
        break;

      case 'SponAdd':
        if (isset($_REQUEST['Spid'])) {
          // Already exists just allow edits
          Add_Spon_Request($_REQUEST['Spid']);
        } else {
          Add_Spon_Request();
        }
        break;

      case 'SponDel':
        if (isset($_REQUEST['Spid'])) {
          $Spid = $_REQUEST['Spid'];
          $S = Gen_Get('Sponsorship',$Spid);
          db_delete('Sponsorship',$Spid);
          echo "<h1>Removed Sponsorship record</h1>";
          $Tid = $S['SponsorId'];
          if ($Tid) $Trad = Get_Trader($Tid);
          Show_Biz();
          dotail();
        }
        break;

      case 'SponCancel':

        break;

      case 'SponPaid':

        break;

      case 'ReHash':
        ReHash();
        break;

      case 'Convert': // Old format to new
        SponConvert();

      case 'Create':
        if (isset($_REQUEST['Spid'])) {
          // Already exists just allow edits
          Add_Spon_Request($_REQUEST['Spid']);
        } else if (Spon_Validate()) {
          $_REQUEST['Year'] = $YEAR;
          $S=[];
          $Spid = Insert_db_post('Sponsorship',$S);
          echo "<h1>Sponsorship record created</h1>";
          Add_Spon_Request($Spid);
        } else {
          Add_Spon_Request();
        }
        break;

      default:
        break;


    }

  }

  /*
  Display basic
  sections for different types of Biz.

  actions for different types

  needs js to handle changes to isas


  */

