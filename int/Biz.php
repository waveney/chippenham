<?php

// Handle Businesses - uses same data as Trade, but without the baggage

  include_once("fest.php");
  include_once("TradeLib.php");
  include_once("ProgLib.php");
  A_Check('Staff','Biz');
  global $FESTSYS,$VERSION,$YEAR;
  dostaffhead("Business Admin");

function Show_Biz() {
  global $Trad,$Tid,$YEAR;
  Show_Trader($Tid,$Trad,$Form='Biz',$Mode=3);
  
  if ($Trad['IsTrader']) {
    echo "<h2>Trader Actions: </h2>";
  }
  if ($Trad['IsSponsor']) {
    echo "<h2>Sponsor Actions: <a href=Biz?T=$Tid&ACTION=SponList&Y=$YEAR>List</a>, <a href=Biz?T=$Tid&ACTION=SponAdd>Add</a></h2>";
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
}

function Spon_Header($Tid) {
  $Spon = Get_Trader($Tid);
  echo "<h1>Sponsorships of: <a href=Biz?T=$Tid&ACTION=Show>" . $Spon['SN'] . "</a></h1>\n";
}


function List_Spons($Mode=0) { //Mode 0 = One sponsor, 1 = all
  global $Trad,$Tid,$YEAR,$SponTypes,$SponStates;
  
  if ($Mode == 0) {
    $Spons = Gen_Get_Cond('Sponsorship',"SponsorId=$Tid");
    
    Spon_Header($Tid);    
  } else {
    $Spons = Gen_Get_All('Sponsorship');
  }
  

  if ($Spons) {
    $coln = 0;
    echo "<div class=Scrolltable><table id=indextable border>\n";
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
    foreach ($Spons as $S) {
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
        echo "<td><a href=AddVenue?v=" . $S['ThingId'] . ">" . $Ven['SN'] . "</a>";
        break;
      case 'Event':
        $Ev = Get_Event($S['ThingId']);
        $Ven = Get_Venue($Ev['Venue']);
        echo "<td><a href=EventAdd?e=" . $S['ThingId'] . ">" . $Ev['SN'] . "</a> at <a href=AddVenue?v=" . $Ev['Venue'] . ">" . $Ven['SN'] . "</a> on " .
            FestDate($Ev['Day'],'S') . " at " . timecolon($Ev['Start']);
        break;
      case 'Performer':
        $Perf = Get_Side($S['ThingId']);
        echo "<td><a href=AddPerf?sidenum=" . $S['ThingId'] . ">" . $Perf['SN'] . "</a>";
        break;
      }
      echo "<td>" . $S['Year'] . "<td>" . $S['Importance'] . "<td>" . $SponStates[$S['Status']] . "<td>";
// Actions will go here
    }
    
    echo "</tbody></table></div>\n";
  
  } else {
    echo "No sponsorship records found for " . $Tran['SN'] . "<P>";
  }
  
  echo "<h2><a href=Biz?T=$Tid&ACTION=SponAdd>Add Record</a>, <a href=ListBiz>Back to list of Businesses</a></h2>"; // Invoice all | selected
  dotail();
}


// State 0 = Delete, 1/2= Update, 3=New
function ReHash() {
  global $YEAR;
  $TestMode = 0;
  $Spons = Gen_Get_All('Sponsorship'); // Sponsorship
  $Sponsors = []; // The Orgs themselves
  $Sposored = []; // Sponsor table
  
  foreach ($Spons as $S) {
    $Sid = $S['SponsorId'];
    if (isset($Sponsors[$Sid])) {   
      continue;
    }
    $Sponsors[$Sid] = Get_Trader($Sid);
    if ($S['ThingType']) {
      if (isset($Sponsored[$S['ThingType']][$S['ThingId']])) {
        $Zid = $Sponsored[$S['ThingType']][$S['ThingId']];
        if ($Zid > 0) $Sponsored[$S['ThingType']][$S['ThingId']] = -$Zid;
      } else {
        $Sponsored[$S['ThingType']][$S['ThingId']] = $Sid;
      }
    }
  }
  
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
      $VenY = Gen_Get('VenueYear',"Year=$YEAR AND VenueId=$Vid");
      if ($VenY) {
        if ($VenY['SponsoredBy'] == $who) continue;
        $VenY['SponsoredBy'] = $who;
        if ($TestMode) {
          echo "Updating Venue Year: "; var_dump($VenY); echo "<p>";
        } else Gen_Put('VenueYear',$VenY);
      } else {
        $R = ['VenueId'=>$Vid,'Year'=>$YEAR];
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
      $Ev = Gen_Get('Events',"Year=$YEAR AND EventId=$Eid",'EventId');
      if ($Ev) {
        if ($Ev['SponsoredBy'] == $who) continue;
        $Ev['SponsoredBy'] = $who;
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
      if (!isset($Sponsored[1][$Ev['EventId']])) {
        $Ev['SponsoredBy'] = 0;
        if ($TestMode) {
          echo "Would remove sponsor data from Ev "; var_dump($Ev); echo "<P>";
        } else Gen_Put('Events',$Ev,'EventId');
      }
    } 
  }
  
  if (isset($Sponsored[3])) { // Performers
    foreach ($Sponsored[3] as $Sid=>$who) {
      $SideY = Gen_Get('SideYear',"Year=$YEAR AND SideId=$Sid",'syId');
      if ($SideY) {
        if ($SideY['SponsoredBy'] == $who) continue;
        $SideY['SponsoredBy'] = $who;
        if ($TestMode) {
          echo "Updating Performer: "; var_dump($SideY); echo "<p>";
        } else Gen_Put('SideYear',$SideY,'syId');
      } else {
        $Side = Get_Side($Sid);
        echo "<h2 class=Err>ERROR: There are sponsors of Performer <a href=AddPerf?id=$Sid>" . $Side['SN'] . " - not yet performing...</h2>";
      }
    }
  }
  
  $SideYs = Gen_Get_Cond('SideYear',"Year=$YEAR AND SponsoredBy!=0",'syId');
  if ($SideYs) {
    foreach ($SideYs as $Sy) {
      if (!isset($Sponsored[1][$Sy['SideId']])) {
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
  global $SponTypes,$YEAR;
  echo "<h1>Add/Edit sponsorship record</h1>\n";
  
  echo "<form method=post action=Biz?ACTION=SponCreate>";
  if ($Spid) {
    $S = Gen_Get('Sponsorship',$Spid);
    Register_AutoUpdate('Sponsorship',$Spid);
    $Tid = $S['SponsorId'];
    $Trad = Get_Trader($Tid);
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
  $Lists[3] = ['Not yet written'];
//var_dump($Lists);  

  echo "<table border>";
  echo "<tr>" . fm_number('Buisness Id',$S,'SponsorId') . "<td>Name:<td>" . ($Trad['SN'] ?? 'Unknown') . fm_hidden('Tid',$Tid);
  echo "<tr><td colspan=2" . fm_radio('Sponsorship Type',$SponTypes,$S,'ThingType',"onclick=PCatSel(event,'ThingType')",0);
  $i=0;
  foreach($Lists as $cat=>$dog) {
    if (!empty($dog)) {
      echo "<td id=MPC_$i " . ($cat == ($S['ThingType']  ?? 0)?'':'hidden') . "> : " . fm_select($dog,$S,'ThingId') . "</td>";
    }
    $i++;
  }
  echo "<tr>" . fm_number('Value',$S,'Importance') . "<td colspan=3>Used to sort sponsors and in Invoices";
  echo "</table>";
  if ($Spid == 0) echo "<input type=submit name=ACTION value=Create>";
  
  dotail(); // change?
}

/// START HERE

  $Tid = ($_REQUEST['id']??$_REQUEST['T']??0);
  if ($Tid) $Trad = Get_Trader($Tid);

  if (isset($_REQUEST['ACTION'])) {
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
        Add_Spon_Request();
        
        break;
              
      case 'SponEdit':

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
        if (isset($_REQUEST['Tid'])) {
          // Already exists
          Add_Spon_Request();
        }
        

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
  
  
?>

