<?php

global $TableIndexes;
// If table's index is 'id' it does not need to be listed here
$TableIndexes = array(  'Sides'=>'SideId', 'SideYear'=>'syId', 'FestUsers'=>'UserId', 'Venues'=>'VenueId', 'Events'=>'EventId', 
                        'Bugs'=>'BugId', 'BigEvent'=>'BigEid', 'DanceTypes'=>'TypeId', 
                        'Directories'=>'DirId', 'Documents'=>'DocId', 'EventTypes'=>'ETypeNo',
                        'MusicTypes'=>'TypeId','TimeLine'=>'TLid', 'BandMembers'=>'BandMemId', 'ActYear'=>'ActId',
                        'TradeLocs'=>'TLocId','Trade'=>'Tid','TradeYear'=>'TYid'
                        );



//var_dump($TableIndexes);

function db_open () {
  global $db,$CONF;
  if (@ $CONF = parse_ini_file("Configuration.ini")) {
    @ $db = new mysqli($CONF['host'],$CONF['user'],$CONF['passwd'],$CONF['dbase']);
  } else {
    @ $db = new mysqli('localhost','wmff','','wmff');
    $CONF = ['dbase'=>'wmff'];
  }
  if (!$db || $db->connect_error ) die ('Could not connect: ' .  $db->connect_error);
}

db_open();

function Gen_Get($Table,$id, $idx='id') {
  global $db;
  $res = $db->query("SELECT * FROM $Table WHERE $idx=$id");
  if ($res) {
    $ans = $res->fetch_assoc();
    if ($ans) return $ans;
  }
  return [];
}

function Gen_Put($Table, &$now, $idx='id') {
//  if ($Table == 'TradeYear') {var_dump($now);  debug_backtrace();}
  
  if (!empty($now[$idx])) {
    $e=$now[$idx];
    $Cur = Gen_Get($Table,$e,$idx);

    return Update_db($Table,$Cur,$now);
  } else {
    return $now[$idx] = Insert_db ($Table, $now );
  }
}

function Gen_Get_All($Table, $extra='', $idx='id') {
  global $db;
  $Ts = [];
  $res = $db->query("SELECT * FROM $Table $extra");
  if ($res) while ($ans = $res->fetch_assoc()) $Ts[$ans[$idx]] = $ans;
  return $Ts;
}

function Gen_Get_Names($Table, $extra='', $idx='id', $Name='Name') {
  global $db;
  $Ts = [];
  $res = $db->query("SELECT * FROM $Table $extra");
  if ($res) while ($ans = $res->fetch_assoc()) $Ts[$ans[$idx]] = $ans[$Name];
  return $Ts;
}

function Gen_Get_Cond($Table,$Cond, $idx='id') {
  global $db;
  $Ts = [];
//  var_dump($Cond);
  $res = $db->query("SELECT * FROM $Table WHERE $Cond");
  if ($res) while ($ans = $res->fetch_assoc()) $Ts[$ans[$idx]] = $ans;
  return $Ts;
}

function Gen_Get_Cond1($Table,$Cond) {
  global $db;
//  var_dump($Cond);
//  $Q = "SELECT * FROM $Table WHERE $Cond";var_dump("Q=",$Q);
  $res = $db->query("SELECT * FROM $Table WHERE $Cond LIMIT 1");
  if ($res) {
    $ans = $res->fetch_assoc();
    if ($ans) return $ans;
  }
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
  global $db, $Event_Types;
  $Event_Types = array();
  $res = $db->query("SELECT * FROM EventTypes ORDER BY Importance DESC ");
  if ($res) while ($typ = $res->fetch_assoc()) $Event_Types[$typ['ETypeNo']] = $typ;
  return $Event_Types;
}

function Get_Event_Types($tup=0) { // 0 just names, 1 all data
  global $Event_Types;
  if ($tup) return $Event_Types;
  $ans = array();
  foreach($Event_Types as $t=>$et) $ans[$t] = $et['SN'];
  return $ans;
}

function Logg($what) {
  global $db,$USERID;
  $qry = "INSERT INTO LogFile SET Who='$USERID', changed='" . date('d/m/y H:i:s') . "', What='" . addslashes($what) . "'";
  $db->query($qry);
}

function table_fields($table) {
  global $db,$CONF;
  static $tables = array();
  if (isset($tables[$table])) return $tables[$table];

  $qry = "SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='" . $CONF['dbase'] ."' AND TABLE_NAME='" . $table . "'";
  $Flds = $db->query($qry);
  while ($Field = $Flds->fetch_array()) {
    $tables[$table][$Field['COLUMN_NAME']] = $Field['DATA_TYPE'];
  }
  return $tables[$table];
}


function Get_Emails($roll) {
  global $db;
  global $Area_Type;
  $qry = "SELECT Email FROM FestUsers WHERE $roll=" . $Area_Type['Edit and Report'];
  $res = $db->query($qry);
  $ans = "";
  if ($res) while ($row = $res->fetch_assoc()) {
    if (strlen($ans)) $ans .= ",";
    $ans .= $row['Email'];
  }
  return $ans;
}

$UpdateLog = '';

function Report_Log($roll) {
  global $Access_Type,$USER,$USERID,$UpdateLog;
  if ($UpdateLog) {
    if ($USER['AccessLevel'] == $Access_Type['Participant']) {
      switch ($USER['Subtype']) {
      case 'Side':
        $Side = Get_Side($USERID);
        $who = $Side['SN'];
        $Src = 1;
        $SrcId = $USERID;
        break;
        
      case 'Trade':
        $Trad = Get_Trader($USERID);
        $who = $Trad['SN'];
        $Src = 2;
        $SrcId = $USERID;
        break;
        
      default :
        $Src = 0;
        $SrcId = 0;        
        return;
      }
    } else {
      $who = $USER['Login'];
      $Src = 0;
    }

    $emails = Get_Emails($roll);
    if ($Src && $emails) {
      NewSendEmail($Src,$SrcId, $emails,Feature('ShortName') . " update by $who",$UpdateLog);
    }
    Logg(Feature('ShortName') . " update by $who\n" . $UpdateLog);
    $UpdateLog = '';
  }
}

function Update_db($table,&$old,&$new,$proced=1) {
  global $db;
  global $TableIndexes;
  global $UpdateLog;

 // if ($table == 'TradeYear') {   var_dump($new);    debug_print_backtrace();}
  $Flds = table_fields($table);
  $indxname = (isset($TableIndexes[$table])?$TableIndexes[$table]:'id');
  $newrec = "UPDATE $table SET ";
  $fcnt = 0;

  foreach ($Flds as $fname=>$ftype) {
    if ($indxname == $fname) { // Skip
    } elseif (isset($new[$fname])) {
      if ($ftype == 'text') {
        $dbform = addslashes($new[$fname]);
      } elseif ($ftype == 'tinyint' || $ftype == 'smallint') {
        $dbform = 0;
        if ($new[$fname]) {
          if ((string)(int)$new[$fname] = $new[$fname]) { $dbform = $new[$fname]; } else { $dbform = 1; }
        }
      } else {
        $dbform = $new[$fname];
      }

      if (!isset($old[$fname]) || $dbform != $old[$fname]) {
        $old[$fname] = $dbform;
        if ($fcnt++ > 0) { $newrec .= " , "; }
        $newrec .= " $fname=" . '"' . $dbform . '"';
      }
    } else {
      if ($ftype == 'tinyint' || $ftype == 'smallint' ) {
        if ($old[$fname]) {
          $old[$fname] = 0;
            if ($fcnt++ > 0) { $newrec .= " , "; }
          $newrec .= " $fname=0";
        }
      } 
    }
  }

//    if ($table == 'TradeYear') {   var_dump($newrec);    debug_print_backtrace();}

//echo "$fcnt<p>";
  if ($proced && $fcnt) {
    $newrec .= " WHERE $indxname=" . $old[$indxname];
//if ($table == 'TradeYear') echo "Updating $table with: "; var_dump($newrec);debug_print_backtrace(); echo "<P>";
    $update = $db->query($newrec);
    $UpdateLog .= $newrec . "\n";
    if ($update) {
//      echo "<h2>$table Updated - $newrec</h2>\n";
//      echo "<h2>$table Updated</h2>\n";
    } else {
      echo "<h2 class=ERR>An error occoured: ((($newrec))) " . $db->error . "</h2>";
    }
    return $update;
  }
}

function Update_db_post($table, &$data, $proced=1) { 
  return Update_db($table,$data,$_REQUEST,$proced);
}

function Insert_db($table, &$from, &$data=0, $proced=1) {
  global $db;
  global $TableIndexes;
  global $UpdateLog;
  $newrec = "INSERT INTO $table SET ";
  $fcnt = 0;
  $Flds = table_fields($table);
  $indxname = (isset($TableIndexes[$table])?$TableIndexes[$table]:'id');
  foreach ($Flds as $fname=>$ftype) {
    if (isset($from[$fname]) && $from[$fname] != '' && $indxname!=$fname ) { 
      if ($fcnt++ > 0) { $newrec .= " , "; }
      if ($ftype == 'text') {
        $dbform = addslashes($from[$fname]);
        if ($data) $data[$fname] = $dbform;
        $newrec .= " $fname=" . '"' . $dbform . '"';
      } elseif ($ftype == "tinyint" || $ftype == 'smallint') {
        $dbform = 0;
        if ($from[$fname]) {
          if ((string)(int)$from[$fname] = $from[$fname]) { $dbform = $from[$fname]; } else { $dbform = 1; }
        }
        if ($data) $data[$fname] = $dbform;
        $newrec .= " $fname=$dbform ";
      } else {
        if ($data) $data[$fname] = $from[$fname];
        $newrec .= " $fname=$from[$fname]";
      }
    }
  }
  if ($proced) {
  
//  debug_print_backtrace();
// var_dump($newrec);exit;
    $insert = $db->query($newrec);
    if ($insert) {
      $UpdateLog .= $newrec . "\n";
      $snum = $db->insert_id;
//      echo "<h2>$table New entry - $newrec - $snum</h2>";
//      echo "<h2>$table New entry added</h2>";
      if ($data) $data[$indxname]=$snum;
      $from[$indxname]=$snum;
      return $snum;
    } else {
      echo "<h2 class=ERR>An error occoured: ((($newrec))) " . $db->error . "</h2>";
    }
  }
  return 0;
}

function Insert_db_post($table,&$data,$proced=1) {
  $data['Dummy'] = 1;
  return Insert_db($table,$_REQUEST,$data,$proced);  
}

function db_delete($table,$entry) {
  global $db,$TableIndexes;
  $indxname = (isset($TableIndexes[$table])?$TableIndexes[$table]:'id');
//echo "DELETE FROM $table WHERE $indxname='$entry'<p>";
  return $db->query("DELETE FROM $table WHERE $indxname='$entry'");
}

function db_delete_cond($table,$cond) {
  global $db;
  return $db->query("DELETE FROM $table WHERE $cond");
}

function db_update($table,$what,$where) {
  global $db;
  return $db->query("UPDATE $table SET $what WHERE $where");
}

function db_get($table,$cond) {
  global $db;
  $res = $db->query("SELECT * FROM $table WHERE $cond");
  if ($res) return $res->fetch_assoc();
  return 0;
}

// Read YEARDATA Data - this is NOT year specific - Get fest name, short name, version everything else is for future

$_YearFeatures = [];

function Feature($Name,$default='') {  // Return value of feature if set Year data value overrides system value
  global $FESTSYS,$YEARDATA,$_YearFeatures,$_Features;
  if (!$_Features) {
    $_Features = parse_ini_string($FESTSYS['Features']?? '');
    $_YearFeatures = parse_ini_string($YEARDATA['FestFeatures'] ?? '');
  }
  return $_YearFeatures[$Name] ?? ($_Features[$Name] ?? $default);
}

function Feature_Reset() {
  global $_YearFeatures,$YEARDATA,$_Features,$FESTSYS;
  $_YearFeatures = parse_ini_string($YEARDATA['FestFeatures'] ?? '');
  $_Features = parse_ini_string($FESTSYS['Features']?? '');
}

function Capability($Name,$default='') {  // Return value of Capability if set from FESTSYS
  static $Capabilities = [];
  global $FESTSYS;
  if (!$Capabilities) {
    $Capabilities = [];
    foreach (explode("\n",$FESTSYS['Capabilities']) as $Cape) {
      $Dat = explode(":",$Cape,3);
      if ($Dat[0])$Capabilities[$Dat[0]] = trim($Dat[1]);
    }
  }
  if (isset($Capabilities[$Name])) return $Capabilities[$Name];
  return $default;
}

$FESTSYS = Gen_Get('SystemData',1);
$SHOWYEAR = Feature('ShowYear');
$YEAR = $PLANYEAR = Feature('PlanYear');  //$YEAR can be overridden
include_once("Version.php");
$Event_Types = Event_Types_ReRead();// Caching


function TnC($Name,$default='') {  // Return value of T and C if set from TsAndCs
  $Res = Gen_Get_Cond1('TsAndCs2',"Name='$Name'");
  return (empty($Res['Content'])?$default:$Res['Content']);
}

function set_ShowYear($last=0) { // Overrides default above if not set by a Y argument
  global $YEAR,$SHOWYEAR,$YEARDATA;
  if ($last == 0 && !isset($_REQUEST['Y'])) {
    $YEAR = $SHOWYEAR;
    $YEARDATA = Get_General($YEAR);
    Feature_Reset();
  } else if (!isset($_REQUEST['Y'])) {
    $YEAR = $last;
    $YEARDATA = Get_General($YEAR);
    Feature_Reset();
  }
}

// Works for simple tables
// Deletes = 0 none, 1=one, 2=many  Putfn=name of put fn or empty for gen_put call
function UpdateMany($table,$Putfn,&$data,$Deletes=1,$Dateflds='',$Timeflds='',$Mstr='SN',$MstrNot='',$Hexflds='') {
  global $TableIndexes;
  include_once("DateTime.php");
  $Flds = table_fields($table);
  $DateFlds = explode(',',$Dateflds);
  $TimeFlds = explode(',',$Timeflds);
  $HexFlds = explode(',',$Hexflds);
  $indxname = (isset($TableIndexes[$table])?$TableIndexes[$table]:'id');
  if (!isset($Flds['SN']) && isset($Flds['Name'])) $Mstr='Name';

// var_dump($Flds);
//return;
  if (isset($_REQUEST['Update'])) {
    if ($data) foreach($data as $t) {
      $i = $t[$indxname];

      if ($i) {
        if (isset($_REQUEST["$Mstr$i"]) && $_REQUEST["$Mstr$i"] == $MstrNot) {
          if ($Deletes) {
//          echo "Would delete " . $t[$indxname] . "<br>";
              db_delete($table,$t[$indxname]);
            if ($Deletes == 1) return 1;
          }
          continue;
        } else {
          $recpres = 0;
          foreach ($Flds as $fld=>$ftyp) {
            if ($fld == $indxname) continue;
            if (in_array($fld,$DateFlds)) {
              $t[$fld] = Date_BestGuess($_REQUEST["$fld$i"]);
              $recpres = 1;
            } else if (in_array($fld,$TimeFlds)) {
              $t[$fld] = Time_BestGuess($_REQUEST["$fld$i"]);
              $recpres = 1;
            } else if (in_array($fld,$HexFlds)) {
              $t[$fld] = hexdec($_REQUEST["$fld$i"]);
              $recpres = 1;
            } else if (isset($_REQUEST["$fld$i"])) {
              $t[$fld] = $_REQUEST["$fld$i"];
              $recpres = 1;
            } else {
              $t[$fld] = 0;
            }
          }
// if ($i==15)  {       var_dump($recpres,$t);exit; };
//          return;
          if ($recpres) {
            if ($Putfn) {
              $Putfn($t);
            } else {
              Gen_Put($table,$t,$indxname);
            }
          }
        }
      }
    }
    if (isset($_REQUEST[$Mstr . "0"] ) && $_REQUEST[$Mstr . "0"] != $MstrNot) {
//echo "Here";
      $t = array();
      foreach ($Flds as $fld=>$ftyp) {
        if ($fld == $indxname) continue;
        if (isset($_REQUEST[$fld . "0"])) {
          if (in_array($fld,$DateFlds)) {
            $t[$fld] = Date_BestGuess($_REQUEST[$fld . "0"]);
          } else if (in_array($fld,$TimeFlds)) {
            $t[$fld] = Time_BestGuess($_REQUEST[$fld . "0"]);
          } else if (in_array($fld,$HexFlds)) {
            $t[$fld] = hexdec($_REQUEST[$fld . "0"]);
          } else {
            $t[$fld] = $_REQUEST[$fld . "0"];
          }
        }
      }
//var_dump($t); exit;
      Insert_db($table,$t);
    }
    return 1;
  } 
}
