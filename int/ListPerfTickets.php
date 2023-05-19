<?php
  include_once("fest.php");
  A_Check('Staff');

  dostaffhead("List Performer Tickets", ["/js/clipboard.min.js","/js/emailclick.js", "/js/InviteThings.js"] );
  global $YEAR,$PLANYEAR,$Dance_Comp,$Dance_Comp_Colours,$Event_Types,$YEARDATA;
  include_once("DanceLib.php"); 
  include_once("MusicLib.php"); 
  include_once("ProgLib.php");
  include_once("DocLib.php");
  include_once("DateTime.php");
  
  $SideQ = $db->query("SELECT * FROM Sides AS s, SideYear as y WHERE s.SideId=y.SideId AND y.Year='$YEAR' AND y.YearState>=2 AND " .
           "( y.FreePerf>0 OR y.FreeYouth>0 OR y.FreeChild>0 ) ORDER BY SN");

  if (!$SideQ || $SideQ->num_rows==0) {
    echo "<h2>No Performers Found</h2>\n";
    dotail();
  } 
  
  $TotA = $TotY = $TotC = $AC = $YC = 0;
      $CampSites = Gen_Get_All('Campsites');
      $CampTypes = Gen_Get_All('Camptypes');
  $CampTot = [];
  
  $Collect = $_REQUEST['COL'] ?? 0;
  
  if ($Collect) {
    echo "<form method=post action=ListPerfTickets>";
    Register_Autoupdate('CollectPerf',0);
  }
  
  $Users = Get_AllUsers(2);

  $coln = 1; // Start at 1 for select col
  
  echo "All you should need to do is click the Collect button to the right of each performer.<p>" .
       "If you click one in error you have 15 seconds to click the Oops button to revert it.<p>" .
       "In the event of problems call Richard.<p>\n";
  echo "<div class=tablecont><table id=indextable border width=100% style='min-width:1400px'>\n";
  echo "<thead><tr>";
  if (Access('SysAdmin')) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Contact</a>\n";

    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Adults</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Youth</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Child</a>\n";

  if ($Collect) {
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Collected</a>\n";  
  } else {
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Adults</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Youth</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Child</a>\n";

    foreach ($CampSites as $CSi => $CS) {
      if (($CS['Props'] & 1) ==0) continue;
      if (0 && ($CS['Props'] & 2)) {
          echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>" . $CS['Name'] . "</a>\n";
      } else {
        foreach($CampTypes as $CTi => $CT) {
          echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>" . $CS['Name'] . " - " . $CT['Name'] . "</a>\n";
          $CampTot[$CSi][$CTi] = 0;
        }
      }
    }
  }

  echo "</thead><tbody>";

  while ($fetch = $SideQ->fetch_assoc()) {
//  echo "<tr><td colspan=20>"; var_dump($fetch);
    echo "\n<tr>";
    if (Access('SysAdmin')) echo "<td>" . ($sid = $fetch['SideId']);
    $syId = $fetch['syId'];
    echo "<td><a href=AddPerf?id=" . $fetch['SideId'] . "&Y=$YEAR>" . (empty($fetch['SN'])?'Nameless':$fetch['SN']) . "</a>";
    echo "<td>" . $fetch['Contact'];
    
    if ($Collect) {
      $CampUse = Gen_Get_Cond('CampUse',"SideYearId=$syId");  
      echo "<td>" . $fetch['FreePerf'] . ($CampUse ? " - Camping": '');  
      echo "<td>" . $fetch['FreeYouth'] . ($CampUse ? " - Camping": '');  
      echo "<td>" . $fetch['FreeChild'];
      
      if ($CampUse) {
        $AC += $fetch['FreePerf'];
        $YC += $fetch['FreeYouth'];
        $TotC += $fetch['FreeChild'];      
      } else {
        $TotA += $fetch['FreePerf'];
        $TotY += $fetch['FreeYouth'];
        $TotC += $fetch['FreeChild'];      
      }
      
      echo "<td id=Collect$sid>" . ($fetch['TicketsCollected']
        ? "Collected " . date("D M j G:i:s",$fetch['TicketsCollected']) . " from " . ($Users[$fetch['CollectedBy']]['SN'] ?? 'Unknown')
        : "<button type=button class=FakeButton onclick='TicketsCollected($sid)'>Collect</button>");
    } else {
      echo "<td>" . $fetch['FreePerf'];
      echo "<td>" . $fetch['FreeYouth'];
      echo "<td>" . $fetch['FreeChild'];
      $TotA += $fetch['FreePerf'];
      $TotY += $fetch['FreeYouth'];
      $TotC += $fetch['FreeChild'];

      $syid = $fetch['syId'] ?? -1;
      $CampUse = Gen_Get_Cond('CampUse',"SideYearId=$syid");
      $CampU = $Camp = [];
      foreach ($CampUse as $CU) {
        $CampU[$CU['CampSite']][$CU['CampType']] = $CU['Number'];
//      $Camp[$CU['CampSite']] = 1;
      }
 
      if ($CampU) {
        $AC += $fetch['FreePerf'];
        $YC += $fetch['FreeYouth'];

        foreach ($CampSites as $CSi => $CS) {
          if (($CS['Props'] & 1) ==0) continue;
          if (0 && ( $CS['Props'] & 2)) {
            echo "<td>" . ($CampU[$CSi][$CTi] ?? 0);
          } else {
            foreach($CampTypes as $CTi => $CT) {
              echo "<td>" . ($CampU[$CSi][$CTi] ?? 0);
              $CampTot[$CSi][$CTi] += ($CampU[$CSi][$CTi] ?? 0);
            }
          }
        }
      } else {
        foreach ($CampSites as $CS) {
          if (($CS['Props'] & 1) ==0) continue;
          foreach($CampTypes as $CT) echo "<td>";
        }
      }
    }
  }
  
  if (!$Collect) {
    echo "<tr><td><td>TOTALS<br>Camping<td><td>$TotA<br>$AC<td>$TotY<br>$YC<td>$TotC\n";

    foreach ($CampSites as $CSi => $CS) {
      if (($CS['Props'] & 1) ==0) continue;
      if (0 && ($CS['Props'] & 2)) {
          echo "<td>";
      } else {
        foreach($CampTypes as $CTi => $CT) {
          echo "<td>" . $CampTot[$CSi][$CTi];
        }
      }
    }
  }

  echo "</table></div>";
  dotail();
?>
