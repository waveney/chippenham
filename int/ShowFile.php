<?php
  include_once("fest.php");
//  A_Check('Staff');
  include_once ("DocLib.php");
  include_once ("ViewLib.php");
  global $USERID;
  $read = 1;

if (isset($_REQUEST['f'])) {
  ViewFile("Store" . File_FullPName($_REQUEST['f']));
} else if (isset($_REQUEST['l'])) {
  ViewFile($_REQUEST['l']);
} else if (isset($_REQUEST['l64'])) {
  ViewFile(base64_decode($_REQUEST['l64']));
} else if (isset($_REQUEST['d'])) {
  $tar = (isset($_REQUEST['N'])? $_REQUEST['N'] :'');
  ViewFile("Store" . File_FullPName($_REQUEST['d']),0,$tar);
  exit;
} else if (isset($_REQUEST['D'])) {
  $tar = (isset($_REQUEST['N'])? $_REQUEST['N'] :'');
  ViewFile($_REQUEST['D'],0,$tar);
  exit;
}
  dotail();

?>
