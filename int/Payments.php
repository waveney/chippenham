<?php

  include_once("fest.php");
  include_once("BudgetLib.php");
  include_once("DanceLib.php");
  include_once("DocLib.php");

  A_Check('Committee','Finance');
  
  function SortCode($sc) {
    if (!$sc) return '';
    $xsc = preg_replace("/[^0-9]/",'',$sc) . '000000';
    return substr($xsc,0,2) . '-' . substr($xsc,2,2) . '-' .substr($xsc,4,2);
  }

  $csv = 0;
  if (isset($_REQUEST['F'])) $csv = $_REQUEST['F'];

  if ($csv) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=PerformerPayments.csv');

    // create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

  } else {
    dostaffhead("All Performer Payments");
  }

  global $db,$YEAR,$BUDGET;

  $qry = "SELECT s.*, y.* FROM Sides s, SideYear y WHERE y.Year='$YEAR' AND y.TotalFee>0 AND s.SideId=y.SideId AND (y.Coming=2 OR y.Yearstate>=2 ) ORDER BY s.SN";
  $pays = $db->query($qry);
  if (!$pays) {
    echo "Nothing to pay";
    dotail();
  }
  $tot = 0;

  $AllActive = Get_AllUsers(0);

  if ($csv) {
    $heads = ['Name','Total Fee','Sort Code','Ac Number','Ac Name','Booked by'];
    foreach($BUDGET as $i=>$b) {
      if ($b['id']) $heads[] = $b['SN'];
    }
    $heads[] = 'Homeless';

    fputcsv($output, $heads,',','"');

  } else {
    echo "<h2><a href=Payments?Y=$YEAR&F=CSV>Output as CSV</a></h2>";
    $coln = 0;
    echo "<div class=Scrolltable><table id=indextable border>\n";
    echo "<thead><tr>";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>id</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Total Fee</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Sort Code</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Ac Number</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Ac Name</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Booked by</a>\n";

    foreach($BUDGET as $i=>$b) {
      if ($b['id']) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>" . $b['SN'] . "</a>\n";
    }
    if ($BUDGET) echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Homeless</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Contract</a>\n";
    echo "</thead><tbody>";
  }

  while ($payee = $pays->fetch_assoc()) {
    $bud = [];
    $bud[$payee['BudgetArea']] = $payee['TotalFee'];
    if ($payee['BudgetArea2']) {
      $bud[$payee['BudgetArea2']] = $payee['BudgetValue2'];
      $bud[$payee['BudgetArea']] -= $payee['BudgetValue2'];
    }
    if ($payee['BudgetArea3']) {
      $bud[$payee['BudgetArea3']] = $payee['BudgetValue3'];
      $bud[$payee['BudgetArea']] -= $payee['BudgetValue3'];
    }

    if ($csv) {
      $data = [$payee['SN'],$payee['TotalFee'], SortCode($payee['SortCode']), $payee['Account'], $payee['AccountName'],($AllActive[$payee['BookedBy']] ?? 'Unknown')];

      foreach($BUDGET as $i=>$b)  $data[]= (isset($bud[$i])?$bud[$i]:"");
      $csvdata = [];
      foreach ($data as $d) $csvdata[] = (is_numeric($d)?"$d":$d);

      fputcsv($output,$csvdata);
    } else {
      echo "<tr><td>" . $payee['SideId'] . "/" . $payee['syId'] . "<td><a href=AddPerf?id=" . $payee['SideId'] . ">" . $payee['SN'] . "</a>";
      echo "<td>" . $payee['TotalFee'];
      echo "<td>" . SortCode($payee['SortCode']) . "<td>" . $payee['Account'] . "<td>" . $payee['AccountName'];
      echo "<td>" . ($AllActive[$payee['BookedBy']] ?? 'Unknown');
      $tot += $payee['TotalFee'];

      foreach($BUDGET as $i=>$b) {
        echo "<td>";
        if (isset($bud[$i])) echo $bud[$i];
      }
      echo "<td>";
      if ($files = glob("Contracts/$YEAR/" . $payee['SideId'] . ".*")) {
        $IssPfx = '';
        $file = '';
        if ($payee['Contracts']) $IssPfx = "." . $payee['Contracts'];
        $files = glob("Contracts/$YEAR/" . $payee['SideId'] . "$IssPfx.*");
        if ($files) {
          $file = $files[0];
        } else if ($payee['Contracts'] == 1) {
          $files = glob("Contracts/$YEAR/" . $payee['SideId'] . ".*");
          if ($files) $file = $files[0];
        }
        if ($file) {
          echo "<a href='ShowFile?l=$file'>View</a>";
        }
      }
      echo "\n";
    }
  }

  if ($csv) {

  } else {
    echo "</table></div>";
    echo "Total: $tot<p>";

    dotail();
  }


