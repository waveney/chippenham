<?php
  global $Access_Type,$USER,$USERID,$YEAR,$FESTSYS,$YEARDATA,$NEXTYEARDATA,$Months,$PLANYEARDATA,$PLANYEAR;
  Set_User();
  
  // Header bar  
  // Icon
  // Public bar
  // Private bar (may be zero height)

  $V=$FESTSYS['V'];  
  $Bars = 1;
  $UserName = '';
  if (isset($_COOKIE{'FEST2'}) || isset($USER{'AccessLevel'})) {
    $Bars = 2;
    $UserName = (isset($USER['Login'])? $USER['Login'] : "");
  }
//   $Bars = 1; 

// text=>link or text=>[submenu] (recuresive)
// 1st char 0f text * - not selectable, ! Icon, ? Only Dance, # Not Dance, = Get Tickets
// 1st char of link ! - external
  $Menus = [
    'Public'=> [
      '<Home'=>'',
      'Line-Up'=>[
        'Dance'=>'LineUp?T=Dance',
        'Music'=>'LineUp?T=Music', 
//        'Comedy'=>'LineUp?T=Comedy',
        'Family'=>'LineUp?T=Family',
//        'Traders'=>'int/TradeShow',

        ],
      "Timetable"=>[
        'By Venue'=>'WhatsOnWhere',
        'By Time'=>'WhatsOnWhen',
        'Now'=>'WhatsOnNow',
        '@'=>0, //Special
        ],
      'Information'=>[
        'Festival Map'=>'Map',
        'Camping'=>'InfoCamping',
        'Parking'=>'InfoParking',
        'Travel'=>'InfoGettingHere',
        'Mailing List'=>'InfoMailingList',
        'Contact Us'=>'contact',
        'Data Privacy'=>'InfoData',
        'Festival Software'=>'InfoSoftware',
        ],
      '-Get Involved'=>[
        'Volunteer'=>'InfoStewards',
        'Sponsorship'=>'InfoSponsors',
//        'Trade Stand Applications'=>'InfoTrade', 
//        'Art Show Application' => 'int/ArtForm',
//        '*Live and Loud'=>'LiveNLoud',
//        '*Buskers Bash'=>'BuskersBash',
//        'Donate'=>'Donate',
         ],
      '-Gallery'=>[
        '@'=>1, //Special
        '>All Galleries'=>'int/ShowGallery?g=All_Galleries',
        
       ],
      '!/images/icons/Facebook.png'=>('!http://facebook.com/' . Feature('Facebook','**NONE**')),
      '!/images/icons/Twitter.png'=>('!http://twitter.com/' . Feature('Twitter','**NONE**')),
      '!/images/icons/Instagram.png'=>'!http://instagram.com/' . (Feature('Instagram','**NONE**')),
      '!/images/icons/YouTube.png'=>'!http://YouTube.com/' . (Feature('YouTube','**NONE**')),
      '=Buy Tickets'=>'Tickets',
      '%Donate'=>'Donate',
      ],
    'Private'=> [  
      'Staff Tools'=>'int/Staff',
      '-Documents'=>'int/Dir',
//      '-Time Line'=>"int/TimeLine?Y=$YEAR",
      "Logout $UserName"=>'int/Login?ACTION=LOGOUT',
      ],
    'Perf'=>[
      'Edit Your Data'=>"int/AddPerf?sidenum=$USERID",
      '-Public view'=>"int/ShowDance?sidenum=$USERID",
      '?Dance Loc Map'=>'/Map?F=3',
      '?Dance FAQ'=>'int/DanceFAQ',
      '#Performer T&amp;Cs'=>'int/MusicFAQ',    
      ],
    'Trade'=>[
      'Edit Trader Info'=>"int/TraderPage?id=$USERID",
      'Trade FAQ'=>'int/TradeFAQ',
      ], 

    'Testing'=>[
      'Staff Tools'=>'int/Staff',
      ],         
  ];

global $MainBar,$HoverBar,$HoverBar2;
$MainBar = $HoverBar = $HoverBar2 = '';

function Show_Bar(&$Bar,$level=0,$Pval=1) { 
  global $USERID,$host,$PerfTypes,$MainBar,$HoverBar,$HoverBar2,$YEARDATA,$Event_Types_Full;
  $host= "https://" . $_SERVER['HTTP_HOST'];
//  echo "<ul class=MenuLevel$level>";
  $P=$Pval*100;
  $Pi = 0;
  foreach ($Bar as $text=>&$link) {
    $Minor = 0;
    $xtra = '';
    $Pi++; $P++;
    if (!$text) continue;
    switch (substr($text,0,1)) {
      case '*' : 
        $str = "<a class='NotYet MenuMinor2'>" . substr($text,1);
        $MainBar .= $str;
        $HoverBar .= $str;
        continue 2;
      case '!' :
        if (preg_match('/\*\*NONE\*\*/',$link,$res)) continue 2;
        $Minor = 1;
        $text = "<img src='" . substr($text,1) . "' class=HeaderIcon>";
        break;
      case '-' :
        $Minor = 1;
        $text = substr($text,1);
        break;
      case '=' :
        if (empty($YEARDATA) || $YEARDATA['TicketControl'] > 2 || $YEARDATA['TicketControl'] == 0) continue 2;
        $xtra = "id=MenuGetTicket";
        $text = substr($text,1);
        break;
      case '<' :
        $Minor = 2;
        $text = substr($text,1);
        break;
      case '?' :
        include_once("int/DanceLib.php");
        $Side = Get_Side($USERID);
        if (!$Side['IsASide']) continue 2;
        $text = substr($text,1);
        break;
      case '#' :
        include_once("int/DanceLib.php");
        $Side = Get_Side($USERID);
        $NotD = 0;
        foreach ($PerfTypes as $p=>$d) if (($d[0] != 'IsASide') && $Side[$d[0]]) $NotD = 1;
        if (!$NotD) continue 2;
        $text = substr($text,1);
        break;
      case '%' :
        if (!Feature('Donate')) continue 2;
        $xtra = "id=MenuDonate";
        $text = substr($text,1);
        break;
      case '@' :
        switch ($link) {
        case 0:
          foreach ($Event_Types_Full as $ET) {
            if ($ET['DontList']) continue;
            $Bar[$ET['Plural']] = (empty($ET['Sherlock']) || is_numeric($ET['Sherlock'])?("Sherlock?t=" . $ET['SN']):$ET['Sherlock']);
          }
          break;
        case 1:
          $Gals = Gen_Get_All('Galleries',"WHERE Level=0 ORDER BY MenuBarOrder DESC");
          foreach ($Gals as $G) {
            if ($G['MenuBarOrder']>0) $Bar[$G['SN']] = "int/ShowGallery?g=" . $G['id'];
          }
          break;          
          
        }
        continue 2;
      
      case '>' : // Move to End
        $text = substr($text,1);
        $Bar[$text] = $link;
        continue 2;
        break;
      
      default:
    }
    if (is_array($link)) {
      $MainBar .= "<div class='dropdown MenuMinor$Minor' id=MenuParent$P $xtra onmouseover=NoHoverSticky(event)>";
      $MainBar .= "<a onclick=NavStick(event) onmouseover=NavSetPosn(event,$P)>$text</a>";
      $MainBar .= "<div class=dropdown-content id=MenuChild$P>";
      if ($level == 1) $xtra .= " style='animation-duration: " . (150 * $Pi) . "ms; '";      
      $HoverBar .= "<div class=hoverdown id=HoverParent$P onclick=HoverDownShow($P) $xtra >$text<img class=hoverdownarrow src=/images/icons/Down-arrow.png id=DownArrow$P></div>";
      $HoverBar .= "<div class=hoverdown-content id=HoverChild$P>";
      Show_Bar($link,$level+1,$P);
      $MainBar .= "</div></div>";
      $HoverBar .= "</div>";
    } elseif (substr($link,0,1) == "!") {
      $MainBar .= "<a class='MenuMinor$Minor headericon' $xtra href='" . substr($link,1) . "' target=_blank>$text</a>";
      $HoverBar2 .= "<div class=hoverdown><a class='headericon' $xtra href='" . substr($link,1) . "' target=_blank>$text</a></div>";
    } elseif ($link == 0) {
      // Nothing
    } else {
      if ($level == 1) $xtra .= " style='animation-duration: " . (150 * $Pi) . "ms; '";
      $MainBar .=  "<a href='$host/$link' class='MenuMinor$Minor' $xtra onmouseover=NoHoverSticky(event)>$text</a>";
      $HoverBar .=  "<div class=hoverdown><a href='$host/$link' $xtra >$text</a></div>";
    }
  }
}



/* START HERE */

  // This generates the info into MainBar and HoverBar

//  TidyBar($Menus);
// var_dump($Menus);
  $MainBar .= "<nav class='PublicBar PublicBar$Bars navigation' align=right>";
  Show_Bar($Menus['Public']);
  $MainBar .= "</nav>";
//  echo $MainBar;
  
  if ($Bars == 2) {
    $MainBar .=  "<div class='navigation PrivateBar MenuMinor0' align=right>";
    if ( isset($USER['AccessLevel']) && $USER['AccessLevel'] == $Access_Type['Participant'] ) {
      switch ($USER['Subtype']) {
        case 'Perf': 
          Show_Bar($Menus['Perf']);
          break;
        case 'Trader':    
          Show_Bar($Menus['Trade']);
          break;
        default:
          break;
      }
      if (isset($_COOKIE['FEST2'])) {
        $MainBar .=  "<div class=MenuTesting>";
        Show_Bar($Menus['Testing']);
        $MainBar .=  "</div>";
      }
    } else if (isset($_COOKIE['FEST2']) && $UserName ) {
      Show_Bar($Menus['Private']);
    }
    $MainBar .= "</div>";
  }
  
//   var_dump($YEARDATA); 
  echo "<div class=main-header>"; 
  $NFrom = $DFrom = ($PLANYEARDATA['DateFri']+$PLANYEARDATA['FirstDay']);
  $NTo = $DTo = ($PLANYEARDATA['DateFri']+$PLANYEARDATA['LastDay']);
  $NMonth = $DMonth = $Months[$PLANYEARDATA['MonthFri']];
  $NYear = $PLANYEARDATA['NextFest']; 

  if ($PLANYEARDATA['Years2Show'] > 0) {
    $NFrom = ($NEXTYEARDATA['DateFri']+$NEXTYEARDATA['FirstDay']);
    $NTo = ($NEXTYEARDATA['DateFri']+$NEXTYEARDATA['LastDay']);
    $NMonth = $Months[$NEXTYEARDATA['MonthFri']];
    $NYear = $YEARDATA['NextFest'];
  }   

  echo "<a href=/>";
    echo "<img src=" . $FESTSYS['WebsiteBanner2'] . "?V=$V class='header-logo head-white-logo'>";
    echo "<img src=" . $FESTSYS['WebSiteBanner'] . "?V=$V class='header-logo head-coloured-logo'>";
    if ($PLANYEARDATA['Years2Show'] < 2) { // TODO Handle Both
      $Yr = substr($PLANYEAR,0,4);
      echo "<div class=SmallDates>$DFrom - $DTo $DMonth $Yr</div>";
      echo "<div class=FestDates>$DFrom - $DTo<br>$DMonth<br>$Yr</div>";
    } else {
      $NYear = substr($NYear,0,4) ;
      echo "<div class=SmallDates>$NFrom - $NTo $NMonth $NYear</div>";
      echo "<div class=FestDates>$NFrom - $NTo<br>$NMonth<br>$NYear</div>";    
    }
  echo "</a>";
  echo "<div class=MenuIcon><div id=MenuIconIcon class=MenuMenuIcon onclick=ShowHoverMenu()>Menu<img src=/images/icons/MenuIcon.png></div>";
  echo "<div id=MenuIconClose onclick=CloseHoverMenu() class=MenuMenuClose>Close<img src=/images/icons/MenuClose.png></div>";
  echo "<div id=HoverContainer><div id=HoverMenu>$HoverBar$HoverBar2</div></div></div>";
  echo "<div id=MenuBars>";
  echo $MainBar;

  echo "</div></div>";
  
?>

