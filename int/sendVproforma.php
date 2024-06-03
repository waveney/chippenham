<?php 
// Set fields in data
include_once("fest.php");
include_once("VolLib.php");
include_once("Email.php");
global $PLANYEAR;

$id = $_REQUEST['I'];
$proforma = $_REQUEST['N'];
$Code = $_REQUEST['C'];
$atts = [];

$Vol = Get_Volunteer($id);
$subject = Feature('FestName') . " $PLANYEAR and " . $Vol['SN'];
$To = $Vol['Email'];
//var_dump($Vol);
    $too = [['to',$To,$Vol['SN']],
            ['from','Stewards' . '@' . Feature('HostURL'),Feature('ShortName') ],
            ['replyto','Stewards' . '@' . Feature('HostURL'),Feature('ShortName') ] ];
//$to = $Side['Email']; // Temp value
//var_dump($_REQUEST);
echo Email_Proforma(EMAIL_VOL,$id, $too,$proforma,$subject,'Vol_Details',$Vol,'Volunteers',$atts);
