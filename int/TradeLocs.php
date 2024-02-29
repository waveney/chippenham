<?php
  include_once("fest.php");
  A_Check('Committee','Trade');

  dostaffhead("Manage Trade Locations");
  global $YEAR,$LocTypes;

  include_once("TradeLib.php");
  include_once("InvoiceLib.php");
  
  echo "<button class='floatright FullD' onclick=\"($('.FullD').toggle())\">All Locs</button><button class='floatright FullD' hidden onclick=\"($('.FullD').toggle())\">Curent Locs</button> ";
  echo "<div class='content'><h2>Manage Trade Locations</h2>\n";
  
  echo "Artisan Messages trigger local Artisan related emails<p>Only set the Invoice Code for locations that override normal trade type invoice codes<p>";
  
  echo "Set No List to exclude from venues on Show Trade<br>";
  
//  echo "Power Offset - Bit number for power properties<p>";
  
  echo "Properties bit 0 = table, 1=Power - not in use<br>";
  echo "Width if >0, total usage is calculated, if Nat Depth > 0 it is used to consume extra width<br>";
  echo "Type 0- Trade, 1-Infra structure, 2-other<P>";
  
  $Locs=Get_Trade_Locs(1);
  $LocNames = Get_Trade_Locs(0);

//  var_dump($_REQUEST);

  if (UpdateMany('TradeLocs','Put_Trade_Loc',$Locs,1)) $Locs=Get_Trade_Locs(1);

  $coln = 0;
  $InvCodes =  Get_InvoiceCodes();
  $t = [];
  
  echo "<form method=post action=TradeLocs>";
  echo "<div class=Scrolltable><table id=indextable border>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Index</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Prefix</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Type</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>No List</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>In Use</a>\n";
  echo "<th class=FullD hidden><a href=javascript:SortTable(" . $coln++ . ",'T')>Days</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Artisan Msgs</a>\n";
  echo "<th class=FullD hidden><a href=javascript:SortTable(" . $coln++ . ",'T')>Invoice Code</a>\n";
//  echo "<th class=FullD hidden><a href=javascript:SortTable(" . $coln++ . ",'N')>Power Offset</a>\n";
  echo "<th class=FullD hidden><a href=javascript:SortTable(" . $coln++ . ",'N')>Props</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Total Width</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Nat Depth</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Link</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Part of</a>\n";

  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Notes</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Map</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Map Scale</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Show Scale</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Setup</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Assign</a>\n";
  echo "</thead><tbody>";
  foreach($Locs as $t) {
    $i = $t['TLocId'];
    echo "<tr " . ($t['InUse']?'': " class=FullD hidden") . "><td>$i" . fm_text1("",$t,'SN',1,'','',"SN$i");
    echo "<td>" . fm_select($Prefixes,$t,"prefix",0,'',"prefix$i");
    echo "<td>" . fm_select($LocTypes,$t,'Type',0,'',"Type$i");
    echo "<td>" . fm_checkbox('',$t,'NoList','',"NoList$i");
//    echo fm_text1('',$t,'Pitches',0.25,'','',"Pitches$i");
    echo "<td>" . fm_checkbox('',$t,'InUse','',"InUse$i");
    echo "<td class=FullD hidden>" . fm_select($Trade_Days,$t,"Days",0,'',"Days$i");
    echo "<td>" . fm_checkbox("",$t,'ArtisanMsgs','',"ArtisanMsgs$i");
    echo "<td class=FullD hidden>" . fm_select($InvCodes,$t,'InvoiceCode',1,'',"InvoiceCode$i");
//    echo fm_number1('',$t,'PowerOffset',' class=FullD hidden','',"PowerOffset$i");
    echo fm_number1('',$t,'Props',' class=FullD hidden','',"Props$i");
    echo fm_text1('',$t,'TotalWidth',0.25,'','',"TotalWidth$i");
    echo fm_text1('',$t,'NatDepth',0.25,'','',"NatDepth$i");
    echo fm_text1('',$t,'HasLink',1,'','',"HasLink$i");
    echo "<td>" . fm_select($LocNames,$t,'PartOf',1,'',"PartOf$i");
    echo fm_text1('',$t,'Notes',1,'','',"Notes$i");
    echo fm_text1('',$t,'MapImage',1,'','',"MapImage$i");
    echo fm_text1('',$t,'Mapscale',0.5,'','',"Mapscale$i");
    echo fm_text1('',$t,'Showscale',0.5,'','',"Showscale$i");
    echo "<td><a href=TradeSetup?i=$i&Y=$YEAR>Setup</a>";
    echo "<td><a href=TradeAssign?i=$i>Assign</a>";
    echo "\n";
  }
  echo "<tr><td><td><input type=text name=SN0 >";
  echo "<td>" . fm_select2($Prefixes,0,'prefix0');
  echo "<td>" . fm_select($LocTypes,$t,'Type',0,'',"Type0");
//  echo fm_text1('',$t,'Pitches',0.25,'','',"Pitches0");
  echo "<td><input type=checkbox name=NoList0>";
  echo "<td><input type=checkbox name=InUse0>";
  echo "<td class=FullD hidden>" . fm_select2($Trade_Days,0,'Days0');
  echo "<td><input type=checkbox name=ArtisanMsgs0>";
  echo "<td class=FullD hidden>" . fm_select($InvCodes,$t,'InvoiceCode',1,'',"InvoiceCode0");
//  echo fm_number1('',$t,'PowerOffset',' class=FullD hidden','',"PowerOffset0");
  echo fm_number1('',$t,'Props',' class=FullD hidden','',"Props0");
  echo fm_text1('',$t,'TotalWidth',0.25,'','',"TotalWidth0");
  echo fm_text1('',$t,'NatDepth',0.25,'','',"NatDepth0");

  echo fm_text1('',$t,'HasLink',1,'','',"Link0");
  echo "<td>" . fm_select($LocNames,$t,'PartOf',1,'',"PartOf0");

  echo "<td><input type=text name=Notes0 >";
  echo "<td><input type=text name=MapImage0 >";
  echo fm_text1('',$t,'Mapscale',0.5,'','',"Mapscale0"); 
  echo fm_text1('',$t,'Showscale',0.5,'','',"Showscale0"); 
  
  // if (Access('SysAdmin')) echo "<tr><td class=NotSide>Debug<td colspan=10 class=NotSide><textarea id=Debug></textarea><p><span id=DebugPane></span>";

  echo "</table></div>\n";
  echo "<input type=submit name=Update value=Update>\n";
  echo "</form></div>";

  dotail();

