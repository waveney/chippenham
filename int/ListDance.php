<?php
  include_once("fest.php");
  A_Check('Staff');

  dostaffhead("List Dance", ["/js/clipboard.min.js","/js/emailclick.js", "/js/InviteThings.js"] );
  include_once("DanceLib.php");
  include_once("ProgLib.php");
  include_once("DateTime.php");
  global $YEAR,$PLANYEAR,$Dance_Comp,$Dance_Comp_Colours,$Event_Types,$YEARDATA,$Coming_Type,$Coming_Colours,$Coming_States;
  global $db,$Invite_States;

  echo "<h2>List Dance Sides $YEAR</h2>\n";
  $Sel = $_REQUEST['SEL'];

  $col5 = $col6 = $col7 = $col7a = $col8 = $col9 = $col9a = $col9b = $col9c = $col10 = '';
  $Totals['Fri'] = $Totals['Sat'] = $Totals['Sun'] = $Totals['Mon'] = 0;
  $Totals['FriP'] = $Totals['SatP'] = $Totals['SunP'] = $Totals['MonP'] = 0;

  echo fm_hidden('Year',$YEAR);
  if ($Sel && $Sel !='TinList') {
    if (Access('Staff','Dance')) echo "<div class=floatright style=text-align:right><div class=Bespoke>" .
       "Sending:<button class=BigSwitchSelected id=BespokeM onclick=Add_Bespoke()>Generic Messages</button><br>" .
       "Switch to: <button class=BigSwitch id=GenericM onclick=Add_Bespoke()>Bespoke Messages</button></div>" .
       "<div class=Bespoke hidden id=BespokeMess>" .
       "Sending:<button class=BigSwitchSelected id=GenericM1 onclick=Remove_Bespoke()>Bespoke Messages</button><br>" .
       "Switch to: <button class=BigSwitch id=BespokeM1 onclick=Remove_Bespoke()>Generic Messages</button></div>" .
       "</div>";

    if (Access('SysAdmin')) {
      echo "Debug: <span id=DebugPane></span><p>";
    } else {
      echo "<div hidden>Debug: <span id=DebugPane></span><p></div>";
    }
   echo "Click on column header to sort by column.  Click on Side's name for more detail and programme when available,<p>\n";

//  echo "Days to fest: $Days2Festival<p>";

   echo "If you click on the email link, press control-V afterwards to paste the standard link into message.<p>";
 } else {
//   echo "<h2><a href=ListDance?SEL=TinList&F=CSV>List as a CSV</a></h2>\n";
 }

  $DanceState = $Event_Types[1]['State'];
  $Days2Festival = Days2Festival();

  $Types = Get_Dance_Types(1);
  if ($Types) foreach ($Types as $i=>$ty) $Colour[strtolower($ty['SN'])] = $ty['Colour'];

  $link = 'AddPerf';
  $LastYear = $YEARDATA['PrevFest'];


  switch ($Sel) {
  case 'ALL':
    $SideQ = $db->query("SELECT s.*, y.*, s.SideId FROM Sides AS s LEFT JOIN SideYear as y ON s.SideId=y.SideId AND y.Year='$YEAR' WHERE s.IsASide=1 ORDER BY SN");
    $col5 = "Invite";
    $col6 = "Coming";
    $col7 = "Wshp";
    if (Feature('DanceComp')) $col9 = "Dance Comp";
    break;

  case 'INV':
    $flds = "s.*, ly.Invite, ly.Coming, y.Invite, y.Invited, y.Coming";
    $SideQ = $db->query("SELECT $flds FROM Sides AS s LEFT JOIN SideYear as y ON s.SideId=y.SideId AND y.Year='$PLANYEAR' " .
                        "LEFT JOIN SideYear as ly ON s.SideId=ly.SideId AND ly.Year='$LastYear' WHERE s.IsASide=1 AND s.SideStatus=0 ORDER BY SN");
    $col5 = "Invited $LastYear";
    $col6 = "Coming $LastYear";
    $col7 = "Invite $PLANYEAR";
    $col8 = "Invited $PLANYEAR";
    $col9 = "Coming $PLANYEAR";
    break;

  case 'Coming':
    echo "In the Missing Col: A=Address, D=Days, I=Insurance, M=Mobile, P=Performers Nos<br>\n";
    echo "A <b>P</b> in the Notes Col, indicates the performer numbers have changed<p>\n";

    $SideQ = $db->query("SELECT s.*, y.* FROM Sides AS s, SideYear as y WHERE s.IsASide=1 AND s.SideId=y.SideId AND y.Year='$YEAR' AND y.Coming=" .
                $Coming_Type['Y'] . " ORDER BY SN");
    $col5 = "Fri";
    $col6 = "Sat";
    $col7 = "Sun";
    $col7a = "Mon";
    $col8 = "Missing";
    if (Feature('DanceComp')) {
      $col9 = "Dance Comp";
    } else {
      $col9 = "Wshp";
    }
    if ($DanceState >= 1) $col9b = "Seen";
    $col9c = "Messages";
    if (Access('Staff','Dance')) $col10 = "Proforma Emails";
    $Comp = $stot = $Seen = 0;
    break;

  case 'TinList':
    $SideQ = $db->query("SELECT s.*, y.* FROM Sides AS s, SideYear as y WHERE s.IsASide=1 AND s.SideId=y.SideId AND y.Year='$YEAR' AND y.Coming=" .
                $Coming_Type['Y'] . " ORDER BY SN");
    $col5 = "Fri";
    $col6 = "Sat";
    $col7 = "Sun";
    $col7a = "Mon";
    break;

  default:
    $flds = "s.*, y.Sat, y.Sun, y.Mon";
    $SideQ = $db->query("SELECT $flds FROM Sides AS s, SideYear as y WHERE s.IsASide=1 AND s.SideId=y.SideId AND y.Year='$YEAR' AND y.Coming=" .
                $Coming_Type['Y'] . " ORDER BY SN");
    $col5 = "Fri";
    $col6 = "Sat";
    $col7 = "Sun";
    $col7a = "Mon";
  }

  if (!$SideQ || $SideQ->num_rows==0) {
    echo "<h2>No Sides Found</h2>\n";
  } else {
    $coln = ($col10?1:0); // Start at 1 for select col
    echo "<div class=Scrolltable2><table id=indextable border width=100% style='min-width:1400px'>\n";
    echo "<thead><tr>";
    if ($col10) echo "<th><input type=checkbox name=SelectAll id=SelectAll onchange=ToolSelectAll(event)>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Type</a>\n";
    if ($Sel && $Sel !='TinList') {
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Contact</a>\n";
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Email</a>\n";
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Notes</a>\n";
//      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Link</a>\n";
    }
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>$col5</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>$col6</a>\n";
    if ($col7) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>$col7</a>\n";
    if ($col7a) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>$col7a</a>\n";
    if ($col8) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>$col8</a>\n";
    if ($col9) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>$col9</a>\n";
    if ($col9a) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>$col9a</a>\n";
    if ($col9b) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>$col9b</a>\n";
    if ($col9c) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>$col9c</a>\n";
    if ($col10) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>$col10</a>\n";
//    for($i=1;$i<5;$i++) {
//      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>EM$i</a>\n";
//    }

    echo "</thead><tbody>";

    $ProcDays = intval(Feature('ProcessDays'));
    $Dance_Comp[0] = '';
    while ($fetch = $SideQ->fetch_assoc()) {
      $IsComp = 0;
      $snum = $fetch['SideId'];
      echo "<tr>";
      if ($col10) echo "<td><input type=checkbox name=E$i class=SelectAllAble>";
      echo "<td><a href=$link?sidenum=$snum&Y=$YEAR>" . (empty($fetch['SN'])?'Nameless':$fetch['SN']) . "</a>";
      if ($fetch['SideStatus']) {
        echo "<td>Folded";
      } else {
        $ty = strtolower($fetch['Type']);
        $colour = '';
        foreach($Types as $T) {
          if ($T['Colour'] == '') continue;
          $lct = "/" . strtolower($T['SN']) . "/";
          if (preg_match($lct,$ty)) {
            $colour = $T['Colour'];
            break;
          }
        }
        if ($colour) {
          echo "<td style='background:$colour;'>" . $fetch['Type'];
        } else {
          echo "<td>" . $fetch['Type'];
        }
      }
      if ($Sel && $Sel !='TinList') {
        echo "<td>" . $fetch['Contact'];
        echo "<td>";
          if ($fetch['Email']) {
            if (Feature("EmailButtons")) {
               echo "<button type=button id=Email$snum onclick=ProformaSend('Dance_Blank',$snum,'Email','SendProfEmail',1)>Email</button>";
            } else echo linkemailhtml($fetch,'Side',(!$fetch['Email'] && $fetch['AltEmail']? 'Alt' : '' ));
          }
        echo "<td>";
        if ($fetch['Notes'] || $fetch['YNotes'] || $fetch['PrivNotes'] || $fetch['Likes']) {
          $Htext = htmlspec($fetch['Notes'] . "\n" . $fetch['YNotes'] . "\n" . $fetch['PrivNotes'] . "\n" . $fetch['Likes']	);
          echo "<img src=images/icons/LetterN.jpeg width=20 title=\"$Htext\">";
        }
        if (($_REQUEST['SEL'] == 'Coming') && $fetch['PerfNumChange']) echo " <b>P</b>";

      }
      if ($col5 == "Invite") {
        echo "<td>";
        if (isset($fetch['Invite'])) echo $Invite_States[$fetch['Invite']];
        if (isset($fetch['Coming'])) {
          echo "<td style='background:" . $Coming_Colours[$fetch['Coming']] . "'>";
          echo $Coming_States[$fetch['Coming']] . "\n";
        } else {
          echo "<td>";
        }
      } else {
        $fri = "";
        if ($fetch['Fri']) {
          $fri= "y";
          $Totals['Fri']++;
          if (($ProcDays & 1) && $fetch["ProcessionFri"]) {
            $fri .= "+P";
            $Totals['FriP']++;
          }
        }
        $sat = "";
        if ($fetch['Sat']) {
          $sat= "y";
          $Totals['Sat']++;
          if (($ProcDays & 2) && $fetch["ProcessionSat"]) {
            $sat .= "+P";
            $Totals['SatP']++;
          }

        }
        $sun = "";
        if ($fetch['Sun']) {
          $sun= "y";
          $Totals['Sun']++;
          if (($ProcDays & 4) && $fetch["ProcessionSun"]) {
            $sun .= "+P";
            $Totals['SunP']++;
          }

        }
        $mon = "";
        if ($fetch['Mon']) {
          $mon= "y";
          $Totals['Mon']++;
          if (($ProcDays & 8) && $fetch["ProcessionMon"]) {
            $mon .= "+P";
            $Totals['MonP']++;
          }

        }

        echo "<td>$fri<td>$sat<td>$sun<td>$mon\n";
      }
      if ($col7 == 'Wshp') {
        echo "<td>";
        if ($fetch['Workshops']) echo "Y";
      }
      if ($col8 == "Missing") {
        $stot++;
        echo "<td>";
        if ((!Feature('PublicLiability') || $fetch['Insurance']) && $fetch['Mobile'] &&
                ((($fetch['Performers'] > 0) && $fetch['Address']) || ($fetch['Performers'] < 0)) &&
                ($fetch['Sat'] || $fetch['Sun'] || $fetch['Mon'] )) {
          echo "None";
          $Comp++;
          $IsComp = 1;
        } else {
          if (Feature('DanceNeedInsurance') && !$fetch['Insurance']) echo "I";
          if (Feature('DanceNeedPerformers') && $fetch['Performers'] == 0) echo "P";
          if (Feature('DanceNeedAddress') && $fetch['Address'] == '' && $fetch['Performers'] >= 0) echo "A";
          if (Feature('DanceNeedMobile') && !$fetch['Mobile']) echo "M";
          if (Feature('DanceNeedDays') && !$fetch['Sat'] && !$fetch['Sun'] && !$fetch['Mon']) echo "D";
        }
        if ($fetch['Insurance'] == 1) echo " (Check)";
      }
      if ($col9 == 'Dance Comp') {
        if (!isset($fetch['DanceComp'])) $fetch['DanceComp'] = 0;
        echo "<td style='background:" . $Dance_Comp_Colours[$fetch['DanceComp']] . "'>" . $Dance_Comp[$fetch['DanceComp']] ;
      }
      if ($col9 == 'Wshp') {
        echo "<td>";
        if ($fetch['Workshops']) {
          $Wtext = htmlspecialchars($fetch['Workshops']);
          echo "<img src=images/icons/LetterW.jpeg width=20 title='$Wtext'>";
        }

      }


      if ($col9b == 'Seen') {
        echo "<td>" . ($fetch['TickBox1']?'y':'');
        if ($fetch['TickBox1']) $Seen++;
      }

      if ($col9c == 'Messages') {
        echo "<td width=250 height=38 style='max-width:200;max-height:38;'>";
        echo "<div id=Vited$snum class=scrollableY>";
        if (isset($fetch['Invited'])) echo $fetch['Invited'];
        echo "</div>";
      }

      if ($col10 == "Proforma Emails") {
        echo "<td>";

        if (($mess = Feature('DanceSpecialMessage'))) {
          $Mname = preg_replace('/ /', '',$mess);
          echo "<button type=button id=$Mname$snum class=ProfButton onclick=ProformaSend('Dance_$Mname',$snum,'$Mname','SendProfEmail')" .
                 ">$mess</button>";
        }

        if (($mess = Feature('DanceSpecialMessage2'))) {
          $Mname = preg_replace('/ /', '',$mess);
          echo "<button type=button id=$Mname$snum class=ProfButton onclick=ProformaSend('Dance_$Mname',$snum,'$Mname','SendProfEmail')" .
          ">$mess</button>";
        }
        
        if ($fetch['Email']) {
          if (!$IsComp && ($_REQUEST['SEL'] == 'Coming')) {
            echo "<button type=button id=Detail$snum class=ProfButton onclick=ProformaSend('Dance_Details',$snum,'Details','SendProfEmail')" .
                 Proforma_Background('Details') . ">Details!</button>";
          }


          if ($DanceState >= 1 && !$fetch['TotalFee']) {
            if (strstr($fetch['Invited'],'Program:')) {
              if (!$fetch['TickBox1']) echo "<button type=button id=Prog$snum class=ProfButton onclick=ProformaSend('Dance_Program_Check',$snum,'ProgChk','SendProfEmail')" .
                                             Proforma_Background('ProgChk') . ">Prog Check</button>";
              echo "<button type=button id=Prog$snum class=ProfButton onclick=ProformaSend('Dance_Program_Revised',$snum,'NewProg','SendProfEmail')" .
                   Proforma_Background('NewProg') . ">New Prog</button>";

            } else {
              echo "<button type=button id=Prog$snum class=ProfButton onclick=ProformaSend('Dance_Program',$snum,'Program','SendProfEmail')" .
                   Proforma_Background('Program') . ">Program</button>";
            }
          }

          if ($DanceState == 4 && $Days2Festival < 20) {
              echo " <button type=button id=Prog$snum class=ProfButton onclick=ProformaSend('Dance_Final_Info',$snum,'FinalInfo','SendProfEmail')" .
                   Proforma_Background('FinalInfo') . ">Final Info</button>";
              echo "<button type=button id=Prog$snum class=ProfButton onclick=ProformaSend('Dance_Final_Info2',$snum,'FinalInfo2','SendProfEmail')" .
                   Proforma_Background('FinalInfo2') . ">Final Info2</button>";
              echo "<button type=button id=Prog$snum class=ProfButton onclick=ProformaSend('Dance_Tickets',$snum,'MorrisTickets','SendProfEmail')" .
                   Proforma_Background('MorrisTickets') . ">Morris Tickets</button>";
          }
        } else {
          echo "No Email!";
        }
      }

//      for($i=1;$i<5;$i++) {
//        echo "<td>" . ($fetch["SentEmail$i"]?"Y":"");
//      }
    }
    if ($Sel && $Sel !='TinList') {
      if ($Totals['Sat']) {
        echo "<tr><td><td>Totals:<td><td><td><td><td>" .
           $Totals['Fri'] . ($Totals['FriP']? " (+ " . $Totals['FriP'] . ")":'') . "<td>" .
           $Totals['Sat'] . ($Totals['SatP']? " (+ " . $Totals['SatP'] . ")":'') . "<td>" .
           $Totals['Sun'] . ($Totals['SunP']? " (+ " . $Totals['SunP'] . ")":'') . "<td>" .
           $Totals['Mon'] . ($Totals['MonP']? " (+ " . $Totals['MonP'] . ")":'') . "<td>" .

           "<td><td><td><td><td>";
      }
    }
    echo "</tbody></table></div>\n";

    if ($col10) {
      $Dtypes = Get_Dance_Types(0);
      echo "<div id=SelTools data-t1=Tool_Type,2 data-t2=Tool_Invite,8 data-t3=Tool_Coming,10 data-t4=Tool_Coming_Last,7></div>"; // Encode all tools below selectname,col to test
      echo "<b>Select: Type=" . fm_select($Dtypes,$_REQUEST,'Tool_Type',1,' oninput=ToolSelect(event)') ;
      echo " Invite=" . fm_select($Invite_States,$_REQUEST,'Tool_Invite',1,' oninput=ToolSelect(event)') ;
      echo " Coming $PLANYEAR=" . fm_select($Coming_States,$_REQUEST,'Tool_Coming',1,' oninput=ToolSelect(event)') ;
      echo " Coming $LastYear=" . fm_select($Coming_States,$_REQUEST,'Tool_Coming_Last',1,' oninput=ToolSelect(event)') . "</b><p>";
//      echo " Day=" . fm_select($Coming_States,$_REQUEST,'Tool_Coming',1,' oninput=ToolSelect(event)') . "</b><p>";
    }

    if ($col8 == "Missing") {
      echo "Complete: $Comp / $stot, Seen: $Seen / $stot<br>\n";
    }
  }

  dotail();
