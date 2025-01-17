<?php
  include_once("fest.php");

  A_Check('Staff');

  include_once("ProgLib.php");
  include_once("int/DispLib.php");
  include_once("int/DanceLib.php");
  include_once("int/MusicLib.php");

  global $db,$Coming_Type,$YEAR,$PLANYEAR,$Book_State,$EType_States;
  $Set = 0;
  $Order = "s.SN";
  if (isset($_REQUEST['IMP'])) $Order = "EffectiveImportance DESC, s.RelOrder DESC, s.SN";

  if (isset($_REQUEST['CSV'])) {
    $csv = 1;
    $output = fopen('php://output', 'w');
  } else {
    $csv = 0;
    dostaffhead("List of Performers");
    echo "<a href=PerformerList?CSV>Output as CSV for a spreadsheet</a><p>";
  }

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
                      " AND s.IsOther=1 AND y.ReleaseDate<$now AND s.NotPerformer=0 ORDER BY $Order",
    'Youth' => "SELECT s.*, y.*, IF(s.DiffImportance=1,s.YouthImportance,s.Importance) AS EffectiveImportance  FROM Sides AS s, SideYear AS y " .
    "WHERE s.SideId=y.SideId AND y.year='$YEAR' AND y.YearState>=" . $Book_State['Booking'] .
    " AND s.IsYouth=1 AND y.ReleaseDate<$now AND s.NotPerformer=0 ORDER BY $Order"
  ];

  $Displayed = [];
  $SetNum = 1;
  foreach ($Perf_Cats as $Title=>$fetch) {
    if ($Set && ($Set != $SetNum++)) continue;
    if ($csv) {
      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename=Performers.csv');

      fputcsv($output, [$Title]);
    } else {
      echo "<div style='text-align:center;font-size:24;font-weight:bold;margin:10;'>$Title</div>";
    }
    $Slist = [];
    $perfQ = $db->query($fetch);
    if ($perfQ) while($side = $perfQ->fetch_assoc()) $Slist[] = $side;

    if (!$csv) echo "<table class=PerfT width=100% border>";
    $Pair = 0;
    foreach ($Slist as $perf) {
      if ($perf['NotPerformer'] ) continue;
      if ($csv) {
        fputcsv($output, [$perf['SN']]);
        } else {
        echo "<tr><td >" . $perf['SN'];
      }
    }
    if (!$csv) echo "</table>";
  }
  if (!$csv) echo "</div>";

  if ($csv==0) dotail();
  exit;
?>

