<?php
  include_once("fest.php");
  A_Check('Staff','Photos');

  dostaffhead("Manage Galleries");

  include_once("ImageLib.php");
  include_once("TradeLib.php");
  global $Medias;

  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
      case 'Add':
      echo "<form method=post action=GallManage>";
      $coln = 0;

      echo "<div class=Scrolltable><table id=indextable border>\n";
      echo "<thead><tr>";
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Media</a>\n";
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Level</a>\n";
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Gallery Set</a>\n";  
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Menu Bar Order</a>\n";
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Set Order</a>\n";
      echo "</thead><tbody>";
        
      echo "<tr><td><input type=text size=20 name=SN0 >";
      echo "<td>" . fm_select($Medias,$_REQUEST,"Media0") . fm_number1("",$_REQUEST,'Level0');
      echo "<td><input type=text name=GallerySet0 >";
      echo "<td><input type=text name=MenuBarOrder0 >";
      echo "<td><input type=text name=SetOrder0 >";
      echo "</table></div>";
      echo "<input type=submit name=ACTION value=Create>\n";
      dotail();
      
      
      case 'Create':      
//var_dump($_REQUEST);
        $Gal = ['SN'=>$_REQUEST['SN0'], 'Media'=>$_REQUEST['Media0'], 'Level'=>$_REQUEST['Level0'], 'GallerySet'=>$_REQUEST['GallerySet0'],
                'MenuBarOrder'=>$_REQUEST['MenuBarOrder0'], 'SetOrder'=>$_REQUEST['SetOrder0']];
        Gen_Put('Galleries',$Gal);
        break;
        
    }
  }


  $coln = 0;
  $Gals = Get_Gallery_Names();
  
  
//  if (UpdateMany('Galleries','Put_Gallery_Name',$Gals,1)) $Gals = Get_Gallery_Names();

  $coln = 0;
  echo "<h2>Galleries</h2><p>";
  echo "Non zero postive Menu Bar entries will be in the site banner ordered by that number<br>";
  echo "Any negative Menu Bar entry will not appear on 'All Galleries'<br>";
  echo "The Set Order is used for sub Galleries<p>";
 
  echo "<form method=post action=GallManage>";
  Register_IndexedAutoUpdate('Galleries',1,1);
  echo "<div class=Scrolltable><table id=indextable border>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Media</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Level</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Gallery Set</a>\n";  
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Menu Bar Order</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Set Order</a>\n";
  echo "</thead><tbody>";
  if ($Gals) foreach($Gals as $g) {
    $i =  $g['id'];
    echo "<tr><td>" . $i;
    echo fm_text1("",$g,'SN',1,'','',"SN$i") . "</a>";

    echo "<td>" . fm_select($Medias,$g,'Media',0,'',"Media$i");
    echo fm_number1("",$g,'Level','','',"Level$i");
    echo fm_text1("",$g,'GallerySet',1,'','',"GallerySet$i");
    echo fm_number1("",$g,'MenuBarOrder','','',"MenuBarOrder$i");
    echo fm_number1("",$g,'SetOrder','','',"SetOrder$i");
    echo "<td><a href=" . ($g['Media']?'GallVManage':'GallCManage') . "?g=" . $g['id'] . ">Edit</a>";
    echo "<td><a href=ShowGallery?g=" . $g['id'] . ">Show</a>";

    echo "\n";
  }

  if (Access('SysAdmin')) {
    echo "<tr><td class=NotStaff>Debug<td colspan=5 class=NotStaff><textarea id=Debug></textarea><p><span id=DebugPane></span>";
  }

  echo "</table></div>\n";
//  echo "<input type=submit name=Update value=Update>\n";
  echo "</form>";
  echo "<h2><a href=GallManage?ACTION=Add>Add a new Gallery</a></h2>";

  dotail();

?>
