<?php 
// Set fields in data
include_once("fest.php");
include_once("VolLib.php");
include_once("Email.php");
global $PLANYEAR;

$id = $_GET['I'];
$proforma = $_GET['N'];
$Code = $_GET['C'];

$Vol = Get_Volunteer($id);
$subject = Feature('FestName') . " $PLANYEAR and " . $Vol['SN'];
$To = $Vol['Email'];
//var_dump($Vol);
    $too = [['to',$To,$Vol['SN']],
            ['from','Stewards' . '@' . Feature('HostURL'),Feature('ShortName') ],
            ['replyto','Stewards' . '@' . Feature('HostURL'),Feature('ShortName') ] ];
//$to = $Side['Email']; // Temp value
//var_dump($_REQUEST);
echo Email_Proforma(1,$id, $too,$proforma,$subject,'Vol_Details',$Vol,'Volunteers');
