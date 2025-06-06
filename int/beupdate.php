<?php
// Updates to data following on screen drag drops, returns info pane html
//    $("#Infomation").load("dpupdate.php", "D=" + dstId + "&S=" + srcId + "&Y=" + $("#DayId").text() + "&E=" +
//                        $("input[type='radio'][name='EInfo']:checked").val()        );
//  include_once("minimalfiles/header.php");
/* ids are
        E$CurOrder::        Empty box
        N$CurOrder::        Note id
        I$CurOrder::        Note input
        S$CurOrder:$tt:$id        Grid with entry
        M$CurOrder:$tt:$id        Note with entry
        J$CurOrder:$tt:$id        Note input entry
        Z0:Side:$id        Side Side
        Z0:Act:$id        Act Side
        Z0:Other:$id        Other Side
    $("#InformationPane").load("beupdate.php", "D=" + dstId + "&S=" + srcId + "&EV=" + $("#EVENT").text() + "&E=" +
                        $("input[type='radio'][name='EInfo']:checked").val()        );

D=Z0:Side:32&S=S10:Side:32&EV=167&E=
*/

  include_once("fest.php");
  include_once("ProgLib.php");
  include_once("CheckDance.php");

function RecordBeEventChanges($Ev) {
  global $PLANYEAR;

  $Rec = Gen_Get_Cond1('EventChanges',"( EventId=$Ev AND Field='Side1' )");
  if (empty($Rec['id'])) {
    $Rec = ['EventId'=>$Ev, 'Year'=>$PLANYEAR, 'Changes'=>'Changes', 'Field'=>'Side1' ];
    Gen_Put('EventChanges',$Rec);
  }
}

$srcmtch = $dstmtch = [];

//var_dump($_REQUEST);
  if (isset($_REQUEST['D'])) {
    $dstId = $_REQUEST['D'];
    $srcId = $_REQUEST['S'];
    $Ev   = $_REQUEST['EV'];

    preg_match('/(.)(\d*):(.*):(\d*)/',$dstId,$dstmtch);
    preg_match('/(.)(\d*):(.*):(\d*)/',$srcId,$srcmtch);

    $stt = $srcmtch[3];
    $sid = $srcmtch[4];
    $dtt = $dstmtch[3];
    $did = $dstmtch[4];

    switch ($srcmtch[1] . $dstmtch[1]) {

    case 'EE': // No Action
      break;

    case 'ES':
      db_delete_cond('BigEvent',"Event=$Ev AND ( Type='$dtt' OR Type='Perf' OR Type='Act' OR Type='Other') AND Identifier=$did"); // Fudge for old data

      break;

    case 'EZ': // No Action
      break;

    case 'SE': // Move
      $a = db_update('BigEvent','EventOrder=' . $dstmtch[2],"Event=$Ev AND Type='$stt' AND Identifier=$sid");
      break;

    case 'SS': // Move and replace
      db_delete_cond('BigEvent',"Event=$Ev AND ( Type='$dtt' OR Type='Perf' OR Type='Act' OR Type='Other' )  AND Identifier=$did");
      db_update('BigEvent','EventOrder=' . $dstmtch[2],"Event=$Ev AND Type='$stt' AND Identifier=$sid");
      break;

    case 'SZ': // Remove
      db_delete_cond('BigEvent',"Event=$Ev AND ( Type='$stt' OR Type='Perf' OR Type='Act' OR Type='Other' )  AND Identifier=$sid");
      if (Feature('RecordEventChanges') && $sid >0) {
        RecordPerfEventChange($sid,$Type='Perform');
        RecordBeEventChanges($Ev);
      }
      break;

    case 'ZE': // New
      $new = array('Event'=>$Ev, 'Type'=>$stt, 'Identifier'=>$sid, 'EventOrder'=>$dstmtch[2]);
      Insert_db('BigEvent',$new);
      if (Feature('RecordEventChanges') && $sid >0) {
        RecordPerfEventChange($sid,$Type='Perform');
        RecordBeEventChanges($Ev);
      }

      break;

    case 'ZS': // New Replace
      break;

    case 'ZZ': // No Action
      break;
    }

  }
 // Return setup

  $Ei    = $_REQUEST['E'];  // Used for return info
//  echo "fred";
  CheckDance($Ei);
