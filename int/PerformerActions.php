<?php
  include_once("fest.php");
  A_Check('Steward');

  dostaffhead("Performer Actions", ["/js/clipboard.min.js", "/js/emailclick.js", "/js/InviteThings.js"]);

  global $YEAR,$PLANYEAR,$Book_Colours,$Book_States,$Book_Actions,$Book_ActionExtras,$Importance,$InsuranceStates,$PerfTypes,$Cancel_Colours,$Cancel_States,$Book_ActionColours;
  include_once("DanceLib.php"); 
  include_once("MusicLib.php"); 
  include_once("Email.php"); 


  $Type = (isset($_GET['T'])? $_GET['T'] : 'M' );
  if ($Type == 'Z') {
    $TypeSel = " IsASide=0 AND IsAnAct=0 AND IsFunny=0 AND IsFamily=0 AND IsCeilidh=0 AND IsOther=0 ";
    $Perf = "Uncategorised performers";
    $DiffFld = "Importance";    
  }  else {
    $Perf = ""; 
    foreach ($PerfTypes as $p=>$d) if ($d[4] == $Type) { $Perf = $p; $PerfD = $d; };

    $TypeSel = $PerfD[0] . "=1 ";
    $DiffFld = $PerfD[2] . "Importance";
  }

  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
     
    case 'Contract':
    
    
    case 'Confirm':
    
    
    case 'Decline':
    
    case 'Book':
    
    case 'Cancel':
    
    }
  } 
