<?php
  include_once("fest.php");
  include_once("DanceLib.php");
  include_once("MusicLib.php"); // TODO Merge two libs
  include_once("DateTime.php");
  include_once("ProgLib.php");
  include_once("DispLib.php");
  include_once("PLib.php");
  include_once("Email.php");

// TODO change for all access types inc participant
  global $USER,$USERID,$Access_Type,$PerfTypes;
  // 2D Access check hard coded here -- if needed anywhere else move to fest
  if (isset($_REQUEST['SideId'])) { $snum = $_REQUEST['SideId']; }
  elseif (isset($_REQUEST['sidenum'])) { $snum = $_REQUEST['sidenum']; }
  elseif (isset($_REQUEST['id'])) { $snum = $_REQUEST['id'];} 
  elseif (isset($_REQUEST['i'])) { $snum = $_REQUEST['i'];} 
  else { $snum = 0; }
  
  if (!is_numeric($snum)) $snum=0;
  Set_User();

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

  dostaffhead("Add/Change Performer", ["/js/clipboard.min.js", "/js/emailclick.js", "/js/Participants.js","js/dropzone.js","css/dropzone.css", "js/InviteThings.js"]);
  global $YEAR,$PLANYEAR,$Mess,$BUTTON,$YEARDATA;  // TODO Take Mess local
  $ShowAvailOnly = 0;

  $AllDone = 0;
  
  echo '<h2>Add/Edit Performer</h2>'; // TODO CHANGE
  global $Mess,$Action,$Dance_TimeFeilds,$ShowAvailOnly;
  $DateFlds = ['ReleaseDate'];
// var_dump($_REQUEST);
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
      $snum=$_REQUEST['SideId'];
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
      $_REQUEST['NotPerformer'] = 1;
      $_REQUEST['NoEvents'] = 1;
      $_REQUEST['YearState'] = 2;
      if (empty($_REQUEST['FreePerf'])) $_REQUEST['FreePerf'] = 1;
      
      $proc = 1;
      $Side = [];
      if (!isset($_REQUEST['SN'])) {
        echo "<h2 class=ERR>NO NAME GIVEN</h2>\n";
        $proc = 0;
      }
      $_REQUEST['AccessKey'] = rand_string(40);
      $_REQUEST['SideId'] = $snum = Insert_db_post('Sides',$Side,$proc);
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
//  echo "<!-- " . var_dump($_REQUEST) . " -->\n";
  if ($AllDone) {
  } else if (isset($_REQUEST['SideId']) ) { // Response to update button 
    
    Clean_Email($_REQUEST['Email']);
    Clean_Email($_REQUEST['AltEmail']);
    Parse_TimeInputs($Dance_TimeFeilds);    
    Parse_DateInputs($DateFlds);
 
    $Sidey = Default_SY();
    if ($snum > 0) {         // existing Side 
      $Side = Get_Side($snum);
      if ($Side) {
        $Sideyrs = Get_Sideyears($snum);
        if (isset($Sideyrs[$YEAR])) $Sidey = $Sideyrs[$YEAR];
      } else {
        echo "<h2 class=ERR>Could not find Performer $snum</h2>\n";
      }

      if (isset($_REQUEST['InviteAct']) || isset($_REQUEST['ReminderAct'])) {

        if (strlen($_REQUEST['Invited'])) $_REQUEST['Invited'] .= ", ";
        $_REQUEST['Invited'] .= date('j/n');
      } elseif (isset($_REQUEST['NewAccessKey'])) {
        $_REQUEST['AccessKey'] = rand_string(40);
      } elseif (isset($_REQUEST['Contract'])) { 
        Contract_Save($Side,$Sidey,2); 
      } elseif (isset($_REQUEST['Contract2'])) { 
        Contract_Save($Side,$Sidey,2,1); 
      } elseif (isset($_REQUEST['Decline'])) { 
        Contract_Decline($Side,$Sidey,2); 
      } elseif (isset($_REQUEST['View'])) { 
        Show_Side($snum); 
        dotail();
      } elseif (isset($_REQUEST['Delete'])) { 
        db_delete('Sides', $snum);
        echo "<h2>Deleted</h2>";
        dotail();
      } elseif (isset($_REQUEST['ReIssue'])) { 
        
      }

      Update_db_post('Sides',$Side);
      if (isset($_REQUEST['Year']) && ($_REQUEST['Year'] >= $PLANYEAR)) {
//      var_dump($Sidey);
        if (isset($Sidey) && $Sidey && isset($Sidey['syId']) && $Sidey['syId']){
          Update_db_post('SideYear',$Sidey);
        } else {
          $Sidey['Year'] = $PLANYEAR;
          $syId = Insert_db_post('SideYear',$Sidey);
          $Sidey['syID'] = $syId;
        }
      }
//      UpdateBand($snum);
      Report_Log("Dance"); // TODO Dance needs to depend on IsAs
//      UpdateOverlaps($snum);
    } else { //New Side
      $proc = 1;
      $Side = array();
      if (!isset($_REQUEST['SN'])) {
        echo "<h2 class=ERR>NO NAME GIVEN</h2>\n";
        $proc = 0;
      }
      $_REQUEST['AccessKey'] = rand_string(40);
      $snum = Insert_db_post('Sides',$Side,$proc);
      if ($snum) Insert_db_post('SideYear',$Sidey,$proc);
    }
    UpdateBand($snum);
    UpdateOverlaps($snum);

  } elseif ($snum > 0) { //Link from elsewhere 
    $Side = Get_Side($snum);
    if ($Side) {
      $Sideyrs = Get_Sideyears($snum);
      if (isset($Sideyrs[$YEAR])) {
        $Sidey = $Sideyrs[$YEAR];
      } else {
        $Sidey = Default_SY($snum);
      }
      
      if (isset($_REQUEST['TICKBOX'])) {
        switch ($_REQUEST['TICKBOX']) {
        case 1: case 2: case 3: case 4:
          $Sidey["TickBox" . $_REQUEST['TICKBOX']] = 1;
          break;
          
        case 'Rec': 
          if (!isset($Sidey['Coming']) || !$Sidey['Coming'] ) $Sidey['Coming'] = 1;
          break;
          
        case 'DCMRec' :
          $Lasty = $Sideyrs[$YEARDATA['PrevFest']];
          $Lasty['TickBox3'] = 1;  // May not be used
          $Lasty['TickBox4'] = 2; 
          Put_SideYear($Lasty);
          $ShowAvailOnly = 1;
          $Sidey['TickBox3'] = 1;  // May not be used
          echo "<script>$(document).ready(function() {var elmnt = document.getElementById('Availability');elmnt.scrollIntoView(true);})</script>";
          break;  
          
        case (preg_match('/FCV(.)/',$_REQUEST['TICKBOX'],$mtch)?true:false):
          $Sidey['TickBox4'] = $mtch[1]+1;
          Put_SideYear($Lasty);
          // Swap to current info
          $Sidey = (isset($Sideyrs[$PLANYEAR])?$Sideyrs[$PLANYEAR]:Default_SY($snum));
          $ShowAvailOnly = 1;
//          echo "<script>$(document).ready(function() {var elmnt = document.getElementById('Availability');elmnt.scrollIntoView(true);})</script>";
          break;  
               
          
        default:
          echo "<h2>Unrecognised Button</h2>";
          
        }
        Put_SideYear($Sidey);
        echo "<h2>Thankyou for recording that, your other records are below</h2>";
      }
      
    } else {
      Error_Page("Could not find Performer $snum");
    }
  } else {
    $Sidey = Default_SY();
    $Side = ['SideId'=>$snum]; 
  }

  Show_Part($Side,'Side',Access('Staff'),'AddPerf');
  Show_Perf_Year($snum,$Sidey,$YEAR,Access('Staff'));

  if ($snum > 0) {
    if (Access('SysAdmin')) {
      echo "<div class=floatright>";
      echo "<h2><a href=ShowPerf?id=$snum>Public View</a>";
      echo "<input type=Submit id=smallsubmit name='NewAccessKey' class=Button$BUTTON value='New Access Key'>";
      echo "<input type=Submit id=smallsubmit name='Contract2' class=Button$BUTTON value='Confirm Contract'>";
      echo "<input type=Submit id=smallsubmit name='Delete' class=Button$BUTTON value='Delete'>";
      echo "</h2></div>\n";
    }
    if (Access('SysAdmin')) {
      echo "<Center><input type=Submit name='Update' value='Save Changes' class=Button$BUTTON> - " .
         "(All normal changes are recorded as you type - SysAdmin ONLY)\n";
    }
    if (Access('Staff','Dance')) {
      if (!isset($Sidey['Coming']) || $Sidey['Coming'] == 0) {
        if (!isset($Sidey['Invited']) || $Sidey['Invited'] == '') {
//          echo " <input type=submit name=InviteAct value=Invite  class=Button$BUTTON > ";
        } else {
//          echo " <input type=submit name=ReminderAct value=Reminder class=Button$BUTTON > ";
        }
      }
    }

    if ( Access('Staff') && $capmatch) {
      $E = (($Side['HasAgent'] && !$Side['BookDirect'] )?"'Agent'":'');
      echo "<div class=ContractShow hidden>";
      if ($Book_States[$Sidey['YearState']] == 'Contract Ready') {
        echo "<button type=button id=GContract$snum class=ProfButton onclick=MProformaSend('Music_Contract',$snum,'Contract','sendMproforma.php',1,$E)" . 
                     Music_Proforma_Background('Contract') . ">Email Generic Contract</button>"; 
        echo "<button type=button id=BContract$snum class=ProfButton onclick=MProformaSend('Music_Contract',$snum,'Contract','SendPerfEmail.php',2,$E)" . 
                     Music_Proforma_Background('Contract') . ">Email Bespoke Contract</button>"; 
      } elseif ($Book_States[$Sidey['YearState']] == 'Contract Sent') {
        echo "<button type=button id=GContract$snum class=ProfButton onclick=MProformaSend('Music_Contract',$snum,'Contract','sendMproforma.php',1,$E)" . 
                     Music_Proforma_Background('Contract') . ">Resend Generic Contract</button>"; 
        echo "<button type=button id=BContract$snum class=ProfButton onclick=MProformaSend('Music_Contract',$snum,'Contract','SendPerfEmail.php',2,$E)" . 
                     Music_Proforma_Background('Contract') . ">Resend Bespoke Contract</button>"; 
      } elseif ($Book_States[$Sidey['YearState']] == 'None') {
        echo "<input type=Submit name='Action' value='Record as Non Performer' class=Button$BUTTON >\n";
      }
//      echo "<input type=Submit id=smallsubmit name=ACTION class=Button$BUTTON value='Send Generic Contract'>";
//      echo "<input type=Submit id=smallsubmit name=ACTION class=Button$BUTTON value='Send Bespoke Contract'>";  
      echo "</div>";
    } else {
//      var_dump( $Book_States[$Sidey['YearState']] , $capmatch);
    }

    echo "</center>\n";
  } else { 
    echo "<Center><input type=Submit name=Create value='Create' class=Button$BUTTON >\n";
    echo "<input type=Submit name='Action' value='Create as Non Performer' class=Button$BUTTON >\n";
    echo "</center>\n";
  }
  echo "</form>\n";

  echo Show_Prog('Side',$snum,1);
  echo Extended_Prog('Side',$snum,1);

  dotail();
?>
