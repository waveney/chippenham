<?php
// General Table operations

function Gen_Get($Table,$id) {
  global $db;
  $res = $db->query("SELECT * FROM $Table WHERE id=$id");
  if ($res) if ($ans = $res->fetch_assoc()) return $ans;
  return [];
}

function Gen_Put($Table, &$now) {
  global $db,$GAMEID;
  if (isset($now['id'])) {
    $e=$now['id'];
    $Cur = Gen_Get($Table,$e);
    return Update_db($Table,$Cur,$now);
  } else {
    return $now['id'] = Insert_db ($Table, $now );
  }
}

function Gen_Get_All($Table) {
  global $db;
  $Ts = [];
  $res = $db->query("SELECT * FROM $Table");
  if ($res) while ($ans = $res->fetch_assoc()) $Ts[] = $ans;
  return $Ts;
}

function Gen_Get_Cond($Table,$Cond) {
  global $db;
  $Ts = [];
  $res = $db->query("SELECT * FROM $Table WHERE $Cond");
  if ($res) while ($ans = $res->fetch_assoc()) $Ts[] = $ans;
  return $Ts;
}

function Gen_Get_Cond1($Table,$Cond) {
  global $db;
//  $Q = "SELECT * FROM $Table WHERE $Cond";var_dump("Q=",$Q);
  $res = $db->query("SELECT * FROM $Table WHERE $Cond");
  if ($res) if ($ans = $res->fetch_assoc()) return $ans;
  return [];
}

function Gen_Select($Clause) {
  global $db;
  $Ts = [];
  $res = $db->query($Clause);
  if ($res) while ($ans = $res->fetch_assoc()) $Ts[] = $ans;
  return $Ts;
}

?>
