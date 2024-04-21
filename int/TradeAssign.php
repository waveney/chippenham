<?php
  include_once("fest.php");
  A_Check('Committee','Trade');

  dostaffhead("Manage Trade Pitches",['js/Trade.js']);

  include_once("TradeLib.php");
  include_once("PitchMap.php");

  global $Pitches,$tloc,$loc,$Traders,$Trade_State,$db,$Trade_Types,$Trade_Types;
  $Trade_Types = Get_Trade_Types(1);

  function TraderList($Message='',$trloc) {
    global $Pitches,$tloc,$loc,$Traders,$Trade_Types,$Trade_State;
    echo "<div class=PitchWrap><div class=PitchCont>";
    if (!$Traders) {
      echo "No Traders Here Yet";
    } else {
      echo "<div class=Scrolltable><table border><tr><td>Name<td>info<td>Size<td>Pitch";
      foreach ($Traders as $Trad) {
        $tid = $Trad['Tid'];
        echo "<tr><td draggable=true class='TradeName Trader$tid' id=TradeN$tid ondragstart=drag(event) ondragover=allow(event) ondrop=drop(event) " .
             "style='background:" . (($Trad['PAID']??0) ? $Trade_Types[$Trad['TradeType']]['Colour'] : 'white' ) . "'>" . 
             preg_replace('/\|/','',$Trad['SN']);
        if (!($Trad['PAID']??0)) {
          echo " <span class=err>" . ($Trad['BookingState'] == $Trade_State['Quoted'] ?"NOT ACCEPTED": "NOT PAID") .  "</span>";
        }
        echo "<td><img src=/images/icons/information.png width=20 " /* title='" . $Trad['GoodsDesc'] . "' */ . " onclick=UpdateTraderInfo($tid)><td>";          
        $pitched = 0;
        for ($i=0; $i<3; $i++) 
          if ($Trad["PitchLoc$i"] == $trloc) {
            if ($pitched) {
              if (preg_match('/^(\d*)/',$Trad["PitchSize$i"],$mtch) && ($mtch[1]==0)) continue;
              echo "<tr><td><td>&amp;<td>";
            }
            echo $Trad["PitchSize$i"] . "<td id=PitchLoc$i>";
            echo fm_text0('',$Trad,"PitchNum$i",0.25,'','',"PitchNum$i:$tid");
            $pitched = 1;
          }
      }
      echo "</table></div>";
      echo "<input type=submit name=Update value=Update> <span class=Err>$Message</span>";
      echo "<a href=TradeSetup?l=$loc style='font-size:20;'>Setup</a>";
    }
    echo "</div></div>";
  }

  function Update_Pitches() {
    $Change = 0;
    foreach($_REQUEST as $P=>$V) {
      if (preg_match('/PitchNum(\d):(\d+)/',$P,$matches)) {
        $Tid = $matches[2];
        $Tpn = $matches[1];
        $Trady = Get_Trade_Year($Tid);
        if ($V != $Trady["PitchNum$Tpn"]) {
          $Trady["PitchNum$Tpn"]=$V;
          Put_Trade_Year($Trady);
          $Change = 1;
        }
      }
    }
    return $Change;
  }
  
  
  // No pitch used more than once, no invalid pitch #s (not = pitch and pitch for trade)
  // All traders have a pitch
  
  function Validate_Pitches_At($Loc) {
    global $Traders,$Pitches,$tloc,$Trade_State,$PitchesByName;
    
    $Usage = [];$TT = [];
    $NotAssign = '';
    $TLocId = $tloc['TLocId'];
    if ($Traders) {
      foreach ($Traders as $idx=>$Trad) {
        if ( $Trad['BookingState'] == $Trade_State['Deposit Paid'] || $Trad['BookingState'] == $Trade_State['Balance Requested'] || $Trad['BookingState'] == $Trade_State['Fully Paid'] ) {
          $Traders[$idx]['PAID'] = 1;
        } else {
          $Traders[$idx]['PAID'] = 0;
        } 
        for ($i=0; $i<3; $i++) {
          if ($Trad["PitchLoc$i"] == $TLocId) {
            $Found = 0;
            $list = explode(',',$Trad["PitchNum$i"]);
            foreach ($list as $p) {
              if (!$p) continue;
              if (!$Traders[$idx]['PAID']) continue;
              if ((!isset($PitchesByName[$p])) && (!isset($Pitches[$p]))) return $Trad['SN'] . " assigned to an invalid pitch number $p";
//              $Pid = $PitchesByName[$p]['id'];
              if (isset($Usage[$p])) return "Clash on pitch $p - " . $Usage[$p] . " and " . $Trad['SN'];
//              if ($Pitches[$Pid]['Type']) return $Trad['SN'] . " assigned to a non pitch";
              if ($Trad['SN']) $Usage[$p] = $Trad['SN'];
              $TT[$p] = $Trad['TradeType'];
              $Found = $p;
            }
            if ($Traders[$idx]['PAID'] && !$Found) $NotAssign = "No pitch for " . $Trad['SN'];
          }
        }
      }
    }
    return $NotAssign;
  }
  

  $trloc = $loc = Get_Location(); 
  if (isset($_REQUEST['Update'])) Update_Pitches(); // Note this can't use Update Many as weird format of ids
  $Pitches = Get_Trade_Pitches($loc);
  $PitchesByName = [];
  foreach ($Pitches as $Pi) if ($Pi['Type'] == 0) $PitchesByName[$Pi['SN'] ?? $Pi['Posn']] = $Pi;
  $tloc = Get_Trade_Loc($loc);
  if ($tloc['PartOf']) {
    $trloc = $tloc['PartOf'];
  }
  
  $Traders = Get_Traders_For($trloc,0); // Only those who have accepted/paid 1);
  
  echo "<form method=post>";
  echo fm_hidden('l',$loc);

  echo "<h2>Pitch setup for " . $tloc['SN'] . "</h2>";
  echo "<b>Note Drag and drop is not working yet</b><br>\n";
  $Message = Validate_Pitches_At($loc);

  echo Pitch_Map($tloc,$Pitches,$Traders,4,1,'TradeAssign');

//  Pitch_Map($tloc,$Pitches,$Traders);
  TraderList($Message,$trloc);
  echo "<h2><a href=TradeLocs?Y=$YEAR>Trade Locs</a></h2>";
  
  echo "<div id=TraderInfo><div id=TraderContent>Info about a selected trader appears here</div></div>";
  
  dotail();
 

