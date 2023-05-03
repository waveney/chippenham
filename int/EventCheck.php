<?php
  include_once("ProgLib.php");
  include_once("DanceLib.php");
/* Read all events for each venue order by start check end of one /start of next / allow for setup (sometimes)
 * Repeat including subevents?  Or do sub events as part of main check?
 *
 * Check for don't use ifs
 */

function EventCheck($checkid=0) {
  global $db, $YEAR;
  $Venues = Get_Venues(1); // All info not just names

  $EVENT_Types = Get_Event_Types(1);

  $LastVenue = -1;
  $LastEventEmpty = 1;
  $errors = 0;
  $res=$db->query("SELECT * FROM Events WHERE Year='$YEAR' AND Status=0 ORDER BY Venue, Day, Start");
  
  $Perfs = [];
  if ($res) {
    while($ev = $res->fetch_assoc()) { // Basic Events against basic events check
      if (empty($ev['SN'])) {
        echo "The <a href=EventAdd?e=" . $ev['EventId'] . ">Event (" . $ev['id'] . ")</a>  does not have a Name.<p>";
              $errors++;
        continue;
      }        

      if (empty($ev['Venue'])) {
        echo "The <a href=EventAdd?e=" . $ev['EventId'] . ">Event (" . $ev['SN'] . ")</a>  does not have a Venue.<p>";
              $errors++;
        continue;
      }        
      if (empty($ev['Start'])) {
        echo "The <a href=EventAdd?e=" . $ev['EventId'] . ">Event (" . $ev['SN'] . ")</a>  does not have a start time.<p>";
              $errors++;
        continue;
      }        
      if (empty($ev['End'])) {
        echo "The <a href=EventAdd?e=" . $ev['EventId'] . ">Event (" . $ev['SN'] . ")</a>  does not have an end time.<p>";
              $errors++;
        continue;
      }        

      if ($ev['End']<$ev['Start']) {
        echo "The <a href=EventAdd?e=" . $ev['EventId'] . ">Event (" . $ev['SN'] . ")</a> Starts after it Ends.<p>";
              $errors++;
        continue;
      }        

        
      if ($ev['End'] == $ev['Start']) {
        echo "The <a href=EventAdd?e=" . $ev['EventId'] . ">Event (" . $ev['SN'] . ")</a> Starts and Ends at the same time.<p>";
              $errors++;
        continue;
      }        

      if ($ev['IgnoreClash']) continue;
      $ThisEventEmpty = 1;
      for ($i=1;$i<5;$i++) if ($ev["Side$i"] ) $ThisEventEmpty = 0;

      $evlist[] = $ev;
      if ($ev['Venue'] != $LastVenue) { // New Venue
        $LastVenue = $ev['Venue'];
        $LastEvent = $ev;
      } else if ($LastEvent['Day'] != $ev['Day']) { // New Day
        $LastEvent = $ev;
      } else {
        $end = $LastEvent['End'];
        if ($LastEvent['SubEvent'] < 0 ) $end = $LastEvent['SlotEnd'];
        if ($ev['Start'] == $ev['End']) continue; // Skip this at present - don't even update last
        if ($end <= timeadd($ev['Start'],-$ev['Setup'])) { // No error
        } else {
          if ($Venues[$ev['Venue']]['SetupOverlap']) {
            if ($end <= $ev['Start'] && $EVENT_Types[$LastEvent['Type']]['HasDance'] ) { // No error
            } else if ($checkid==0 || $checkid==$ev['EventId'] || $checkid==$LastEvent['EventId'] ) {
              if ($ev['SubEvent'] != $LastEvent['EventId'] ) {
                echo "The <a href=EventAdd?e=" . $ev['EventId'] . ">Event (" . $ev['SN'] . ")</a> at " . SName($Venues[$ev['Venue']]) . " starting at " .
                   $ev['Start'] . " on (A)" . DayList($ev['Day']) . " clashes with <a href=EventAdd?e=" . 
                   $LastEvent['EventId'] . ">this event (" . $LastEvent['SN'] . ")</a><p>\n";
                $errors++;
              }
            }
          } else {
            if ($ev['SubEvent'] == $LastEvent['EventId'] && $LastEventEmpty) { // No Error
            } else if ($ev['EventId'] == $LastEvent['SubEvent'] && $ThisEventEmpty) { // No Error
            } else if ($checkid==0 || $checkid==$ev['EventId'] || $checkid==$LastEvent['EventId']) {
// var_dump($ev['SubEvent'], $LastEvent['EventId'],$LastEventEmpty);           
// var_dump($ev['EventId'], $LastEvent['SubEvent'],$ThisEventEmpty);           
              if ($ev['SubEvent'] != $LastEvent['EventId'] ) {
                echo "The <a href=EventAdd?e=" . $ev['EventId'] . ">Event (" . $ev['SN'] . ")</a> at " . SName($Venues[$ev['Venue']]) . " starting at " .
                   $ev['Start'] . " on (B) " . DayList($ev['Day']) . " clashes with <a href=EventAdd?e=" . 
                   $LastEvent['EventId'] . ">this event (" . $LastEvent['SN'] . ")</a><p>\n";
                $errors++;
              }
            }
          }
        }
        $LastEvent = $ev;
      }
      $LastEventEmpty = 1;
      for ($i=1;$i<5;$i++) if ($ev["Side$i"]) $LastEventEmpty = 0;
    }   
    // Big Events...

    foreach($evlist as $e=>$ev) {
      if ($ev['BigEvent']) {
        $realstart = timereal($ev['Start']) - $ev['Setup'];
        $realend = timereal($ev['SubEvent']<0 ? $ev['SlotEnd'] : $ev['End']);
        $Other = Get_Other_Things_For($ev['EventId']);
        if ($Other) foreach($Other as $oi=>$oe) {// Big Events other venues against ordinary events
          if ($oe['Type'] == 'Venue') { 
            $cfv=$oe['Identifier'];
            foreach($evlist as $ci=>$ce) {
              if ($ce['Day'] == $ev['Day']) {
                if ($ce['Venue'] == $cfv ) {
                  $chkstart = timereal($ce['Start']) - $ce['Setup'];
                  $chkend = timereal($ce['SubEvent']<0 ? $ce['SlotEnd'] : $ce['End']);
                  if (($chkstart >= $realstart && $chkstart < $realend) || ($chkend > $realstart && $chkend <= $realend)) {
                    if ($checkid==0 || $checkid==$ev['EventId'] || $checkid==$ce['EventId']) {
                      if ($ev['SubEvent'] != $ce['EventId'] ) {
                        echo "The <a href=EventAdd?e=" . $ev['EventId'] . ">Big Event (" . $ev['SN'] . ")</a> at " . $Venues[$ce['Venue']]['SN'] . " starting at " .
                           $ev['Start'] . " on (C) " . DayList($ev['Day']) . " clashes with <a href=EventAdd?e=" . 
                           $ce['EventId'] . ">this event (" . $ce['SN'] . ")</a><p>\n";
                        $errors++;
                      }
                    }
                  }
                }
              }
            }
          }
        }
        // Now cross check other big events for other venues against other venues
        foreach ($evlist as $f=>$fv) {        
          if ($e!=$f && $fv['BigEvent'] && $ev['Day'] == $fv['Day']) {
            $chkstart = timereal($fv['Start']) - $fv['Setup'];
            $chkend = timereal($fv['SubEvent']<0 ? $fv['SlotEnd'] : $fv['End']);
            if (($chkstart >= $realstart && $chkstart <= $realend) || ($chkend >= $realstart && $chkend <= $realend)) { // Overlap now check o vens
              $COther = Get_Other_Things_For($fv['EventId']);
              foreach($COther as $icoi=>$coe) {
                  if ($coe['Type'] == 'Venue') {
                  foreach($Other as $oi=>$oe) {
                    if ($oe['Type'] == 'Venue' && $coe['Identifier'] == $oe['Identifier']) { // Clash
                      if ($checkid==0 || $checkid==$ev['EventId'] || $checkid==$ce['EventId']) {
                        if ($ev['SubEvent'] != $oe['EventId'] ) {
                          echo "The <a href=EventAdd?e=" . $ev['EventId'] . ">Big Event (" . $ev['SN'] . ")</a>  starting at " .
                             $ev['Start'] . " on (D) " . DayList($ev['Day']) . " clashes with <a href=EventAdd?e=" . 
                             $ce['EventId'] . ">this big event (" . $ce['SN'] . ")</a> on use of " . SName($Venues[$oe['Identifier']]) . "<p>\n";
                          $errors++;
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }

    foreach ($evlist as $e=>$ev) { //Check for don't use if other venue used
      if ($ev['BigEvent']) continue; // For now
      if (isset($Venues[$ev['Venue']]['DontUseIf']) && $Venues[$ev['Venue']]['DontUseIf']) {
        $block = $Venues[$ev['Venue']]['DontUseIf'];
        $realstart = timereal($ev['Start']) - $ev['Setup'];
        $realend = timereal($ev['SubEvent']<0 ? $ev['SlotEnd'] : $ev['End']);

        foreach ($evlist as $f=>$fv) {
          if ($fv['Venue'] == $block) {
            $chkstart = timereal($fv['Start']) - $fv['Setup'];
            $chkend = timereal($fv['SubEvent']<0 ? $fv['SlotEnd'] : $fv['End']);
            if (($ev['Day'] == $fv['Day']) && (($chkstart > $realstart && $chkstart < $realend) || ($chkend > $realstart && $chkend < $realend))) { // In use...
              if ($checkid==0 || $checkid==$ev['EventId'] || $checkid==$fv['EventId']) {
                echo "The <a href=EventAdd?e=" . $ev['EventId'] . ">Event</a> is at " . SName($Venues[$ev['Venue']]) . " when " . 
                      SName($Venues[$fv['Venue']]) . " is being used for <a href=EventAdd?e=" . $fv['EventId'] . ">This Event</a>.<p>\n";
                $errors++;
              }
            }
          }
        }
      }
    }
    
    // Check Performer clashes
    // perfs[SideId][events[eid,day,start,end,ignoreC]]
    
    foreach ($evlist as $e=>$ev) { 
      if ($ev['BigEvent']) {
      } else {
        for($i=1;$i<5;$i++) {
          if (($sid = $ev["Side$i"]) != 0) {
            $day = $ev['Day'];
            $estrt = timereal($ev['Start']) - $ev['Setup'];
            $eend = timereal($ev['SubEvent']<0 ? $ev['SlotEnd'] : $ev['End']);
            
            if (isset($perfs[$sid])) {
              foreach($perfs[$sid] as $pd) {
                if (($pd[1] == $day) && !$pd[4] && !$ev['IgnoreClash']) {
                  if ((($pd[2] > $estrt) && (($pd[3] > $estrt) || (($pd[3] == $estrt) && ($ev['Venue'] != $evlist[$pd[0]]['Venue'])))) ||
                       (($pd[2] <= $estrt) && (($eend > $pd[2]) || (($eend == $pd[2]) && ($ev['Venue'] != $evlist[$pd[0]]['Venue']))))) {
                    $Side = Get_Side($sid);
                    echo "<a href=AddPerf?sidenum=$sid>" . $Side['SN'] . " has an Event clash between <a href=EventAdd?e=" . $ev['EventId'] . ">This Event</a>" .
                         " and <a href=EventAdd?e=" . $evlist[$pd]['EventId'] . ">This Event</a>.<p>\n";
                  }
                }
              }
              $perfs[$sid][] = [$e,$day,$estrt,$eend,$ev['IgnoreClash']];
            } else {
              $perfs[$sid] = [[$e,$day,$estrt,$eend,$ev['IgnoreClash']]];
            }
          }
        }
      }
    }
    if ($errors == 0 && $checkid == 0) echo "No errors found<p>\n";
  } else {
    echo "No events have been found...<p>\n";
  }
}      
?>
