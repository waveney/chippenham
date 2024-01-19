<?php 
// Volunteer actions called from JS
include_once("fest.php");
include_once("VolLib.php");

$id = $_GET['id'];
$Action = $_GET['A'];
$Catid = $_GET['Catid'];



switch ($Action) {

case 'Accept1':
  VolAction($Action);

  exit;
  
default:
  echo "$Action unrecognised";
}
?>
