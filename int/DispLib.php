<?php

// Displaying utilities for public site

function formatminimax(&$side,$link,$mnat=2,$sdisp=1) {
  global $YEAR,$PerfTypes;
  echo "<div class=mnfloatleft>";
  if ($side['Photo']) {
    $wi = $side['ImageWidth'];
    $ht = $side['ImageHeight'];
    if ($wi > ($ht * 1.1)) {
      $fmt  = ($wi > ($ht * 2))?'b':'l';
    } else {
      $fmt  = ($ht > ($wi * 1.1))?'p':'s';
    }
  } else {
    $fmt = 't';
  } // fmt t=txt, l=ls, p=pt, s=sq, b=ban
  $Imp = $side['Importance'];
  if ($side['DiffImportance']) {
    $imp = 0;
    foreach($PerfTypes as $pt=>$pd) if (Capability("Enable" . $pd[2])) if ($side[$pd[0]] && $imp < $side[$pd[2] . 'Importance'] ) $imp = $side[$pd[2] . 'Importance'];
  }

  $mnmx = ($Imp >= $mnat?'maxi':'mini');
  $id = AlphaNumeric($side['SN']);
  echo "<div class=$mnmx" . "_$fmt id=$id>";
  echo "<a href=/int/$link?id=" . $side['SideId'] . "&Y=$YEAR>";
  if ($mnmx != 'maxi' && $side['Photo']) echo "<div class=mnmximgwrap><img class=mnmximg src='" . $side['Photo'] ."'></div>";
  echo "<div class=mnmxttl style='font-size:" . (24+$Imp*3) . "px'>" . $side['SN'] . "</div>";
  if ($mnmx == 'maxi' && $side['Photo']) echo "<div class=mnmximgwrap><img class=mnmximg src='" . $side['Photo'] ."'></div>";
  echo "</a>";
  if ($sdisp) echo "<div class=mnmxtxt>" . $side['Description'] . "</div>";
  echo "</div></div>\n";
}

function formatLineups(&$perfs,$link,&$Sizes,$PerfCat,$sdisp=1) {
//  include_once("int/SideOverLib.php");
// Link, if (text) Title, pic, text else Pic Title
// If size = small then fit 5, else fit 3 - if fit change br
// Float boxes with min of X and Max of Y
  global $YEAR,$PerfTypes;
  $LastSize = -1;
  $LinkCat = preg_replace('/ /','_',$PerfCat);
  Expand_PerfTypes();

  foreach ($perfs as $perf) {
    if ($perf['NotPerformer'] ) continue;
    $Imp = $perf['EffectiveImportance'];
    $Id = $perf['SideId'];
    if ($Sizes[$Imp] != $LastSize) {
      if ($LastSize >=0) echo "</div><br clear=all>";
      $LastSize = $Sizes[$Imp];
      echo "<div class=LineupFit" . $LastSize . "Wrapper>";
    }
    echo "<div class='LineupFit$LastSize LineUpBase' onmouseover=AddLineUpHighlight($Id) onmouseout=RemoveLineUpHighlight($Id) id=LineUp$Id>";
    echo "<a href=/int/$link?id=$Id&Y=$YEAR&C=$LinkCat>";

    $Photo = OvPhoto($perf,$PerfCat);
    if (!$Photo) $Photo = '/images/icons/user2.png';
    if ($sdisp) {
      if (Access('SysAdmin')) debug_print_backtrace();
      echo "<div class=LineUpFitTitle style='font-size:" . (18+$Imp) . "px'>" . OvName($perf,$PerfCat) . "</div>";
      echo "<img src=$Photo>";
      echo "<div class=LineUptxt>" . OvDesc($perf,$PerfCat) . "</div>";

    } else {
      echo "<img src=$Photo>";
      echo "<br><div class=LineUpFitTitle style='font-size:" . (18+$Imp*3) . "px'>" . OvName($perf,$PerfCat) . "</div></a>";
    }
    echo "</div>";
  }
  echo "</div><br clear=all>";
}

// Check ET to see if imps should be found
function Get_Imps(&$e,&$imps,$clear=1,$all=0) {
  global $Event_Types,$YEAR,$PerfTypes;

  $ets = $Event_Types[$e['Type']]['State'];
  $useimp = ($Event_Types[$e['Type']]['UseImp'] && ($e['BigEvent']==0));
  $now=time();
  if ($clear) $imps = array();
  for($i=1;$i<5;$i++) {
    if (isset($e["Side$i"]) && $e["Side$i"]) {
      if (($ee = $e["Side$i"]))  {
        $si = Get_Side($ee);
        if ($si) {
          $y = Get_SideYear($ee,$YEAR);
          $s = array_merge($si, munge_array($y));
          $s['Roll'] = (($e['SubEvent'] <= 0)?$e["Roll$i"]:'');
          if ($s && ($all || ((( $s['Coming'] == 2) || ($s['YearState'] >= 2)) && ($ets >1 || ($ets==1 && Access('Participant','Side',$s))) && $s['ReleaseDate'] < $now))) {
            if (!$useimp) {
              $imps[0][] = $s;
            } elseif ($s['DiffImportance']) {
              $iimp = 0;
              foreach($PerfTypes as $pt=>$pd) if ($s[$pd[0]] && $iimp < $s[$pd[2] . 'Importance']) $iimp = $s[$pd[2] . 'Importance'];
              $imps[$iimp][] = $s;
            } else {
              $imps[$s['Importance']][] = $s;
            }
          }
        }
      }
    }
  }
}

function ImpCount($imps) {
  $c = 0;
  foreach ($imps as $imp) foreach($imp as $s) $c++;
  return $c;
}

function Gallery($id,$embed=0) {
  global $YEAR;
  include_once("ImageLib.php");
  $PS = (isset($_REQUEST['S']) ? $_REQUEST['S'] : 50);

  Sanitise($id);
  if (is_numeric($id)) {
    $Gal = db_get('Galleries',"id='$id'");
  } else {
    $nam = preg_replace('/_/',' ',$id);
    $Gal = db_get('Galleries',"SN='$nam'");
  }

  if (!$Gal) {
//    echo "About to call Error_Page<p>";
    Error_Page("Gallery $id does not exist");
  }

  $name = $Gal['SN'];
  if (!$embed) {
    $Banner = 1;
    if (isset($Gal['Banner'])) $Banner = $Gal['Banner'];
    dohead($name, ['/files/gallery.css'],$Banner);
  }

  echo "<h2 class=maintitle>$name</h2><p>";
  if ($Gal['Description']) echo $Gal['Description'] . "<p>";

  if ($Gal['Level'] == 0) {

    echo "Click on any slide to start a Slide Show with that slide.<p>\n";

    if ($Gal['Credits']) {
      echo '<h2 class="subtitle">Credits</h2>';
      echo "Photos by: " . $Gal['Credits'] . "<p>";
    }


    $Imgs = Get_Gallery_Photos($Gal['id']);
    $ImgCount = count($Imgs);


    $PStr = "";
    if ($Gal['GallerySet']) {
      $PGal = preg_replace('/ /','_',$Gal['GallerySet']);
      $PStr = "<div class=floatright><a href=ShowGallery?g=$PGal>Up to " . $Gal['GallerySet'] . "</a></div>";
    }
    if ($ImgCount > $PS) {
      $Page = (isset($_REQUEST['p']) ? $_REQUEST['p'] : 1);
      $lastP = ceil($ImgCount/$PS);
      if ($Page > $lastP) $Page = $lastP;
      $PStr .= "<div class=gallerypage>Page : ";
      $bl = "<a href=ShowGallery?g=$id";
      if ($PS != 50) $bl .= "&S=$PS";
      $bl .= "&p=";
      $PStr .= $bl . "1>First</a> ";
      if ($Page > 1) $PStr .= $bl . ($Page-1) . ">Prev</a> ";
      for ($p = 1; $p <= $lastP; $p++) {
        if ($p == $Page) {
          $PStr .= "$p ";
        } else {
          $PStr .= $bl . $p . ">$p</a> ";
        }
      }
      if ($Page != $lastP) $PStr .= $bl . ($Page+1) . ">Next</a> ";
      $PStr .= $bl . $lastP . ">Last</a></div><p>";
      $first = ($Page-1)*$PS;
      $last = $first+$PS;
    } else {
      $first = 0;
      $last = $PS;
    }
    $PStr .= "<br clear=all><p>\n";

    echo $PStr;

    echo '<div id=galleryflex>';


    $count = 0;
    if ($Imgs) {
      foreach ($Imgs as $img) {
        if ($count >= $first && $count < $last) {

          echo "<div class=galleryarticle><a href=/int/SlideShow?g=$id&s=$count><img class=galleryarticleimg src=\"" . $img['File'] . "\"></a>";
          if ($img['Caption']) echo "<div class=gallerycaption> " . $img['Caption'] . "</div>";
          echo "</div>\n";
        }
        $count++;
      }
    } else {
      echo "<h2 class=Err>Sorry that Gallery is empty</h2>\n";
    }

    echo "</div>" . $PStr;

    if ($Gal['Credits']) {
      echo "<p>Photos by: " . $Gal['Credits'] . "<p>";
    }
  } else { //Sub Gallery
    if ($Gal['GallerySet']) {
      $PGal = preg_replace('/ /','_',$Gal['GallerySet']);
      $PStr = "<div><a href=ShowGallery?g=$PGal>Up to " . $Gal['GallerySet'] . "</a></div>";
      echo $PStr . "<br clear=all>";
    }

    $Gals = Gen_Get_Cond('Galleries'," GallerySet='" . $Gal['SN'] . "' ORDER BY SetOrder DESC" );

    echo "<div id=flex5>\n";
    foreach ($Gals as $G) {
      if ($G['id'] == $Gal['id']) continue;
      echo "<div class=GalleryFlexCont><a href=/int/ShowGallery?g=" . $G['id'] . "&Y=$YEAR>" . $G['SN'] . "</a><br>";
      if ($G['Image']) {
        $GalEnt = Gen_Get('GallPhotos',$G['Image']);
        echo "<img src=" . $GalEnt['File'] . " class=GalImg><br>";
      }
      if ($G['Description']) echo $G['Description'];
      echo "</div>";

    }
    echo "</div><br>";
  }

  if (!$embed) dotail();
}

$ShownInArt = $EShownInArt = [];
global $ShownInArt, $EShownInArt;


function Count_Perf_Type($type,$Year=0) {
  global $YEAR,$db,$Coming_Type;
  $now = time();
  if ($Year == 0) $Year=$YEAR;
  $ans = $db->query("SELECT count(*) AS Total FROM Sides s, SideYear y WHERE s.SideId=y.SideId AND y.Year='$Year' AND s.$type=1 AND ( y.Coming=" . $Coming_Type['Y'] .
                    " OR y.YearState>2 ) AND y.ReleaseDate<$now");
  $Dsc = 0;
  if ($ans) {
    $res = $ans->fetch_assoc();
    $Dsc = $res['Total'];
  }
  return $Dsc;
}

function Expand_Imp(&$Art,$Cometest,$Importance,$lvl,$future) {
  global $db,$YEAR,$Coming_Type,$ShownInArt;
  $now = time();
    $ans = $db->query("SELECT s.* FROM Sides s, SideYear y WHERE s.SideId=y.SideId AND y.Year='$YEAR' AND s.Photo!='' AND $Cometest " .
                    " AND ((s.DiffImportance=0 AND s.Importance>$lvl) OR (s.DiffImportance=1 AND s.$Importance>$lvl)) AND y.ReleaseDate<$now ORDER BY RAND() LIMIT 5");
    if (!$ans) { $Art = []; return; }

    while ( $Dstuff = $ans->fetch_assoc()) {
      if (in_array($Dstuff['SideId'],$ShownInArt)) continue;
      $ShownInArt[] = $Dstuff['SideId'];

      $Art['SN'] = $Dstuff['SN'];
      $Art['Link'] = ('/int/ShowPerf?id=' . $Dstuff['SideId']);
      $Art['Text'] = $Dstuff['Description'];
      $Art['Image'] = $Dstuff['Photo'];
      $Art['ImageWidth'] = (isset($Dstuff['ImageWidth'])?$Dstuff['ImageWidth']:100);
      $Art['ImageHeight'] = (isset($Dstuff['ImageHeight'])?$Dstuff['ImageHeight']:100);
      return;
    }
    $Art = [];
}

function Expand_Many(&$Art,$Cometest,$Generic,$Name,$LineUp,$future,$Year=0,$Pfx='') {
  global $db,$YEAR,$PLANYEAR,$Coming_Type,$ShownInArt;
  if ($Year== 0) $Year=$YEAR;
  $D2F = Days2Festival();

  $now = time();
  $Art['SN'] = $Name . ' Line&nbsp;Up';
  if ($LineUp) $Art['Link'] = "/LineUp?T=$LineUp";

    $ans = $db->query("SELECT count(*) AS Total FROM Sides s, SideYear y WHERE s.SideId=y.SideId AND y.Year='$Year' AND $Cometest " .
           " AND y.ReleaseDate<$now AND s.IsNonPerf=0");
    $Dsc = 0;
    if ($ans) {
      $res = $ans->fetch_assoc();
      $Dsc = $res['Total'];
    }

    if (($D2F > 0) || ($Year == $YEAR+1) ) {
      $Art['Text'] = "$Pfx$Dsc $Generic" . ($Dsc == 1?" has":"s have") . " already confirmed for $Year.";
    } else {
      $Art['Text'] = "$Pfx$Dsc $Generic" . ($Dsc == 1?"":"s") . " performed in $Year.";
    }

    $ans = $db->query("SELECT s.Photo,s.SideId,s.ImageHeight,s.ImageWidth,s.SN FROM Sides s, SideYear y " .
                    "WHERE s.SideId=y.SideId AND y.Year='$Year' AND s.Photo!='' AND $Cometest AND s.IsNonPerf=0" .
                    " AND y.ReleaseDate<$now ORDER BY RAND() LIMIT 10");

    if (!$ans) return;
    while ( $DMany = $ans->fetch_assoc()) {
      if (empty($DMany['SideId']) || in_array($DMany['SideId'],$ShownInArt)) continue;
      $ShownInArt[] = $DMany['SideId'];

      $Art['Text'] .= "  Including <a href=/int/ShowPerf?id=" . $DMany['SideId'] . ">" . $DMany['SN'] . "</a>";
      $Art['Image'] = $DMany['Photo'];
      $Art['ImageWidth'] = (isset($DMany['ImageWidth'])?$DMany['ImageWidth']:100);
      $Art['ImageHeight'] = (isset($DMany['ImageHeight'])?$DMany['ImageHeight']:100);
      return;
    }
}

function Expand_Special(&$Art,$future=0) {
  global $db,$YEAR,$Coming_Type,$PLANYEAR,$Coming_States;
  global $ShownInArt;
  global $EShownInArt;
  $now = time();
//echo "In special 1";
  $words = explode(' ',$Art['SN']);
//var_dump($words);
  switch ($words[0]) {
  case '@Dance_Imp':
    Expand_Imp($Art,'s.IsASide=1 AND y.Coming=' . $Coming_Type['Y'] ,'DanceImportance', (isset($words[1])?$words[1]:0),$future);
    return;

  case '@Music_Imp':
    Expand_Imp($Art,'s.IsAnAct=1 AND y.YearState>1' ,'MusicImportance', (isset($words[1])?$words[1]:0),$future);
    return;

  case '@Family_Imp':
    Expand_Imp($Art,'s.IsFamily=1 AND y.YearState>1' ,'FamilyImportance', (isset($words[1])?$words[1]:0),$future);
    return;

  case '@Youth_Imp':
    Expand_Imp($Art,'s.IsYouth=1 AND y.YearState>1' ,'YouthImportance', (isset($words[1])?$words[1]:0),$future);
    return;

  case '@Ceilidh_Imp':
    Expand_Imp($Art,'s.IsCeilidh=1 AND y.YearState>1' ,'CeilidhImportance', (isset($words[1])?$words[1]:0),$future);
    return;

  case '@Dance_Many':
    Expand_Many($Art,'s.IsASide=1 AND y.Coming=' . $Coming_Type['Y'], 'Dance Team', 'Dancing','Dance',$future);
    return;

  case '@Music_Many':
    Expand_Many($Art,'s.IsAnAct=1 AND y.YearState>1', 'Music Act', 'Music','Music',$future);
    return;

  case '@Family_Many':
    Expand_Many($Art,'s.IsFamily=1 AND y.YearState>1', 'Family Entertainer', 'Family Entertainment','Family',$future);
    return;

  case '@Youth_Many':
    Expand_Many($Art,'s.IsYouth=1 AND y.YearState>1', 'Youth Activity', 'Youth Activity','Family',$future);
    return;

  case '@Ceilidh_Many':
    Expand_Many($Art,'s.IsCeilidh=1 AND y.YearState>1', 'Ceilidh and Dance bands and caller', 'Ceilidh and Dance ','Ceilidh',$future);
    return;

  case '@NextYear':
    global $YEARDATA,$Months;
    $NEXTYEARDATA = Get_General($YEARDATA['NextFest']);
    $NFrom = ($NEXTYEARDATA['DateFri']+$NEXTYEARDATA['FirstDay']);
    $NTo = ($NEXTYEARDATA['DateFri']+$NEXTYEARDATA['LastDay']);
    $NMonth = $Months[$NEXTYEARDATA['MonthFri']];
    $NYear = substr($YEARDATA['NextFest'],0,4);

    Expand_Many($Art,'((s.IsASide AND y.Coming=' .
        $Coming_Type['Y'] . ') OR (y.YearState>1 AND (s.IsAnAct=1 OR s.IsFamily=1 OR s.IsYouth=1 OR s.IsCeilidh=1)))',
       'Performer', "Next Year's Festival - $NYear",0,$future,$YEAR+($YEAR==$PLANYEAR?0:1),"We are already planning next year's festival from " .
        "$NFrom<sup>" . ordinal($NFrom) . "</sup> - $NTo<sup>" . ordinal($NTo) . "</sup> $NMonth $NYear and " );
    return;

  case '@ThisYear':
    global $YEARDATA,$Months;
    $From = ($YEARDATA['DateFri']+$YEARDATA['FirstDay']);
    $To = ($YEARDATA['DateFri']+$YEARDATA['LastDay']);
    $Month = $Months[$YEARDATA['MonthFri']];

    Expand_Many($Art,'((s.IsASide AND y.Coming=' . $Coming_Type['Y'] .
        ') OR (y.YearState>1 AND (s.IsAnAct=1 OR s.IsFamily=1 OR s.IsYouth=1 OR s.IsCeilidh=1)))',
       'Performer', "This Year's Festival",0,$future,$YEAR,"We are already planning this year's festival from " .
        "$From<sup>" . ordinal($From) . "</sup> - $To<sup>" . ordinal($To) . "</sup> $Month $PLANYEAR and " );
    return;

  case '@Perf': // Just this performer
    $id = $words[1];
    if (in_array($id,$ShownInArt)) {
      $Art = [];
      return;
    }
    $ShownInArt [] = $id;
    $Perf = Get_Side($id);

    $Art['SN'] = $Perf['SN'];
    $Art['Link'] = '/int/ShowPerf?id=' . $Perf['SideId'];
    $Art['Text'] = (Feature('TopText4Perfs')?$Perf['Description']:' ');
    $Art['Image'] = $Perf['Photo'];
    $Art['ImageWidth'] = (isset($Perf['ImageWidth'])?$Perf['ImageWidth']:100);
    $Art['ImageHeight'] = (isset($Perf['ImageHeight'])?$Perf['ImageHeight']:100);
    $Art['Format'] = 10; // Overlay

    if (($YEAR != $PLANYEAR) && Feature('TopText4Perfs')) {
      $Sy = Get_SideYear($id,$PLANYEAR);
      if (isset($Sy['syId']) && (($Perf['IsASide'] && ($Sy['Coming'] == $Coming_States['Coming'])) || $Sy['YearState']>1)) {
        $Art['Text'] = "CONFIRMED FOR $PLANYEAR<p>\n\n" . $Perf['Description'];
      }
    }
    break;

  case '@Event' : // Just this Event
    include_once("ProgLib.php");
    $id = $words[1];
    if (in_array($id,$EShownInArt)) {
      $Art = [];
      return;
    }
    $EShownInArt [] = $id;
    $E = Get_Event($id);
    $Art['SN'] = $E['SN'];
    $Art['Link'] = '/int/EventShow?e=' . $id;
    $Art['Text'] = $E['Description'];
    break;

    break;

  default:

  }
}

//
// REMEMBER IF YOU ADD A FORMAT TO EDIT Articles.js AS WELL
//


function Show_Articles_For(&$page,$future=0,$datas= '200,350,4,4') { //) {'400,700,20,3' set as 4 col, these for 3 col
  include_once("DanceLib.php");
  global $ShownInArt, $EShownInArt;
  $ShownInArt = $EShownInArt = [];

  if (is_array($page)) {
    $Arts = &$page;
  } else {
    $Arts = Get_All_Articles(0,$page,$future);
  }

  if (!$Arts) return 0;
//  var_dump($Arts);
  echo "<div id=ShowArt data-settings='$datas'></div><p>";
  echo "<div id=OrigArt hidden>";
//  echo "<div id=OrigArt>";
  foreach ($Arts as $i=>$Art) {
// var_dump($Art['SN']);
    if (substr($Art['SN'],0,1) == '@') { // Special
      Expand_Special($Art,$future);  // Will Update content of Art
    }
    $fmt = (isset($Art['Format'])?$Art['Format']:0);
    $Cols = ($Art['ColSet']??1);
    echo "<div id=Art$i data-format=$fmt data-cols=$Cols class=\"Art ArtFormat$fmt\" ";
    if (count($Art)==0 || (!$Art['Text'] && !$Art['Image'] && (!$Art['SN'] || $Art['HideTitle']))) {
      if (substr($Art['SN']??'',0,1) !='@') {
        echo "hidden ></div>";
        continue; // No content...
      }
    }
    echo ">";
// var_dump($Art);
    $TitleColour = " style='color:" . (($Art['TitleColour']??0)?$Art['TitleColour']:feature('DefaultTitleColour','black')) . "' ";
    switch ($fmt) {
    case 0: // Large Image
    default:
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . (empty($Art['ExternalLink'])?"'":"' target=_blank") . ">";
      if (!$Art['HideTitle']) echo "<div class=ArtTitleL $TitleColour id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Image']) echo "<img id=\"ArtImg$i\" class=\"ArtImageL\" src='" . $Art['Image'] . "' data-height=" . $Art['ImageHeight'] .
         " data-width=" . $Art['ImageWidth'] .">";
      if ($Art['Link']) echo "</a>";
      echo "<br><span class=\"ArtTextL\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;

    case 1: // Small Image (to left of title and text)
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . (empty($Art['ExternalLink'])?"'":"' target=_blank") . ">";
      if ($Art['Image']) echo "<img id=\"ArtImg$i\" class=\"ArtImageS\" src=" . $Art['Image'] . " data-height=" . $Art['ImageHeight'] .
        " data-width=" . $Art['ImageWidth'] . ">";
      if (!$Art['HideTitle']) echo "<div class=ArtTitleS $TitleColour id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Link']) echo "</a>";
      echo "<span class=\"ArtTextS\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;

    case 2: // Text Only
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . (empty($Art['ExternalLink'])?"'":"' target=_blank") . ">";
      if (!$Art['HideTitle']) echo "<div class=ArtTitleT $TitleColour id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Link']) echo "</a>";
      echo "<span class=\"ArtTextT\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;

    case 3: // Banner Image
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . (empty($Art['ExternalLink'])?"'":"' target=_blank") . ">";
      if (!$Art['HideTitle']) echo "<div class=ArtTitleBI $TitleColour id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Image']) echo "<img id=\"ArtImg$i\" class=\"ArtImageBI\" src=" . $Art['Image'] . " data-height=" . $Art['ImageHeight'] .
         " data-width=" . $Art['ImageWidth'] .">";
      if ($Art['Link']) echo "</a>";
      echo "<span class=\"ArtTextBI\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;

    case 4: // Banner Text
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . (empty($Art['ExternalLink'])?"'":"' target=_blank") . "'>";
      if (!$Art['HideTitle']) echo "<div class=ArtTitleBT $TitleColour id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Link']) echo "</a>";
      echo "<span class=\"ArtTextBT\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;

    case 5: // Fixed Image large box has ratio of 550:500
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . (empty($Art['ExternalLink'])?"'":"' target=_blank") . ">";
      if (!$Art['HideTitle']) echo "<div class=ArtTitleF $TitleColour id=\"ArtTitle$i\">" . $Art['SN'] . "</div><br>";
      if ($Art['Image']) echo "<img class=\"ArtImageF rounded\" id=\"ArtImg$i\" src=" . $Art['Image'] . " data-height=" . $Art['ImageHeight'] .
          " data-width=" . $Art['ImageWidth'] .">";
      if ($Art['Link']) echo "</a><br style='height:0' clear=\"all\">";
      echo "<div class=\"ArtTextF\" id=\"ArtText$i\">" . $Art['Text'] . "</div>";
      break;

    case 6: // Left/Right
      // Not Written
      break;

    case 7: // 2/3rds Banner Image
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . (empty($Art['ExternalLink'])?"'":"' target=_blank") . ">";
      if (!$Art['HideTitle']) echo "<div class=ArtTitleBI23 $TitleColour id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Image']) echo "<img id=\"ArtImg$i\" class=\"ArtImageBI23\" src=" . $Art['Image'] . " data-height=" . $Art['ImageHeight'] .
         " data-width=" . $Art['ImageWidth'] .">";
      if ($Art['Link']) echo "</a>";
      echo "<span class=\"ArtTextBI23\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;

    case 8: // image below text
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . (empty($Art['ExternalLink'])?"'":"' target=_blank") . ">";
      if (!$Art['HideTitle']) echo "<div class=ArtTitleL $TitleColour id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      echo "<br><span class=\"ArtTextL\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      if ($Art['Image']) echo "<img id=\"ArtImg$i\" class=\"ArtImageL\" src='" . $Art['Image'] . "' data-height=" . $Art['ImageHeight'] .
         " data-width=" . $Art['ImageWidth'] .">";
      if ($Art['Link']) echo "</a>";
      echo "<br clear=all>";
      break;

    case 9: // V Small image to right of heading

      if ($Art['Link']) echo "<a href='" . $Art['Link'] . (empty($Art['ExternalLink'])?"'":"' target=_blank") . ">";
      if ($Art['Image']) echo "<img id=\"ArtImg$i\" class=\"ArtImageVS\" src=" . $Art['Image'] . " data-height=" . $Art['ImageHeight'] .
        " data-width=" . $Art['ImageWidth'] . ">";
      if (!$Art['HideTitle']) echo "<div class=ArtTitleS $TitleColour id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Link']) echo "</a>";
      echo "<span class=\"ArtTextS\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;

    case 10: // Large Image - title overlay - Does NOT use TitleColour
      echo "<div class=container>";
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . (empty($Art['ExternalLink'])?"'":"' target=_blank") . ">";
      if ($Art['Image']) echo "<img id=\"ArtImg$i\" class=\"ArtImageL\" src='" . $Art['Image'] . "' data-height=" . $Art['ImageHeight'] .
        " data-width=" . $Art['ImageWidth'] .">";
      if (!$Art['HideTitle']) echo "<div class=\"ArtTitleOverlay bottom-center \" style='color:" .
        ($Art['TitleColour']?$Art['TitleColour']:feature('OverlayTitleColour','black')) .
        "' id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Link']) echo "</a>";
      echo "</div>";
      if (strlen($Art['Text']) > 2) echo "<span class=\"ArtTextL\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;


    }
    echo "</div><br clear=all>\n";
  }
  echo "</div>";
  echo "\n";
  return 1;
}

function Get_Sponsors($minlvl=0) { // New Code
  global $db,$YEAR,$YEARDATA;
  $full = [];
  $res = $db->query("SELECT s.*, t.SN, t.Photo, t.Logo, t.Website, t.IandT, t.GoodsDesc FROM Sponsorship s, Trade t WHERE " .
    "s.SponsorId=t.Tid AND s.Year='$YEAR' AND Importance>=$minlvl");
  if (!$res->num_rows && !empty($YEARDATA['PrevFest'])) {
    $res = $db->query("SELECT s.*, t.SN, t.Photo, t.Logo, t.Website, t.IandT, t.GoodsDesc FROM Sponsorship s, Trade t WHERE " .
      "s.SponsorId=t.Tid AND s.Year='" . $YEARDATA['PrevFest'] . "' AND Importance>=$minlvl");
    }
  if ($res) while ($spon = $res->fetch_assoc()) {
    if (isset($full[$spon['SN']])) {
      if ($full[$spon['SN']]['Importance'] < $spon['Importance'] ) $full[$spon['SN']]['Importance'] = $spon['Importance'];
    } else {
      $full[$spon['SN']] = $spon;
    }
  }
  ksort($full);
  return $full;
}

function SponsoredBy(&$Data,&$Name,$TType,$Tid,$Logosize='0') {
  global $YEAR;
    if ($Data['SponsoredBy'] ?? 0) {
      $Spid = $Data['SponsoredBy'];
      if ($Spid > 0) {
        $Spon = Gen_Get('Trade',$Spid,'Tid');
        echo "<div class=SponWrap><div class=SponSet><div class=SponWhat>$Name</div> is sponsored by:</div><div class=Sponsoring>" .
             weblink($Spon['Website'],
               (($Spon['Logo'] || $Spon['Photo'])?(" <center><img src='" . ($Spon['Logo']?$Spon['Logo']:$Spon['Photo']) .
                "'  class=sponImage$Logosize></center>"):'') .
               ($Spon['BizName']?$Spon['BizName']:$Spon['SN'] )," class=sponText") . "</div></div><br clear=all>";

      } else {
        $Spids = Gen_Get_Cond('Sponsorship',"Year=$YEAR AND ThingType=$TType AND ThingId=$Tid ORDER BY Importance, RAND()");
        if ($Spids) {
          echo "<div><div class=SponSet><div class=SponWhat>$Name</div> is sponsored by:</div>";
          foreach ($Spids as $Spid) {
            $Spon = Gen_Get('Trade',$Spid['SponsorId'],'Tid');
            echo "<div class=Sponsoring>" .
               weblink($Spon['Website'],
                 (($Spon['Logo'] || $Spon['Photo'])?(" <center><img src='" . ($Spon['Logo']?$Spon['Logo']:$Spon['Photo']) .
                  "'  class=sponImage$Logosize></center>"):'') .
                 ($Spon['BizName']?$Spon['BizName']:$Spon['SN'] )," class=sponText") . "</div>";

          }
          echo "</div><br clear=all>";
        }

      }
    }

}

function SponsoredByWho(&$Data,&$Name,$TType,$Tid,$cols=3) {
  global $YEAR;
    if ($Data['SponsoredBy'] ?? 0) {
      $Spid = $Data['SponsoredBy'];
      if ($Spid > 0) {
        $Spon = Gen_Get('Trade',$Spid,'Tid');
        echo "<td>Sponsored by:" . help('SponsoredBy') . "<td colspan=$cols><a href=Biz?ACTION=Show&id=$Spid>" . $Spon['SN'] . "</a>";
      } else {
        $Spids = Gen_Get_Cond('Sponsorship',"Year=$YEAR AND ThingType=$TType AND ThingId=$Tid ORDER BY Importance, RAND()");
        if ($Spids) {
          echo "<td>Sponsored by: " . help('SponsoredBy') . "<td colspan=$cols>";
          $num = 0;
          foreach ($Spids as $Spid) {
            $Spon = Gen_Get('Trade',$Spid['SponsorId'],'Tid');
            if ($num++ != 0) echo ", ";
            echo "<a href=Biz?ACTION=Show&id=" . $Spid['SponsorId'] . ">" . NoBreak($Spon['SN']) . "</a>";
          }
          echo "</div><br clear=all>";
        } else {
          echo "<td>Not Sponsored " . help('SponsoredBy');
        }
      }
    } else {
      echo "<td>Not Sponsored " . help('SponsoredBy');
//      var_dump($Data);
    }


}



