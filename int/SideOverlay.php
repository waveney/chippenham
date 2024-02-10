<?php
// Update an overlay for a performer

  include_once("fest.php");
  include_once("DanceLib.php");
  include_once("MusicLib.php"); 
  include_once("PLib.php");
  include_once("SideOverLib.php");

// TODO change for all access types inc participant
  global $USER,$USERID,$Access_Type,$PerfTypes;
  // 2D Access check hard coded here -- if needed anywhere else move to fest
  $snum = $_REQUEST['SideId'] ?? $_REQUEST['sidenum'] ?? $_REQUEST['id'] ?? $_REQUEST['i'] ?? 0;
  
  if (!is_numeric($snum)) $snum=0;
  Set_User();

  if ($snum == 0) Error_Page("No Performer selected");

  if (!isset($USER['AccessLevel'])) Error_Page("Not accessable to you - Please use the corect link");
  
  switch ($USER['AccessLevel']) {
  case $Access_Type['Participant'] : 
    if (($USER['Subtype'] == 'Perf' || $USER['Subtype'] == 'Side') && ($snum == $USERID)) break;
    Error_Page("Not accessable to you");
    break;

  case $Access_Type['Upload'] :
  case $Access_Type['Steward'] :
    Error_Page("Not accessable to you");

  case $Access_Type['Staff'] :
  case $Access_Type['Committee'] :
    $capmatch = 0;
    $Side = Get_Side($snum);
    foreach ($PerfTypes as $p=>$d) if ($Side[$d[0]] && Is_SubType($d[2])) $capmatch = 1;
    if (!$capmatch) fm_addall('disabled readonly');
    break;

  case $Access_Type['Internal'] : 
  case $Access_Type['SysAdmin'] : 
    $capmatch = 1;
    break;
  }  

  $Mode = Access('Staff');
  $Isa = '';
  $pc = $_REQUEST['pc'] ?? '';
  
//  var_dump($_REQUEST);
  
  if (empty($pc)) Error_Page("No Category to overlay");
  foreach ($PerfTypes as $p=>$d) {
    if ($pc == $d[2]) {
      $Isa = $d[2];
      break;
    }
  }
  
  if (empty($Isa))  Error_Page("Unknown Category");

  if (isset($_REQUEST['Action'])) {
    include_once("Uploading.php");
    $Action = $_REQUEST['Action'];
    switch ($Action) {
    case 'Photo':
      $Mess = Upload_Photo('Side',$Isa);
      break;
    }
  }
  
  dostaffhead("Add/Change Overlay", ["/js/clipboard.min.js", "/js/Participants.js","js/dropzone.js","css/dropzone.css", "js/InviteThings.js"]);

  $Side = Get_Side($snum);
  $Olay = Get_Overlay($Side, $Isa);
  
  echo "<form method=post action=SideOverlay>";
  if ($Olay) {
    $Oid = $Olay['id'];
  } else {
    $Olay = ['SideId'=>$snum, 'IsType'=>$Isa];
    $Oid = Gen_Put('SideOverlays',$Olay);
  }
  
  Register_AutoUpdate('SideOverlays', $Oid);

  echo fm_hidden('id',$Oid); 
  echo "<table border>";
  echo "<tr><td>Performer:<td colspan=2><a href=AddPerf?id=$snum>" . $Side['SN'] . "</a>" . "<td>Type:" . ($Side['Type'] ?? '');

  echo "<tr><td>Short Blurb:<td colspan=6>" . $Side['Description'];
  echo "<tr><td>Blurb:<td colspan=6>" . $Side['Description'];
  echo "<tr><td>Photo:<td>" . $Side['Photo'] . "<td><img src=" . ($Side['Photo']??'') . " height=100>";
  echo "<tr><td>Website:<td>" . $Side['Website'];
  echo     "<td>Video:<td>" . $Side['Video'];
  echo "<tr><td>Facebook:<td>" . $Side['Facebook'];
  echo     "<td>Twitter / X:<td>" . $Side['Twitter'];
  echo     "<td>Instagram:<td>" . $Side['Instagram'];
  echo "<tr><td>Performer Types:<td>";
    foreach ($PerfTypes as $t=>$p) {
      if (Capability("Enable" . $p[2]) && $Side[$p[0]]) echo $t . ", ";
  }
  echo "<tr><td colspan=6><h2>Overlays for $Isa</h2>Only fill in those items that need different values from the main performer data";
  
  echo "<tr>" . fm_text('Name',$Olay,'SN',2);
  echo "<tr>" . fm_textarea('Short Blurb <span id=DescSize></span>',$Olay,'Description',5,1,
                        "maxlength=200 oninput=SetDSize('DescSize',200,'Description')"); 
  echo "<tr>" . fm_textarea('Blurb for web',$Olay,'Blurb',5,2,'','size=2000' ) . "\n";
  echo "<tr>";
      if (isset($Olay['Website']) && strlen($Olay['Website'])>1) {
        echo fm_text(weblink(trim($Olay['Website'])),$Olay,'Website');
      } else {
        echo fm_text('Website',$Olay,'Website');
      }
      
  echo "<td>Recent Photo" . fm_DragonDrop(1, 'Photo', "Overlay:$Isa", $Oid, $Olay, $Mode);
  echo "<tr>";
    if (isset($Olay['Video']) && $Olay['Video'] != '') {
      echo fm_text("<a href=" . videolink($Olay['Video']) . ">Recent Video</a>",$Olay,'Video',1);
    } else {
      echo fm_text('Recent Video',$Olay,'Video',1);
    }
    if (Access('SysAdmin')) echo fm_text1('Photo Link',$Olay,'Photo',1,'class=NotSide','class=NotSide');

  echo "<tr>" . fm_text(Social_Link($Olay,'Facebook' ),$Olay,'Facebook');
      echo fm_text(Social_Link($Olay,'Twitter'  ),$Olay,'Twitter');
      echo fm_text(Social_Link($Olay,'Instagram'),$Olay,'Instagram');
  if (Access('SysAdmin')) echo "<tr><td class=NotSide>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea><p><span id=DebugPane></span>";

  echo "</table>";
  
  echo "<h2><a href=AddPerf?id=$snum> Back to " . $Side['SN'] . " Main Page</a></h2>";
 
  dotail();