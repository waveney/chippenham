<?php
  include_once("fest.php");
  A_Check('SysAdmin');

  dostaffhead("Dunp Emails");
  include_once("Email.php");

  $ExcludePfx=['BB','LNL','Art','Lol','Stew'];
  
  $Profs = Gen_Get_All('EmailProformas');
  
  foreach ($Profs as $i=>$P) {
    if (preg_match('/^(.+?)_/',$P['SN'], $m)) {
      $pfx = $m[1];
      if (in_array($pfx,$ExcludePfx)) unset($Profs[$i]);
    } else unset($Profs[$i]);
  }
  
  file_put_contents('festfiles/DumpEmails.json',json_encode($Profs)); 
  echo "Emails Dumped<p>";
  dotail();
?>
