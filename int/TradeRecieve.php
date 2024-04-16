<?php

include_once('fest.php');
include_once('TradeLib.php');
include_once('InvoiceLib.php');

global $YEAR,$db;

  A_Check('Committee','Finance');
  
  dostaffhead('Trader Payments Recieved');

  $Trade_Types = Get_Trade_Types(1);
  $qry = "SELECT t.*, y.* FROM Trade AS t, TradeYear AS y WHERE y.BookingState=9 AND t.Tid = y.Tid AND y.Year='$YEAR' ORDER BY t.TradeType,SN";
  
  $coln = 0;
  echo "<div class=Scrolltable><table id=indextable border>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Type</a>\n";
  
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Deposit</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Deposit Ref</a>\n";
  
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Balance</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Balance Ref</a>\n";

  echo "</thead><tbody>";
  $res = $db->query($qry);
//  echo $qry;
  
  while ($fetch = $res->fetch_assoc()) {
    $Tid = $fetch['Tid'];

    $Invoices = Get_InvoicesFor($Tid);
//var_dump($Tid, $Invoices);
    if (empty($Invoices)) continue; // No payments

    echo "<tr><td><a href=Trade?id=$Tid>" . $fetch['SN'] . "<td>" . $Trade_Types[$fetch['TradeType']]['SN'];
    
    if (count($Invoices) == 1) { // Single Final
      echo "<td><td><td>" . $fetch['TotalPaid'] . "<td>" . $Invoices[0]['OurRef'] . "/" . $Invoices[0]['id'];
      
    } else { // Deposit and final
      $Dep = T_Deposit($fetch);
      echo "<td>$Dep<td>" . $Invoices[0]['OurRef'] . "/" . $Invoices[0]['id'];
      echo "<td>" . ($fetch['TotalPaid'] - $Dep) . "<td>" . $Invoices[1]['OurRef'] . "/" . $Invoices[1]['id'];      
    }
  }
  
  echo "</table></div>";
  echo "<p>";
  dotail();
