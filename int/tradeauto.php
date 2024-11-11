<?php
// Set fields in data
include_once("fest.php");
include_once("TradeLib.php");

global $Trade_State, $Trade_States;

$Tid = $_REQUEST['I'];
$Action = $_REQUEST['A'];


//echo "In Setfields";
//var_dump($_REQUEST);

switch ($Action) {

case 'RQ':
  $Trad = Get_Trader($Tid);
  $Trady = Get_Trade_Year($Tid);
  if ( ($Trady['PitchSize0'] != ($Trady['QuoteSize0']??'')) ||
       ($Trady['PitchSize0'] != ($Trady['QuoteSize0']??'')) ||
       ($Trady['PitchSize0'] != ($Trady['QuoteSize0']??''))) {
//    echo "Requotingi<p>";
    $Trady['BookingState'] = $Trade_State['Requote'];
    Put_Trade_Year($Trady);
    Send_Trade_Admin_Email($Trad,$Trady,'Trade_Changes');
    echo 'ReQuote';
  } else {
    echo $Trade_States[$Trady['BookingState']];
  }

  exit;

default:
  exit;
}
