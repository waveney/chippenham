<?php
  include_once("fest.php");
  include_once("festcon.php");

  dominimalhead("Trade Stand Maps",['css/PrintPage.css']);

  include_once("TradeLib.php");
  include_once("PitchMap.php");
/* If logged in or trade stae >=partial view actual traders, otherwise just the grid */

  global $Pitches,$tloc,$loc,$YEARDATA,$EType_States,$Traders,$USER,$USERID;
  $Locs = Get_Trade_Locs(1);
  $BreakNeeded = 0;
  
  foreach($Locs as $loc=>$tloc) {
    if ($tloc['InUse'] == 0) continue;
    if (empty($tloc['MapImage'])) continue;
   
    if ($BreakNeeded)     echo "<p class=PageBreak>";

    $trloc = ($tloc['PartOf']?$tloc['PartOf']:$loc);
    
    echo "<h2>" .$tloc['SN'] . "</h2>";
    $Traders = Get_Traders_For($trloc,1);
    
    $Pitches = Get_Trade_Pitches($loc);  
//  var_dump($ShowTraders,$Traders);

    echo Pitch_Map($tloc,$Pitches,$Traders,3,1,'');
    $BreakNeeded = 1;
  }
  
  dotail();
  