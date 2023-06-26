<?php
  include_once("fest.php");
  
  $V = $_REQUEST['pa4v'] ?? 0;
  A_Check('Participant','Venue',$V);
  
  include_once("ProgLib.php");
  include_once("DanceLib.php");
  include_once("ViewLib.php");
  global $YEAR,$USERID,$USER, $Access_Type, $Event_Types;
    
  $Ven = Get_Venue($V);
  $host = "https://" . $_SERVER['HTTP_HOST'];
  
  $ShowMode = '';
  $AtEnd = [];
  
  dostaffhead("Steward Results for " . $Ven['SN']);
  echo "<div style='background:white;'>";

  $Gash = ['HowMany'=>'','HowWent'=>''];
  $VenList[] = $V;
  
  if ($Ven['IsVirtual']) {
    $res = $db->query("SELECT DISTINCT e.* FROM Events e, Venues v, EventTypes t WHERE e.Year='$YEAR' AND (e.Venue=$V OR e.BigEvent=1 OR " .
                "( e.Venue=v.VenueId AND v.PartVirt=$V )) ORDER BY Day, Start");
    $parts = $db->query("SELECT VenueId FROM Venues v WHERE v.PartVirt=$V");
    while ($part = $parts->fetch_assoc()) $VenList[] = $part['VenueId'];
  } else {
    $res = $db->query("SELECT DISTINCT e.* FROM Events e, EventTypes t WHERE e.Year='$YEAR' AND (e.Venue=$V OR e.BigEvent=1) " .
                " ORDER BY Day, Start");
  }

  if (!$res || $res->num_rows==0) {
    echo "<h3>There are currently no events at " . $Ven['SN'] . " and hence no Steward Results</h3>\n";
    dotail();
    exit;
  }
  
  $SaveStew = $SaveSet = '';
  
  $LastDay = -99;
  while ($e = $res->fetch_assoc()) {
    if ($LastDay != $e['Day']) { $MaxEv = 0; $LastDay = $e['Day']; };
    $WithC = 0;
    if ($e['BigEvent']) {
      $O = Get_Other_Things_For($e['EventId']);
      $found = ($e['Venue'] == $V); 
//      if (!$O && !$found) continue;
      if ( !$found && $Ven['IsVirtual'] && in_array($e['Venue'],$VenList)) $found = 1; 
      foreach ($O as $i=>$thing) {
        if ($thing['Identifier'] == 0) continue;
        switch ($thing['Type']) {
          case 'Venue':
            if (in_array($thing['Identifier'],$VenList)) $found = 1; 
            break;
          case 'Perf':
          case 'Side':
          case 'Act':
          case 'Other':
            if ($thing['Identifier']) $e['With'][] = $thing['Identifier'];
            break;
          default:
            break;
        }
      }
      if ($found == 0) continue;
    } else {
      for($i=1;$i<5;$i++) if ($e["Side$i"]) {
        $e['With'][] = $e["Side$i"];
      }
    }
    if ($e['ExcludePA']) $e['With'] = [];
    $EVs[$e['EventId']] = $e;
  }

  if (!isset($EVs) || !$EVs) {
    echo "<h3>There are currently no events at " . $Ven['SN'] . " and hence no Steward Results</h3>\n";
    dotail();
    exit;
  }

//var_dump($VirtVen);

//  echo "<h2 class=FakeButton><a href=PAShow?pa4v=$V>Browsing Format</a>, <a href=PAShow?pa4v=$V&FILES=1>Embed Files</a>,  <a href=PAShow?pa4v=$V&FILES=2>Header Free for Printing</a></h2>";

  $lastevent = -99;
  
  echo "<form method=post action=StewardResults?V=$V>";
//  Register_AutoUpdate('EventSteward',rand(1,1000000)); //($USER['AccessLevel'] == $Access_Type['Participant']? - rand(1,1000000): $USERID));
  
  foreach ($EVs as $ei=>$e) {
    $eid = $e['EventId'];
    if (DayTable($e['Day'],"Event Results for: " . $Ven['SN'] ,'','style=font-size:24;')) {
      echo "<tr><td class=ES_Time>Time<td class=ES_What>What<td class=ES_Detail>Detail";
      $lastevent = -99;
    }

    if ($e['SubEvent'] == 0) {
      $rows = 1;
      $str = timecolon(timeadd($e['Start'], - $e['Setup'])) . "-" . timecolon($e['End']) . 
        "<td class=ES_What>" . $Event_Types[$e['Type']]['SN'] . ":<td><a href=EventShow?e=" . $e['EventId'] . ">" . $e['SN'] . "</a>";
      if ($Event_Types[$e['Type']]['Public']) {
      } else {
        $str .= "<tr><td class=ES_What><b>NOT PUBLIC</b><td class=ES_Detail>";
        $rows++;
      }

    } else if ($e['SubEvent'] < 0) {
      $rows = 1;
      $str = timecolon(timeadd($e['Start'], - $e['Setup'])) . "-" . timecolon($e['SlotEnd']) . 
        "<td class=ES_What>" . $Event_Types[$e['Type']]['SN'] . ":<td><a href=EventShow?e=" . $e['EventId'] . ">" . $e['SN'] . "</a>";
      if ($Event_Types[$e['Type']]['Public']) {
      } else {
        $str .= "<tr><td class=ES_What><b>NOT PUBLIC</b><td class=ES_Detail>";
        $rows++;
      }
    
    } else {
      $str = timecolon(timeadd($e['Start'], - $e['Setup'])) . "-" . timecolon($e['End']);
      $rows = 1;    
    }
    

//    $str = timecolon(timeadd($e['Start'], - $e['Setup'])) . "-" . timecolon(($e['SubEvent']<0)?$e['SlotEnd']:$e['End']) . "<td>" . ($e['SubEvent']<1? $e['SN']:"") ;
    
//    if ($e['SubEvent']<1) $str .= "<tr><td class=ES_What>Price:<td class=ES_Detail>" . Price_Show($e,1);
    
    if ($e['NeedSteward'] && $e['StewardTasks']) {
      if ($e['SubEvent'] < 1 || $SaveStew != $e['StewardTasks']) {
        $rows++;
        $SaveStew = $e['StewardTasks'];
        $str .= "<tr><td class=ES_What>Stewards<td class=ES_Detail>$SaveStew";
      }
    }
    if ($e['SetupTasks']) { 
      if ($e['SubEvent'] < 1 || $SaveSet != $e['StewardTasks']) {
        $rows++;
        $SaveSet = $e['SetupTasks'];
        $str .= "<tr><td class=ES_What>Setup<td class=ES_Detail>$SaveSet";
      }
    }

    $se = $e['SubEvent'];
    
    $Comms = Gen_Get_Cond('EventSteward',"Year='$YEAR' AND EventId=$eid");

    if ($Comms) {
      $Numbers = $Comments = [];
      $Num = 0;
      foreach ($Comms as $C) {
        $Num++;
        if (!empty($C['HowMany'])) $Numbers[] = $C['HowMany'];
        if (!empty($C['HowWent'])) $Comments[] = $C['HowWent'];
      }
      
      $str .= "<tr><td>$Num " . Plural($Num,$t0='',$t1='Result',$t2='Results') . ":<td>";
      if ($Numbers) $str .= "Numbers: " . implode('; ', $Numbers) . "<br>";
      if ($Comments) $str .= "Comments: " . implode('; ', $Comments);
      $rows++;
    } else {
      $str .= "<tr><td>No Comments<td>"; 
      $rows++;
    }
    
    echo "<tr><td class=ES_Time rowspan=$rows>" . $str;
  }
  if (Access('SysAdmin')) {
    echo "<tr><td class=NotSide>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea><p><span id=DebugPane></span>";
  }
  echo "</table>\n";
  
  echo "</div>";
  dotail();

?>
