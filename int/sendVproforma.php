<?php 
// Set fields in data
include_once("fest.php");
include_once("VolLib.php");
include_once("Email.php");
global $FESTSYS,$PLANYEAR;

$id = $_GET['I'];
$proforma = $_GET['N'];

$Vol = Get_Volunteer($id);
$subject = $FESTSYS['FestName'] . " $PLANYEAR and " . $Vol['SN'];
$To = $Vol['Email'];
    $too = [['to',$To,$Vol['SN']],
            ['from','Stewards' . '@' . $FESTSYS['HostURL'],$FESTSYS['ShortName'] ],
            ['replyto','Stewards' . '@' . $FESTSYS['HostURL'],$FESTSYS['ShortName'] ] ];
//$to = $Side['Email']; // Temp value
echo Email_Proforma(1,$id, $too,$proforma,$subject,'Get_Vol_Details',$Vol,'Volunteers');

?>
