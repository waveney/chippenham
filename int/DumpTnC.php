<?php
  include_once("fest.php");
  A_Check('SysAdmin');

  dostaffhead("Dunp Ts and Cs");
  $ExcludePfx=['BB','LNL','Art','Lol','Stew'];

  $Profs = Gen_Get_All('TsAndCs2');
  $m = [];

  foreach ($Profs as $i=>$P) {
    if (preg_match('/^(.+?)_/',$P['Name'], $m)) {
      $pfx = $m[1];
      if (in_array($pfx,$ExcludePfx)) unset($Profs[$i]);
    }
  }

  file_put_contents('festfiles/DumpTsNCs.json',json_encode($Profs));
  echo "Ts and Cs Dumped<p>";
  dotail();
?>
