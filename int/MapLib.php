<?php
// Various things to do with maps

function Get_Map_Point_Types() {
  global $db;
  $res = $db->query("SELECT * FROM MapPointTypes ORDER BY id");
  if ($res) while ($mpt = $res->fetch_assoc()) $full[] = $mpt;
  return $full;
}

function Get_Map_Point_Type($mid) {
  global $db;
  $res = $db->query("SELECT * FROM MapPointTypes WHERE id=$mid");
  if ($res) $ans = $res->fetch_assoc();
  return $ans;
}

function Put_Map_Point_Type(&$now) {
  $Cur = Get_Map_Point_Type($now['id']);
  Update_db('MapPointTypes',$Cur,$now);
}

function Get_Map_Points() {
  global $db;
  $res = $db->query("SELECT * FROM MapPoints ORDER BY id");
  if ($res) while ($mpt = $res->fetch_assoc()) $full[] = $mpt;
  return $full;
}

function Get_Map_Point($mid) {
  global $db;
  $res = $db->query("SELECT * FROM MapPoints WHERE id=$mid");
  if ($res) $ans = $res->fetch_assoc();
  return $ans;
}

function Put_Map_Point(&$now) {
  $Cur = Get_Map_Point($now['id']);
  Update_db('MapPoints',$Cur,$now);
}


// Call this after any mappoint or venue update
function Update_MapPoints() {
  global $db,$PLANYEAR;

  $types = Get_Map_Point_Types();
  file_put_contents("cache/mapptypes.json",json_encode($types)); 

  $data = array();
  $res = $db->query("SELECT * FROM Venues WHERE Status=0 AND Lat!='' AND MapImp>=0 ");// Normal Code
  if ($res) while($ven = $res->fetch_assoc()) {
    $usage = (($ven['Dance']?'D':'_').
              ($ven['Music']?'M':'_').
              ($ven['Child']?'F':'_').
              ($ven['Ceilidh']?'H':'_').
              ($ven['Craft']?'C':'_').
              ($ven['Other']?'O':'_').
              ($ven['Comedy']?'Y':'_'));
    if ($usage == '________' || $usage == '______O_' ) {
      $used4 = 'Many things';
    } else {
      $lst = [];
      if ($ven['Dance']) $lst[] = 'Dance';
      if ($ven['Music']) $lst[] = 'Music';    
      if ($ven['Comedy']) $lst[] = 'Comedy';    
      if ($ven['Child']) $lst[] = 'Family';
      if ($ven['Ceilidh']) $lst[] = 'Ceilidh';
      if ($ven['Craft']) $lst[] = 'Craft';    
      if ($ven['Other']) $lst[] = 'Other things';
      $used4 = FormatList($lst);  
    }
    $data[] = array('id'=>$ven['VenueId'], 'name'=>$ven['SN'], 'lat'=>$ven['Lat'], 'long'=>$ven['Lng'],
        'imp'=>$ven['MapImp'],'icon'=>$ven['IconType'],'atxt'=>0,'desc'=>$ven['Description'],
        'usage'=>$usage,
        'used4'=>$used4,
        'image'=>$ven['Image'],'extra'=>$ven['DirectionsExtra']);
  }

  $res = $db->query("SELECT * FROM MapPoints WHERE InUse=0");
  if ($res) while($mp = $res->fetch_assoc()) {
    $data[] = array('id'=>(1000000+$mp['id']), 'name'=>$mp['SN'], 'lat'=>$mp['Lat'], 'long'=>$mp['Lng'],
        'imp'=>$mp['MapImp'],'icon'=>$mp['Type'],'atxt'=>$mp['AddText'],'direct'=>$mp['Directions'],'link'=>$mp['Link']);
  }

  $res = Gen_Get_Cond('FoodAndDrink',"Year=$PLANYEAR");
  foreach ($res as $mp) {
    $data[] = array('id'=>(2000000+$mp['id']), 'name'=>$mp['SN'], 'lat'=>$mp['Lat'], 'long'=>$mp['Lng'],
        'imp'=>$mp['MapImp'],'icon'=>$mp['Type'],'atxt'=>$mp['Description'],'direct'=>1,'link'=>$mp['Website']);
  }


  return file_put_contents("cache/mappoints.json",json_encode($data));

}

// Features 1= normal, 0 none, 2 no venues, 3=Dance only, 4 = CarParks, 5 = Music only, 6=Craft, 7=Family, 8=Comedy, 9=Campsites
// 10=Ceilidh, 11=Food&Drink (Not yet)

function Init_Map($CentType,$Centerid,$Zoom,$Features=1) { // CentType 0=Venue, 1=Mappoint, -1= Festival home
  global $FESTSYS;  
  $DirLat  = Feature('DefaultDirLat');
  if ($DirLat == 0) $DirLat = Feature('MapLat');
  $DirLong  = Feature('DefaultDirLong');
  if ($DirLong == 0) $DirLong = Feature('MapLong');
  if ($CentType > 0) {
    $mp = Get_Map_Point($Centerid);
    $Lat = $mp['Lat'];
    $Long = $mp['Lng'];
  } else if ($CentType == 0) {
    $ven = Get_Venue($Centerid);
    $Lat = $ven['Lat'];
    $Long = $ven['Lng'];
  } else {
    $Lat = Feature('MapLat',0);
    $Long = Feature('MapLong',0);
  }

  $V = $FESTSYS['V'];
  echo fm_hidden('MapLat',$Lat) . fm_hidden('MapLong',$Long) . fm_hidden('MapZoom',$Zoom) . fm_hidden('MapFeat',$Features) .
       fm_hidden('DirLat',$DirLat) . fm_hidden('DirLong',$DirLong) . fm_hidden('MapDataDate',filemtime("cache/mappoints.json"));
       
  echo "<script src='https://maps.googleapis.com/maps/api/js?key=" . $FESTSYS['GoogleAPI'] . "' ></script>";
  echo "<script src=/js/maplabel.js?V=$V ></script>";
  echo "<script src=/js/Mapping.js?V=$V ></script>";
}

function Show_Map_Point($V) {
  echo "<div id=VenueMap><div id=MapWrap>";
  echo "<div id=DirPaneWrap><div id=DirPane><div id=DirPaneTop></div><div id=Directions></div></div></div>";
  echo "<p><div id=map style='min-height:300px; max-height:400px'></div></div><p>";
  echo "<button class=PurpButton onclick=ShowDirect($V)>Directions</button> (From the " . Feature('DirectionDefault','Square') . " if it does not know your location)\n";
  echo "</div><script>Register_Onload(Set_MinHeight,'.venueimg','.MainContent')</script>\n";
  Init_Map(1,$V,18);
  echo "</div></div>"; 
}

?>
