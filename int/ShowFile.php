<?php
  include_once("fest.php");
//  A_Check('Staff');
  include_once ("DocLib.php");
  include_once ("ViewLib.php");
  global $USERID;
  $read = 1;

if (isset($_REQUEST['f'])) {
  ViewFile("Store" . File_FullPName($_GET['f']));
} else if (isset($_REQUEST['l'])) {
  ViewFile($_GET['l']);
} else if (isset($_REQUEST['l64'])) {
  ViewFile(base64_decode($_GET['l64']));
} else if (isset($_REQUEST['d'])) {
  $tar = (isset($_GET['N'])? $_GET['N'] :'');
  ViewFile("Store" . File_FullPName($_GET['d']),0,$tar);
  exit;
} else if (isset($_REQUEST['D'])) {
  $tar = (isset($_GET['N'])? $_GET['N'] :'');
  ViewFile($_GET['D'],0,$tar);
  exit;
}
  dotail();

?>
