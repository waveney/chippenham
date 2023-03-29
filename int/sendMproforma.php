<?php 
// Set fields in data
include_once("fest.php");
include_once("DanceLib.php");
include_once("MusicLib.php");
include_once("Email.php");
global $FESTSYS,$PLANYEAR,$USER,$USERID,$PerfTypes;
// Send an Email Proforma for a performer other than dance
$id = $_REQUEST['I'];
$proforma = $_REQUEST['N'];

$Side = Get_Side($id);
$Sidey = Get_SideYear($id);
$From = '';

$IsAC = 0;
  set_user();

foreach ($PerfTypes as $t=>$p) {
  if ($Side[$p[0]]) {
    $IsAC++;
    $EmailsFrom = Feature($p[1] . 'EmailsFrom','');
    if ($IsAC > 1) break;
    if ($EmailsFrom == 'USER') { $IsAC+=2; break; }
    if (!empty($EmailsFrom)) $From = $EmailsFrom;
  }
}

if (empty($Sidey['BookedBy'])) {
//var_dump($USER);
  $From = $USER['FestEmail'];
} else if ($IsAC > 1 || empty($From)) {
  if ($USERID == $Sidey['BookedBy']) {
    if (!empty($USER['FestEmail'])) $From = $USER['FestEmail'];
  } else {
    $User = Gen_Get('FestUsers',$Sidey['BookedBy'],'UserId');
//var_dump($User);
    $From = $User['FestEmail'];
  }
}
  

$subject = $FESTSYS['FestName'] . " $PLANYEAR and " . $Side['SN'];
$To = $Side['Email'];
if (isset($_REQUEST['E']) && isset($Side[$_REQUEST['E']]) ) {
  $To = $Side[$_REQUEST['E']];
}

    $too = [['to',$To,$Side['Contact']],
            ['from',$From . '@' . $FESTSYS['HostURL'],$FESTSYS['ShortName'] . ' ' . $From],
            ['replyto',$From . '@' . $FESTSYS['HostURL'],$FESTSYS['ShortName'] . ' ' . $From]];

  Email_Proforma(1,$id, $too,$proforma,$subject,'Dance_Email_Details',[$Side,$Sidey],$logfile='Perf');
  Dance_Email_Details_Callback($proforma,[$Side,$Sidey]);

  $prefix = '';

    if ($proforma) $prefix .= "<span " . Music_Proforma_Background($proforma) . ">$proforma:";
    $prefix .= date('j/n/y');
    if ($proforma) $prefix .= "</span>";
    if (strlen($Sidey['Invited'])) {
      $Sidey['Invited'] = $prefix . ", " . $Sidey['Invited'];
    } else {
      $Sidey['Invited'] = $prefix;  
    }
    Put_SideYear($Sidey);
//echo $Res;
?>
