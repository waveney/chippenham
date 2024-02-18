<?php
  include_once("fest.php");
  A_Check('Committee','Trade');

  dostaffhead("Trade Emails");

  include_once("TradeLib.php");
  include_once("DateTime.php"); 
  include_once("Email.php"); 
  include_once("InvoiceLib.php");
 
  global $db,$YEAR,$PLANYEAR,$Trade_State,$Trade_States,$Trade_State_Colours,$USER;

  $Messages = Get_Email_Proformas();
  $Trade_Loc_Names = Get_Trade_Locs(0);
  $Trade_Type_Names = [];
  $Trade_Type_Colours = [];
  foreach ($TradeTypeData as $i=>$tt) { 
    $Trade_Type_Names[$i] = $tt['SN']; 
    $Trade_Type_Colours[$i] = $tt['Colour']; 
  };

//var_dump($_REQUEST);
  if (!isset($_REQUEST['SEND'])) {
    // Basic message text
    // Select (BID &/ TC), Previous (not BID/TC), All
    // Select list?


    echo "<h2>FIRST select message type to be sent</h2>";
    echo "This is the message that will be sent.<p>"; 
    echo "The Message here is editable to send, but the edited form is not stored.<p>";

    $Messkey = isset($_REQUEST['MessNum'])?$_REQUEST['MessNum']:1;

    echo "<h2>";
    foreach ($Messages as $mes) {
      if (!preg_match('/Trade_/',$mes['SN'])) continue;
      if ($mes['id'] == $Messkey) {
        echo $mes['SN'];
        $Mess = $mes['Body'];
      } else {
        echo "<a href=EmailTraders?MessNum=" . $mes['id'] . ">" . $mes['SN'] . "</a>";
      }
      echo "&nbsp; &nbsp; &nbsp; ";
    }
    echo "</h2><p>\n";

    echo "<h2>THEN</h2>";
    echo "<h3>Select subset of traders - if no States/Locations/types are selected that category is treated as all</h3><p>";
    echo "<form method=post><div class=Scrolltable><table class=Devemail>";
    
    echo "<tr height=100>" . fm_radio("State",$Trade_States,$_REQUEST,'Tr_State','',1,'','',$Trade_State_Colours,1);
    echo "<tr height=30>" . fm_radio("Location",$Trade_Loc_Names,$_REQUEST,'Tr_Loc','',1,'','',null,1);
    echo "<tr height=30>" . fm_radio("Trade Type",$Trade_Type_Names,$_REQUEST,'Tr_Type','',1,'','',$Trade_Type_Colours,1);
    echo "<tr>" . fm_text('Or List of Tids',$_REQUEST,'Tr_List',6) . "<tr><td>Sep by commas";
    echo "</table></div><p>";
    
    if (!isset($Mess)) $Mess = $Messages[1]['Body'];
    $Sender = $USER['SN'];
    $Mess = preg_replace('/\$PLANYEAR/',$PLANYEAR,$Mess);

    $_REQUEST['Mess'] = preg_replace('/\*SENDER\*/',$Sender,$Mess);

    echo "<form method=post><div class=Scrolltable><table class=Devemail>";
    echo "<tr><td colspan=8>" . fm_checkbox("Just list who it would go to do not actually send anything",$_REQUEST,'JustList');
    echo "<tr>" . fm_text('Start at', $_REQUEST,'STARTAT');
    echo "<tr>" . fm_textarea('Message',$_REQUEST,'Mess',10,25);
    echo "<input type=submit name=SEND value=Send>\n";
    
    echo "</table></div><form>\n";
  } else {
    $Limited = '';
    $TRM = 0;

    if (empty($_REQUEST['Tr_List'])) {
      $ts  = $lt = $ttt = [];
      foreach ($Trade_States as $i=>$n) if (isset($_REQUEST["Tr_State$i"] )) $ts[] = $i;
      foreach ($Trade_Loc_Names as $i=>$n) if (isset($_REQUEST["Tr_Loc$i"] )) $lt[] = $i;
      foreach ($TradeTypeData as $i=>$td) if (isset($_REQUEST["Tr_Type$i"] )) $ttt[] = $i;
    
      if (empty($ts) && empty($lt) && empty($ttt)) {
        $qry = "SELECT t.* FROM Trade t WHERE t.IsTrader=1 AND t.status=0";   
      } else {
        $qry = "SELECT t.*, y.* FROM Trade t LEFT JOIN TradeYear y ON t.Tid=y.Tid AND y.Year='$YEAR'";   
      }
      $res = $db->query($qry);

      if (!$res || $res->num_rows==0) {
        echo "None found!";
        dotail();
      }

    } else {
      $TR_List = explode(',',$_REQUEST['Tr_List']);
      $TRM = 1;
    }
      
//echo "<P>";    var_dump($TR_List);
      
    $Mess = $_REQUEST['Mess'];

    $Sent_Count = 0;
    $StartAt = (isset($_REQUEST['STARTAT']) ? ($_REQUEST['STARTAT']?$_REQUEST['STARTAT']:0) : 0);

    $EndAt = $StartAt +5;// Batch size 5 for testing 20 in real life  // TODO review that

    while ($Trad = ($TRM? Get_Trader(array_shift($TR_List)) : $res->fetch_assoc())) {
      if ($Trad['Status'] != 0) continue;  //Remove dead/blocked traders
      if (!empty($ttt)) {
        $valid = 0;
        foreach ($ttt as $tt) if ($Trad['TradeType'] == $tt) $valid = 1;
        if (!$valid) continue;   
      }
      if (isset($Trad['BookingState'])) {
        if (!empty($ts)) {
          $valid = 0;
          foreach ($ts as $st) if ($Trad['BookingState'] == $st) $valid = 1;
          if (!$valid) continue;
        }
        if (!empty($lt)) {
          $valid = 0;
          foreach ($lt as $tl) if (($Trad['PitchLoc0'] == $tl ) || ($Trad['PitchLoc1'] == $tl ) || ($Trad['PitchLoc2'] == $tl ) ) $valid =1;
          if (!$valid) continue;
        }
      } else { 
        if (!empty($lt)) continue;
        if (empty($ts) || $ts[0] == 0) {} // allow record
        else continue;
      }
      
      $Key = $Trad['AccessKey'];
      if (!$Key) {
        echo "Ommitting " . $Trad['SN'] . " as it does not have an Access Key.<br>";
        continue;
      };

      if ($Sent_Count >= $StartAt && $Sent_Count < $EndAt) {
        if ($_REQUEST['JustList'] ?? 0) {
          echo "Would Send to " . $Trad['SN'] . "<br>";
        } else {
          if (0 && $Trad['Tid'] != 681) { // Diagnostic code
            echo "Caught in error doing " . $Trad['Tid'] . ' ' . $Trad['SN'] . "<p>";
            var_dump($_REQUEST); // Diagnostic code on error path
            exit;
          }
          Send_Trader_Email($Trad,$Trad,$Mess);
          echo "Sent to " . $Trad['SN'] . "<br>";
        }
      }
      $Sent_Count++;
    }
    if ($Sent_Count > $EndAt) {
      echo "<P><form method=post>";
      echo fm_hidden('STARTAT', $EndAt+1) . fm_hidden('Mess',$Mess) . fm_hidden('SEND',$_REQUEST['SEND']) ; 

      foreach ($Trade_States as $i=>$n) if (isset($_REQUEST["Tr_State$i"] )) echo fm_hidden("Tr_State$i",$i);
      foreach ($Trade_Loc_Names as $i=>$n) if (isset($_REQUEST["Tr_Loc$i"] )) echo fm_hidden("Tr_Loc$i",$i);
      foreach ($TradeTypeData as $i=>$td) if (isset($_REQUEST["Tr_Type$i"] )) echo fm_hidden("Tr_Type$i",$i);
      echo fm_hidden('Tr_List',$_REQUEST['Tr_List']);
      
      if (isset($_REQUEST['JustList'])) echo fm_hidden('JustList',$_REQUEST['JustList']);
      echo "<input type=submit name=MORE value='Next batch " . ($EndAt-1) . "'>\n";
    } else {
      echo "All Done";
    }
  }

  dotail();
?>
