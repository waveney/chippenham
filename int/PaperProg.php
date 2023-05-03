<?php
  include_once("fest.php");

  A_Check('Staff');
  
  include_once("int/fest.php");
  include_once("int/ProgLib.php");
  include_once("int/DispLib.php");
  include_once("int/DanceLib.php");
  include_once("int/MusicLib.php");
  dominimalhead("Performer Print Pages", ['css/PrintPage.css']);

  global $db,$Coming_Type,$YEAR,$PLANYEAR,$Book_State,$EType_States;  

  $Order = "EffectiveImportance DESC, s.RelOrder DESC, s.SN";
  if (isset($_REQUEST['ALPHA'])) $Order = "s.SN";
  $now = time();
  $Perf_Cats = [
   'Music'=>"SELECT s.*, y.*, IF(s.DiffImportance=1,s.MusicImportance,s.Importance) AS EffectiveImportance FROM Sides AS s, SideYear AS y " .
                      "WHERE s.SideId=y.SideId AND y.year='$YEAR' AND y.YearState>=" . $Book_State['Booking'] . 
                      " AND s.IsAnAct=1 AND y.ReleaseDate<$now AND s.NotPerformer=0 ORDER BY $Order",
             'Dance Displays'=>"SELECT s.*, y.*, IF(s.DiffImportance=1,s.DanceImportance,s.Importance) AS EffectiveImportance " .
                      "FROM Sides AS s, SideYear AS y WHERE s.SideId=y.SideId AND y.year='$YEAR' AND y.Coming=" . $Coming_Type['Y'] . 
                      " AND s.IsASide=1 AND y.ReleaseDate<$now AND s.NotPerformer=0 ORDER BY $Order",
             'Ceilidhs and Folk Dance'=> "SELECT s.*, y.*, IF(s.DiffImportance=1,s.OtherImportance,s.Importance) AS EffectiveImportance  FROM Sides AS s, SideYear AS y " .
                      "WHERE s.SideId=y.SideId AND y.year='$YEAR' AND y.YearState>=" . $Book_State['Booking'] . 
                      " AND s.IsCeilidh=1 AND y.ReleaseDate<$now AND s.NotPerformer=0 ORDER BY $Order",
             'Family and Community' => "SELECT s.*, y.*, IF(s.DiffImportance=1,s.FamilyImportance,s.Importance) AS EffectiveImportance  FROM Sides AS s, SideYear AS y " .
                      "WHERE s.SideId=y.SideId AND y.year='$YEAR' AND y.YearState>=" . $Book_State['Booking'] . 
                      " AND s.IsFamily=1 AND y.ReleaseDate<$now AND s.NotPerformer=0 ORDER BY $Order",
             'Other Performers' => "SELECT s.*, y.*, IF(s.DiffImportance=1,s.OtherImportance,s.Importance) AS EffectiveImportance  FROM Sides AS s, SideYear AS y " .
                      "WHERE s.SideId=y.SideId AND y.year='$YEAR' AND y.YearState>=" . $Book_State['Booking'] . 
                      " AND s.IsOther=1 AND y.ReleaseDate<$now AND s.NotPerformer=0 ORDER BY $Order"
            ];
  
  $Displayed = [];
  echo "<script>document.getElementsByTagName('body')[0].style.background = 'none';</script><div class=PaperP>";
  foreach ($Perf_Cats as $Title=>$fetch) {
    echo "<h2><center>$Title</center></h2>";
    $Slist = [];
    $perfQ = $db->query($fetch);
    if ($perfQ) while($side = $perfQ->fetch_assoc()) $Slist[] = $side;

    echo "<table class=PerfT width=100% border>";  
    $Pair = 0;
    foreach ($Slist as $perf) {
      if ($perf['NotPerformer'] ) continue;
      if (isset($Displayed[$perf['SideId']])) continue;
      if (empty($perf['Description']) && Feature('OmitEmptyDescriptions')) continue;
      $Displayed[$perf['SideId']] = 1;
      $Imp = $perf['EffectiveImportance'];
      if ($Pair == 0) echo "<tr>";
//      if ($Pair == 0) echo "<div class=PPair>";
      $Photo = $perf['Photo'];
      if (!$Photo) $Photo = '/images/icons/user2.png';
      echo "<td class=Pic$Pair rowspan=2><img src=$Photo class=PL$Imp>";
      if ($Pair == 1) echo "<tr>";
      echo "<td class=Desc$Pair ><span class=PName$Imp>" . $perf['SN'] . "</span> <span PDesc$Imp>" . $perf['Description'] . "</span>";

//      echo "<div class=PPPicP$Pair><img src=$Photo class=PPPic$Imp></div>";
//      echo "<div class=PPDescP$Pair><div class=PPName$Imp>" . $perf['SN'] . "</div><div PPDesc$Imp>" . $perf['Description'] . "</div></div>";
//      if ($Pair == 1) echo "</div><br>";
      $Pair = ($Pair+1)%2;
    }
    echo "</table><br>";
//    if ($Pair == 1) echo "</div>";
//    echo "<br clear=all>";
  }
  echo "</div>";
  exit;
?>

