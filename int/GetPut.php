<?php
// General Table operations

function Gen_Get($Table,$id, $idx='id') {
  global $db;
  $res = $db->query("SELECT * FROM $Table WHERE $idx=$id");
  if ($res) if ($ans = $res->fetch_assoc()) return $ans;
  return [];
}

function Gen_Put($Table, &$now, $idx='id') {
  global $db,$GAMEID;
  if (isset($now[$idx])) {
    $e=$now[$idx];
    $Cur = Gen_Get($Table,$e,$idx);
    return Update_db($Table,$Cur,$now);
  } else {
    return $now[$idx] = Insert_db ($Table, $now );
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
