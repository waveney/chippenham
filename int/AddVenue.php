<?php
  include_once("fest.php");
  A_Check('Committee','Venues');

  dostaffhead("Add/Change Venue");
  include_once("ProgLib.php");
  include_once("MapLib.php");
  include_once("DispLib.php");
  include_once("DanceLib.php");

  global $YEAR,$Venue_Status,$Surfaces;

  Set_Venue_Help();

  echo "<div class='content'><h2>Add/Edit Venues</h2>\n";
  echo "<form method=post action='AddVenue'>\n";
  if (isset($_REQUEST['VenueId'])) { /* Response to update button */
    $vid = $_REQUEST['VenueId'];
    if ($vid > 0) {                                 // existing Venue
      $Venue = Get_Venue($vid);
      Update_db_post('Venues',$Venue);
    } else { /* New */
      $proc = 1;
      if (empty($_REQUEST['SN'])) {
        echo "<h2 class=ERR>NO NAME GIVEN</h2>\n";
        $proc = 0;
      }
      $_REQUEST['AccessKey'] = rand_string(40);
      $vid = Insert_db_post('Venues',$Venue,$proc);
    }
    Update_MapPoints();
  } elseif (isset($_REQUEST['v'])) {
    $vid = $_REQUEST['v'];
    $Venue = Get_Venue($vid);
  } elseif (isset($_REQUEST['Copy'])) {
    $cvid = $_REQUEST['Copy'];
    $Venue = Get_Venue($cvid);
    $vid = -1;
  } elseif (isset($_REQUEST['Delete'])) {
    $cvid = $_REQUEST['Delete'];
    db_delete("Venues",$cvid);
    echo "Venue Deleted.";
    dotail();
  } elseif (isset($_REQUEST['NEWACCESS'])) {
    $Vens = Get_Venues(1);
    if ($Vens) foreach ($Vens as $Ven) {
      $Ven['AccessKey'] = rand_string(40);
      Put_Venue($Ven);
    }
    echo "All Access Keys Now changed";
    dotail();

  } else {
    $Venue = array();
    $vid = -1;
  }

  if ($vid > 0) Register_Autoupdate('Venues',$vid);
  $RealSites = Get_Real_Venues(0);
  $VirtSites = Get_Virtual_Venues();

  echo "<div class=tablecont  style='width:70%;float:left;'><table border>\n";
    if (isset($vid) && $vid > 0) {
      echo "<tr><td>Venue Id:<td>$vid" . fm_hidden('VenueId',$vid);
      $VenY = Gen_Get_Cond1('VenueYear',"Year=$YEAR AND VenueId=$vid");
      SponsoredByWho($VenY,$Venue['SN'],1,$vid);
    } else {
      echo fm_hidden('VenueId',-1);
    }
    echo "<tr>" . fm_text('Short Name', $Venue,'ShortName');
    if (isset($vid) && $vid > 0 && isset($VenY['QRCount'])) echo "<td>QR Count:<td>" . $VenY['QRCount'];
    echo "<tr>" . fm_text('Name',$Venue,'SN',3);
    echo "<tr>" . fm_text('Address',$Venue,'Address',3);
    echo          fm_text('Post Code',$Venue,'PostCode',1);
    echo "<tr>" . fm_textarea('Description',$Venue,'Description',5,2);
    echo "<tr>" . fm_textarea('Directions Extra',$Venue,'DirectionsExtra',5,2);
    echo "<tr>" . fm_text('Lat',$Venue,'Lat',1);
    echo          fm_text('Long',$Venue,'Lng',1);
    echo          fm_text('MapImp',$Venue,'MapImp',1);
    echo "<tr>" . fm_text('Image',$Venue,'Image',1);
    echo          fm_text('Caption',$Venue,'Caption',3);
    echo "<tr>" . fm_text('Image2',$Venue,'Image2',1);
    echo          fm_text('Caption2',$Venue,'Caption2',3);
    echo "<tr>" . fm_text('Website',$Venue,'Website',1);
    echo     "<td>" . fm_checkbox('Supress Free',$Venue,'SupressFree');
    echo          fm_text('Banner',$Venue,'Banner',1);
    echo "<tr><td>" . fm_checkbox('Bar',$Venue,'Bar') . "<td>" . fm_checkbox('Food',$Venue,'Food') . fm_text('Food/Bar text',$Venue,'BarFoodText') . "\n";
    echo "<td>" . fm_checkbox("Parking",$Venue,'Parking');

    echo "<tr>" . fm_text('Notes',$Venue,'Notes',3);
    echo "<td colspan=2>Do NOT use if:" . fm_select($RealSites,$Venue,'DontUseIf',1) . " In use";
    echo "<tr><td>Status<td>" . fm_select($Venue_Status,$Venue,'Status');
    echo "<td>" . fm_checkbox('Dance Setup Overlap',$Venue,'SetupOverlap');
    echo "<td>" . fm_checkbox('Is Virtual',$Venue,'IsVirtual');
    echo "<td colspan=2>Part of:" . fm_select($VirtSites,$Venue,'PartVirt',1) . fm_checkbox('Suppress Parent',$Venue,'SuppressParent');
    echo "<tr><td>Venue For:<td colspan=3>" . fm_checkbox('Dance',$Venue,'Dance');
    echo fm_checkbox('Music',$Venue,'Music');
    echo fm_checkbox('Comedy',$Venue,'Comedy');
    echo fm_checkbox('Children',$Venue,'Child');
    echo fm_checkbox('Craft',$Venue,'Craft');
    echo fm_checkbox('Other',$Venue,'Other');
    echo "<td colspan=2>" . fm_checkbox('Ignore Multiple Use Warning',$Venue,'AllowMult');

    echo "<tr><td>" . fm_simpletext("Dance Importance",$Venue,'DanceImportance','size=4');
    echo "<td>" . fm_simpletext("Music Importance",$Venue,'MusicImportance','size=4');
    echo "<td>" . fm_simpletext("Other Importance",$Venue,'OtherImportance','size=4');
    echo "<tr><td colspan=2>Treat as Minor for Dance on:" . help('Minor') . "<td>" . fm_checkbox('Sat',$Venue,'MinorFri') .
         "<td>" . fm_checkbox('Sat',$Venue,'MinorSat') . "<td>" . fm_checkbox('Sun',$Venue,'MinorSun') .
         "<td>" . fm_checkbox('Mon',$Venue,'MinorMon') . "<td>" . fm_checkbox('Dance off Grid on Paper',$Venue,'DanceOffGridPaper');
    echo "<tr><td>Surfaces:<td>" . fm_select($Surfaces,$Venue,'SurfaceType1',0);
    echo "<td>" . fm_select($Surfaces,$Venue,'SurfaceType2',0) . "\n";
    echo "<tr>" . fm_text('Dance Rider',$Venue,'DanceRider',5);
    echo "<tr>" . fm_text('Music Rider',$Venue,'MusicRider',5);
    echo "<tr>" . fm_text('Other Rider',$Venue,'OtherRider',5);
    echo "<tr>" . fm_text('Disability Statement',$Venue,'DisabilityStat',5);
    echo "<tr>" . fm_text('What Three Words',$Venue,'3Words',5);
    if (isset($Venue['AccessKey']) && Access('SysAdmin')) echo "<tr><td>Access Key:<td colspan=5>" . $Venue['AccessKey'];
    echo "</table></div>\n";
  if (isset($Venue['Image']) && $Venue['Image']) {
    echo "<div style='width:25%;float:left;'><img src=" . $Venue['Image'] . " width=400><br>";
    if (!empty($Venue['Caption'])) echo $Venue['Caption'] . "<br>";
    if (isset($Venue['Image2']) && ($Venue['Image2'])) {
      echo "<img src=" . $Venue['Image2'] . " width=400><br>";
      if (!empty($Venue['Caption2'])) echo $Venue['Caption2'] . "<br>";
    }
    if (!empty($Venue['Banner']) ) {
      echo "<img src=" . $Venue['Banner'] . " width=400><br>Banner Image<br>";
    }
    echo "</div>";
  }


  echo "<br clear=all><div >";

  if ($vid > 0) {
    echo "<Center><input type=Submit name='Update' value='Update'>\n";
    echo "</center>\n";
  } else {
    echo "<Center><input type=Submit name=Create value='Create'></center>\n";
  }
  echo "</form>\n";
  echo "<h2><a href=VenueList>List Venues</a> , \n";
    echo "<a href=AddVenue>Add Another Venue</a>, \n";
    echo "<a href=AddVenue?Copy=$vid>Copy To Another Venue</a>, \n";
    if (Access('SysAdmin')) echo "<a href=AddVenue?Delete=$vid>Delete Venue</a>, \n";
    echo "<a href=VenueShow?v=$vid&Mode=1>Show Venue</a></h2>";

  echo "</div>";
  dotail();
?>

