<?php
  include_once("fest.php");
  include_once("VolLib.php");
  include_once("TradeLib.php");
  include_once("DanceLib.php");
  A_Check('Committee');

  dostaffhead("View Campsite Useage");

  global $USER,$PLANYEAR,$db;
  echo "<div class='content'><h2>Campsite Usage</h2>\n";
  
  $CampSites = Gen_Get_All('Campsites');
  $CampTypes = Gen_Get_All('Camptypes');


// Performer Records
  $LowestSY = Gen_Get_Cond1('SideYear',"Year=$PLANYEAR ORDER BY syId ASC");
  $LowId = ($LowestSY? $LowestSY['syId'] : 0);
  $HighestSY = Gen_Get_Cond1('SideYear',"Year=$PLANYEAR ORDER BY syId DESC");
  $HighId = ($HighestSY? $HighestSY['syId'] : 0);

  $PerfUse = Gen_Get_Cond('CampUse',"SideYearId>=$LowId AND SideYearId<=$HighId AND Number>0 ORDER BY SideYearId, CampType");
  
  $Use = $Total = [];
  $LastSY = $LastAct = 0;
  $SNames = [];
  
  foreach ($PerfUse as $PU) {
    $Csi = $PU['CampSite'];
    if ($PU['SideYearId'] != $LastSY) {
      $LastAct = 0;
      $LastSY = $PU['SideYearId'];
      $Sidey = Gen_Get('SideYear',$PU['SideYearId'],'syId');
      if ($Sidey['Coming'] !=2 && $Sidey['YearState'] < 2) continue; // Not Confirmed
      $Side = Get_Side($Sidey['SideId']);
      $SNames[$Sidey['SideId']] = $Side['SN'];
//      $Use[$Csi]['Cat'] = 1;
      $LastAct = 1;
    }
    if ($LastAct == 0) continue; // Not Confirmed
    $Use[$Csi][$LastSY]['Sid'] = $Sidey['SideId'];
    $Cti = $PU['CampType'];
    $Use[$Csi][$LastSY][$Cti]= $PU['Number'];
    if (isset($Total[$Csi][$Cti])) { 
      $Total[$Csi][$Cti] += $PU['Number'];
    } else {
      $Total[$Csi][$Cti] = $PU['Number'];
    }
  }

// Volunteer Records TODO

  $VolMgr = Access('Committee','Volunteers');
  $res=$db->query("SELECT * FROM Volunteers WHERE Status=0 ORDER BY SN");
  $VolList = [];
  
  if ($res) while ($Vol = $res->fetch_assoc()) {
    $id = $Vol['id'];
    if (empty($id) || empty($Vol['SN']) || empty($Vol['Email']) ) continue;
    $VY = Get_Vol_Year($id);
    if (($VY['Year'] != $PLANYEAR) || empty($VY['id']) || ($VY['Status'] == 2) || ($VY['Status'] == 4) || $VY['CampNeed']<10) continue;
    
    $Volunt[$id] = $Vol;
//    $link = "<a href=Volunteers?A=" . ($VolMgr? "Show":"View") . "&id=$id>";
//    echo "<tr" . ((($VY['Year'] != $PLANYEAR) || empty($VY['id']) || ($VY['Status'] == 2) || ($VY['Status'] == 4))?" class=FullD hidden" : "" ) . ">";
    $Csi = $VY['CampNeed'];
    $VolList[] = $id;
    $Use[$Csi][-$id]['Sid'] = $id;
    if ($VY['CampNeed']<20) {
      $Cti = $VY['CampType'];
      $Use[$Csi][-$id][$Cti] = 1;


      if (isset($Total[$Csi][$Cti])) { 
        $Total[$Csi][$Cti] += 1;
      } else {
        $Total[$Csi][$Cti] = 1;
      }     
    } else {
      $Use[$Csi][-$id]['T'] = $VY['CampText'];
      if (isset($Total[$Csi])) {
        $Total[$Csi]['Users'] ++;
      } else  {
        $Total[$Csi]['Users'] = 1;
      }
    }
  }

  $TradeList = [];
  $TradeCamp = Gen_Get_Cond('TradeYear',"Year=$PLANYEAR AND CampNeed>=10 AND BookingState>5 AND BookingState<10",'TYid');
  foreach($TradeCamp as $TC) {
    $Tid = $TC['Tid'];
    $TraderList[$Tid] = Get_Trader_All($Tid);
    $Csi = $TC['CampNeed'];
    $Use[$Csi][-1000000-$Tid]['Sid'] = $Tid;
    if ($TC['CampNeed']<20) {
      $Cti = $TC['CampType'];
      $Use[$Csi][-1000000-$Tid][$Cti] = 1;

      if (isset($Total[$Csi][$Cti])) { 
        $Total[$Csi][$Cti] += 1;
      } else {
        $Total[$Csi][$Cti] = 1;
      }     
    } else {
      $Use[$Csi][-1000000-$Tid]['T'] = $TC['CampText'];    
      if (isset($Total[$Csi])) {
        $Total[$Csi]['Users']++;
      } else {
        $Total[$Csi]['Users'] = 1;
      }
    }
  }
  

//echo "Totals: ";var_dump($Total);
// echo "<p>Use: ";var_dump($Use);
//echo "<p>Vols: "; var_dump($Volunt);

  echo "<button class='floatright FullD' onclick=\"($('.FullD').toggle())\">All Details</button>" .
       "<button class='floatright FullD' hidden onclick=\"($('.FullD').toggle())\">Just Totals</button> ";

  echo "This only lists CONFIRMED bookings.<p>";
  echo "<table border><tr><th>Campsite, who<th>Users";
  foreach ($CampTypes as $CT) echo "<th>" . $CT['Name'];
  foreach ($CampSites as $CS) {
    $Csi = $CS['id'];

    echo "<tr class=FullD hidden><td><h2>" . $CS['Name'] , "</h2>";
//    foreach($CampTypes as $CT) echo "<td>" . $CT['Name'];
    
    for($Syid = $LowId; $Syid <= $HighId; $Syid++) {
      if (!isset($Use[$Csi][$Syid]['Sid'])) continue;
      
      echo "<tr class=FullD hidden><td><a href=AddPerf?id=" . $Use[$Csi][$Syid]['Sid'] . ">" . $SNames[$Use[$Csi][$Syid]['Sid']] . "</a><td>";
      foreach($CampTypes as $CT) echo "<td>" . (isset($Use[$Csi][$Syid][$CT['id']])? $Use[$Csi][$Syid][$CT['id']]:'');
    }

    foreach($VolList as $V) {
//echo "V is $V<br>";
      if (!isset($Use[$Csi][-$V]['Sid'])) continue;
      echo "<tr class=FullD hidden><td>Volunteer - <a href=Volunteers?A=" . ($VolMgr? "Show":"View") . "&id=$V>" . $Volunt[$V]['SN'] . "</a><td>";
      if (isset($Use[$Csi][-$V]['T'])) {
        echo "<td colspan=4>" . $Use[$Csi][-$V]['T'];
      } else {
        foreach($CampTypes as $CT) echo "<td>" . (isset($Use[$Csi][-$V][$CT['id']])? $Use[$Csi][-$V][$CT['id']]:'');
      }
    }
      
    foreach($TraderList as $Tid=>$T) {
      if (!isset($Use[$Csi][-1000000-$Tid]['Sid'])) continue;
      echo "<tr class=FullD hidden><td>Trader - <a href=Trade?id=$Tid>" . $T['SN'] . "</a><td>";
      if (isset($Use[$Csi][-1000000-$Tid]['T'])) {
        echo "<td colspan=4>" . $Use[$Csi][-1000000-$Tid]['T'];
      } else {
        foreach($CampTypes as $CT) echo "<td>" . (isset($Use[$Csi][-1000000-$Tid][$CT['id']])? $Use[$Csi][-1000000-$Tid][$CT['id']]:'');
      }
    }

    echo "<tr><td class=FullD>" . $CS['Name'] . "<td class=FullD hidden><b>Totals:</b><td>";
    if (empty($Total[$Csi]['Users'])) {
      echo "-"; 
      foreach ($CampTypes as $CT) echo "<td>" . (isset($Total[$Csi][$CT['id']])?$Total[$Csi][$CT['id']]:0);
    } else {
      echo $Total[$Csi]['Users'] . "<td colspan=4> - ";
    }
    
  }
  echo "</table>";
  

  dotail();

?>
