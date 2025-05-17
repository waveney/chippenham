<?php

include_once "fest.php";
include_once "ChangeLib.php";
global $YEAR,$DayLongList;

A_Check("SysAdmin");

if (isset($_REQUEST['Del'])) {
//  $ec = Gen_Get('EventChanges',$_REQUEST['Del']);
  db_delete('EventChanges',$_REQUEST['Del']);
}

dostaffhead("Edit Event Changes");

TableStart();
TableHead("id",'N');
TableHead('EventId','N');
TableHead('Event Name');
TableHead('Where');
TableHead('Day');
TableHead('When');
TableHead('Field Changed');
TableHead('New Value');
TableHead('Actions');
TableTop();

$EChanges = Gen_Get_Cond('EventChanges',"Year='$YEAR' ORDER by EventId");
$Vens = Get_Venues(1);
Register_AutoUpdate('Generic',0);

foreach($EChanges as $id=>$ec) {
  echo "<tr><td>$id";
  
  $Ev = Get_Event($ec['EventId']);
  $dname = $DayLongList[$Ev['Day']];
  
  echo "<td>" . $ec['EventId'];
  echo "<td>" . $Ev['SN'];
  echo "<td>" . $Vens[$Ev['Venue']]['SN'];
  echo "<td>$dname<td>" . timecolon($Ev['Start']) . " - " . timecolon($Ev['End']);
  echo fm_text1('',$ec,'Field',1,'','',"EventChanges:Field:$id");
  echo fm_text1('',$ec,'Changes',1,'','',"EventChanges:Changes:$id");
  echo "<td><a href=EventUpdateEdit?Del=$id>Del</a>";
}

TableEnd();
dotail();
