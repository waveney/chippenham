<?php 
// Volunteer actions called from JS
include_once("fest.php");
include_once("VolLib.php");

$id = $_REQUEST['id'];
$Action = $_REQUEST['A'];
$Catid = $_REQUEST['Catid'];



switch ($Action) {

case 'Accept1':
  VolAction($Action,1);

  exit;
  
default:
  echo "$Action unrecognised";
}
