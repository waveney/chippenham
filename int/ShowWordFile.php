<?php
  include_once("fest.php");
  A_Check('Steward');

  dostaffhead("View Word File");

  global $USERID;

  $file = $_REQUEST['l'];
  $sfx = pathinfo($file,PATHINFO_EXTENSION);

  system("rm Temp/$USERID.*");
  $tf = $USERID . time() . ".$sfx";
  $c = copy($file,"Temp/$tf");
  echo "<iframe src='https://view.officeapps.live.com/op/view.aspx?src=http%3A%2F%2F" . Feature('HostURL') . "%2Fint%2FTemp%2F$tf'";
  echo " width=100% height=800";
  echo "></iframe>";
  dotail();
?>

