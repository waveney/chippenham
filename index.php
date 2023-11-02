<?php
  include_once("int/fest.php");
  set_ShowYear();  
  global $YEARDATA,$NEXTYEARDATA,$PLANYEARDATA,$Months,$SHOWYEAR;
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

  $NFrom = $DFrom = ($PLANYEARDATA['DateFri']+$PLANYEARDATA['FirstDay']);
  $NTo = $DTo = ($PLANYEARDATA['DateFri']+$PLANYEARDATA['LastDay']);
  $NMonth = $DMonth = $Months[$PLANYEARDATA['MonthFri']];
  $NYear = $PLANYEARDATA['NextFest']; 


//  var_dump($YEARDATA);
  if (($YEARDATA['Years2Show'] > 0)) {
    $NFrom = ($NEXTYEARDATA['DateFri']+$NEXTYEARDATA['FirstDay']);
    $NTo = ($NEXTYEARDATA['DateFri']+$NEXTYEARDATA['LastDay']);
    $NMonth = $Months[$NEXTYEARDATA['MonthFri']];
    $NYear = substr($YEARDATA['NextFest'],0,4);
  }

  if ($PLANYEARDATA['Years2Show'] > 0) {
    $NFrom = ($NEXTYEARDATA['DateFri']+$NEXTYEARDATA['FirstDay']);
    $NTo = ($NEXTYEARDATA['DateFri']+$NEXTYEARDATA['LastDay']);
    $NMonth = $Months[$NEXTYEARDATA['MonthFri']];
    $NYear = $YEARDATA['NextFest'];
  }   


  $Sy = substr($SHOWYEAR,0,4);
  $TopBans = Get_All_Articles(0,'TopPageBanner',$future);

  if ($TopBans) {
    $Imgs = explode("\n",$TopBans[0]['Text']);
    
    $Banner  = "<div class=WMFFBanner400>";
    $Banner .= '<div class="rslides_container" style="margin:0;"><ul class="rslides" id="slider1">';
    
    foreach ($Imgs as $img) {
      $Banner .= "<li><img src='$img' class=WMFFBannerDefault>";
    }
    $Banner .= '</ul></div><script>$(function() { $(".rslides").responsiveSlides(); });</script>';
    $Banner .= "<div class=BanOverlay>" . Feature('BannerOverlay');
    $Banner .= "<img src=/images/icons/underline.png?1>";
    $Banner .= "</div>";

    if ($PLANYEARDATA['Years2Show'] > 0) {  
      $Banner .= "<div class=BanDates2>Next Year: $NFrom<sup>" . ordinal($NFrom) . "</sup> - $NTo<sup>" . ordinal($NTo) .
                 "</sup> $NMonth $NYear<p><div class=BanNotice></div></div>";
    } else {
      $Banner .= "<a href=/Tickets class=BanDates>$DFrom<sup>" . ordinal($DFrom) . "</sup> - $DTo<sup>" . ordinal($DTo) . 
        "</sup> $DMonth $Sy</a>";   
    }

    $Banner .= "<img align=center src=/images/icons/torn-top.png class=TornTopEdge>";
    $Banner .= "</div>";

    dohead("$DFrom -YY- $DTo $DMonth $Sy", ['/js/WmffAds.js', "/js/HomePage.js", "/js/Articles.js", "/js/responsiveslides.js", "/css/responsiveslides.css"],$Banner ); 
  } else {
    $Banner  = "<div class=WMFFBanner400><img src=" . Feature('DefaultPageBanner') . " class=WMFFBannerDefault>";
    $Banner .= "<div class=BanOverlay>" . Feature('BannerOverlay');
    $Banner .= "</div>";

    if ($PLANYEARDATA['Years2Show'] == 0) {  
      $Banner .= "<div class=BanDates2>Next Year: $NFrom<sup>" . ordinal($NFrom) . "</sup> - $NTo<sup>" . ordinal($NTo) .
                 "</sup> $NMonth $NYear<div class=BanNotice></div></div>";
    } else if ($PLANYEARDATA['TicketControl'] == 1) {
      $Banner .= "<a href=/Tickets class=BanDates>$DFrom<sup>" . ordinal($DFrom) . "</sup> - $DTo<sup>" . ordinal($DTo) . "</sup> $DMonth $Sy</a>";     
    } else {
      $Banner .= "<span class=BanDates>$DFrom<sup>" . ordinal($DFrom) . "</sup> - $DTo<sup>" . ordinal($DTo) . "</sup> $DMonth $Sy</span>"; 
    }

    $Banner .= "<img align=center src=/images/icons/torn-top.png class=TornTopEdge>";
    $Banner .= "</div>";
//    if (Days2Festival() > -7) {
      dohead("$DFrom - $DTo $DMonth $Sy", ['/js/WmffAds.js', "/js/HomePage.js", "/js/Articles.js"],$Banner ); 
      
//      var_dump($PLANYEARDATA);
//    } else {
//      dohead("$NFrom -XX- $NTo $NMonth $NYear", ['/js/WmffAds.js', "/js/HomePage.js", "/js/Articles.js"],$Banner );     
//    }
  }


  $TopShow = "Top";
  if ( !Show_Articles_For($TopShow,$future)) {
    echo "<center><a href=/Tickets>Details to Follow</a></center>";
  }
  echo "<br clear=all>";

  echo "<div style=margin:10>";
  echo '<center><h2>Sponsors & Supporters</h2></center>';
  echo "<center>Our festival would not be possible without the amazing help and generosity of many organisations including the following major sponsors<p>";
  echo "</center>";
  $Spons = Get_Sponsors();
  echo "<div hidden>" . fm_hidden('ChangeTime',Feature('SponsorTime',2000)); // IN msec
  foreach ($Spons as $s) {
    echo "<li class=SponsorsIds id=" .$s['id'] . "><div class=sponcontainer><div class=sponContent>";
    if ($s['Website']) echo weblinksimple($s['Website']);
    if ($s['Image']) echo "<img src='" . $s['Image'] . "' class=sponImage>";
    if (!$s['Image'] || $s['IandT']) echo "<div class=sponText>" . $s['SN'] . "</div>";
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
?>
