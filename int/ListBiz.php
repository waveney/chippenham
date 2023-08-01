<?php
  include_once("fest.php");
  A_Check('Steward');

  dostaffhead("List Business (other than traders)", ["/js/clipboard.min.js", "/js/emailclick.js"]);
  global $YEAR,$PLANYEAR,$Trade_States,$Trader_Status,$Trade_State_Colours;
  include_once("TradeLib.php");

  $Orgs = isset($_REQUEST['ORGS']);
  
  echo "<h2>List Businesses and Organisions Business (other than traders</h2>\n"; 
 
  echo "Click on column header to sort by column.  Click on Business's name for more detail<p>\n";

  echo "If you click on the email link, press control-V afterwards to paste the standard link into message.<p>";

  $qry = "SELECT t.* FROM Trade AS t WHERE (t.IsSponsor=1 OR t.IsAdvertiser=1 OR t.IsSupplier=1 OR t.IsOther=1) ORDER BY SN";  
  
  $res = $db->query($qry);

  if (!$res || $res->num_rows==0) {
    echo "<h2>No Businesses or Organisations Found</h2>\n";
  } else {
    $coln = 0;
    echo "<div class=Scrolltable><table id=indextable border>\n";
    echo "<thead><tr>";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Biz Name</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Contact</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Email</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Web</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Trader</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Sponsor</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Adverts</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Supply</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Other</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Invoices</a>\n";   
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Actions</a>\n";   
  
    echo "</thead><tbody>";

    while ($fetch = $res->fetch_assoc()) {
      $Tid = $fetch['Tid'];
      echo "<tr><td>$Tid<td width=300><a href=Biz?ACTION=Show&id=$Tid>" . ($fetch['SN']??'No Name Given') . "</a>";
      echo "<td>" . $fetch['BizName'];
      echo "<td>" . $fetch['Contact'];
      echo "<td>" . linkemailhtml($fetch,'Trade');
      echo "<td>";
        if (strlen($fetch['Website'])>6) echo weblink($fetch['Website'],'Web','target=_blank');

      echo "<td>" . ($fetch['IsTrader']?'Y':'');
      echo "<td>" . ($fetch['IsSponsor']?'Y':'');
      echo "<td>" . ($fetch['IsAdvertiser']?'Y':'');
      echo "<td>" . ($fetch['IsSupplier']?'Y':'');
      echo "<td>" . ($fetch['IsOther']?'Y':'');
      
      echo "<td><a href=InvoiceManage?FOR=$Tid>Invoices</a>";
      echo "<td><a href=InvoiceManage?ACTION=NEW&Tid=$Tid>New Invoice</a>";
      
    }
    echo "</tbody></table></div>\n";
  }
  dotail();
?>
