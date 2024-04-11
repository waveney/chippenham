<?php

function &TradersInLoc() {
  global $TradersInLoc;
  return $TradersInLoc;
}

/* Get map size
   get scale 
   send the image
   setup the svg
   plot the pitches
   */

function Pitch_Map(&$loc,&$Pitches,$Traders=0,$Pub=0,$Scale=1,$LinkRoot='') {  
  // Pub 0 = Public map, 1 = Trade (may be same as 0), 2 = Trader Only before public, 3 = Setup, 4=Assign, 5=EMP, 5=Infra Only
  global $TradeTypeData,$Trade_State,$TradersInLoc;
  $CatMask   = [1,1,1,3,1,3,2];
  $ShowPitch = [0,0,1,1,1,0,0];
  $ShowLinks = [1,1,1,0,0,1,1];
  $DragOver  = [0,0,0,0,1,0,0];
  $TradersInLoc = [];
  
  if (!$loc['MapImage']) return;
  
  $PLocId = $TLocId = $loc['TLocId'];
  if ($loc['PartOf']) $PLocId = $loc['PartOf'];
  $XtraInfra = Gen_Get_Cond('Infrastructure',"Location=$TLocId  ORDER BY PlaceOrder ASC,id");
  
//  var_dump($loc,$Pub,$Scale,$LinkRoot);
  $scale=$Scale*$loc['Showscale'];
  $Mapscale = $loc['Mapscale'];
//  $sp = $scale*100;
  $Factor = 20*$scale*$Mapscale;
  $Links = 1;  // For infra structure
  $Staff = (isset($_REQUEST['STAFF'])?'&STAFF':'');

  $Key = [];

  $PitchesByName = [];
  foreach ($Pitches as $Pi) if ($Pi['Type'] == 0) $PitchesByName[$Pi['SN'] ?? $Pi['Posn']] = $Pi;
 
//  var_dump($PitchesByName);
  $Usage = [];$TT = [];$TNum = [];
  if ($Traders) {
    foreach ($Traders as $Trad) 
      for ($i=0; $i<3; $i++) 
        if ($Trad["PitchLoc$i"] == $PLocId) {
          $list = explode(',',$Trad["PitchNum$i"]);
          foreach ($list as $p) {
            if (!$p) continue;
            if (!is_numeric($p) && isset($PitchesByName[$p])) $p = $PitchesByName[$p]['Posn'];
            if ($p) $Usage[$p] = (isset($Usage[$p])?"CLASH!":$Trad['SN']);
            if ( $Trad['BookingState'] == $Trade_State['Deposit Paid'] || 
                 $Trad['BookingState'] == $Trade_State['Balance Requested'] || 
                 $Trad['BookingState'] == $Trade_State['Fully Paid'] ) {
              $TT[$p] = $Trad['TradeType'];
            } else {
              $TT[$p] = -1;
            }
            $TNum[$p] = $Trad['Tid'];
          }
        }
  }
  
//  var_dump($Usage,$TT);
  
  $ImgHt = 1200;
  $ImgWi = 700;
  $stuff = getimagesize(ltrim($loc['MapImage'],'/'));
  if ($stuff) {
    $ImgHt = $stuff[1];
    $ImgWi = $stuff[0];
  }

// var_dump($ImgHt,$ImgWi);

//  echo "scale=$scale sp=$sp Ht=$ImgHt Mapscale=$Mapscale <br>";
  echo "<div class=img-overlay-wrap>";
  echo "<img src=" . $loc['MapImage'] . " width=" . ($ImgWi*$scale) . ">";
  echo "<svg width=" . ($ImgWi*$scale) . " height=" . ($ImgHt*$scale) . ">";
  
  echo '<pattern id="diagonalHatch" patternUnits="userSpaceOnUse" width="4" height="4">
          <path d="M-1,1 l2,-2
           M0,4 l4,-4
           M3,5 l2,-2" 
           style="stroke:LightSeaGreen; stroke-width:1" />
        </pattern>
        <defs>
          <!-- A marker to be used as an arrowhead -->
          <marker
          id="arrow"
          viewBox="0 0 10 10"
          refX="5"
          refY="5"
          markerWidth="6"
          markerHeight="6"
          orient="auto-start-reverse">
          <path d="M 0 0 L 10 5 L 0 10 z" />
        </marker>
      </defs>
';
  
  if ($XtraInfra) {
    foreach($XtraInfra as $Inf) {  
      if ($Inf['Category'] !=0 && (($Inf['Category'] & $CatMask[$Pub]) == 0)) continue; // Not needed in this case
      //    var_dump($Pitch,$TradeTypeData,$TT);
      $Xpos = ($Inf['X'] * $Factor);
      $Ypos = ($Inf['Y'] * $Factor);
      $Xwidth = ($Inf['Xsize'] * $Factor);
      $Yheight = ($Inf['Ysize'] * $Factor);
      $Lopen = 0;
      if ($Links && !empty($Inf['HasLink'])) {
        $lnk = $Inf['HasLink'];
        if ($LinkRoot) $lnk = preg_replace('/TradeStandMap/',$LinkRoot,$lnk);
        echo "<a href=$lnk$Staff&t=$Pub>";
        $Lopen = 1;
      }
  
      $Name = $Inf['ShortName'] ?? $Inf['Name'] ?? '?';
      $fill = 'White'; $stroke = 'black'; $Pen = 'black';
      $TxtXf = $TxtYf = 1;
      
      switch ($Inf['ObjectType']) {
      case 0: // Rectangle
        echo "<rect x=$Xpos y=$Ypos width=$Xwidth height=$Yheight ";
        if (!empty($Inf['MapColour'])) {
          if ($Inf['MapColour'] != '/') {
            $fill = $Inf['MapColour'];

            if ($Name[0] == "~") {
              $Pen = 'White';
              $Name = substr($Name,1);
            } else if ($Name[0] == "/") {
              $fill =  "url($Name)";
              $Name= '';
            }
          } else {
            $fill = "url(#diagonalHatch)";
            $stroke = 'LightSeaGreen';
          }
        }
        echo " style='fill:$fill;stroke:$stroke;";
        if ($Inf['Angle']) echo "transform: rotate(" . $Inf['Angle'] . "Deg); ";
        echo "'/>"; 
        
        // Fire Ex?
        if ($Inf['FireEx']) { // TODO and display type for cat 2's
           echo "<rect x=" . ($Xpos + $Xwidth - $Factor) . " y=" . ($Ypos + $Factor-2 ) . " width=$Factor height=$Factor ";
           echo " style='fill:red; stroke:red; ";
           if ($Inf['Angle']) echo "transform: rotate(" . $Inf['Angle'] . "Deg); ";        
           echo "' />";
           $Key []= 'FireEx';
        }
        
        break;
        
      case '1': // Text no action
        $Xwidth = 1000; $Yheight = 1000;
        break;
      
      case '2': // Circle
        echo "<circle cx=$Xpos cy=$Ypos r=$Xwidth ";
        if (!empty($Inf['MapColour'])) {
          if ($Inf['MapColour'] != '/') {
            $fill = $Inf['MapColour'];

            if ($Name[0] == "~") {
              $Pen = 'White';
              $Name = substr($Name,1);
            } else if ($Name[0] == "/") {
              $fill =  "url($Name)";
              $Name= '';
            }
          } else {
            $fill = "url(#diagonalHatch)";
            $stroke = 'LightSeaGreen';
          }
        }
        echo " style='fill:$fill;stroke:$stroke;";
        if ($Inf['Angle']) echo "transform: rotate(" . $Inf['Angle'] . "Deg); ";
  //?     echo "' id=Posn$Posn ondragstart=drag(event) ondragover=allow(event) ondrop=drop(event); // Not used at present
        echo "'/>"; 
        $Inf['X'] -= $Inf['Xsize']; // For text positioning
        $Inf['Y'] -= $Inf['Ysize']/2; // For text positioning
        $TxtXf = $TxtYf = 2;
      
        break;
      
      case '3': // arrow
        echo "<line x1=$Xpos y1=$Ypos x2=$Xwidth y2=$Yheight stroke=$stroke marker-end=url(#arrow) />";
        break;
      
      case '4': // Image
        echo "<image x=$Xpos y=$Ypos width=$Xwidth height=$Yheight href=$Name ";
        if ($Inf['Angle']) echo "style='transform: rotate(" . $Inf['Angle'] . "Deg);' ";        
        
        echo " />";
        $Name = '';
      }
      
      // Now do any text

      echo "<title>$Name</title>";

      echo "<text x=" . (($Inf['X']+0.2) * $Factor)  . " y=" . ((($Inf['Y']+0.7)/$Mapscale) * $Factor);
      echo " style='";
      if ($Inf['Angle']) echo "transform: rotate(" . $Inf['Angle'] . "Deg);";
      echo "fill:$Pen; font-size:10px;'>";
      if ($Name) {
      // Divide into Chunks each line has a chunk display Ysize chunks - the posn is a chunk,  chunk length = 3xXsize 
      // Chunking - split to Words then add words to full - if no words split word (hard)
      // Remove x t/a 
      // Lowercase 
      // Spilt at words of poss, otherwise at length (for now)

        $ChSize = max(floor($Inf['Xsize']*45*$Mapscale/($Inf['Font']+10))*$TxtXf,2);
        $Ystart = 0.6*($Inf['Font']+10)/10 -0.2;
        $MaxCnk = max(floor(($Inf['Ysize']*2.5*$Mapscale))*$TxtYf,1);
  //      $Name = preg_replace('/.*t\/a (.*)/',
  //      $Chunks = str_split($Name,$ChSize);
        $Chunks = ChunkSplit($Name,$ChSize,$MaxCnk);

        foreach ($Chunks as $i=>$Chunk) {
          if ($i>=$MaxCnk) break; 
   //       $Chunk = substr($Name,0,$ChSize);
          echo "<tspan x=" . (($Inf['X']+0.2) * $Factor)  . " y=" . (($Inf['Y']+$Ystart/$Mapscale) * $Factor) . 
               " style=' font-weight:bold; font-size:" . (10+$Inf['Font']*2) . "px;'>$Chunk</tspan>";
          $Ystart += 1.2*(10+$Inf['Font']*2.1)/20;
        }
      }
      echo "</text>";
      if ($Lopen) echo "</a>";
    }
  }
  
  $Links=$ShowLinks[$Pub];

  //var_dump($Pitches);
  foreach ($Pitches as $Pitch) {
    $Posn = $Pitch['Posn'];
    $Name = '';
    $Lopen = 0;
    if (isset($Usage[$Posn])) $Name = $Usage[$Posn];
    if ($Pitch['Type']) $Name = $Pitch['SN'];
    if ($Links) {
      if ($Links == 1 && !$Pitch['Type']) {
        if (isset($TNum[$Posn])) {
          echo "<a href=#Trader" . $TNum[$Posn] . ">";
          $Lopen = 1;
          $TradersInLoc[] = $TNum[$Posn];
        }
      } elseif ($Links == 2) {
        echo "<a href='TradeShow?SEL=" . $Pitch['SN'] . "$Staff'>";
        $Lopen = 1;
      }
    } else {
//      echo "<a>";
//      $Lopen = 1;
    }
    
//    var_dump($Pitch,$TradeTypeData,$TT);
    $Xpos = ($Pitch['X'] * $Factor);
    $Ypos = ($Pitch['Y'] * $Factor);
    $Xwidth = ($Pitch['Xsize'] * $Factor);
    $Yheight = ($Pitch['Ysize'] * $Factor);

    if ($Pitch['Type'] != 2) {
      $PitchName = (($Pitch['Type'] || empty($Pitch['SN']))? $Pitch['Posn'] : $Pitch['SN']);
      echo "\n<rect x=$Xpos y=$Ypos width=$Xwidth height=$Yheight id=Pitch:$PitchName";
      echo " style='fill:" . ($Pitch['Type']?$Pitch['Colour']:(($TT[$Posn]??-1)>=0?($Name?($TradeTypeData[$TT[$Posn]]['Colour']??0)  : "yellow"):"white")) . 
           ";stroke:black;";
      if ($Pitch['Angle']) echo "transform: rotate(" . $Pitch['Angle'] . "Deg);";
      if ($DragOver[$Pub]) {
        echo "' id=Posn$Posn ondragstart=drag(event) ondragover=allow(event) ondrop=drop(event) />"; // Not used at present
      } else {
        echo "'/>"; 
      }

      echo "<title>$Name</title>";
    }
    
    echo "\n<text x=" . ($Xpos+2)  . " y=" . ($Ypos+$loc['TextFudge']); //(($Pitch['Y']+($Name?0.7:1.2)/$Mapscale) * $Factor -60);
    $YAdd = (11+$Pitch['Font']);
    $Delta = 0;
    
    echo " style='";
    if ($Pitch['Angle']) echo "transform: rotate(" . $Pitch['Angle'] . "Deg); ";
    echo "font-size:10px;'>";
    if ($ShowPitch[$Pub]) {
      echo "#" . $Posn;
      if (($Pitch['Type'] == 0) && $Pitch['SN']) echo " - " . ($Pitch['SN']??'');
      $Delta = 11;
    } else if (($Pub == 2) && ($Pitch['SN']??'')) {
      echo $Pitch['SN'];
      $Delta = 11;
    }
    if ($Name) {
    // Divide into Chunks each line has a chunk display Ysize chunks - the posn is a chunk,  chunk length = 3xXsize 
    // Chunking - split to Words then add words to full - if no words split word (hard)
    // Remove x t/a 
    // Lowercase 
    // Spilt at words of poss, otherwise at length (for now)
    
      $ChSize = ceil($Pitch['Xsize']*35*$Mapscale/($Pitch['Font']+10))*$scale;
      $MaxCnk = floor(($Pitch['Ysize']*2.5*$Mapscale*$scale) - 1);
//      $Name = preg_replace('/.*t\/a (.*)/',
//      $Chunks = str_split($Name,$ChSize);
      $Chunks = ChunkSplit($Name,$ChSize,$MaxCnk);
      
      foreach ($Chunks as $i=>$Chunk) {
        if ($i>=$MaxCnk) break; 
 //       $Chunk = substr($Name,0,$ChSize);
        $tl = ((strlen($Chunk) > (($ChSize-3)*$Pitch['Xsize']/3))?"textLength=" . ($Xwidth-2):'');
        echo "\n<tspan x=" . ($Xpos+1) . " dy=$Delta $tl " .
             " style='font-size:" . ($Pitch['Font']+10) . "px;'>$Chunk</tspan>";
        $Delta = $YAdd;
      }
    }
    echo "</text>";
    if ($Lopen) echo "</a>";

  }   
  echo "</svg>";
  echo "</div>";
}
