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

function Gen_Get_All($Table, $extra='') {
  global $db;
  $Ts = [];
  $res = $db->query("SELECT * FROM $Table $extra");
  if ($res) while ($ans = $res->fetch_assoc()) $Ts[$ans['id']] = $ans;
  return $Ts;
}

function Gen_Get_Names($Table, $extra='') {
  global $db;
  $Ts = [];
  $res = $db->query("SELECT * FROM $Table $extra");
  if ($res) while ($ans = $res->fetch_assoc()) $Ts[$ans['id']] = $ans['Name'];
  return $Ts;
}

function Gen_Get_Cond($Table,$Cond) {
  global $db;
  $Ts = [];
  $res = $db->query("SELECT * FROM $Table WHERE $Cond");
  if ($res) while ($ans = $res->fetch_assoc()) $Ts[$ans['id']] = $ans;
  return $Ts;
}

function Gen_Get_Cond1($Table,$Cond) {
  global $db;
//  $Q = "SELECT * FROM $Table WHERE $Cond";var_dump("Q=",$Q);
  $res = $db->query("SELECT * FROM $Table WHERE $Cond LIMIT 1");
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

function Event_Types_ReRead() {
  global $db, $Event_Types_Full;
  $Event_Types_Full = array();
  $res = $db->query("SELECT * FROM EventTypes ORDER BY Importance DESC ");
  if ($res) while ($typ = $res->fetch_assoc()) $Event_Types_Full[$typ['ETypeNo']] = $typ;
  return $Event_Types_Full;
}

$Event_Types_Full = Event_Types_ReRead();

function Get_Event_Types($tup=0) { // 0 just names, 1 all data
  global $Event_Types_Full;
  if ($tup) return $Event_Types_Full;
  $ans = array();
  foreach($Event_Types_Full as $t=>$et) $ans[$t] = $et['SN'];
  return $ans;
}

?>
