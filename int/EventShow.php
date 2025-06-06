<?php
  include_once("fest.php");



  include_once("ProgLib.php");
  include_once("DispLib.php");
  include_once("MusicLib.php");
  include_once("DanceLib.php");
  global $YEARDATA,$Importance,$DayLongList,$YEAR,$PerfTypes,$Event_Types;
/*
  Have different formats for different types of events, concerts, ceidihs, workshop
*/

function Print_Thing($thing,$right=0) {
  global $YEAR,$SHOWYEAR,$PerfTypes;
  global $Perf_Rolls;

  echo "<div class=EventMini id=" . AlphaNumeric($thing['SN']) . ">";
  if (( $thing['Coming'] != 2) && ( $thing['YearState'] < 2) && ($YEAR >= $SHOWYEAR)) {
    echo "<a href=/int/ShowPerf?id=" . $thing['SideId'] . ">" . NoBreak($thing['SN'],3) . "</a>";
    echo " are no longer coming";
  } else {
    echo "<a href=ShowPerf?id=" . $thing['SideId'] . ">";
    if ($thing['Photo']) echo "<img class=EventMiniimg" . ($right?'right':'') . " src='" . $thing['Photo'] ."'>";
    $iimp = $thing['Importance'];
    if ($thing['DiffImportance']) {
      $iimp = 0;
      foreach($PerfTypes as $pt=>$pd) if (Capability("Enable" . $pd[2])) if ($thing[$pd[0]] && ($iimp < $thing[$pd[2] . 'Importance'])) $iimp = $thing[$pd[2] . 'Importance'];
    }
    echo "<h2 class=EventMinittl style='font-size:" . (27+ $iimp) . "px;'>";
    echo $thing['SN'];
    if (!empty($thing['Roll'])) {
      echo " (" . $Perf_Rolls[$thing['Roll']] . ")";
    } else if ($thing['IsASide'] && isset($thing['Type']) && $thing['Type']) echo " (" . $thing['Type'] . ") ";
    echo "</a></h2>";
    if ($thing['Description']) echo "<p class=EventMinitxt>" . $thing['Description'] . "</p>";
  }
  echo "</div>\n";
}

$lemons = 0;

function Print_Participants($e,$when=0,$thresh=0) {
  global $lemons,$DayLongList,$YEARDATA;
  $imps = [];
  Get_Imps($e,$imps,1,(Access('Staff')?1:0));
  $things = 0;

  if (!$imps) return;

  if ($lemons++ == 0) echo "<div class=tablecont><table class=lemontab border>\n";
  if ($imps) echo "<tr>";
  if ($when && $imps) {
    echo "<td>";
    if ($e['Start'] == $e['End']) {
      echo "Times not yet known";
    } else {
      if ($e['LongEvent']) echo "On: " . FestDate($e['Day'],'L') . "<br>\n";
      echo "From: " . timecolon($e['Start']) . "<br>";
      echo " to: " . timecolon($e['End']) . "<br>";
    }
  }
  $ks = array_keys($imps);
  sort($ks);
  $things = 0;
  foreach ( array_reverse($ks) as $imp) {
    foreach ($imps[$imp] as $thing) {
      if ($things && (($things&1) == 0)) echo "<tr><td>";
      $things++;
      echo "<td>";
      if (( $thing['Coming'] != 2) && ($thing['YearState'] < 2)) {
        echo "<a href=/int/ShowPerf?id=" . $thing['SideId'] . ">" . NoBreak($thing['SN'],3) . "</a>";
 //       var_dump($thing);
        echo " are no longer coming";
      } else {
        formatminimax($thing,'ShowPerf',$thresh); // 99 should be from Event type
      }
    }
  }
  echo "\n";
}

/* Name, Type, Where (inc address etc), From, Until, Cost (if any)
  If No Sub events Then:
    Participants + descr ordered by Importance
    If BE get participants from GET Stuff and more venues if applicable
  else if headliners give headliners
    for each time
      particpants + descr + Photo - ordered by Importance

  If it is public then this will be accessable by main site, otherwise only if you have the link - not planning on restrictions (currently)
*/

  $Eid = ( $_REQUEST['e'] ?? 0);
  if (!is_numeric($Eid)) exit("Invalid Event Number");
  $Ev = Get_Event($Eid);
  if (empty($Ev['EventId'])) {
    dohead('Unknown Event',[],1); // TODO Event specific banners
    echo "<h1>This Event is not known</h1>";
    dotail();
  }
  $YEAR = $Ev['Year'];
  $Ven = Get_Venue($Ev['Venue']);
  $ETs = Get_Event_Types(1);
  $OtherPart = $OtherVenues = $OtherNotes = [];
  $OVens = Get_Real_Venues();
  $InsideShow = Access('Committee') && isset($_REQUEST['InsideShow']);

  $Se = $Ev['SubEvent'];
  $Subs = array();
  if ($Se < 0 ) {// Has Sub Events - Treat as Root
    $Subs = ($InsideShow?Get_All_Subevents_For($Eid):Get_All_Public_Subevents_For($Eid));
//    $res=$db->query("SELECT * FROM Events WHERE SubEvent=$Eid ORDER BY Day, Start, Type");
//    while($sev = $res->fetch_assoc()) $Subs[] = $sev;
  } else if ($Se > 0) { // Is Sub Event - Find Root
    $Eid = $Se;
    $Subs = ($InsideShow?Get_All_Subevents_For($Eid):Get_All_Public_Subevents_For($Eid));
    //    $Ev = Get_Event($Eid);
//    $res=$db->query("SELECT * FROM Events WHERE SubEvent=$Eid ORDER BY Day, Start, Type");
//    while($sev = $res->fetch_assoc()) $Subs[] = $sev;
  } else if ($Ev['BigEvent']) {
    $Others = Get_Other_Things_For($Eid);
    foreach ($Others as $o) {
      switch ($o['Type']) {
        case 'Venue':
          if ($o['Identifier']) $OtherVenues[] = $o;
          break;
        case 'Act':
        case 'Perf':
        case 'Side':
        case 'Other':
          if ($o['Identifier']) $OtherPart[] = $o;
          break;
        case 'Note':
          $OtherNotes[count($OtherPart)] = $o['Notes'];
        default:
          break;
      }
    }
  }

  $xtra = '';
  if (($ETs[$Ev['Type']]['IncType']) && !strpos(strtolower($Ev['SN']),strtolower($ETs[$Ev['Type']]['SN']))) {
    $xtra = " (" . $ETs[$Ev['Type']]['SN'];
    if ($Ev['ListDance']) $xtra .= " / " . $ETs[1]['SN'];
    if ($Ev['ListMusic']) $xtra .= " / " . $ETs[14]['SN'];
    if ($Ev['ListComedy']) $xtra .= " / " . $ETs[17]['SN'];
    if ($Ev['ListWorkshop']) $xtra .= " / " . $ETs[5]['SN'];
    if ($Ev['ListSession']) $xtra .= " / " . $ETs[6]['SN'];

    $xtra .= ")";
  }
  
  $Banner = 1;
  if ($ETs[$Ev['Type']]['Banner']) $Banner = $ETs[$Ev['Type']]['Banner'];
  
  dohead($Ev['SN'] . $xtra,[],$Banner);
  $DescNotShown = true;
  if ($Ev['NonFest']) echo "This event is not run by the folk festival, but is shown here for your information.<p>\n";
  if ($Ev['Description'] && !$Ev['Blurb']) {
    echo $Ev['Description'] . "<P>";
    $DescNotShown = false;
  }

  if ($Ev['Status']) echo "<h1 class=Red>Event Cancelled</h1>";


  // On, Start, End, Durration, Price, Where
  echo "<div class=tablecont><table><tr><td>";
  if ($Ev['LongEvent']) {
    echo "Starting On:<td>" . FestDate($Ev['Day'],'L') . "\n";
    echo "<tr><td>Finishing On:<td>" . FestDate($Ev['EndDay'],'L') . "\n";
  } else {
    echo "On:<td>" . FestDate($Ev['Day'],'L') . "\n";
    if ($Ev['DoorsOpen']) echo "<tr><td>Doors Open at:<td>" . timecolon($Ev['DoorsOpen']). "\n";
    echo "<tr><td>Starting at:<td>" . timecolon($Ev['Start']) . "\n";
    echo "<tr><td>Finishing at:<td>" . timecolon($Ev['End']) . "\n";
  }
  if ($Ev['AgeRange']) echo "<tr><td>Aimed at ages:<td>" . $Ev['AgeRange'] . "<p>";
  if ($Ev['Price1'] || $Ev['SpecPrice'] ) {
    echo "<tr><td>Price:<td>";
    if ($Ev['SpecPrice']) {
      echo $Ev['SpecPrice'];
    } else {
      echo Price_Show($Ev,1);
      if (!$Ev['ExcludePass']) { echo ", or by " . Feature('SeasonName','Weekend Ticket');
        if (!$Ev['ExcludeDay'] && (($YEARDATA[$DayLongList[$Ev['Day']] . "Pass"])??0)) echo " or " . $DayLongList[$Ev['Day']] . " ticket\n";
      }
    }
    if ($Ev['TicketCode']) {
      $bl = "<a href=" . $Ev['TicketCode'] . " target=_blank>" ;
      echo " -  <strong>$bl Buy Now</a></strong>\n";
    } else if ($Ev['SpecPriceLink']) {
      echo " -  <strong><a href=" . $Ev['SpecPriceLink'] . " target=_blank>Buy Now</a></strong>\n";
    }
  } else if ($Ev['SeasonTicketOnly']) {
    echo "<tr><td>Entry by:<td>" . Price_Show($Ev); // Feature('SeasonName','Weekend Ticket') . " only";
  } else {
    echo "<tr><td>Price:<td>" . Feature('FreeText','Free');
  }
  echo "<tr><td valign=top>";
    if (isset($OtherVenues[0])) {

      echo "Starting Location:<td>" . Venue_Parents($OVens,$Ev['Venue']) . "<a href=VenueShow?v=" . $Ven['VenueId'] . ">" . VenName($Ven) . "</a>";
//      echo "<div class=floatright><a onclick=ShowDirect(" . $Ven['VenueId'] . ")>Directions</a></div>\n";
      if ($Ven['Address']) echo " - " . $Ven['Address'] . $Ven['PostCode'] ."\n";
      if ($Ven['Description']) echo "<br>" . $Ven['Description'] . "\n";
      echo "<tr><td>Also at:<td>";
      $ct=0;
      foreach ($OtherVenues as $Ov) {
        $OVi = $Ov['Identifier'];
        if (empty($OVens[$OVi])) continue;
        if ($ct++) echo ", ";
        echo Venue_Parents($OVens,$OVi) . "<a href=VenueShow?v=$OVi>" . ($OVens[$OVi]??"Not In Use") . "</a>";
      }
    } else if ($Ven['VenueId']) {
      echo "Where:<td width=750>" . Venue_Parents($OVens, $Ven['VenueId']) . "<a href=VenueShow?v=" . $Ven['VenueId'] . ">" . VenName($Ven) . "</a>";
//      echo "<div class=floatright><a onclick=ShowDirect(" . $Ven['VenueId'] . ")>Directions</a></div>\n";
      if ($Ven['Address']) echo " - " . $Ven['Address'] . $Ven['PostCode'] ."\n";
      if ($Ven['Description']) echo "<br>" . $Ven['Description'] . "\n";
      if (($Vy = Get_VenueYear($Ven['VenueId']))) {
        $VenY = array_merge($Ven,$Vy);
        SponsoredBy($VenY, VenName($Ven), 1, $Ven['VenueId'],75);
      }
    } else {
      echo "Where: <b>Not Yet Known</b><p>\n";
    }

  if ($Ven['Bar'] || $Ev['Bar'] || $Ven['Food'] || $Ev['Food'] || $Ven['BarFoodText'] || $Ev['BarFoodText']) {
    echo "<tr><td>&nbsp;<tr><td>";
    if ($Ven['Bar'] || $Ev['Bar']) echo "<img src=/images/icons/baricon.png width=50 title='There is a bar'> ";
    if ($Ven['Food'] || $Ev['Food']) echo "<img src=/images/icons/foodicon.jpeg width=50 title='There is Food'> ";
    if ($Ven['BarFoodText']) { echo "<td>" . $Ven['BarFoodText']; }
    else if ($Ev['BarFoodText']) { echo "<td>" . $Ev['BarFoodText']; }
  }
  echo "</table></div><p>\n";

  SponsoredBy($Ev,$Ev['SN'],2,$Eid);

  // Headlines
  if ($ETs[$Ev['Type']]['UseImp']) {
    switch ($ETs[$Ev['Type']]['SN']) {
    case 'Ceildih':
      echo Get_Event_Participants($Eid,0,0,$size=17,$mult=2); // TODO Not sure of Mode here
      break;

    default:
      // scan e + se by imp , then if any imp > 0 list them, with in page links
      $imps=array();
      $sublst = array($Ev);
      $sublst = array_merge($sublst,$Subs);
      foreach ($sublst as $e) Get_Imps($e,$imps,0);
      $HighImp = 0;
      foreach ($imps as $i=>$v) if ($i > 0 && isset($imps[$i])) $HighImp = $i;
      if ($HighImp) {
        echo "With: ";
        $with = 0;
        $ks = array_keys($imps);
        sort($ks);
        foreach(array_reverse($ks) as $i) {
          if (isset($imps[$i])) {
            foreach ($imps[$i] as $thing) {
              if ($with++) echo ", ";
              if (feature('EventWithDown')) {
                echo "<a href=#" . AlphaNumeric($thing['SN']) . " style='font-size:" . (17+$i*2) . "'>" . $thing['SN'] . "</a>";
              } else {
                echo "<a href=/int/ShowPerf?id=" . $thing['SideId'] . " style='font-size:" . (17+$i*2) . "'>" . NoBreak($thing['SN']) . "</a>";
              }
            }
          }
        }
      echo "<p>";
      }
    }
  }

  if ($Ev['Image']) echo "<img src='" . $Ev['Image'] . "'>";
  if ($Ev['Blurb']) {
    echo "<div style='width:800px;'>" . $Ev['Blurb'] . "</div><P>";
  } elseif ($Ev['Description'] && $DescNotShown) {
    echo "<div style='width:800px;'>" . $Ev['Description'] . "</div><P>";
  }
  if ($Ev['Website']) echo "<h3>" . weblink($Ev['Website'],'Website for this event') . "</h3><p>\n";

  if (!$Ev['BigEvent'] && ($Ev['IsConcert'] || ($Event_Types[$Ev['Type']]['IsConcert']) )) { // Concert Formating
    echo "<div class=tablecont><table class=lemontab border>\n";
    if (empty($ks)) $ks = array_keys($imps);
    foreach(array_reverse($ks) as $i) {
      if (isset($imps[$i])) {
        foreach ($imps[$i] as $thing) {
          echo "<tr><td>";
          Print_Thing($thing);
        }
      }
    }
    echo "</table></div>";
  } else {
    if (!$Se) {
      if ($Ev['BigEvent']) {
        if (isset($OtherPart[1])) echo "Participants" . ($Ev['NoOrder']?'':" in order") . ":<p>\n";
        echo "<div class=mini style='width:480;'>\n";
        foreach ($OtherPart as $oi=>$O) {
          if ($Ev['UseBEnotes'] && isset($OtherNotes[$oi])) echo "<b>" . $OtherNotes[$oi] . ":</b> ";
          $id = $O['Identifier'];
          $side = Get_Side($id);
          $sy = Get_SideYear($id);
          if (is_array($sy)) $side = array_merge($side,$sy);
          Print_Thing($side);
        }
        echo "</div><br clear=all>\n";
      } else {
        Print_Participants($Ev);
      }
    } else { // Sub Events
      Print_Participants($Ev,1,$ETs[$Ev['Type']]['Format']-1);
      foreach($Subs as $sub) if (Event_Has_Parts($sub) && $sub['EventId'] != $Ev['EventId']) Print_Participants($sub,1,$ETs[$Ev['Type']]['Format']-1);
    }
  }
  if ($lemons) echo "</table></div>";
  if ($Ev['LongEvent']) {
  } else if ($Se) {
    echo "<p>Ending at: " . $Ev['End'];
  }

  dotail();