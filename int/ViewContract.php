<?php
  include_once("fest.php");

  $Headers = !isset($_REQUEST['NoHead']);
  if ($Headers) dostaffhead("View Contract");
  include_once("Contract.php");
  include_once("ViewLib.php");
  global $YEAR,$Book_State;

  $snum=0;
  if (isset($_REQUEST['sidenum'])) $snum = $_REQUEST['sidenum'];

  $Side = Get_Side($snum);
  $ctype = ($Side['IsAnAct']?1:0);

  $Sidey = Get_SideYear($snum);
  $Opt = 0;
  $IssNum = $Sidey['Contracts'];
  if ($Sidey['YearState'] == $Book_State['Contract Signed']) $Opt += 1;
  if ($Sidey['Contracts']) $Opt +=2;
  if (isset($_REQUEST{'I'})) { $IssNum = $_REQUEST['I']; $Opt += 4; }

  switch ($Opt) {
  case 0:
  case 1:
  case 2:
    echo Show_Contract($snum,0);
    break;
  case 3:
    echo Show_Contract($snum,1);
    break;
  default:
    ViewFile("Contracts/$YEAR/$snum.$IssNum.pdf");
    break;
  }

  echo "</div>";
  if ($Headers) dotail();

