<?php
  include_once("fest.php");

  dostaffhead("Ts and Cs");
 
  global $USER,$USERID,$db,$PLANYEAR;

  A_Check('Committee'); // Will refine gate later
  
  $TnC = Gen_Get('TsAndCs',1);
  Register_AutoUpdate('TsAndCs',1);
  
  echo "<table border>";
  echo "<tr>" . fm_textarea('Trade T&Cs',$TnC,'TradeTnC',4,4);
  echo "<tr>" . fm_textarea('Trade FAQ',$TnC,'TradeFAQ',4,4);  
  echo "<tr>" . fm_textarea('Trade Times',$TnC,'TradeTimes',4,4);
  echo "<tr>" . fm_textarea('Volunteer T&Cs',$TnC,'VolTnC',4,4);
  echo "<tr>" . fm_textarea('Perf T&Cs',$TnC,'PerfTnC',4,4);
  echo "<tr>" . fm_textarea('Ticket T&Cs',$TnC,'TicketTnC',4,4);

  echo "<tr>" . fm_textarea('Dance FAQ',$TnC,'DanceFAQ',4,4);
  echo "<tr>" . fm_textarea('Music FAQ',$TnC,'MusicFAQ',4,4);
  echo "<tr>" . fm_textarea('Dummy Contract',$TnC,'DummyContract',4,4);
  echo "<tr>" . fm_textarea('Camping General',$TnC,'CampGen',4,4);
 
  if (Access('SysAdmin')) echo "<tr><td class=NotSide>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea>";  
  echo "</table><br>\n";
  
  dotail();
?>
