<?php
  include_once("fest.php");

  A_Check('Staff','Events');

  include_once("ProgLib.php");
  include_once("DanceLib.php");
  include_once("ViewLib.php");
  global $YEAR,$USERID,$USER, $Access_Type, $Event_Types;
  global $db;

// UNFINISHED CODE - DONT USE YET

  $V = $_REQUEST['V'];
  $Ven = Get_Venue($V);
  $host = "https://" . $_SERVER['HTTP_HOST'];

  $ShowMode = '';
  $AtEnd = [];
  if (isset($_REQUEST['Embed'])) $ShowMode = 'Embed';
  if (isset($_REQUEST['HeaderFree'])) $ShowMode = 'HeaderFree';

  if ($ShowMode == 'HeaderFree') {
    dominimalhead("PA Requirements for " . $Ven['SN'],['files/Newstyle.css','css/festconstyle.css',"js/qrcode.js"]);
  } else {
    dostaffhead("PA Requirements for " . $Ven['SN'],["js/qrcode.js"]);
  }
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
    echo "<h3>There are currently no events at " . $Ven['SN'] . " and hence no current PA Requirements</h3>\n";
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
    echo "<h3>There are currently no events at " . $Ven['SN'] . " and hence no current PA Requirements</h3>\n";
    dotail();
    exit;
  }

//var_dump($VirtVen);

//  echo "<h2 class=FakeButton><a href=PAShow?pa4v=$V>Browsing Format</a>, <a href=PAShow?pa4v=$V&FILES=1>Embed Files</a>,  <a href=PAShow?pa4v=$V&FILES=2>Header Free for Printing</a></h2>";

  if ($ShowMode != 'HeaderFree') {
    echo "<form>" . fm_hidden('pa4v',$V);
    echo "<input type=submit name=Basic value='Browsing Format'> ";
    echo "<input type=submit name=Embed value='Embed Files'> ";
    echo "<input type=submit name=HeaderFree value='Editable/Printer Version'> ";
    echo "</form>";
  } else {
    echo "<div style='width:1000;'>";
  }

  $lastevent = -99;

  echo "<form method=post action=StewardShow?V=$V>";
  Register_AutoUpdate('EventSteward',rand(1,1000000)); //($USER['AccessLevel'] == $Access_Type['Participant']? - rand(1,1000000): $USERID));

  foreach ($EVs as $ei=>$e) {
    $eid = $e['EventId'];
    if (DayTable($e['Day'],"Event Sheet for: " . $Ven['SN'] ,'','style=font-size:24;')) {
      echo "<tr><td class=ES_Time>Time<td class=ES_What>What<td class=ES_Detail>Detail";
      $lastevent = -99;
    }

    if ($e['SubEvent'] == 0) {
      $rows = 4;
      $str = timecolon(timeadd($e['Start'], - $e['Setup'])) . "-" . timecolon($e['End']) .
        "<td class=ES_What>" . $Event_Types[$e['Type']]['SN'] . ":<td><a href=EventShow?e=" . $e['EventId'] . ">" . $e['SN'] . "</a>";
      if ($Event_Types[$e['Type']]['Public']) {
        $str .= "<tr><td class=ES_What>Price:<td class=ES_Detail>" . Price_Show($e,1);
      } else {
        $str .= "<tr><td class=ES_What><b>NOT PUBLIC</b><td class=ES_Detail>";
      }

    } else if ($e['SubEvent'] < 0) {
      $rows = 4;
      $str = timecolon(timeadd($e['Start'], - $e['Setup'])) . "-" . timecolon($e['SlotEnd']) .
        "<td class=ES_What>" . $Event_Types[$e['Type']]['SN'] . ":<td><a href=EventShow?e=" . $e['EventId'] . ">" . $e['SN'] . "</a>";
      if ($Event_Types[$e['Type']]['Public']) {
        $str .= "<tr><td class=ES_What>Price:<td class=ES_Detail>" . Price_Show($e,1);
      } else {
        $str .= "<tr><td class=ES_What><b>NOT PUBLIC</b><td class=ES_Detail>";
      }

    } else {
      $str = timecolon(timeadd($e['Start'], - $e['Setup'])) . "-" . timecolon($e['End']);
      $rows = 3;
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
    if ($e['StagePA']) { $str .= "<tr><td class=ES_What>Stage PA<td class=ES_Detail>" . $e['StagePA']; $rows++;}
    if (isset($e['With'])) {

      $rows += count($e['With']);

      if (isset($e['With'])) foreach ($e['With'] as $snum) {
        $side = Get_Side($snum);
        $str .= "<tr><td class=ES_What><a href=ShowPerf?id=$snum>" . $side['SN'] . "</a><td class=ES_Detail>";
        if ($side['StagePA'] == '@@FILE@@') {
          $files = glob("PAspecs/$snum.*");
          if ($files) {
            $Current = $files[0];
            $Cursfx = pathinfo($Current,PATHINFO_EXTENSION );
            if (file_exists("PAspecs/$snum.$Cursfx")) {
              if ($ShowMode) {
                $AtEnd[$snum] = "PAspecs/$snum.$Cursfx";
                $str .= "See Below";
              } else {
                $str .= "<a href=ShowFile?l=PAspecs/$snum.$Cursfx>View File</a>";
              }
            } else {
              $str .= "None";
            }
          } else {
            $str .= "None";
          }
        } else if ($side['StagePA']) {
          $str .= $side['StagePA'];
        } else $str .= "None";
      }
    }

    $se = $e['SubEvent'];
    $str .= "<tr>" . fm_number('Attendance',$Gash,'HowMany','','',"HowMany:$eid:$se"); // Need to think how to do these so multiple people can enter it
    $str .= "<tr>" . fm_textarea('Comments',$Gash,'HowWent',1,1,'','',"HowWent:$eid:$se");

    echo "<tr><td class=ES_Time rowspan=$rows>" . $str;
  }
  if (Access('SysAdmin')) {
    echo "<tr><td class=NotSide>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea><p><span id=DebugPane></span>";
  }
  echo "</table>\n";

  if ($ShowMode == 'HeaderFree') {

    echo "<h3> To find out more scan this:</h3>"; // pixels should be multiple of 41
    echo "<br clear=all><div id=qrcode></div>";
    echo '<script type="text/javascript">
      var qrcode = new QRCode(document.getElementById("qrcode"), {
        text: "' . $host . "/int/Access?Y=$YEAR&t=m&i=$V&k=" . $Ven['AccessKey'] . '",
        width: 205,
        height: 205,
      });
      </script>';
  }


  if ($AtEnd) {
    echo "<br clear=all><p>";
    foreach($AtEnd as $snum=>$IncFile) {
      $side = Get_Side($snum);
      echo "<h2>" . $side['SN'] . "</h2>";
      ViewFile($IncFile,1,'',0);
    }
  }

  if ($ShowMode == 'HeaderFree') {
    exit;
  }

  if (Access('Staff')) {

    echo "<h3>Link to send to Manager/Steward: $host/int/Access?Y=$YEAR&t=p&i=$V&k=" . $Ven['AccessKey'];
    if (Access('SysAdmin')) echo "<a href='Access?Y=$YEAR&t=p&i=$V&k=" . $Ven['AccessKey'] . "'> Use\n";
    echo "</h3>\n";
  }
  echo "</div>";
  dotail();

?>
