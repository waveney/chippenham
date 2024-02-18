<?php
// Participant finder

  include_once("fest.php");
  $x = '';
  $k = $_REQUEST['S'];
  if (isset($_REQUEST['X'])) $x = "AND " . $_REQUEST['X'];
  if (isset($_REQUEST['Y'])) {
    $y = $_REQUEST['Y'];
    $qry = $db->query("SELECT s.SideId, s.SN FROM Side s, SideYear y WHERE s.SideId=y.SideId AND s.SN LIKE '%$k%' AND $y $x LIMIT 10");
  } else {
    $qry = $db->query("SELECT s.SideId, s.SN FROM Side s WHERE s.SN LIKE '%$k%' $x LIMIT 10");
  }
  $res = array();
  while ($ans = $qry->fetch_assoc()) {
    $res[$ans['SideId']] = $ans['SN'];
  }
  echo json_encode($res);
