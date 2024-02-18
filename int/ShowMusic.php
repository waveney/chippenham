<?php
  include_once("fest.php");

  set_ShowYear();
  include_once("DanceLib.php");
  
  $id = 0;
  if (isset($_REQUEST['sidenum'])) {
    $id = $_REQUEST['sidenum'];
  } else if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
  } else {
    echo "No Side Indicated";
  }
  if (!is_numeric($id)) Error_page("Not a performer");
  
  Show_Side($id,'',1);

  dotail();
?>

