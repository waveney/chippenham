<?php
  include_once("int/fest.php");
  include_once("int/DateTime.php");
  include_once("int/MapLib.php");
  dohead("Camping",[],'images/icons/CampingBanner.png');
  global $YEARDATA;

  echo TnC('CampGen') . "<p>\n";

  $Camps = Gen_Get_All('Campsites'," ORDER BY Importance DESC ");
  $Blobs = 0;
  foreach ($Camps as $C) if ($C['Props'] == 1) $Blobs++;

  echo "<div class=TwoCols><script>Register_Onload(Set_ColBlobs,'Blob',$Blobs)</script>";
  echo "<div class=OneCol id=TwoCols1>";

  $Blobnum = 0;
  $Mapp = 0;
  
  foreach ($Camps as $C) {
    if ($C['Props'] != 1) continue; // Remove not in use and restricted
    echo "<div id=Blob$Blobnum>";
    echo "<h2>" . $C['Name'] . "</h2>";
    if ($C['Image']) echo "<img src=" . $C['Image'] . "</img><br>";
    echo $C['ShortDesc'] . "<p>";
    if (!empty($C['LongDesc'])) echo $C['LongDesc'] . "<p>";
    if ($Mapp == 0) $Mapp = $C['MapPoint'];
//    echo "<button onclick=ShowDirect(" . (1000000 + $T['id']) . ")>Directions</button>\n";
    echo "</div>";
    $Blobnum++;
    
  }

  echo "</div><div class=OneCol id=TwoCols2></div></div>";
   echo "</div>";   
//  echo "<div id=Blob$Blobnum><div id=BlobMap>";
    echo "<div id=MapWrap><div id=DirPaneWrap><div id=DirPane><div id=DirPaneTop></div><div id=Directions></div></div>";
    echo "</div><div id=map  style='min-height:600px; max-height:600px'></div></div>";
  if ($Mapp) {
    Init_Map(1,$Mapp,15,9);
  } else {
    Init_Map(-1,4,15,9);  
  }

  echo "</div>"; 

  dotail();
?>
