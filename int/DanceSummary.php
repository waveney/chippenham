<?php
  include_once("fest.php");
  A_Check('Staff');

  dostaffhead("Dance Summary");
  include_once("DanceLib.php");
  global $YEARDATA,$Coming_Type,$PLANYEAR;
  global $db;


  echo "<h2>Dance Summary $PLANYEAR</h2>\n";
  $mtch = [];
  $Types = Get_Dance_Types(1);
  $Category = array(// (Show Condition - optional)Text => SQL test
                'Invited'=>"y.Invited<>''",
                'Coming'=>("y.Coming=" . $Coming_Type['Y']),
                'Possibly'=>( "y.Coming=" . $Coming_Type['P']),
                'Not Coming'=>( "( y.Coming=" . $Coming_Type['N'] . " OR y.Coming=" . $Coming_Type['NY'] . " )"),
                'Recieved'=>( "y.Coming=" . $Coming_Type['R']),
                'No Reply'=>( "y.Invited<>'' AND y.Coming=0" ),
                'Bl'=>"Blank",
                'Coming on Sat'=>("y.Coming=" . $Coming_Type['Y'] . " AND y.Sat=1 "),
                'Coming on Sun'=>("y.Coming=" . $Coming_Type['Y'] . " AND y.Sun=1 "),
                '($YEARDATA["FirstDay"] <= 3 && $YEARDATA["LastDay"] > 2)Coming on Mon'=>("y.Coming=" . $Coming_Type['Y'] . " AND y.Mon=1 "),
                'Bla'=>"Blank",
                'Fri Evening'=>("y.Coming=" . $Coming_Type['Y'] . " AND y.FriEve=1 "),
                'Sat Evening'=>("y.Coming=" . $Coming_Type['Y'] . " AND y.SatEve=1 "),
                '($YEARDATA["FirstDay"] <= 3 && $YEARDATA["LastDay"] > 2)Sun Evening'=>("y.Coming=" . $Coming_Type['Y'] . " AND y.SunEve=1 "),
                );

  echo "<div class=Scrolltable><table border><tr><th>Category<th>Total";
  if ($Types) foreach ($Types as $typ) echo "<th style='background:" . $typ['Colour'] . ";'>" . $typ['SN'];
  echo "<th>Other</tr>\n";


  foreach ($Category as $cat=>$srch) {
    if ($srch == 'Blank') { echo "<tr height=15>"; continue; }
    if (preg_match('/\((.*?)\)(.*)/',$cat,$mtch)) {
      if (!eval("return " . $mtch[1] . " ;")) continue;
      $cat = $mtch[2];
    }

    $qtxt = "SELECT y.SideId FROM SideYear y WHERE y.Year='$PLANYEAR' AND y.SideId>0 AND $srch";
    $qry = $db->query($qtxt);
    $catcount = $qry->num_rows;
    echo "<tr><td>$cat<td align=right>$catcount";
    $runtotal=0;
    if ($Types) foreach($Types as $typ) {
      $lctyp = strtolower($typ['SN']);
      $qtxt = "SELECT y.SideId, s.Type FROM SideYear y, Sides s WHERE y.SideId=s.SideId AND y.SideId>0 AND y.Year='$PLANYEAR' AND $srch " .
                "AND LOWER(s.Type) LIKE '%$lctyp%'";
//var_dump($qtxt);
      $qry = $db->query($qtxt);
      $tcount = $qry->num_rows;
      //echo "<td>(($qtxt)) $tcount";
      echo "<td align=right style='background:" . $typ['Colour'] . ";'>$tcount";
      $runtotal += $tcount;
    }
    echo "<td align=right>" . max(0,$catcount - $runtotal) . "</tr>\n";
  }
  echo "</table></div>\n";
  dotail();
?>

