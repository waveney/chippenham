<?php 
// Set fields in data
include_once("fest.php");
include_once("DanceLib.php");
include_once("Email.php");
global $FESTSYS,$PLANYEAR;

$id = $_GET['I'];
$proforma = $_GET['N'];

$Side = Get_Side($id);
$Sidey = Get_SideYear($id);
$subject = $FESTSYS['FestName'] . " $PLANYEAR and " . $Side['SN'];
$To = $Side['Email'];
if (isset($_REQUEST['E']) && isset($Side[$_REQUEST['E']]) ) {
  $To = $Side[$_REQUEST['E']];
}
//var_dump($_REQUEST);
$too = [['to',$To,$Side['Contact']],
        ['from','Dance@' . $FESTSYS['HostURL'],$FESTSYS['ShortName'] . ' Dance'],
        ['replyto','Dance@' . $FESTSYS['HostURL'],$FESTSYS['ShortName'] . ' Dance']];
//$to = $Side['Email']; // Temp value
echo Email_Proforma(1,$id, $too,$proforma,$subject,'Dance_Email_Details',[$Side,$Sidey],$logfile='Dance');

?>
