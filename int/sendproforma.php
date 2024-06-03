<?php 
// Set fields in data
include_once("fest.php");
include_once("DanceLib.php");
include_once("Email.php");
global $PLANYEAR;

$id = $_REQUEST['I'];
$proforma = $_REQUEST['N'];

$Side = Get_Side($id);
$Sidey = Get_SideYear($id);
$subject = Feature('FestName') . " $PLANYEAR and " . $Side['SN'];
$To = $Side['Email'];
if (isset($_REQUEST['E']) && isset($Side[$_REQUEST['E']]) ) {
  $To = $Side[$_REQUEST['E']];
}
    $DanceEmailsFrom = Feature('DanceEmailsFrom','Dance');
    $too = [['to',$To,$Side['Contact']],
            ['from',$DanceEmailsFrom . '@' . Feature('HostURL'),Feature('ShortName') . ' ' . $DanceEmailsFrom],
            ['replyto',$DanceEmailsFrom . '@' . Feature('HostURL'),Feature('ShortName') . ' ' . $DanceEmailsFrom]];
//$to = $Side['Email']; // Temp value
echo Email_Proforma(EMAIL_DANCE,$id, $too,$proforma,$subject,'Dance_Email_Details',[$Side,$Sidey],'Dance');
