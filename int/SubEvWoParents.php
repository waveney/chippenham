<?php
  include_once("fest.php");
  include_once("ProgLib.php");
  global $YEAR;
  
  $Parents = [];

  dostaffhead("List Events");
  $AllEvs = Gen_Get_Cond('Events',"Year=$YEAR AND SubEvent!=0",'EventId'); // all events with SE != 0 for year

  $Venues = Get_Real_Venues();  
  // Find all parents
  foreach($AllEvs as $E) if ($E['SubEvent']<0) $Parents[$E['EventId']] = 1;
  
  // Find all events that are SE and without parents

  echo "<form method=post action=EventList>";
  echo "<div class=tablecont><table id=indextable border>\n";
  echo "<thead><tr>";
  $coln = 1;
  echo "<th><input type=checkbox name=SelectAll id=SelectAll onchange=ToolSelectAll(event)>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Event Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Day</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Start</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>End</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Venue</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Type</a>\n";
  echo "</thead><tbody>";

  
  foreach($AllEvs as $E) if ($E['SubEvent']>0 && empty($Parents[$E['SubEvent']])) {
    $Eid = $E['EventId'];
    echo "<tr><td><input type=checkbox name=E$Eid class=SelectAllAble>";
    echo "<td>$Eid<td><a href=EventAdd?e=$Eid>";
      if (strlen($E['SN']) >2) { echo $E['SN'] . "</a>"; } else { echo "Nameless</a>"; };
      echo "<td>" . DayList($E['Day']) . "<td>" . timecolon($E['Start']) . "<td>";
      echo timecolon($E['End']); 
      echo "<td>" . (isset($Venues[$E['Venue']]) ? $Venues[$E['Venue']] : "Unknown");
      echo "<td>" . ($E['Status'] == 1 ? "<div class=Cancel>Cancelled</div> " : "") . 
            (isset($Event_Types[$E['Type']]['SN']) ? $Event_Types[$E['Type']]['SN'] : "?" );
   }

  echo "</tbody></table></div>\n";
  if (Access('Staff','Events')) {
    $realvens = Get_Real_Venues();
    echo "Selected: <input type=Submit name=ACTION value=Delete " .
        " onClick=\"javascript:return confirm('are you sure you want to delete these?');\">, "; 

  }
  echo "</form>\n";
  dotail();
 ?>
