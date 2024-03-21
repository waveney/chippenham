<?php
  include_once("int/fest.php");

  dohead("Sponsorship",[],1);

  echo TnC('Sponsor_Page');

  global $SHOWYEAR;
  set_ShowYear();
  echo "<h2>Our Sponsors in " . substr($SHOWYEAR,0,4) . "</h2>";

  echo "<div class=sponflexwrap>\n";

  include_once("int/TradeLib.php");
  include_once("int/Biz.php");
  include_once("int/DispLib.php");
  $Spons = Get_Sponsors();
  shuffle($Spons);

  foreach ($Spons as $s) {
    echo "<div class=sponflexcont>\n";
    if ($s['Website']) echo weblinksimple($s['Website']);
    if ($s['Image']) echo "<img src='" . $s['Image'] . "' width=200>";
    echo "<div class=sponttl>" . $s['SN'] . "</div>";
    if ($s['Website']) echo "</a>";
    if ($s['Description']) echo "<p>" . $s['Description'];
    
    $Sponsrd = Gen_Get_Cond('Sponsorship',"Year=$SHOWYEAR AND SponsorId=" . $s['SponsorId'] . " ORDER BY Importance Desc");
    
    if ($Sponsrd) {
      $SPlst = [];
      foreach ($Sponsrd as $Sp) {
        $SpType = $Sp['ThingType'];
        $SpId = ($Sp['ThingId'] ??0);
        
        switch ($SponTypes[$SpType]) {
          case 'General':
            $SPlst[]= "the Festival";
            break;
          case 'Venue':
            $Ven = Get_Venue($SpId);
            $SPlst[]=  "<a href=int/VenueShow?v=$SpId>" . ($Ven['SN'] ?? '<span class=Err>Unknown</span>') . "</a>";
            break;
          case 'Event':
            $Ev = Get_Event($SpId);
            if ($Ev) $Ven = Get_Venue($Ev['Venue']);
            $SPlst[]=  "<a href=int/EventShow?e=$SpId>" . ($Ev['SN'] ?? '<span class=Err>Unknown</span>') . 
                       "</a> at <a href=int/VenueShow?v=" . ($Ev['Venue'] ??0) . ">" . ($Ven['SN'] ?? '<span class=Err>Unknown</span>') . "</a> on " . 
                       ($Ev? (FestDate($Ev['Day'],'S') . " at " . timecolon($Ev['Start'])) : "<span class=Err>Unknown</span>");
            break;
          case 'Performer':
            $Perf = Get_Side($SpId);
            $SPlst[]= "<a href=ShowPerf?id=$SpId>" . ( $Perf['SN']  ?? '<span class=Err>Unknown</span>') . "</a>";
            break;
        }
      }
      
      $Last = array_pop($SPlst);
      if ($SPlst) {
        echo "<p>" . $s['SN'] . " is sponsoring: " . implode(', ',$SPlst) . " and $Last"; 
      } else {
        echo "<p>" . $s['SN'] . " is sponsoring $Last";
      }
    
    } else {
      echo "<p>" . $s['SN'] . " is sponsoring the Festival";
    }
    echo "</div>\n";
  }
  echo "</div>";

  dotail();