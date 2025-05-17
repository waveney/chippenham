<?php

include_once "fest.php";
include_once "ChangeLib.php";
global $YEAR,$DayLongList;

A_Check("SysAdmin");

if (isset($_REQUEST['Del'])) {
  //  $ec = Gen_Get('EventChanges',$_REQUEST['Del']);
  db_delete('PerfChanges',$_REQUEST['Del']);
}

dostaffhead("Edit Performer Changes");

TableStart();
TableHead("id",'N');
TableHead('SideId','N');
TableHead('Performer Name');
//TableHead('Where');
//TableHead('Day');
//TableHead('When');
TableHead('Field Changed');
TableHead('New Value');
TableHead('Actions');
TableTop();

$PChanges = Gen_Get_Cond('PerfChanges',"Year='$YEAR' ORDER by SideId");
Register_AutoUpdate('Generic',0);

foreach($PChanges as $id=>$pc) {
  echo "<tr><td>$id";
  
  $Side = Get_Side($pc['SideId']);
  
  echo "<td>" . $pc['SideId'];
  echo "<td>" . $Side['SN'];
  echo fm_text1('',$pc,'Field',1,'','',"PerfChanges:Field:$id");
  echo fm_text1('',$pc,'Changes',1,'','',"PerfChanges:Changes:$id");
  echo "<td><a href=PerfUpdateEdit?Del=$id>Del</a>";
}

TableEnd();
dotail();
