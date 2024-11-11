<?php
// Set fields in data
include_once("fest.php");
include_once("DanceLib.php");

$id = $_REQUEST['I'];
$Opt = $_REQUEST['O'];

//echo "In Setfields";
//var_dump($_REQUEST);

switch ($Opt) {
case 'I':
case 'J': // Dont Save
  $Sidey = Get_SideYear($id);
  if (!$Sidey) $Sidey = Default_SY($id);
  $prefix = '';
  $label = (isset($_REQUEST['L'])?$_REQUEST['L']:'');

  if ($label) $prefix .= "<span " . Proforma_Background($label) . ">$label:";
  $prefix .= date('j/n/y');
  if ($label) $prefix .= "</span>";
  if (strlen($Sidey['Invited'])) {
    $Sidey['Invited'] = $prefix . ", " . $Sidey['Invited'];
  } else {
    $Sidey['Invited'] = $prefix;
  }

  if ($Opt == 'I') {
    Put_SideYear($Sidey);
    if ($label == 'Change' || $label == 'Reinvite') {
      Dance_Record_Change($id, $prefix);
    }
  }
  echo $Sidey['Invited'];
  break;

case 'K': // Same as I/J but for Music data not dance data
case 'L': // Dont Save
  global $Book_State;
  include_once("MusicLib.php");
  $Sidey = Get_SideYear($id);
  if (!$Sidey) $Sidey = Default_SY($id);
  $prefix = '';
  $label = (isset($_REQUEST['L'])?$_REQUEST['L']:'');

  if ($label) $prefix .= "<span " . Music_Proforma_Background($label) . ">$label:";
  $prefix .= date('j/n/y');
  if ($label) $prefix .= "</span>";
  if (strlen($Sidey['Invited'])) {
    $Sidey['Invited'] = $prefix . ", " . $Sidey['Invited'];
  } else {
    $Sidey['Invited'] = $prefix;
  }

  if ($Opt == 'K') {
    if ($label == 'Contract') {
      $Sidey['YearState'] = $Book_State['Contract Sent'];
    }
    Put_SideYear($Sidey);
  }
  echo $Sidey['Invited'];
  break;

case 'R': // Read SideYear
  $Sidey = Get_SideYear($id);
  echo $Sidey[$_REQUEST['F']];
  break;

case 'Y':
  $Sidey = Get_SideYear($id);
  if (!$Sidey) $Sidey = Default_SY($id);
  $Sidey[$_REQUEST['F']]=$_REQUEST['V'];
  Put_SideYear($Sidey);
  break;

case 'TP':
  include_once("TLLib.php");
  $tl = Get_TLent($id);
  $tl['Progress'] = $_REQUEST['V'];
  if ($tl['Progress'] == 100) {
    $tl['Completed'] = time();
  }
  Put_TLent($tl);
  break;

case 'Z':
  global $Book_Colours,$Book_State;
  $Sidey = Get_SideYear($id);
  if (!$Sidey) $Sidey = Default_SY($id);
  $State = $Sidey['YearState'];
  echo "<td style='background-color:" . $Book_Colours[$State] . "' id=BookState$id >" . $Book_State[$State];
  break;

case 'PC':
  Set_User();
  global $USER,$USERID;
  $Sidey = Get_SideYear($id);
  if (empty($Sidey['TicketsCollected'])) {
    $Sidey['TicketsCollected'] = time();
    $Sidey['CollectedBy'] = $USERID;
    Put_SideYear($Sidey);
    echo "Collected " . date("D M j G:i:s",$Sidey['TicketsCollected']) . " from " . ($USER['SN'] ?? 'Unknown') .
         " <button id=Oops$id type=button onclick=TicketsCollected($id,0)>Oops - undo that</button>";
  } else { // error message to be presented
    $User = Get_User($Sidey['CollectedBy']);
    echo "<span class=Err>ERROR - already Collected " . date("D M j G:i:s",$Sidey['TicketsCollected']) . " from " . ($User['SN'] ?? 'Unknown') . "</span>";
  }
  exit;

case 'NC':
  $Sidey = Get_SideYear($id);
  $Sidey['TicketsCollected'] = 0;
  Put_SideYear($Sidey);
  echo "<button type=button class=FakeButton onclick='TicketsCollected($id)'>Collect</button>";
  exit;

default:
  $Side = Get_Side($id);
  $Side[$_REQUEST['F']]=$_REQUEST['V'];
  Put_Side($Side);
}
