<?php
  include_once("int/fest.php"); 

  set_ShowYear();
  include_once("TradeLib.php");
  global $db,$YEAR,$SHOWYEAR,$PLANYEAR,$Trade_States,$Trade_State,$YEAR,$Trade_Days,$Prefixes, $YEARDATA, $EType_States;

  dohead("Traders in $YEAR",['css/festconstyle.css'],'','T');


  $Partial = (array_flip($EType_States))['Partial'];
  if ($YEARDATA['TradeState'] < $Partial && !isset($_REQUEST['STAFF'])) {
    echo "Sorry the Traders are not yet listed for " . substr($SHOWYEAR,0,4) . "<p>";
    
    echo "Here is what was here last year<p>";
    
    set_ShowYear(Feature('PrevRealFest',$YEARDATA['PrevFest']));
//    dotail();
  }

  global $Locs,$LocUsed;
  $Locs = Get_Trade_Locs(1);
  $TTypes = Get_Trade_Types(1);
  $Traders = Get_Traders_Coming(1,"Fee DESC");
  $TTUsed = $LocUsed = $AllList = [];
  $Staff = (isset($_REQUEST['STAFF'])?'&STAFF':'');
//  var_dump($Traders);
  
  if ($Traders) foreach ($Traders as $ti=>$Trad) {
    if (!$Trad['ListMe'] && !Access('Staff')) continue;
    $TT = $Trad['TradeType'];
    $TTUsed[$TT][] = $ti;
    
    for ($i=0; $i<3; $i++) {
      if (!isset($Trad["PitchLoc$i"])) continue;
      $L = $Trad["PitchLoc$i"];
      if ($L) {
        $LocUsed[$L][] = $ti;
        if ($Locs[$L]['PartOf']) $LocUsed[$Locs[$L]['PartOf']][] = $ti;
      }
    }
    $AllList[] = $ti; 
  }
  
  $Overview = $Locs[Feature('TradeBaseMap')];

function ShowForm($Dir='H',$Loc=0,$Type=0) {
  global $Locs,$LocUsed,$YEAR, $TTypes, $TTUsed;
// Work OUt the selection form
  $Staff = (isset($_REQUEST['STAFF'])?'&STAFF':'');

  $ShowForm = "<form>" . fm_hidden('Y',$YEAR);
  if ($Loc) $ShowForm .= fm_hidden('CurLoc',$Loc);
  if ($Type) $ShowForm .= fm_hidden('CurType',$Type);
  if ($Staff) $ShowForm .= fm_hidden('STAFF',1);

  $ShowForm .= "<div class=tablecont><table class=InfoTable>";
  $ShowForm .=  "<tr><td>Show by Location:"; // <td>Show by Type

  $ShowForm .=  (($Dir=='H')?"<td>":"");
    foreach($Locs as $loc) {
      if ($loc['InUse'] && isset($LocUsed[$loc['TLocId']]) && !$loc['NoList']) {
        $ShowForm .=  (($Dir=='H')?"":"<tr><td>");
        $ShowForm .=  "<input type=submit name=SELLoc value='" . $loc['SN'] . "'> ";
      }
    }
  $ShowForm .=  (($Dir=='H')?"<td>":"<tr><td>");
  $ShowForm .=  "<input type=submit name=SELLoc value='Show All Locations'> ";

  $ShowForm .=  "<tr><td>Show by Type:";

  $ShowForm .=  (($Dir=='H')?"<td>":"");

  foreach($TTypes as $typ) {
    if (!$typ['Addition'] && isset($TTUsed[$typ['id']])) 
      $ShowForm .= '<input type=submit name=SELType value="' . $typ['SN'] . '" style="background:' . $typ['Colour'] . ';color:black;"> ';
  }

  $ShowForm .=  (($Dir=='H')?"<td>":"<tr><td>");
  $ShowForm .=  "<input type=submit name=SELType value='Show All Types'> ";
  $ShowForm .=  "</table></div></form><p>";
  return $ShowForm;
}

// Work out pitches, Map and Title (if any)
  $List = [];
  $SLoc = 0;
  $Title = '';
  $Scale = 1;
  $ShowTraders = 0;

  if (isset($_REQUEST['l'])) {
    if (is_numeric($_REQUEST['l'])) {
      $PLocId = $LocId = $_REQUEST['l'];
      if ($Locs[$LocId]['PartOf']) $PLocId = $Locs[$LocId]['PartOf'];
      $List = $LocUsed[$PLocId];

      $SLoc = $loc = $Locs[$LocId];
      $Title = 'All Traders ' . $Prefixes[$loc['prefix']] . ' ' . $loc['SN'];
      $Pitches = Get_Trade_Pitches($LocId);
      $ShowTraders = 1;

      } else {
      $_REQUEST['SELLoc'] = $_REQUEST['l'];
    }
  } 
  
  if (isset($_REQUEST['SELLoc'])) {
    $sel = $_REQUEST['SELLoc'];
    $sel = preg_replace('/_/',' ',$sel);
    if ($sel == 'Show All Locations') {
      $List = $AllList;
      $Title = 'All Traders';
      $ShowTraders = 1; 
      } else {
      foreach($Locs as $loc) 
        if ($sel == $loc['SN']) {
          $List = $LocUsed[$loc['TLocId']];
          $SLoc = $loc;
          $Title = 'All Traders ' . $Prefixes[$loc['prefix']] . ' ' . $loc['SN'];
          $Pitches = Get_Trade_Pitches($loc['TLocId']);
          $ShowTraders = 1;
          break;
        }
      if (!$List) foreach($TTypes as $typ)
        if ($sel == $typ['SN']) {
          $List = $TTUsed[$typ['id']];
          $Title = 'All ' . $typ['SN'] . " Traders";
          $ShowTraders = 1;
          break;
        }
    }
  }
  
  if (isset($_REQUEST['SELType'])) {
    $sel = $_REQUEST['SELType'];
    $sel = preg_replace('/_/',' ',$sel);
    if ($sel == 'Show All Types') {
      $List = $AllList;
      $Title = 'All Traders';
      $ShowTraders = 1;

      } else if (!$List) {
      foreach($TTypes as $typ) {
        if ($sel == $typ['SN']) {
          $List = $TTUsed[$typ['id']];
          $Title = 'All ' . $typ['SN'] . " Traders";
          $ShowTraders = 1;
          break;
        }
      }
    }
  }
  
  
  if ($SLoc) {
    echo ShowForm();
    if ($Title) echo "<h2>$Title</h2>";
    Pitch_Map($SLoc,$Pitches,$Traders,0,1,'TradeShow') ;
  } else if ($Overview) {
    $Pitches = Get_Trade_Pitches($Overview['TLocId']);
//    echo "<div style='float:left;display:inline'>" . ShowForm(($Scale==1)?'V':'H') . " </div>"; 
    echo ShowForm();
    Pitch_Map($Overview,$Pitches,0,0,$Scale,'TradeShow');

  }
  echo "<br clear=all><p>";

  if (!isset($_REQUEST['SELType']) && !isset($_REQUEST['SELLoc']) ) dotail();  
   
  if ($YEAR != $PLANYEAR) {
    echo "These traders where at the Folk Festival.<p>";
  } else {
    echo "These traders will be at the Folk Festival.<p>";
  }
  echo "To become a trader see the <a href=/InfoTrade>trade application page</a>.  ";
  echo "Only those traders who have paid their deposits are shown here.<p>";

 
  echo "<div id=flex>\n";
  $Done = [];
  if ($List) foreach($List as $ti) {
    $trad = $Traders[$ti];
    if (isset($Done[$ti])) continue;
    $Doone[$ti]=1;
 //var_dump($ti,$trad);
    echo "<div class=TradeFlexCont id=Trader" . $trad['Tid'] .  ">";
    if ($trad['Website']) echo weblinksimple($trad['Website']);

    if ($trad['Photo']) echo "<img src=" . $trad['Photo'] . ">";
    echo "<h2>" . $trad['SN'] . "</h2>";
    if ($trad['Website']) echo "</a>";
    
    $txt = nl2br($trad['GoodsDesc']);
    
    echo "<div class=Tradetext>$txt</div>"; // TODO Handle non html chars also do double nl to p
    
    if (!$SLoc) {
      if ($trad['PitchLoc0'] == $trad['PitchLoc1']) $trad['PitchLoc1'] = 0;
      if ($trad['PitchLoc0'] == $trad['PitchLoc2']) $trad['PitchLoc2'] = 0;    
      if ($trad['PitchLoc1'] == $trad['PitchLoc2']) $trad['PitchLoc2'] = 0;    
    
      if (isset($Locs[$trad['PitchLoc0']])) {
        echo ($YEAR >= $PLANYEAR?"<p>Will be trading ":"<p>Was trading ") . $Prefixes[$Locs[$trad['PitchLoc0']]['prefix']] . ' ' . $Locs[$trad['PitchLoc0']]['SN'];
        if ($trad['PitchLoc2']) {
          echo ", " . $Prefixes[$Locs[$trad['PitchLoc1']]['prefix']] . ' ' . $Locs[$trad['PitchLoc1']]['SN'] . " and " 
           		. $Prefixes[$Locs[$trad['PitchLoc1']]['prefix']] . ' ' . $Locs[$trad['PitchLoc2']]['SN'];
        } else if ($trad['PitchLoc1']) {
          echo " and " . $Prefixes[$Locs[$trad['PitchLoc1']]['prefix']] . ' ' . $Locs[$trad['PitchLoc1']]['SN'];
        }
      if ($trad['Days']) echo " on " . $Trade_Days[$trad['Days']];
      } else {
        if ($trad['Days']) echo $Trade_Days[$trad['Days']];    
      }
    }
    echo "</div>";
  }
  echo "</div>";
  dotail();
