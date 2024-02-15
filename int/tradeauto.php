<?php 
// Set fields in data
include_once("fest.php");
include_once("TradeLib.php");

$Tid = $_GET['I'];
$Action = $_GET['A'];

//echo "In Setfields";
//var_dump($_GET);

switch ($Action) {

case 'RQ':
  $Trad = Get_Trader($Tid);
  $Trady = Get_Trade_Year($Tid);
//var_dump($Trady);
  if ( ($Trady['PitchSize0'] != ($Trady['QuoteSize0']??'')) || 
       ($Trady['PitchSize0'] != ($Trady['QuoteSize0']??'')) ||  
       ($Trady['PitchSize0'] != ($Trady['QuoteSize0']??''))) {
    $Trady['BookingState'] = $Trade_State['Requote'];
    Put_Trade_Year($Trady);
    Send_Trade_Admin_Email($Trad,$Trady,'Trade_Changes');
    return 'ReQuote';
  }
  return $Trade_States[$Trady['BookingState']];

default:
}
