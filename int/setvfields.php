<?php 
// Set fields in data
include_once("fest.php");
include_once("VolLib.php");

$id = $_GET['I'];
$Opt = $_GET['O'];

//echo "In Setfields";
//var_dump($_GET);

switch ($Opt) {

case 'VC':
  Set_User();
  global $USER,$USERID;
  $VY = Get_Vol_Year($id);
  if (empty($VY['TicketsCollected'])) {
    $VY['TicketsCollected'] = time();
    $VY['CollectedBy'] = $USERID;
    Put_Vol_Year($VY);
    echo "Collected " . date("D M j G:i:s",$VY['TicketsCollected']) . " from " . ($USER['SN'] ?? 'Unknown') .
         " <button id=Oops$id type=button onclick=VTicketsCollected($id,0)>Oops - undo that</button>";
  } else { // error message to be presented
    $User = Get_User($Sidey['CollectedBy']);
    echo "<span class=Err>ERROR - already Collected " . date("D M j G:i:s",$VY['TicketsCollected']) . " from " . ($User['SN'] ?? 'Unknown') . "</span>";
  }
  exit;
    
case 'VNC':
  $VY = Get_Vol_Year($id);
  $VY['TicketsCollected'] = 0;
    Put_Vol_Year($VY);
  echo "<button type=button class=FakeButton onclick='VTicketsCollected($id)'>Collect</button>";
  exit;

default:
}
?>
