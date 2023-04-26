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

function formatLineups(&$perfs,$link,&$Sizes,$sdisp=1) {
// Link, if (text) Title, pic, text else Pic Title
// If size = small then fit 5, else fit 3 - if fit change br
// Float boxes with min of X and Max of Y
  global $YEAR;
  $LastSize = -1;
  
  foreach ($perfs as $perf) {
    $Imp = $perf['EffectiveImportance'];
    $Id = $perf['SideId'];
    if ($Sizes[$Imp] != $LastSize) {
      if ($LastSize >=0) echo "</div><br clear=all>";
      $LastSize = $Sizes[$Imp];
      echo "<div class=LineupFit" . $LastSize . "Wrapper>";
    }
    echo "<div class='LineupFit$LastSize LineUpBase' onmouseover=AddLineUpHighlight($Id) onmouseout=RemoveLineUpHighlight($Id) id=LineUp$Id>";
    echo "<a href=/int/$link?sidenum=$Id&Y=$YEAR>";
    $Photo = $perf['Photo'];
    if (!$Photo) $Photo = '/images/icons/user2.png';
    if ($sdisp) {
      echo "<div class=LineUpFitTitle style='font-size:" . (18+$Imp) . "px'>" . $perf['SN'] . "</div>";
      echo "<img src=$Photo>";
      echo "<div class=LineUptxt>" . $perf['Description'] . "</div>";
       
    } else {
      echo "<img src=$Photo>";
      echo "<br><div class=LineUpFitTitle style='font-size:" . (18+$Imp*3) . "px'>" . $perf['SN'] . "</div></a>";
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
      if ($ee = $e["Side$i"])  { 
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
  $PS = (isset($_GET['S']) ? $_GET['S'] : 50);


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
    if ($ImgCount > $PS) {
      $Page = (isset($_GET['p']) ? $_GET['p'] : 1);
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
    $PStr .= "<p>\n";
  
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
    $Gals = Gen_Get_Cond('Galleries'," GallerySet='" . $Gal['SN'] . "' ORDER BY MenuBarOrder DESC" );

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

function Expand_Imp(&$Art,$Isa,$Cometest,$Importance,$lvl,$future) {
  global $db,$YEAR,$Coming_Type,$ShownInArt;
  $now = time();
    $ans = $db->query("SELECT s.* FROM Sides s, SideYear y WHERE s.$IsA=1 AND s.SideId=y.SideId AND y.Year='$YEAR' AND s.Photo!='' AND $Cometest " .    
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

function Expand_Many(&$Art,$Isa,$Cometest,$Generic,$Name,$LineUp,$future) {
  global $db,$YEAR,$Coming_Type,$ShownInArt;
  $now = time();
  $Art['SN'] = $Name;
  $Art['Link'] = "/LineUp?T=$LineUp";

    $ans = $db->query("SELECT count(*) AS Total FROM Sides s, SideYear y WHERE s.SideId=y.SideId AND y.Year='$YEAR' AND s.$Isa=1 AND $Cometest " . 
           " AND y.ReleaseDate<$now");
    $Dsc = 0;
    if ($ans) {
      $res = $ans->fetch_assoc();
      $Dsc = $res['Total'];
    }
    
    $Art['Text'] = "$Dsc $Generic" . ($Dsc == 1?" has":"s have") . " already confirmed for $YEAR.";


    $ans = $db->query("SELECT s.Photo,s.SideId,s.ImageHeight,s.ImageWidth,s.SN FROM Sides s, SideYear y " .
                    "WHERE s.SideId=y.SideId AND y.Year='$YEAR' AND s.Photo!='' AND s.$Isa=1 AND $Cometest " . 
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
  global $db,$YEAR,$Coming_Type;
  global $ShownInArt;
  global $EShownInArt;
  $now = time();
//echo "In special 1";  
  $words = explode(' ',$Art['SN']);
//var_dump($words);
  switch ($words[0]) {
  case '@Dance_Imp':
    Expand_Imp($Art,'IsASide',"y.Coming=" . $Coming_Type['Y'] ,'DanceImportance', (isset($words[1])?$words[1]:0),$future);
    return;
    
  case '@Music_Imp': 
    Expand_Imp($Art,'IsAnAct',"y.YearState>1" ,'MusicImportance', (isset($words[1])?$words[1]:0),$future);
    return;
  
  case '@Family_Imp':
    Expand_Imp($Art,'IsFamily',"y.YearState>1" ,'FamilyImportance', (isset($words[1])?$words[1]:0),$future);
    return;

  case '@Ceilidh_Imp':
    Expand_Imp($Art,'IsCeilidh',"y.YearState>1" ,'CeilidhImportance', (isset($words[1])?$words[1]:0),$future);
    return;

  case '@Dance_Many':
    Expand_Many($Art,'IsASide',"y.Coming=" . $Coming_Type['Y'], 'Dance Team', 'Dancing','Dance',$future);
    return;
    
  case '@Music_Many':
    Expand_Many($Art,'IsAnAct',"y.YearState>1", 'Music Act', 'Music','Music',$future);
    return;

  case '@Family_Many':
    Expand_Many($Art,'IsFamily',"y.YearState>1", 'Family Entertainer', 'Family Entertainment','Family',$future);
    return;

  case '@Ceilidh_Many':
    Expand_Many($Art,'IsCeilidh',"y.YearState>1", 'Ceilidh and Dance bands and caller', 'Ceilidh and Dance ','Ceilidh',$future);
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
    $Art['Text'] = $Perf['Description'];
    $Art['Image'] = $Perf['Photo'];
    $Art['ImageWidth'] = (isset($Perf['ImageWidth'])?$Perf['ImageWidth']:100);
    $Art['ImageHeight'] = (isset($Perf['ImageHeight'])?$Perf['ImageHeight']:100);
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
// REMEMBER IF YOU ADD A FORMAT EDIT Articles.js AS WELL
//


function Show_Articles_For(&$page,$future=0,$datas='400,700,20,3') {
  if ($future == 0 && !Feature('UseArticles')) return 0;
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
    $fmt = (isset($Art['Format'])?$Art['Format']:0);
    echo "<div id=Art$i data-format=$fmt class=\"Art ArtFormat$fmt\" ";
// var_dump($Art['SN']);    
    if (substr($Art['SN'],0,1) == '@') { // Special
      Expand_Special($Art,$future);  // Will Update content of Art
    }
    if (count($Art)==0 || (!$Art['Text'] && !$Art['Image'] && (!$Art['SN'] || $Art['HideTitle']))) {
      echo "hidden ></div>";
      continue; // No content...
    }
    echo ">";
    switch ($fmt) {
    case 0: // Large Image
    default:
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . "'>";
      if (!$Art['HideTitle']) echo "<div class=\"ArtTitleL" . ($Art['RedTitle']?' Red':'') . "\" id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Image']) echo "<img id=\"ArtImg$i\" class=\"ArtImageL\" src='" . $Art['Image'] . "' data-height=" . $Art['ImageHeight'] . 
         " data-width=" . $Art['ImageWidth'] .">";
      if ($Art['Link']) echo "</a>";
      echo "<br><span class=\"ArtTextL\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;
          
    case 1: // Small Image (to left of title and text)
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . "'>";
      if ($Art['Image']) echo "<img id=\"ArtImg$i\" class=\"ArtImageS\" src=" . $Art['Image'] . " data-height=" . $Art['ImageHeight'] . 
        " data-width=" . $Art['ImageWidth'] . ">";
      if (!$Art['HideTitle']) echo "<div class=\"ArtTitleS" . ($Art['RedTitle']?' Red':'') . "\" id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Link']) echo "</a>";
      echo "<span class=\"ArtTextS\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;
          
    case 2: // Text Only
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . "'>";
      if (!$Art['HideTitle']) echo "<div class=\"ArtTitleT" . ($Art['RedTitle']?' Red':'') . "\" id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Link']) echo "</a>";
      echo "<span class=\"ArtTextT\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;
      
    case 3: // Banner Image
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . "'>";
      if (!$Art['HideTitle']) echo "<div class=\"ArtTitleBI" . ($Art['RedTitle']?' Red':'') . "\" id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Image']) echo "<img id=\"ArtImg$i\" class=\"ArtImageBI\" src=" . $Art['Image'] . " data-height=" . $Art['ImageHeight'] . 
         " data-width=" . $Art['ImageWidth'] .">";
      if ($Art['Link']) echo "</a>";
      echo "<span class=\"ArtTextBI\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;
              
    case 4: // Banner Text
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . "'>";
      if (!$Art['HideTitle']) echo "<div class=\"ArtTitleBT" . ($Art['RedTitle']?' Red':'') . "\" id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Link']) echo "</a>";
      echo "<span class=\"ArtTextBT\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;
      
    case 5: // Fixed Image large box has ratio of 550:500
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . "'>";
      if (!$Art['HideTitle']) echo "<div class=\"ArtTitleF" . ($Art['RedTitle']?' Red':'') . "\" id=\"ArtTitle$i\">" . $Art['SN'] . "</div><br>";
      if ($Art['Image']) echo "<img class=\"ArtImageF rounded\" id=\"ArtImg$i\" src=" . $Art['Image'] . " data-height=" . $Art['ImageHeight'] . 
          " data-width=" . $Art['ImageWidth'] .">";
      if ($Art['Link']) echo "</a><br style='height:0' clear=\"all\">";
      echo "<div class=\"ArtTextF\" id=\"ArtText$i\">" . $Art['Text'] . "</div>";
      break;

    case 6: // Left/Right
      // Not Written
      break;
    
    case 7: // 2/3rds Banner Image
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . "'>";
      if (!$Art['HideTitle']) echo "<div class=\"ArtTitleBI23" . ($Art['RedTitle']?' Red':'') . "\" id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Image']) echo "<img id=\"ArtImg$i\" class=\"ArtImageBI23\" src=" . $Art['Image'] . " data-height=" . $Art['ImageHeight'] . 
         " data-width=" . $Art['ImageWidth'] .">";
      if ($Art['Link']) echo "</a>";
      echo "<span class=\"ArtTextBI23\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;
              
    case 8: // image below text
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . "'>";
      if (!$Art['HideTitle']) echo "<div class=\"ArtTitleL" . ($Art['RedTitle']?' Red':'') . "\" id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      echo "<br><span class=\"ArtTextL\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      if ($Art['Image']) echo "<img id=\"ArtImg$i\" class=\"ArtImageL\" src='" . $Art['Image'] . "' data-height=" . $Art['ImageHeight'] . 
         " data-width=" . $Art['ImageWidth'] .">";
      if ($Art['Link']) echo "</a>";
      echo "<br clear=all>";
      break;
          
    case 9: // V Small image to right of heading
 
      if ($Art['Link']) echo "<a href='" . $Art['Link'] . "'>";
      if ($Art['Image']) echo "<img id=\"ArtImg$i\" class=\"ArtImageVS\" src=" . $Art['Image'] . " data-height=" . $Art['ImageHeight'] . 
        " data-width=" . $Art['ImageWidth'] . ">";
      if (!$Art['HideTitle']) echo "<div class=\"ArtTitleS" . ($Art['RedTitle']?' Red':'') . "\" id=\"ArtTitle$i\">" . $Art['SN'] . "</div>";
      if ($Art['Link']) echo "</a>";
      echo "<span class=\"ArtTextS\" id=\"ArtText$i\">" . $Art['Text'] . "</span>";
      break;
    

    }
    echo "</div><br clear=all>\n";          
  }
  echo "</div>";
  echo "\n";
  return 1;
}



