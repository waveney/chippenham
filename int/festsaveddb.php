<?php

//var_dump($TableIndexes);

function db2_open () {
  global $db2,$CONF;
  if (@ $CONF = parse_ini_file("Configuration.ini")) {
    @ $db2 = new mysqli($CONF['host'],$CONF['user'],$CONF['passwd'],'SavedChip');
  } else {
    @ $db2 = new mysqli('localhost','wmff','','wmff');
    $CONF = ['dbase'=>'wmff'];
  }
  if (!$db2 || $db2->connect_error ) die ('Could not connect: ' .  $db2->connect_error);
}

db2_open();

function Gen2_Get($Table,$id, $idx='id') {
  global $db2;
  $res = $db2->query("SELECT * FROM $Table WHERE $idx=$id");
  if ($res) {
    $ans = $res->fetch_assoc();
    if ($ans) return $ans;
  }
  return [];
}

function Gen2_Get_All($Table, $extra='', $idx='id') {
  global $db2;
  $Ts = [];
  $res = $db2->query("SELECT * FROM $Table $extra");
  if ($res) while ($ans = $res->fetch_assoc()) $Ts[$ans[$idx]] = $ans;
  return $Ts;
}

function Gen2_Get_Names($Table, $extra='', $idx='id', $Name='Name') {
  global $db2;
  $Ts = [];
  $res = $db2->query("SELECT * FROM $Table $extra");
  if ($res) while ($ans = $res->fetch_assoc()) $Ts[$ans[$idx]] = $ans[$Name];
  return $Ts;
}

function Gen2_Get_Cond($Table,$Cond, $idx='id') {
  global $db2;
  $Ts = [];
//  var_dump($Cond);
  $res = $db2->query("SELECT * FROM $Table WHERE $Cond");
  if ($res) while ($ans = $res->fetch_assoc()) $Ts[$ans[$idx]] = $ans;
  return $Ts;
}

function Gen2_Get_Cond1($Table,$Cond) {
  global $db2;
//  var_dump($Cond);
//  $Q = "SELECT * FROM $Table WHERE $Cond";var_dump("Q=",$Q);
  $res = $db2->query("SELECT * FROM $Table WHERE $Cond LIMIT 1");
  if ($res) {
    $ans = $res->fetch_assoc();
    if ($ans) return $ans;
  }
  return [];
}

function Gen2_Select($Clause) {
  global $db2;
  $Ts = [];
  $res = $db2->query($Clause);
  if ($res && is_object($res)) while ($ans = $res->fetch_assoc()) $Ts[] = $ans;
  return $Ts;
}



function db2_get($table,$cond) {
  global $db2;
  $res = $db2->query("SELECT * FROM $table WHERE $cond");
  if ($res) return $res->fetch_assoc();
  return 0;
}

// Read YEARDATA Data - this is NOT year specific - Get fest name, short name, version everything else is for future


