<?php
// Update an overlay for a performer

  include_once("fest.php");
  include_once("DanceLib.php");
  include_once("MusicLib.php"); 
  include_once("PLib.php");

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

  $Isa = $_REQUEST['pc'] ?? '';
  if (!$Isa) Error_Page("No Category to overlay");

  if (isset($_REQUEST['Action'])) {
    include_once("Uploading.php");
    $Action = $_REQUEST['Action'];
    switch ($Action) {
    case 'Photo':
      $Mess = Upload_Photo('Side',$Isa);
      break;
    }
    
  dostaffhead("Add/Change Overlay", ["/js/clipboard.min.js", "/js/Participants.js","js/dropzone.js","css/dropzone.css", "js/InviteThings.js"]);

  $Side = Get_Side($Snum);
  $Olay = Get_Overlay($Side, $Isa);
  
  echo "<form method=post action=SideOverlay>";
  if ($Olay) {
    Register_AutoUpdate('SideOverlay', $Olay['id']);
    $Oid = $Olay['id'];
  } else {
    $Oid = -1;
  }
  
  echo fm_hidden('id',$Oid); 
  echo "<table><tr>"
 "
   . "
  
  echo '<h2>Add/Edit Performer</h2>'; // TODO CHANGE
  global $Mess,$Action,$Dance_TimeFeilds,$ShowAvailOnly;
  $DateFlds = ['ReleaseDate'];
// var_dump($_POST);
// TODO Change this to not do changes at a distance and needing global things
  $Action = ''; 
  $Mess = '';
  if (isset($_REQUEST['Action'])) {
    include_once("Uploading.php");
    $Action = $_REQUEST['Action'];
    switch ($Action) {
    case 'PASpecUpload':
      $Mess = Upload_PASpec();
      break;
    case 'Insurance':
      $Mess = Upload_Insurance();
      break;
    case 'Photo':
      $Mess = Upload_Photo();
      break;
    case (preg_match('/DeleteOlap(\d*)/',$Action,$mtch)?true:false):
      // Delete Olap
      $snum=$_POST['SideId'];
      $olaps = Get_Overlaps_For($snum);
//      echo "<br>"; var_dump($olaps);
      if (isset($olaps[$mtch[1]])) {
        db_delete("Overlaps",$olaps[$mtch[1]]['id']);
      } 
      break;
    case 'TICKBOX':

      break; // Action is taken later after loading
      
    case 'Record as Non Performer' :
      $Side = Get_Side($snum);
      $Sidey = Get_SideYear($snum);
      if (!$Sidey) $Sidey = Default_SY($snum);
      $Side['NotPerformer'] = 1;
      $Sidey['NoEvents'] = 1;
      $Sidey['YearState'] = 2;
      if (empty($Sidey['FreePerf'])) $Sidey['FreePerf'] = 1;
      Put_Side($Side);
      Put_SideYear($Sidey);
      global $Save_Sides,$Save_SideYears;
      $Save_SideYears = $Save_Sides = []; // Clears Cached values

      $Side = Get_Side($snum); // Sets all the defaults
      $Sidey = Get_SideYear($snum);
// var_dump($Sidey);exit;
      echo "<h1>Setup as a non performer</h1>";
      $AllDone = 1;
      break;
      
    case 'Create as Non Performer' :
      $_POST['NotPerformer'] = 1;
      $_POST['NoEvents'] = 1;
      $_POST['YearState'] = 2;
      if (empty($_POST['FreePerf'])) $_POST['FreePerf'] = 1;
      
      $proc = 1;
      $Side = [];
      if (!isset($_POST['SN'])) {
        echo "<h2 class=ERR>NO NAME GIVEN</h2>\n";
        $proc = 0;
      }
      $_POST['AccessKey'] = rand_string(40);
      $_POST['SideId'] = $snum = Insert_db_post('Sides',$Side,$proc);
      if ($snum) Insert_db_post('SideYear',$Sidey,$proc);
      echo "<h1>Created as a non performer</h1>";
      $Side = Get_Side($snum);
      $Sidey = Get_SideYear($snum);

      $AllDone = 1;
      break; 


    case 'Send Generic Contract':
      SendProfEmail();
 //   'Dance_Final_Info',$snum,'FinalInfo','SendProfEmail')
    
    case 'Send Bespoke Contract':
    
    default:
      $Mess = "!!!";
    }
  }
//  echo "<!-- " . var_dump($_POST) . " -->\n";
  if ($AllDone) {
