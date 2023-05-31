<?php

  include_once("fest.php");
  include_once("DanceLib.php");
  include_once("ProgLib.php");
  
  A_Check('SysAdmin');
  
  global $YEAR;
  
  // Get file to be analysed
  
  dostaffhead('Analytics Analysis');
  
  if (!isset($_REQUEST['F'])) {
    echo "<h1>Analyse Analytics</h1>";
    echo "<form method=post action=Analytics.php>";
    echo fm_text("File Name:",$_REQUEST,'F',4);
    dotail();
  }
  
  $File = fopen("Store/" . $_REQUEST['F'],"r");
  
  $Venues = Get_Venues();
  
  $PageUse = [];
  // open file
  $Res = fgetcsv($File); // Headers
  
  while ($Res = fgetcsv($File)) {
//  var_dump($Res);echo "<br>";
    if (count($Res)<2) continue;
    $Url = $Res[0];
    $Count = $Res[1];
    
    $Count = preg_replace('/,/','',$Count);
    
    $Url = preg_replace('/&Y=' . $YEAR . '/','', $Url);
    $Url = preg_replace('/^\/\?.*/','/',$Url);
    $Url = preg_replace('/&fbclid=.*/','',$Url);
    $Url = preg_replace('/\?fbclid=.*/','',$Url);
    
    if (strstr($Url,'Direct') || strstr($Url,'AddPerf') || strstr($Url,'EventAdd') || strstr($Url,'Login') || 
        strstr($Url,'EventList') || strstr($Url,'NewDanceProg') ||
        strstr($Url,'Staff') || strstr($Url,'SendPerfEmail') || strstr($Url,'CreatePerf') || strstr($Url,'Volunteers?A=Show') || 
        strstr($Url,'int/Volunteers?A=List') || strstr($Url,'ListDance') || strstr($Url,'PaperTime') || strstr($Url,'ListUsers') || 
        strstr($Url,'ListArticles') || strstr($Url,'AddUser') || strstr($Url,'/int/Dir') || strstr($Url,'/int/StewardShow') || 
        strstr($Url,'/int/AddVenue	') || strstr($Url,'/int/MasterData	') || strstr($Url,'/int/AddArticle') || strstr($Url,'/int/TEmailProformas	') || 
        strstr($Url,'Volunteers?ACTION=Accept') || strstr($Url,'ListMusic')) continue;
    
    $Url = preg_replace('/sidenum=/','id=',$Url);
    
    if (isset($PageUse[$Url])) {
      $PageUse[$Url] += $Count;
    } else {
      $PageUse[$Url] = $Count;
    }
  }
  
  echo "Read all data<p>";
  
  arsort($PageUse);
  
  echo "<table border><tr><td>Page<td>Count\n";
    
  foreach($PageUse as $Page=>$Cnt) {
    if (preg_match('/ShowPerf\?id=(\d*)/',$Page,$mtch)) {
      $Side = Get_Side($mtch[1]);
      echo "<tr><td>Performer Show: " . ($Side['SN'] ?? $mtch[1]) . "<td>$Cnt\n";      
    
    } elseif (preg_match('/VenueShow\?v=(\d*)/',$Page,$mtch)) {
      echo "<tr><td>Venue Show: " . ($Venues[$mtch[1]] ?? $mtch[1]) . "<td>$Cnt\n";
    } else {
      echo "<tr><td>$Page<td>$Cnt\n";
    }
  }
    
  echo "</table>\n";
  dotail();
    
  // get URL, Count
  // Convert Count to int
  
  // remove '&Y=xxxx' from URL
  // remove &fbclid=... from URL
  // Convert /?... to /
  
  // VenueShow&v=nnn to Venue: Name
  
  // ShowPerf sidenum=nnn to id=nnn
  
  // ShowPerf id=nnn to Performer: Name
  // Add to records
  
  // Sort by use
  
  // Print table

?>
