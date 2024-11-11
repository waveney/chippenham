<?php

function Prog_Headers($Public='',$headers=1,$What='Dance') {
  if ($Public && $headers) {
    dohead("$What Programme", ["js/tableHeadFixer.js","js/NewDanceProg.jsdefer", "css/festconstyle.css" ]);
  } else {
    dominimalhead("$What Programme", ["js/tableHeadFixer.js","js/NewDanceProg.jsdefer", "css/festconstyle.css"] );
  }

  include_once("DanceLib.php");
  include_once("MusicLib.php");
  include_once("ProgLib.php");
  ini_set('display_errors', '0');
}

function Grab_Data($day='',$Media='Dance',$Paper=0) {
  global $DAY,$Times,$Back_Times,$lineLimit,$Sides,$SideCounts,$EV,$VenueUse,$evs,$Sand,$Earliest,$Latest,$OffGrid,$VenueInfo,$Venues,$VenueNames;

//  $cats = ['Side','Act','Comedy','Ch Ent','Other'];
  $Times = array();
  $lineLimit = array();
  $SideCounts = array();
  $Earliest = 2400;  $Latest = 0;
  $EV = array();
  $Venues = Get_Venues_For($Media);
  $VenueNames = Get_Real_Venues(0);
  $VenueInfo = Get_Real_Venues(1);
  $VenueUse = array();
  $evs = array();
  $Sand = 0;
  $UsedTimes = [];
  $OffGrid = [];
  if (isset($_REQUEST['SAND'])) $Sand = 1;

  if ($day) { $DAY=$day;
  } else if (isset($_REQUEST['d'])) { $DAY = $_REQUEST['d']; } else { $DAY='Sat'; }

  if (!isset($_REQUEST['EInfo'])) $_REQUEST['EInfo'] = 0;
/*
  for ($t=9;$t<($Media=='Dance'?18:24);$t++) { // TODO fix for non 30 minute slots TODO Start and end from Master
    $Times[] = $t*100;
    if ($Media != 'Dance') $Times[] = $t*100+15;
    $Times[] = $t*100+30;
    if ($Media != 'Dance') $Times[] = $t*100+45;
  }

  $Back_Times = array_reverse($Times);
*/
  if ($Media == 'Dance' && (Feature('DanceDefaultSlot') == 30)) {
    $Round = 30;
    $DefLineLimit = 2;
  } else {
    $Round = 15;
    $DefLineLimit = (($Media == 'Dance')?2:1);
  }

  if ($Media == 'Dance') {
    $Sides = Select_Come_Day($DAY);
    $DefLineLim = Feature('DanceDefaultPerSlot',2);
  } else {
    $Sides = Select_Act_Come($DAY);
    $DefLineLim = Feature('MusicDefaultPerSlot',1);
  }

  if ($Sides) foreach ($Sides as $i=>$s) { $SideCounts[$i]=0; }
  foreach ($Times as $t) $lineLimit[$t]=$DefLineLim;

  $evs = Get_Events_For($Media,$DAY);
//var_dump($evs);
  if ($evs) foreach ($evs as $ei=>$ev) {
    if ($ev['ListOffGrid']) {
      $OffGrid[] = $ev;
      continue;
    }
    $eid = $ev['EventId'];
    $v = $ev['Venue'];

    if ($Paper && $VenueInfo[$v]['DanceOffGridPaper']) {
      $OffGrid[] = $ev;
      continue;
    }

    if ($ev['SubEvent'] < 0) { $et = $ev['SlotEnd']; } else { $et = $ev['End']; }
    if ($et == 0 || $ev['Start']==0) continue; // Skip events with undefined times
    $UsedTimes[]= $ev['Start'];
    $UsedTimes[]= $et;

    $duration = timereal($et) - timereal($ev['Start']);
    $t = $ev['Start']; //timeround($ev['Start'],$Round); FUDGE 2023

    $EV[$v][$t]['e'] = $ei;
    $EV[$v][$t]['d'] = $duration;

    $plim=4;
    if ($ev['Type'] != 1 || $ev['ShowNameOnGrid']) { //$ev['SN'] && $ev['SN'] != 'Dancing') {
      $EV[$v][$t]['n'] = $ev['SN'];
      $plim =3;
    }


    $lineLimit[$t] = (isset($lineLimit[$t]) ? max(2,$lineLimit[$t]) : 2); // Min value

    if (!$ev['BigEvent']) {
      $VenueUse[$v] = 1;

/* This condenses sides and acts and others into grid - when you want to handle non-sides dpupdate only works for sides now */
      $parts=0;
//      foreach ($cats as $kit) {
        for($i=1;$i<5;$i++) {
          if ($ev["Side$i"]) {
            if ($parts++ <= $plim) {
              $lineLimit[$t] = max($lineLimit[$t],$parts + (isset($EV[$v][$t]['n']) ?1:0));
              $EV[$v][$t]["S$parts"] = $ev["Side$i"];
            } else {
              $EV[$v][$t]["S4"] = -1;
            }
          }
        }
//      }
      if ($parts) {
//        if ($Latest < $et) echo "Found latest as $eid at $et in $v<p>";
        $Earliest = min($ev['Start'],$Earliest);
        $Latest = max(timeadd2($ev['Start'],30),$Latest);
      }
    } else { //BE
      // $VenueUse[$v] = 1; Not marking venue (or other venues) used for Big Events
      $Other = Get_Other_Things_For($eid);
      $bes = $bev = array();
      foreach($Other as $i=>$o) {
        if ($o['Identifier'] == 0) continue;
        if ($o['Type'] == 'Venue') $bev[] = $o['Identifier'];
        if ($o['Type'] == 'Side' || $o['Type'] == 'Perf' || $o['Type'] == 'Act' || $o['Type'] == 'Other' ) $bes[] = $o['Identifier'];
        if (!$ev['ExcludeCount']) if ($o['Type'] == 'Side') $SideCounts[$o['Identifier']]++;
      }

      foreach ($bev as $vi=>$ov) {
        $EV[$ov][$t]['e'] = $ei;
        $EV[$ov][$t]['d'] = $duration;
        if (isset($EV[$v][$t]['n'])) $EV[$ov][$t]['n'] = $EV[$v][$t]['n'];
        if (count($bes) < ($DefLineLim + 1)) {
          $parts=0;
          foreach($bes as $si=>$s) {
            if ($parts++ <= $plim) {
              $lineLimit[$t] = max($lineLimit[$t],$parts);
              $EV[$ov][$t]["S$parts"] = $s;
            } else {
              $EV[$ov][$t]["S4"] = -1;
            }
          }
        }
      }

      if (count($bes) < ($DefLineLim + 1)) {
        $parts=0;
        foreach($bes as $si=>$s) {
          if ($parts++ <= $plim) {
            $lineLimit[$t] = max($lineLimit[$t],$parts);
            $EV[$v][$t]["S$parts"] = $s;
          } else {
            $EV[$v][$t]["S4"] = -1;
          }
        }
      }

    }
  }
  $Times = array_unique($UsedTimes);
  asort($Times);
  $Back_Times = array_reverse($Times);
// var_dump($UsedTimes,$Times); exit;

//  var_dump($lineLimit);exit;
}

/*
  1) Events more than half an hour - afects #2 - done for scan
  2) Number of Other locations needed - done
  3) Events with titles
*/

function Scan_Data($condense=0,$Media='Dance') {
  global $DAY,$Times,$Back_Times,$lineLimit,$EV,$Sides,$SideCounts,$VenueUse,$evs,$MaxOther,$VenueInfo,
         $Venues,$VenueNames,$OtherLocs,$SlotSize,$OffGrid;

  if ($Media == 'Dance' && (Feature('DanceDefaultSlot') == 30)) {
    $Round = 30;
    $DefLineLimit = 2;
  } else {
    $Round = 15;
    $DefLineLimit = (($Media == 'Dance')?2:1);
  }

  $OtherLocs = array();
  $OtherLocUse = [];

  if ($Venues) foreach ($Venues as $v) if (isset($VenueUse[$v]) && $condense && $VenueInfo[$v]["Minor$DAY"]) $OtherLocs[] = $v;

  $MaxOther = 0;

  if ($condense) {
    foreach ($Times as $time) {
      $ThisO = 0;
      for ($i = 0; $i <10; $i++) if (isset($OtherLocUse[$i]['t']) && $OtherLocUse[$i]['t']>0) {
        $OtherLocUse[$i]['t']--;
        if ($OtherLocUse[$i]['t']) $ThisO++;
      }
      foreach($OtherLocs as $v) {
        if (isset($EV[$v][$time]['e'])) {
          if ($evs[$EV[$v][$time]['e']]['BigEvent']) continue;
          $inuse = 0;
          for ($i=1;$i<5;$i++) if (isset($EV[$v][$time]["S$i"]) && $EV[$v][$time]["S$i"] ) $inuse = 1;
          if ($inuse) {
            $ThisO++;
            if (0 && $EV[$v][$time]['d'] != $Round) { // Fudge for 2023
              $slots = intval(ceil(timereal($EV[$v][$time]['d'])/$Round));
              $i=0;
              while(isset($OtherLocUse[$i]['t']) && $OtherLocUse[$i]['t']>0) $i++;
              $OtherLocUse[$i]['t'] = $slots;
              $OtherLocUse[$i]['v'] = $v;
            }
          }
        }
      }
      $MaxOther= max($MaxOther, $ThisO);
    }
  }

}

/* Advanced Griding plans:
  Build data into large array then when all done
  work down each venue in turn
  scan the data to produce the output
  Will know things that cross grid boundry
  special colours for non standard events

  Grid [times][venues][Name, Dur, with0-3]
  grid[v][t][d: duration, n:name, err:error, w:wrap, s1..4:participants, e:evnt, h:hide]
*/
// Creates Raw Grid
function Create_Grid($condense=0,$Media='Dance') {
  global $DAY,$Times,$Back_Times,$grid,$lineLimit,$EV,$Sides,$SideCounts,$VenueUse,$evs,$OffGrid,$MaxOther,$VenueInfo,$Venues,$VenueNames,$OtherLocs,$Sand,$VenueList,$SlotSize;

  if ($Media == 'Dance' && (Feature('DanceDefaultSlot') == 30)) {
    $Round = 30;
    $DefLineLimit = 2;
  } else {
    $Round = 15;
    $DefLineLimit = (($Media == 'Dance')?2:1);
  }

  $ForwardUse = array();
  $grid = array();
  $VenueList = array();
  $AllTimes = Feature('AllDanceTimes');

  if ($Venues) foreach ($Venues as $v) {
    if (!isset($VenueUse[$v])) continue;
    $ETime = 0;
    $STime = 0;
    $RowSets = 0;
    foreach ($Times as $t) {
      if (isset($EV[$v][$t]['e'])) {
        $ev = &$EV[$v][$t];
      } else {
        $ev = 0;
      }

      if (!empty($ForwardUse[$v]) || $ETime) {
        if ($ev) {
          // find original forward point and mark overlap
          foreach($Back_Times as $bt) if (($bt < $t) && !empty($grid[$v][$t]) && ($grid[$v][$t]['c'] > 1)) { $grid[$v][$t]['err'] = 1; break; };
        }
        $ForwardUse[$v] = max(0,$ForwardUse[$v]-$Round);
        if ($ETime > $t) {
          $grid[$v][$t]['h'] = 1;
          $RowSets++;
          continue;
        }

        if ($STime) $grid[$v][$STime]['r'] = $RowSets;
        $ETime = $STime = $RowSets = 0;
      }

      if (!$ev) {
        // No action I think
      } else {
        if ($AllTimes || $ev['d'] > $Round) { // Blockout ahead and wrap this event
          $grid[$v][$t]['d'] = $ev['d'];
          $ForwardUse[$v] = $ev['d'] - $Round; // Wrong
          $ETime = timeadd($t,$ev['d']);
          $STime = $t;
        }
        $grid[$v][$t]['e'] = $ev['e'];
        if (!empty($ev['n'])) $grid[$v][$t]['n'] = $ev['n'];

        $things = 0;
        for ($i=1;$i<5;$i++) {
          if (!empty($ev["S$i"])) {
            $grid[$v][$t]["S$i"] = $ev["S$i"];
            $things++;
          }
        }

        $s = (empty($ev['S1'])?0:$ev['S1']);
        if ($s && !empty($Sides[$s]) && $Sides[$s]['Share'] == 2 && $things==1) $grid[$v][$t]['w'] = 1; // Set Wrap if no share
      }
    }

    if ($ETime && $STime) $grid[$v][$STime]['r'] = $RowSets;
  }

  if ($Venues) foreach ($Venues as $v) if (isset($VenueUse[$v])) $VenueList[] = $v;
  if ($condense)  for($i=1; $i<=$MaxOther; $i++)  $VenueList[] = -$i;
}

// Creates Raw Grid
function Create_New_Grid($condense=0,$Media='Dance',$drag) {
  global $DAY,$Times,$Back_Times,$grid,$lineLimit,$EV,$Sides,$SideCounts,$VenueUse,$evs,$OffGrid,$MaxOther,$VenueInfo,$Venues,$VenueNames,$OtherLocs,$Sand,$VenueList,$SlotSize;
  global $Earliest,$Latest,$SlotSize,$OffGrid,$pgrid,$pshow,$DTimes,$STimes;


  if ($Media == 'Dance' && (Feature('DanceDefaultSlot') == 30)) {
    $Round = 30;
    $DefLineLimit = 2;
  } else {
    $Round = 15;
    $DefLineLimit = (($Media == 'Dance')?2:1);
  }

  $ForwardUse = array(); //??
  $pgrid = [];
  $VenueList = array();
  $AllTimes = Feature('AllDanceTimes');

  // at each grid time have 5 rows
  $STimes= [];

  $DGSlt = Feature('DanceGridSlot',30);
  $End = array_slice($Times,-1) + $DGSlt;
  for($T = $Times[0]; $T <= $End; $T += $DGSlt) {
    $STimes[] = $T;
    $STimes[] = -1;
    $STimes[] = -2;
    $STimes[] = -3;
    $STimes[] = -4;
  }

  foreach ($STimes as $T) $pshow[$T] = 0;
  $RowSet = ($AllTimes?5:4);


  if ($Venues) foreach ($Venues as $v) {
    if (!isset($VenueUse[$v])) continue;
    $Sti = 0; $EFt = $STimes[0];
    foreach ($Times as $t) {
      if (isset($EV[$v][$t]['e'])) {
        $ev = &$EV[$v][$t];
      } else {
        continue;
      }

      $Repeat = 0;
      do {
        $Repeat = 0;
        $EFt = $STimes[$Sti];
        $NxtSti = ($STimes[$Sti]>0? $Sti+$RowSet:$Sti + $STimes[$Sti] + RowSet);
        $NxtEFt = $STimes[$NxtSti];
        if ($t == $EFt) { // We are already at the right spot
        } elseif ($t == $NxtEFt) {
          $Sti = $NxtSti;
        } elseif ($t > $NxtEFt) {
          $Sti = $NxtSti;
          $Repeat = 1;
        } elseif ($STimes[$Sti] >0) $Sti++;
      } while ($Repeat);

      $pgrid[$v][$Sti]['e'] = $ev['e'];
      $pgrid[$v][$Sti]['d'] = ($ev['d']??$DGSlt);
      if (!empty($ev['n'])) $pgrid[$v][$Sti]['n'] = $ev['n'];
      $Ost = $Sti;

      $pshow[$Sti++] = 1;
      if ($drag) $pshow[$Sti+1] = $pshow[$Sti+2] = 1;
      $things = 0;
      for ($i=1;$i<5;$i++) {
        if (isset($ev["S$i"])) {
          $pgrid[$v][$Sti]['s'] = $ev["S$i"];
          $pshow[$Sti++] = 1;
          $things ++;
        }
      }

      /*
      if ($ev['d'] > $DGSlt) { // Not needed I think
        $dur = $ev['d'];
      }*/

      $s = (empty($ev['S1'])?0:$ev['S1']);
      if ($s && !empty($Sides[$s]) && $Sides[$s]['Share'] == 2 && $things==1) $pgrid[$v][$Ost]['w'] = 1; // Set Wrap if no share

    }
  }

  if ($Venues) foreach ($Venues as $v) if (isset($VenueUse[$v])) $VenueList[] = $v;
  if ($condense)  for($i=1; $i<=$MaxOther; $i++)  $VenueList[] = -$i; // Not working on re-write (YET)
}


function Test_Dump($format='') { // far far from complete
  global $DAY,$Times,$Back_Times,$grid,$lineLimit,$EV,$Sides,$SideCounts,$VenueUse,$evs,$MaxOther,$VenueInfo,$Venues,$VenueNames,$OtherLocs,$Sand,
     $SlotSize,$OffGrid;

  echo "<div class=GridWrapper$format><div class=GridContainer$format>";
  echo "<table border id=Grid><thead><tr><th id=DayId width=60>$DAY";
  foreach ($Venues as $v) if (isset($VenueUse[$v])) echo "<th class=DPGridTH id=Ven$v>" . $VenueNames[$v];

  foreach ($Times as $t) {
   }
}

/* New grid format id =G:v:t,l data-d=s  SideLIst ids w
  L = Letter (N=Name,B=Blank,S=Side)
  v = venue
  t = time
  e = event
  s = side
  d = duration
  w = wrap
  ? = special
  h = Hide n rows
*/

function Print_New_Grid($drag=1,$types=1,$condense=0,$Links=1,$format='',$Media='Dance',$Public=0) {
  global $DAY,$Times,$Back_Times,$grid,$lineLimit,$EV,$Sides,$SideCounts,$VenueUse,$evs,$MaxOther,$VenueInfo,$Venues,$VenueNames,$OtherLocs,$Sand,$VenueList;
  global $Earliest,$Latest,$SlotSize,$OffGrid,$pgrid,$pshow,$DTimes,$STimes;

  $links = $condense && !$types && $Links;
//var_dump($links, $condense, $types ,$Links);
  if ($Media == 'Dance' && (Feature('DanceDefaultSlot') == 30)) {
    $Round = 30;
    $DefLineLimit = 2;
  } else {
    $Round = 15;
    $DefLineLimit = (($Media == 'Dance')?2:1);
  }

  $All_Times = Feature('AllDanceTimes');
  $RowSet = ($All_Times?5:4);
  $StartLine = 0;
  $DRAG = ($drag)?"draggable=true ondragstart=drag(event) ondrop=drop(event,$Sand) ondragover=allow(event)":"";
  $WDRAG = ($drag)?"ondrop=drop(event,$Sand) ondragover=allow(event)":"";

  // Start the grid - Headings
  echo "<div class=GridWrapper$format><div class=GridContainer$format>";
  echo "<table border id=Grid><thead><tr><th id=DayId width=60 class=ProgDayHL>$DAY";
  $OtherInUse = array();
  $Cev = $Strt = $Snum = $RunT = [];

  foreach ($VenueList as $v) if ($v > 0) {
    $Cev[$v] = $Strt[$v] = $Snum[$v] = $RunT[$v] = 0;

    if ($condense && $VenueInfo[$v]["Minor$DAY"]) {
    } else {
      echo "<th class=DPGridTH id=Ven$v>";
      if ($links) echo "<a href=/int/VenueShow?v=$v>";
      echo $VenueNames[$v];
      if ($links) echo "</a>";
    }
  } else {
    echo "<th class=DPGridTH id=OLoc$v>Other Location<th class=DPGridTH id=OWhat$v>What";
  }
  echo "</tr></thead><tbody>";


  foreach ($STimes as $sti=>$t) {
    if ($condense && ($t < $Earliest || $t >= $Latest)) continue;
    if ($t>0) {
      echo "<tr><th rowspan=$RowSet width=60 valign=top id=RowTime$sti>" . sprintf('%04d',$t);
      if ($drag && (($t+$RowSet)> 0) ) {
        echo "<button class=botx onclick=UnhideARow($t) id=AddRow$sti>+</button>";
      }
    } else {
      echo "<tr>";
    }

    $line = 0; // BODGE BODGE BODGE
    $OtherLoc = '';
    foreach ($VenueList as $v) {
      $G = &$pgrid[$v][$sti];
//        var_dump($G);

// This block is WRONG for new print - no allowance for condensed YET
        if ($v > 0) { // Search oluse for free entry, mark as used for n slots - at end of time loop decrement any used
          if ($condense && $VenueInfo[$v]["Minor$DAY"]) {
            if ($evs[$G['e']]['BigEvent']) continue;
            if ($G && $line == 0 && ($G['S1'] || $G['S2'] || $G['n']) ) {
              $OtherFound = 0;
              for ($i=1; $i<= $MaxOther; $i++) if (!$OtherInUse[$i]) { $OtherFound=$i; break; }
              if ($OtherFound) {
                $OtherInUse[$OtherFound] = max(1,intval(ceil($G['d']/30)));
                $grid[-$OtherFound][$t] = $G;
              } else {
                // Run out of Others - need to report something
                echo "<span class=Err>RUN OUT OF OTHERS</span>";
              }
            }
            continue;
          }
        } else { // Generate other loc infoZZ
          if ($line == 0) {
            if ($OtherInUse[$v]) {
              continue;
            } else if ($G['S1'] || $G['S2'] || $G['n']) {
              $rows = (isset($G['r'])?($G['r']+1)*$RowSet:1); //$G['d']?intval(ceil($G['d']/30))*4:4;
              $vv = $evs[$G['e']]['Venue'];
              $OtherLoc = "<td id=XX data-d=X rowspan=$rows class=DPOvName>" ;
              if ($links) $OtherLoc .= "<a href=/int/VenueShow?v=$vv>";
              $OtherLoc .= $VenueNames[$vv];
              if ($links) $OtherLoc .= "</a>";
            } else {
              $OtherLoc = "<td id=XXX data-d=X rowspan=4>&nbsp";
            }
          }
        }



        $id = "G:$v:$Strt:$sti"; // Note the ids will be meaningless in condensed mode, but as they will should not be used, so not a problem
        $class = 'DPGridDisp';

        $dev = '';



//        if ($line < 0) echo "<span class=DPETimes>" . sprintf('%04d',$t) . " - " . timeadd($t,$G['d']) . "<br></span>";
        if ($t > 0 && $G) $dev = 'data-e=' . ($G['e']??0) . ':' . ($G['d']??0);
        if (!$G || ($v<0 && !($G['S1'] || !$G['S2'] || $G['n']))) {
          if ($v > 0 && $condense==0) $class = "DPGridGrey";
          if (!isset($lineLimit[$t]) || $line >= $lineLimit[$t]) {
            echo "$OtherLoc<td id=$id class=$class hidden $DRAG data-d=X>&nbsp;";
          } else if (!isset($OtherInUse[-$v])) {
            echo "$OtherLoc<td id=$id class=$class $DRAG data-d=X>&nbsp;";
          }
        } else if ($line >= ($lineLimit[$t]??0)) {
          echo "$OtherLoc<td hidden id=$id $DRAG $dev class=$class>&nbsp";
        } else if (!empty($G['h'])) {
          echo "$OtherLoc<td hidden id=$id $DRAG $dev class=$class>&nbsp;";
        } else if (($All_Times && ($line == 0)) || (($G['d']??0) > $Round)) {
          if ($line == 0) {
            $rows = (isset($G['r'])?($G['r']+1)*$RowSet:1); // Rounding?? *$RowSet; // intval(ceil($G['d']/$Round))*$RowSet; // WRONG
            // Need to create a wrapped event - not editble here currently
            $cls = (empty($G['n'])?'':'class=DPNamed ');
            echo "$OtherLoc<td id=$id $WDRAG $dev $cls " . (($rows > 1)?"rowspan=$rows":'') . " valign=top data-d=W>";
            if (!empty($G['n'])) {
              if ($links) echo "<a href=/int/EventShow?e=" . $G['e'] . ">";
              echo $G['n'];
              if ($links) echo "</a>";
              echo "<br>";
            }
            echo "<span class=DPETimes>" . sprintf('%04d',$t) . " - " . timeadd($t,$G['d']) . "<br></span>";
            for($i=1; $i<5;$i++) {
              if (!empty($G["S$i"])) {
                $si = $G["S$i"];
                if (!isset($Sides[$si])) {
                  $Sides[$si] = Get_Side($si);
                }
                $s = &$Sides[$si];
                $txt = SName($s) . (($types && $s['Type'])?(" (" . trim($s['Type']) . ") "):"");
                echo "<span data-d=$si class='DPESide Side$si'>";
                if ($links) echo "<a href=/int/ShowPerf?id=$si>";
                echo $txt;
                  if ($links) echo "</a>";
                echo "<br></span>";
                if (!$evs[$G['e']]['ExcludeCount'] && isset($SideCounts[$si]) ) $SideCounts[$si]++;
              }
            }
          } else {
            echo "$OtherLoc<td " . ((1 || $Public)?'hidden':'') . " id=$id $DRAG $dev class=$class>&nbsp;";
          }
        } else if ($line == 0  && !empty($G['n'])) {
//          if (Feature('AllDanceTimes')) echo "<span class=DPETimes>" . sprintf('%04d',$t) . " - " . timeadd($t,$G['d']) . "<br></span>";
          echo "$OtherLoc<td id=$id $DRAG $dev data-d='N' class=DPNamed>";
          if ($links) echo "<a href=/int/EventShow?e=" . $G['e'] . ">";
          if (!empty($G['n']) ) echo $G['n'];
          if ($links) echo "</a>";
          echo "<br>";
        } else if ($line != 0 && isset($G['w'])) {
          echo "$OtherLoc<td id=$id $DRAG $dev hidden class=$class>DD&nbsp;";
          if (isset($G['n'])) echo $G['n'];
        } else if (isset($G["S" . ($line+(isset($G['n'])?0:1))])) {
//          if (Feature('AllDanceTimes')) echo "<span class=DPETimes>" . sprintf('%04d',$t) . " - " . timeadd($t,$G['d']) . "<br></span>";
          $si = $G["S" . ($line + (isset($G['n'])?0:1))];
          if (!isset($Sides[$si])) {
            $Sidessi = Get_Side($si);
            $Sidessi['ERROR'] = 1;
            if ($Sidessi['IsASide'] == 0 ) $Sides[$si] = $Sidessi;
          }

          $s = &$Sides[$si];
          $txt = SName($s) . (($types && $s['Type'])?(" (" . trim($s['Type']) . ") "):"");
          if (!$txt || isset($s['ERROR'])) {
            if (!$condense) $txt = "<span class=Cancel>ERR (" . Side_ShortName($si) . ")</span>";
          }
          $class .= " Side$si";
          $rows = (empty($G['w'])?'':('rowspan=' . ($RowSet-$line)));
          echo "$OtherLoc<td id=$id $DRAG $dev data-d=$si $rows class='$class'>";
          if ($links) echo "<a href=/int/ShowPerf?id=$si>";
          echo $txt;
          if ($links) echo "</a>";
          if (!$evs[$G['e']]['ExcludeCount']) {
            if (isset($SideCounts[$si])) $SideCounts[$si]++;
          }
        } else {
          echo "$OtherLoc<td id=$id $DRAG $dev class=$class>&nbsp;";
        }
      }
      echo "\n";

    foreach ($OtherInUse as $i=>$O) if ($O) $OtherInUse[$i]--;
  }
  echo "</tbody></table>";
  echo "</div></div>\n";

  if ($condense==1 && $format==1 && $OffGrid) {
    echo "<br>Also:<br>";
    foreach ($OffGrid as $e) {
      $V = $e['Venue'];
      $eid = $e['EventId'];
      echo "From " . timecolon($e['Start']) . " - " . timecolon($e['End']) . " at ";

      echo Venue_Parents($VenueInfo,$e['Venue']) . ($links?"<a href=/int/VenueShow?v=$V>":'') . $VenueInfo[$e['Venue']]['SN'] . ($links?"</a>":'');

      if ($e['BigEvent']) {
        $Others = Get_Other_Things_For($eid);
        foreach ($Others as $i=>$o) {
          if (($o['Type'] == 'Venue') && ($o['Identifier']>0))
            echo ", " . Venue_Parents($VenueInfo,$o['Identifier']) . ($links?"<a href=/int/VenueShow?v=" . $o['Identifier'] . ">":'') .
                 $Venues[$o['Identifier']]['SN'] . ($links?"</a>":'');
        }
        echo " - " . ($links?"<a href=EventShow?e=" . $e['EventId'] . ">":'') . $e['SN'] . "</a> - " . $e['Description'];
        if (!$e['NoPart']) echo " with ". Get_Other_Participants($Others,0,($links?1:-1),15,1,'',$e);
      } else {
        echo " - " . ($links?"<a href=EventShow?e=" . $e['EventId'] . ">":'') . $e['SN'] . "</a> - " . $e['Description'];
        if (!$e['NoPart']) echo " with ". Get_Event_Participants($eid,0,($links?1:-1),15) . "<br>";
      }
    }
  }

}



function Print_Grid($drag=1,$types=1,$condense=0,$Links=1,$format='',$Media='Dance',$Public=0) {
  global $DAY,$Times,$Back_Times,$grid,$lineLimit,$EV,$Sides,$SideCounts,$VenueUse,$evs,$MaxOther,$VenueInfo,$Venues,$VenueNames,$OtherLocs,$Sand,$VenueList;
  global $Earliest,$Latest,$SlotSize,$OffGrid;

//var_dump($grid);exit;
//var_dump($Earliest,$Latest);
//  var_dump($lineLimit);exit;
  $links = $condense && !$types && $Links;
//var_dump($links, $condense, $types ,$Links);
  if ($Media == 'Dance' && (Feature('DanceDefaultSlot') == 30)) {
    $Round = 30;
    $DefLineLimit = 2;
  } else {
    $Round = 15;
    $DefLineLimit = (($Media == 'Dance')?2:1);
  }

  $All_Times = Feature('AllDanceTimes');
  $RowSet = 4; //($All_Times?5:4);
  $StartLine = 0;
//  if ($condense && $All_Times) $StartLine = -1;

  echo "<div class=GridWrapper$format><div class=GridContainer$format>";
  echo "<table border id=Grid><thead><tr><th id=DayId width=60 class=ProgDayHL>$DAY";
  $OtherInUse = array();
  foreach ($VenueList as $v) if ($v > 0) {
    if ($condense && $VenueInfo[$v]["Minor$DAY"]) {
    } else {
      echo "<th class=DPGridTH id=Ven$v>";
      if ($links) echo "<a href=/int/VenueShow?v=$v>";
      echo $VenueNames[$v];
      if ($links) echo "</a>";
    }
  } else {
    echo "<th class=DPGridTH id=OLoc$v>Other Location<th class=DPGridTH id=OWhat$v>What";
  }
  echo "</tr></thead><tbody>";

  $DRAG = ($drag)?"draggable=true ondragstart=drag(event) ondrop=drop(event,$Sand) ondragover=allow(event)":"";
//  $WDRAG = ($drag)?"ondrop=drop(event,$Sand) ondragover=allow(event)":"";
  foreach ($Times as $t) {
    if ($condense && ($t < $Earliest || $t >= $Latest)) continue;
    echo "<tr><th rowspan=$RowSet width=60 valign=top id=RowTime$t>" . sprintf('%04d',$t);
    if ($drag && ($lineLimit[$t]??0)<4) {
      echo "<button class=botx onclick=UnhideARow($t) id=AddRow$t>+</button>";
    }

    for ($line=$StartLine; $line < 4; $line++) {
//      $sl = "S" .($line+1);
      if ($line) echo "<tr>";
      $OtherLoc = '';
      foreach ($VenueList as $v) {
        $G = &$grid[$v][$t];
//        var_dump($G);
        if ($v > 0) { // Search oluse for free entry, mark as used for n slots - at end of time loop decrement any used
          if ($condense && $VenueInfo[$v]["Minor$DAY"]) {
            if ($evs[$G['e']]['BigEvent']) continue;
            if ($G && $line == 0 && ($G['S1'] || $G['S2'] || $G['n']) ) {
              $OtherFound = 0;
              for ($i=1; $i<= $MaxOther; $i++) if (!$OtherInUse[$i]) { $OtherFound=$i; break; }
              if ($OtherFound) {
                $OtherInUse[$OtherFound] = max(1,intval(ceil($G['d']/30)));
                $grid[-$OtherFound][$t] = $G;
              } else {
                // Run out of Others - need to report something
                echo "<span class=Err>RUN OUT OF OTHERS</span>";
              }
            }
            continue;
          }
        } else { // Generate other loc infoZZ
          if ($line == 0) {
            if ($OtherInUse[$v]) {
              continue;
            } else if ($G['S1'] || $G['S2'] || $G['n']) {
              $rows = (isset($G['r'])?($G['r']+1)*$RowSet:1); //$G['d']?intval(ceil($G['d']/30))*4:4;
              $vv = $evs[$G['e']]['Venue'];
              $OtherLoc = "<td id=XX data-d=X rowspan=$rows class=DPOvName>" ;
              if ($links) $OtherLoc .= "<a href=/int/VenueShow?v=$vv>";
              $OtherLoc .= $VenueNames[$vv];
              if ($links) $OtherLoc .= "</a>";
            } else {
              $OtherLoc = "<td id=XXX data-d=X rowspan=4>&nbsp";
            }
          }
        }
        $id = "G:$v:$t:$line"; // Note the ids will be meaningless in condensed mode, but as they will should not be used, so not a problem
        $class = 'DPGridDisp';

        $dev = '';
//        if ($line < 0) echo "<span class=DPETimes>" . sprintf('%04d',$t) . " - " . timeadd($t,$G['d']) . "<br></span>";
        if ($line == 0 && $G) $dev = 'data-e=' . ($G['e']??0) . ':' . ($G['d']??0);
        if (!$G || ($v<0 && !($G['S1'] || !$G['S2'] || $G['n']))) {
          if ($v > 0 && $condense==0) $class = "DPGridGrey";
          if (!isset($lineLimit[$t]) || $line >= $lineLimit[$t]) {
            echo "$OtherLoc<td id=$id class=$class hidden data-x=A $DRAG data-d=X>&nbsp;";
          } else if (!isset($OtherInUse[-$v])) {
            echo "$OtherLoc<td id=$id class=$class data-x=B $DRAG data-d=X>&nbsp;";
          }
        } else if ($line >= ($lineLimit[$t]??0)) {
          echo "$OtherLoc<td hidden id=$id data-x=C $DRAG $dev class=$class>&nbsp";
        } else if (!empty($G['h'])) {
          echo "$OtherLoc<td hidden id=$id data-x=C2 $DRAG $dev class=$class>&nbsp;";
        } else if (($All_Times && ($line == 0)) || (($G['d']??0) > $Round)) {
          if ($line == 0) {
            $rows = (isset($G['r'])?($G['r']+1)*$RowSet:1); // Rounding?? *$RowSet; // intval(ceil($G['d']/$Round))*$RowSet; // WRONG
            // Need to create a wrapped event - not editble here currently
            $cls = (empty($G['n'])?'':'class=DPNamed ');
            echo "$OtherLoc<td id=$id data-x=D $DRAG $dev $cls " . (($rows > 1)?"rowspan=$rows":'') . " valign=top >";
            if (!empty($G['n'])) {
              if ($links) echo "<a href=/int/EventShow?e=" . $G['e'] . ">";
              echo $G['n'];
              if ($links) echo "</a>";
              echo "<br>";
            }
            echo "<span class=DPETimes>" . sprintf('%04d',$t) . " - " . timeadd($t,$G['d']) . "<br></span>";
            for($i=1; $i<5;$i++) {
              if (!empty($G["S$i"])) {
                $si = $G["S$i"];
                if (!isset($Sides[$si])) {
                  $Sides[$si] = Get_Side($si);
                }
                $s = &$Sides[$si];
                $txt = SName($s) . (($types && $s['Type'])?(" (" . trim($s['Type']) . ") "):"");
                echo "<span id=X$id $DRAG $dev data-d=$si class='DPESide Side$si'>";
                if ($links) echo "<a href=/int/ShowPerf?id=$si>";
                echo $txt;
                  if ($links) echo "</a>";
                echo "<br></span>";
                if (!$evs[$G['e']]['ExcludeCount'] && isset($SideCounts[$si]) ) $SideCounts[$si]++;
                break;
              }
            }
          } else {
            echo "$OtherLoc<td " . ((1 || $Public)?'hidden':'') . " id=$id data-x=Q $DRAG $dev class=$class>&nbsp;";
          }
        } else if ($line == 0  && !empty($G['n'])) {
//          if (Feature('AllDanceTimes')) echo "<span class=DPETimes>" . sprintf('%04d',$t) . " - " . timeadd($t,$G['d']) . "<br></span>";
          echo "$OtherLoc<td id=$id data-x=D2 $DRAG $dev data-d='N' class=DPNamed>";
          if ($links) echo "<a href=/int/EventShow?e=" . $G['e'] . ">";
          if (!empty($G['n']) ) echo $G['n'];
          if ($links) echo "</a>";
          echo "<br>";
        } else if ($line != 0 && isset($G['w'])) {
          echo "$OtherLoc<td id=$id data-x=E $DRAG $dev hidden class=$class>DD&nbsp;";
          if (isset($G['n'])) echo $G['n'];
        } else if (isset($G["S" . ($line+(isset($G['n'])?0:1))])) {
//          if (Feature('AllDanceTimes')) echo "<span class=DPETimes>" . sprintf('%04d',$t) . " - " . timeadd($t,$G['d']) . "<br></span>";
          $si = $G["S" . ($line + (isset($G['n'])?0:1))];
          if (!isset($Sides[$si])) {
            $Sidessi = Get_Side($si);
            $Sidessi['ERROR'] = 1;
            if ($Sidessi['IsASide'] == 0 ) $Sides[$si] = $Sidessi;
          }

          $s = &$Sides[$si];
          $txt = SName($s) . (($types && !empty($s['Type']))?(" (" . trim($s['Type']) . ") "):"");
          if (!$txt || isset($s['ERROR'])) {
            if (!$condense) $txt = "<span class=Cancel>ERR (" . Side_ShortName($si) . ")</span>";
          }
          $class .= " Side$si";
          $rows = (empty($G['w'])?'':('rowspan=' . ($RowSet-$line)));
          echo "$OtherLoc<td id=$id data-x=F $DRAG $dev data-d=$si $rows class='$class'>";
          if ($links) echo "<a href=/int/ShowPerf?id=$si>";
          echo $txt;
          if ($links) echo "</a>";
          if (!$evs[$G['e']]['ExcludeCount']) {
            if (isset($SideCounts[$si])) $SideCounts[$si]++;
          }
        } else {
          echo "$OtherLoc<td id=$id data-x=G $DRAG $dev class=$class>&nbsp;";
        }
      }
      echo "\n";
    }
    foreach ($OtherInUse as $i=>$O) if ($O) $OtherInUse[$i]--;
  }
  echo "</tbody></table>";
  echo "</div></div>\n";

  if ($condense==1 && $format==1 && $OffGrid) {
    echo "<br>Also:<br>";
    foreach ($OffGrid as $e) {
      $V = $e['Venue'];
      $eid = $e['EventId'];
      echo "From " . timecolon($e['Start']) . " - " . timecolon($e['End']) . " at ";

      echo Venue_Parents($VenueInfo,$e['Venue']) . ($links?"<a href=/int/VenueShow?v=$V>":'') . $VenueInfo[$e['Venue']]['SN'] . ($links?"</a>":'');

      if ($e['BigEvent']) {
        $Others = Get_Other_Things_For($eid);
        foreach ($Others as $i=>$o) {
          if (($o['Type'] == 'Venue') && ($o['Identifier']>0))
            echo ", " . Venue_Parents($VenueInfo,$o['Identifier']) . ($links?"<a href=/int/VenueShow?v=" . $o['Identifier'] . ">":'') .
                 $Venues[$o['Identifier']]['SN'] . ($links?"</a>":'');
        }
        echo " - " . ($links?"<a href=EventShow?e=" . $e['EventId'] . ">":'') . $e['SN'] . "</a> - " . $e['Description'];
        if (!$e['NoPart']) echo " with ". Get_Other_Participants($Others,0,($links?1:-1),15,1,'',$e);
      } else {
        echo " - " . ($links?"<a href=EventShow?e=" . $e['EventId'] . ">":'') . $e['SN'] . "</a> - " . $e['Description'];
        if (!$e['NoPart']) echo " with ". Get_Event_Participants($eid,0,($links?1:-1),15) . "<br>";
      }

      echo "<P>";
    }
  }
}

function Side_List() {
  global $DAY,$Sides,$SideCounts,$Sand;
  echo "<div class=SideListWrapper><div class=SideListContainer>";
  echo "<table border id=SideList>";
//  echo "<thead><tr><th>Side<th>i<th>W<th>H</thead><tbody>\n";
  echo "<thead><tr><th>Side<th>H<th>i<th>W<th>H</thead><tbody>\n";
  foreach ($Sides as $id=>$side) {
    if (!isset($side['SN']) || $side['SN'] == '' || $side['IsASide'] == 0) continue;
    $data_w =  ($side['Share'] == 2)?"data-w=1":"";
    echo "<tr><td draggable=true class='SideName Side$id' $data_w id=SideN$id ondragstart=drag(event) ondragover=allow(event) ondrop=drop(event,$Sand)>";
    echo SName($side);
    if ($side['Type']) echo " (" . trim($side['Type']) . ")";
    echo "<td>";
    echo "<input type=checkbox id=SideHL$id onchange=highlight($id)><td>";
    echo "<img src=/images/icons/" . (Has_Info($side)?"redinformation.png":"information.png") . " width=20 onclick=dispinfo('Side',$id)>";
    echo "<td id=SideW$id align=right>" . $side[$DAY . "Dance"];
    echo "<td id=SideH$id align=right>";
    echo $SideCounts[$id] . "\n";
  }
  echo "</table></div></div>\n";
}

function Controls($level=0,$condense=0) {
  global $InfoLevels,$DAY,$Sand,$YEAR;
  if (!isset($_REQUEST['EInfo'])) $_REQUEST['EInfo'] = $level;
  echo "<div class=DPControls><center>";
  echo "Programming Controls";
  echo "<form method=get action=''>";
  echo fm_hidden('Cond',$condense);
  echo "<table><tr><td>";
  echo "<td>";
  $classFri = $classSat = $classSun = $classMon = '';
  $n = "class$DAY";
  $$n = "id=ProgDayHL";
  if ($Sand) echo fm_hidden('SAND',$Sand);
  echo fm_hidden('Y',$YEAR);
  echo "<input type=submit name=d value=Fri $classFri> ";
  echo "<input type=submit name=d value=Sat $classSat> ";
  echo "<input type=submit name=d value=Sun $classSun> ";
  echo "<input type=submit name=d value=Mon $classMon>\n";
  $_REQUEST['EInfo'] = UserGetPref('ProgErr');

  echo "<tr>" . fm_radio("Errors",$InfoLevels,$_REQUEST,'EInfo',"onchange=SaveAndUpdateInfo()");
  echo "</table>";
  echo "<div id=DanceErrsDest> </div>";
  echo "<button onclick=clearHL()>Clear Highlights</button><br>";
  echo "<input type=submit id=smallsubmit value=Refresh>";
  echo "</form>\n";
  echo "<h2><a href=Staff>Staff Tools</a></h2></center>";
  echo "</div>\n";
}

function ErrorPane($level=0) {
  include ("CheckDance.php");
  global $Sand;
  echo "<div class=ErrorWrapper><div class=ErrorContainer id=InformationPane>";
  if ($Sand) echo "In Sandbox Mode Error Checking is not meaningful.";
  CheckDance($level);
  echo "</div></div>\n";
}

function MusicErrorPane($level=0) {
//  include ("CheckMusic.php");
  global $Sand;
  echo "<div class=ErrorWrapper><div class=ErrorContainer id=InformationPane>";
  if ($Sand) echo "In Sandbox Mode Error Checking is not meaningful.";
//  CheckMusic($level);
  echo "</div></div>\n";
}

function Notes_Pane() {
  echo "<div id=Notes_Pane>";
  echo "To add a 3rd or 4th side to a time click on the + sign at the time needed, for more than 4 use a Big Event.<br>";
  echo "To remove a side drag back to the side list.<br>";
  echo "Events > 30 mins shown for info, change using Edit Event (for now).<br>";
  echo "<div>";
}

function InfoPane() {
  echo "<div class=InfoWrapper><div class=InfoContainer id=InfoPane>";
  echo "If you click on a <img src=/images/icons/information.png width=20> icon by a side, information about them will be displayed here.<p>";
  echo "<img src=/images/icons/redinformation.png width=20> Means Important Info";
  echo "</div></div>\n";
}

// MUSIC MUSIC MUSIC MUSIC

function Grab_Music_Data($day='') {
  global $DAY,$Times,$lineLimit,$Sides,$EV,$VenueUse,$evs,$Sand;

  include_once("MusicLib.php");
  $Times = array();
  $lineLimit = array();
  $SideCounts = array();
  $EV = array();
  $VenueUse = array();
  $Sand = 0;
  if (isset($_REQUEST['SAND'])) $Sand = 1;

  if ($day) { $DAY=$day;
  } else if (isset($_REQUEST['d'])) { $DAY = $_REQUEST['d']; } else { $DAY='Sat'; }

  if (!isset($_REQUEST['EInfo'])) $_REQUEST['EInfo'] = 0;
  for ($t=10;$t<24;$t++) {
    $Times[] = $t*100;
    $Times[] = $t*100+15;
    $Times[] = $t*100+30;
    $Times[] = $t*100+45;
  }

  $Sides = Select_Act_Come($DAY);
  foreach ($Times as $t) $lineLimit[$t]=1;

  $evs = Get_Events_For('Music',$DAY);
//var_dump($evs);
  foreach ($evs as $ei=>$ev) {
    $eid = $ev['EventId'];
    if (!$ev['BigEvent']) {
      $v = $ev['Venue'];
      $VenueUse[$v] = 1;
      $t = timeround($ev['Start'],15);
      if ($ev['SubEvent'] < 0) { $et = $ev['SlotEnd']; } else { $et = $ev['End']; }
//      $duration = timeround(timeadd2($ev['Start'],-$t),15);
      $duration = timeround(timeadd2($et,-$ev['Start'],),15);

      $EV[$v][$t]['e'] = $ei;
      $EV[$v][$t]['d'] = $duration;

      $ll = 0;
      if ($ev['SN'] && $ev['SN'] != 'Music') {
        $EV[$v][$t]['n'] = $ev['SN'];
        $ll = 1;
      }
      if ($ev["Side1"]) { $EV[$v][$t]['S1'] = $ev["Side1"]; }
      if ($ev["Side2"]) { $lineLimit[$t] = max($lineLimit[$t],2+$ll); $EV[$v][$t]['S2'] = $ev["Side2"]; }
      if ($ev["Side3"]) { $lineLimit[$t] = max($lineLimit[$t],3+$ll); $EV[$v][$t]['S3'] = $ev["Side3"]; }
      if ($ev["Side4"]) { $lineLimit[$t] = max($lineLimit[$t],4+$ll); $EV[$v][$t]['S4'] = $ev["Side4"]; }
    } else { // No Handling of BEs yet
    }
  }
}

/* Go through a big grid writting every thing in, then scan for compression, then print out - The cell actionw will be different
   Slots can be moved and stretched, and allow for sound check before

   $grid[v][t][n d e p1-4] i0 for label, i1-4 for parts 1-4, d= data per cell i = id, l=len (mins), s=sound check before(mins)
   $gridv[v] use count - venue event count
   $gridt[t] use count - time use count (any number of acts)
   $gridn[t] Name use count - time use count (any number of acts)
*/

/*

function Prog_Music_Grid($drag=1,$types=1,$condense=0,$format='') {
  global $DAY,$Times,$grid,$gridv,$gridt,$gridti;

  Prog_MG_Everything();
  Prog_MG_Compress($condense);
  Prog_MG_Print($drag,$types,$format);
}

function Prog_MG_Everything() {
  global $DAY,$Times,$grid,$gridv,$gridt,$gridti,$evs,$EV;

  foreach ($evs as $ei=>$ev) {
    $eid = $ev['EventId'];
    if (!$ev['BigEvent']) {
      $v = $ev['Venue'];
      $gridv[$v]++;
      $t = timeround($ev['Start'],15);
      if ($ev['SubEvent'] < 0) { $et = $ev['SlotEnd']; } else { $et = $ev['End']; };
      $duration = timeround(timeadd2real($ev['Start'],-$st),15);
      $gridt[$t]++;
      $Name = '';
      if ($ev['SN'] && $ev['SN'] != 'Music') {
        $Name = $ev['SN'];
        $grid[$v][$t]['n'] = $Name;
        $gridn[$t]++;
      }
      $grid[$v][$t]['d'] = $duration;
      for ($i=1;$i<5;$i++) {
        if ($ev["Act$i"]) $grid[$v][$t][$i]=$ev["Act$i"];
      }
    } else { // BIG EVENT not yet
    }
  }
}

function Prog_MG_Compress($Cond=0) {

}

function Prog_MG_Print($drag,$types,$format) {
  global $Sides,$VenueInfo,$Venues,$VenueNames,$OtherLocs,$Sand;
  global $DAY,$Times,$grid,$gridv,$gridt,$gridti;

  echo "<div class=GridWrapper$format><div class=GridContainer$format>";
  echo "<table border id=Grid><thead><tr><th id=DayId>$DAY";

  foreach ($Venues as $v) if (isset($VenueUse[$v])) {
    if ($condense && $VenueInfo[$v]["Minor$DAY"]) {
      $OtherLocs[] = $v;
    } else {
      echo "<th class=DPGridTH id=Ven$v>" . $VenueNames[$v];
    }
  }
  if ($condense) {
    for($i=1; $i<=$MaxOther; $i++) {
      echo "<th class=DPGridTH id=OLoc$i>Other Location<th class=DPGridTH id=OWhat$i>What";
    }
  }
  echo "</tr></thead><tbody>";

  foreach ($Times as $time) {
    echo "<tr><td>$time";

    foreach ($Venues as $v) if (isset($VenueUse[$v])) {
      if (isset($grid[$v][$time])) {
        $ht = $grid[$v][$time]['d'];
        echo "<td rowspan=$ht>";
        if (isset($grid[$v][$time]['n'])) echo "<span class=GridEventName>" . $grid[$v][$time]['n'] ."</span><br>";
        echo "<span class=GridActName>";
        for ($i=1;$i<5;$i++) {
          $sid = $grid[$v][$time][$i];
          if ($sid) {
            $side = $Sides[$sid];
            echo SName($side);
            if ($types && $side['Type']) echo " (" . trim($side['Type']) . ")";
            echo "<br>";
          }
        }
        echo "</span>";
      }
    }

}

    $EventNames = '';
    $EventNamesUsed = 0;

    foreach ($Venues as $v) {
      if (!isset($VenueUse[$v])) continue;
      if ($condense && $VenueInfo[$v]["Minor$DAY"]) continue; // do at end

      if (isset($EV[$v][$time]['e']) && isset($EV[$v][$time]['n'])) {
        $EventNames .= "<td class=DPNamed>" . $EV[$v][$time]['n'];
        $EventNamesUsed = 1;
      } else {
        $EventNames .= "<td class=DPNotNamed>";
      }
    }
    if ($condense) foreach($OtherLocs as $v) {
      if (isset($EV[$v][$time]['e']) && isset($EV[$v][$time]['n'])) {
        $EventNames .= "<td class=DPNotNamed><td class=DPNamed>";
        $EventNames .= $EV[$v][$time]['n'];
        $EventNamesUsed = 1;
      } else {
        $EventNames .= "<td class=DPNotNamed><td class=DPNotNamed>";
      }
    }

    echo "<tr><th rowspan=4>$time";
//    if ($drag && $lineLimit[$time]<4) {
//      echo "<div class=botrightwrap><div class=botrightcont><button class=botx onclick=UnhideARow($time) id=AddRow$time>+</button></div></div>";
//    }
    for ($line=0; $line < 4; $line++) {
      $sl = "S" .($line+1);
      if ($line) echo "<tr>";
      if ($line >= $lineLimit[$time]) continue;
      foreach ($Venues as $v) {
        if (!isset($VenueUse[$v])) continue;
        if ($condense && $VenueInfo[$v]["Minor$DAY"]) continue; // do at end
        if (isset($EV[$v][$time]['e'])) {
          $eid = $EV[$v][$time]['e'];
          $ee = $evs[$eid]['EventId'];

          $sll = $sl;
          $lin = $line;
          if (isset($EV[$v][$time]['n'])) {
            if ($line == 0) {
              echo "<td id=Z$ee:$v:$time:0:0 class=DPNamed>" .$EV[$v][$time]['n'];
              continue;
            } else {
              $sll = "S$line";
              $lin = $line-1;
            }
          }
          if (isset($EV[$v][$time][$sll])) { $s = $EV[$v][$time][$sll]; } else { $s = 0; }
          $row = '';
          if ($s && $Sides[$s]['Share'] == 2) { $row=' rowspan=' . $lineLimit[$time]; $NoShare[$v] = 1; }
          else if ($NoShare[$v]) $row=' hidden';
          echo "<td id=G$ee:$v:$time:$lin:$s class='DPGridDisp Side$s'";
          if ($drag) echo "draggable=true ondragstart=drag(event) ondrop=drop(event,$Sand) ondragover=allow(event)";
          echo "$row>";
          if ($s && ($drag || $evs[$eid]['InvisiblePart'] == 0)) {
            if (isset($Sides[$s])) {
              if ($condense && !$types) echo "<a href=/int/ShowPerf?id=$s>";
              echo SName($Sides[$s]);
              if ($types) echo " (" . trim($Sides[$s]['Type']) . ")";;
              if ($condense && !$types) echo "</a>";
              if (!$evs[$eid]['ExcludeCount']) $SideCounts[$s]++;
            } else {
              echo "ERROR... ($s)";
            }
          } else {
            echo "&nbsp;";
          }
        } else {
          echo "<td class=DPGridGrey>&nbsp;";
        }
      }
      if ($condense) {
         foreach($OtherLocs as $v) {
          if (isset($EV[$v][$time]['e'])) {
            $eid = $EV[$v][$time]['e'];
            $ee = $evs[$eid]['EventId'];
            $inuse = 0;
            for ($i=1;$i<5;$i++) if (isset($EV[$v][$time]["S$i"]) && $EV[$v][$time]["S$i"] ) $inuse = 1;
            if ($inuse) {
              if ($line == 0) echo "<td class=DPGridDisp rowspan=" . $lineLimit[$time] . ">" . $VenueNames[$v];
              $sll = $sl;
              if (isset($EV[$v][$time]['n'])) {
                if ($line == 0) {
                  echo "<td id=Z$ee:$v:$time:0:0 class=DPNamed>" .$EV[$v][$time]['n'];
                  continue;
                } else {
                  $sll = "S$line";
                }
              }
              if (isset($EV[$v][$time][$sll])) { $s = $EV[$v][$time][$sll]; } else { $s = 0; }
              $row = '';
              if ($s && $Sides[$s]['Share'] == 2) { $row=' rowspan=' . $lineLimit[$time]; $NoShare[$v] = 1; }
              else if ($NoShare[$v]) $row=' hidden';
              echo "<td id=G$ee:$v:$time:$line:$s class='DPGridDisp Side$s'";
              if ($drag) echo "draggable=true ondragstart=drag(event) ondrop=drop(event,$Sand) ondragover=allow(event)";
              echo "$row>";
              if ($s && ($drag || $evs[$eid]['InvisiblePart'] == 0)) {
                if (isset($Sides[$s])) {
                  if ($condense && !$types) echo "<a href=/int/ShowPerf?id=$s>";
                  echo SName($Sides[$s]);
                  if ($types) echo " (" . trim($Sides[$s]['Type']) .")";;
                  if ($condense && !$types) echo "</a>";
                  if (!$evs[$eid]['ExcludeCount']) $SideCounts[$s]++;
                } else {
                  echo "ERROR...";
                }
              } else {
                echo "&nbsp;";
              }
            }
          }
        }
      }
    }
  }
  echo "</tbody></table>";
  echo "</div></div>\n";
}

function Notes_Music_Pane() {
  echo "<div id=Notes_Pane>";
  echo "This is just a visual display of the Music programme.  No editing can be done currently.<br>";
  echo "<div>";
}

TODO/PROBLEMS
Merge Stage/Not Stage in Square
Hide/Trim unused times
What appears as "Music"?  Do workshops?  Do Sessions?  Do Ceildihs?
  Should include sessions and workshops at venues that are not dance only

Pick up music sub events and handle appropriately



*/
?>
