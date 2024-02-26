<?php

// List of Infrastructure and where it is and power needs

// Quick hack for power first, only currently for Island park (trade locs), will generalise late for others

// to Delete set Xsize to 0

include_once("fest.php");
include_once("TradeLib.php");
//include_once("TradeLib.php");
  A_Check('Committee','Venues');

  dostaffhead("Manage Other Infrastructure");
  global $PLANYEAR,$ObjectTypes;

  $TradePower = Gen_Get_All("TradePower");
  $Powers = [];
  foreach ($TradePower as $i=>$P) $Powers[$i] = $P['Name'];

  $Locs=Get_Trade_Locs(1);
  $LocNames = Get_Trade_Locs(0);

  
  echo "<div class=content><h2>Manage Infrastructure</h2>\n";
  echo "This is a short term hack, better and more coming.<br>";
  echo "Cat 0- All, 1 = Special<p>";
  
  $Things = Gen_Get_All('Infrastructure');
  if (UpdateMany('Infrastructure','',$Things,1,'','','Name')) $Things=Gen_Get_All('Infrastructure');
  
  $coln = 0;
  $t = [];
  
  echo "<form method=post>";
  echo "<div class=Scrolltable+><table id=indextable border>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Index</a>\n";

  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Display Text</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Location</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Cat</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Object</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Colour</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Font</a>\n";

  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>X pos</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Y pos</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Angle</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>X size</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Y size</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Power</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Num</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Power From</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Power To</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Tables</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>FireEx</a>\n";

  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Link</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Order</a>\n";

  
  echo "</thead><tbody>";
  if ($Things) foreach($Things as $t) {
    $i = $t['id'];
    echo "<tr><td>$i" . fm_text1("",$t,'Name',1,'','',"Name$i") . fm_text1("",$t,'ShortName',1,'','',"ShortName$i");
    echo "<td>" . fm_select($LocNames,$t,'Location',0,'',"Location$i"); 
    echo fm_text1('',$t,'Category',0.1,'','',"Category$i");// Category
    echo "<td>" . fm_select($ObjectTypes,$t,'ObjectType',0,'',"ObjectType$i"); 

    echo fm_text1("",$t,'MapColour',1,'','',"MapColour$i");
    echo fm_text1("",$t,'Font',0.20,'','',"Font$i");
    echo fm_text1("",$t,'X',0.20,'','',"X$i") . fm_text1("",$t,'Y',0.20,'','',"Y$i");
    echo fm_text1("",$t,'Angle',0.20,'','',"Angle$i");
    echo fm_text1("",$t,'Xsize',0.20,'','',"Xsize$i") . fm_text1("",$t,'Ysize',0.20,'','',"Ysize$i");
    echo "<td>". fm_select($Powers,$t,'Power','','',"Power$i") . fm_text1('',$t,'NumberPower',0.1,'','',"NumberPower$i");
    echo fm_text1("",$t,'PowerFrom',1,'','',"PowerFrom$i") . fm_text1("",$t,'PowerTo',1,'','',"PowerTo$i");
    echo fm_text1('',$t,'Tables',0.1,'','',"Tables$i");
    echo fm_text1('',$t,'FireEx',0.1,'','',"FireEx$i");


    echo fm_text1('',$t,'HasLink',1,'','',"HasLink$i");
    echo fm_text1('',$t,'PlaceOrder',1,'','',"PlaceOrder$i");
    echo "\n";
  }
  $t = ['NumberPower'=>1, 'Location'=>Feature('TradeBaseMap')];
  $i = 0;
    echo "<tr><td>$i" . fm_text1("",$t,'Name',1,'','',"Name$i") . fm_text1("",$t,'ShortName',1,'','',"ShortName$i");
    echo "<td>" . fm_select($LocNames,$t,'Location',0,'',"Location$i"); 
    echo fm_text1('',$t,'Category',0.1,'','',"Category$i");// Category
    echo "<td>" . fm_select($ObjectTypes,$t,'ObjectType',0,'',"ObjectType$i"); 
    echo fm_text1("",$t,'MapColour',1,'','',"MapColour$i");
    echo fm_text1("",$t,'Font',0.20,'','',"Font$i");
    echo fm_text1("",$t,'X',0.20,'','',"X$i") . fm_text1("",$t,'Y',0.20,'','',"Y$i");
    echo fm_text1("",$t,'Angle',0.20,'','',"Angle$i");
    echo fm_text1("",$t,'Xsize',0.20,'','',"Xsize$i") . fm_text1("",$t,'Ysize',0.20,'','',"Ysize$i");
    echo "<td>". fm_select($Powers,$t,'Power','','',"Power$i") . fm_text1('',$t,'NumberPower',0.1,'','',"NumberPower$i");
    echo fm_text1("",$t,'PowerFrom',1,'','',"PowerFrom$i") . fm_text1("",$t,'PowerTo',1,'','',"PowerTo$i");

    echo fm_text1('',$t,'Tables',0.1,'','',"Tables$i");
    echo fm_text1('',$t,'FireEx',0.1,'','',"FireEx$i");
    echo fm_text1('',$t,'HasLink',1,'','',"HasLink0");
    echo fm_text1('',$t,'PlaceOrder',1,'','',"PlaceOrder0");

    
  echo "</table></div>\n";
  
  echo "<input type=submit name=Update value=Update>\n";
  echo "</form></div>";

  dotail();

