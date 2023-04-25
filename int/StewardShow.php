<?php
  include_once("fest.php");
  
  $V = $_REQUEST['pa4v'];
  A_Check('Participant','Venue',$V);
  
  include_once("ProgLib.php");
  include_once("DanceLib.php");
  include_once("ViewLib.php");
  global $YEAR,$FESTSYS,$USERID,$USER, $Access_Type;
    
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
  Register_AutoUpdate('VenueManager',($USER['AccessLevel'] == $Access_Type['Participant']? - rand(1,1000000): $USERID));
  
  foreach ($EVs as $ei=>$e) {
    $eid = $e['EventId'];
    if (DayTable($e['Day'],"Event Sheet for " . $Ven['SN'] ,'','style=font-size:24;')) {
      echo "<tr><td>Time<td>What<td colspan=3>Detail";
      $lastevent = -99;
    }

    $str = timecolon(timeadd($e['Start'], - $e['Setup'])) . "-" . timecolon($e['End']) . "<td>" . ($e['SubEvent']<1?$e['SN']:"") ;
    
    $rows = 4;
    if ($e['NeedSteward']) { $str .= "<tr><td>Stewards<td colspan=3>" . $e['StewardTasks']; $rows++;}
    if ($e['SetupTasks']) { $str .= "<tr><td>Setup<td colspan=3>" . $e['SetupTasks']; $rows++;}
    $str .= "<tr><td>Price:<td>" . Price_Show($e,1);
    if ($e['StagePA']) { $str .= "<tr><td>Stage PA<td colspan=3>" . $e['StagePA']; $rows++;}
    $str .= "<tr>" . fm_text('Attendance',$e,'HowMany',3,'',"HowMany:$eid"); // Need to think how to do these so multiple people can enter it
    $str .= "<tr>" . fm_text('Comments',$e,'HowWent',3,'',"HowWent:$eid");

    if (isset($e['With'])) {
    
      $rows += count($e['With']);
    
      if (isset($e['With'])) foreach ($e['With'] as $snum) {
        $side = Get_Side($snum);
        $str .= "<tr><td>" . $side['SN'] . "<td colspan=3>";
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
    
    echo "<tr><td rowspan=$rows>" . $str;
  }
  echo "</table>\n";
  
  if ($AtEnd) {
    foreach($AtEnd as $snum=>$IncFile) {
      $side = Get_Side($snum);
      echo "<h2>" . $side['SN'] . "</h2>";
      ViewFile($IncFile,1,'',0);
    }
  }
 
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
