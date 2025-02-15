<?php
  include_once("int/fest.php");
  set_ShowYear();
  global $YEARDATA,$PLANYEARDATA,$Months,$SHOWYEAR,$PLANYEAR;
  include_once("int/TradeLib.php");
  include_once("int/NewsLib.php");
  include_once("int/DispLib.php");
  include_once("int/DateTime.php");

  if (isset($_REQUEST['F']) && Access('Staff') && is_numeric($_REQUEST['F']) ) {
    $future = $_REQUEST['F'];
  } else {
    $future = 0;
  }

//  set_ShowYear();
  $DFrom = ($YEARDATA['DateFri']+$YEARDATA['FirstDay']);
  $DTo = ($YEARDATA['DateFri']+$YEARDATA['LastDay']);
  $DMonth = $Months[$YEARDATA['MonthFri']];

  $D2F = Days2Festival();

  $ShowDates = 0;
  if ($SHOWYEAR == $PLANYEAR) $ShowDates+=1;
  if ($D2F > -4) $ShowDates +=2;
  if (!empty($YEARDATA['NextFest'])) $ShowDates+=4;
  if ($YEARDATA['Years2Show'] == 0) $ShowDates+=8;

  $ShowActions = [1,0,1,0,3,3,4,4,1,1,1,0,1,1,1,0];

  if (($YEARDATA['Years2Show'] > 0)) {
    $NEXTYEARDATA = Get_General($YEARDATA['NextFest']);
//    var_dump($NEXTYEARDATA);
    $NFrom = ($NEXTYEARDATA['DateFri']+$NEXTYEARDATA['FirstDay']);
    $NTo = ($NEXTYEARDATA['DateFri']+$NEXTYEARDATA['LastDay']);
    $NMonth = $Months[$NEXTYEARDATA['MonthFri']];
    $NYear = substr($YEARDATA['NextFest'],0,4);
  }


  $Sy = substr($PLANYEAR,0,4);
  $TopBans = Get_All_Articles(0,'TopPageBanner',$future);
// var_dump($TopBans);
  $Banner  = "<div class=WMFFBanner400>";

  if ($TopBans) { // Not modified for ShowDates (Yet)
    $Imgs = explode("\n",$TopBans[0]['Text']);


    $Banner .= '<div class="rslides_container" style="margin:0;"><ul class="rslides" id="slider1">';

    foreach ($Imgs as $img) {
      $Banner .= "<li><img src='$img' class=WMFFBannerDefault>";
    }
    $Banner .= '</ul></div><script>$(function() { $(".rslides").responsiveSlides({timeout: ' .
      Feature('TopBannerScrollRate',2000) . '}); });</script>';
  } else {
    $Banner  = "<div class=WMFFBanner400><img src=" . Feature('DefaultPageBanner') . " class=WMFFBannerDefault>";
  }
  $Banner .= "<div class=BanOverlay>" . Feature('BannerOverlay');
  $Banner .= "</div>";

  switch ($ShowActions[$ShowDates]) {
    case 0: // This year
        $Banner .= "<div class=BanDates2>Diary dates: $DFrom<sup>" . ordinal($DFrom) . "</sup> - $DTo<sup>" . ordinal($DTo) .
               "</sup> $DMonth $Sy<div class=BanNotice></div></div>";
        break;

    case 1: // This year past
        $Banner .= "<div class=BanDates2>The festival was: $DFrom<sup>" . ordinal($DFrom) . "</sup> - $DTo<sup>" . ordinal($DTo) .
               "</sup> $DMonth $Sy<div class=BanNotice></div></div>";
        break;

    case 3: // Both, this has past
        $Banner .= "<div class=BanDates2>The festival was: $DFrom<sup>" . ordinal($DFrom) . "</sup> - $DTo<sup>" . ordinal($DTo) .
               "</sup> $DMonth $Sy<div class=BanNotice></div>";
        $Banner .= "Next year's dates: $NFrom<sup>" . ordinal($NFrom) . "</sup> - $NTo<sup>" . ordinal($NTo) .
               "</sup> $NMonth $NYear<div class=BanNotice></div></div>";

    case 4: // Both in future
        $Banner .= "<div class=BanDates2>Diary dates: $DFrom<sup>" . ordinal($DFrom) . "</sup> - $DTo<sup>" . ordinal($DTo) .
               "</sup> $DMonth $Sy<div class=BanNotice></div>";
        $Banner .= "Next year's dates: $NFrom<sup>" . ordinal($NFrom) . "</sup> - $NTo<sup>" . ordinal($NTo) .
               "</sup> $NMonth $NYear<div class=BanNotice></div></div>";
  }

  $Banner .= "<div class=TornWrapper><img align=center src=/images/icons/torn-top.png class=TornTopEdge></div>";
  $Banner .= "</div>";
  dohead("$DFrom - $DTo $DMonth $Sy", ['/js/WmffAds.js', "/js/HomePage.js", "/js/Articles.js"],$Banner );

  $TopShow = "Top";
  if ( !Show_Articles_For($TopShow,$future)) {
    echo "<center><a href=/Tickets>Details to Follow</a></center>";
  }
  echo "<br clear=all>";

  echo "<div style=margin:10>";
  echo '<center><h2>Sponsors & Supporters</h2></center>';
  echo "<center>Our festival would not be possible without the amazing help and generosity of many organisations including the following major sponsors<p>";
  echo "</center>";
  $Spons = Get_Sponsors(1);// echo count($Spons);
  $SPSize = '0';
//  if (count($Spons) < 5) { $SPSize = "300";} elseif (count($Spons) < 7) { $SPSize = "150";}
  echo "<div hidden>" . fm_hidden('ChangeTime',Feature('SponsorTime',2000)); // IN msec
  echo fm_hidden('SPSize',$SPSize);
  foreach ($Spons as $s) {
    if ($s['Importance'] < 0) continue;
    echo "<li class=SponsorsIds id=" .$s['id'] . " data-i=" . ($s['Importance']+1) . "><div class=sponcontainer$SPSize><div class=sponContent>";
    if ($s['Website']) echo weblinksimple($s['Website']);
    if (!empty($s['Logo']) || !empty($s['Photo'])) echo "<img src='" . (empty($s['Logo'])?$s['Photo']: $s['Logo']) . "' class=sponImage$SPSize>";
    if ($s['IandT']) echo "<div class=sponText>" . $s['SN'] . "</div>";
    if ($s['Website']) echo "</a>";
    echo "</div></div>";
  }
  echo "</div>\n";
  echo "<center><table style='table-layout: fixed;width:100%' id=SponDisplay><tr id=SponsorRow height=240></table></center>";

  if ($future) {
    echo "<form method=post>";
    echo fm_text("Days in Future", $_REQUEST,'F');
    echo "<input type=submit name=Show value=Show><p></form>\n";
  }

  dotail();