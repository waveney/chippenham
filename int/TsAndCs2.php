<?php
  include_once("fest.php");

  dostaffhead("Ts and Cs");
 
  global $USER,$USERID,$db,$PLANYEAR;

  A_Check('Committee'); // Will refine gate later
  
  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
      case 'COPYOLD':
        $OldTnC = Gen_Get('TsAndCs',1);
        foreach ($OldTnC as $F=>$V) {
          $T = ['Name'=> $F, 'Content'=>$V];
          Gen_Put('TsAndCs2',$T);
        }
        echo "Ts And Cs copied to new format<p>";
        dotail();
    }
  }
  
function Put_TsAndC(&$now) {
  global $db,$GAMEID;
  if (isset($now['id'])) {
    $Cur = Gen_Get('TsAndCs2',$now['id']);
    return Update_db('TsAndCs2',$Cur,$now);
  } else {
    return $now['id'] = Insert_db ('TsAndCs2', $now );
  }
}

  $TnC = Gen_Get_All('TsAndCs2');
  if (UpdateMany('TsAndCs2','Put_TsAndC',$TnC,1,'','','Name')) $TnC = Gen_Get_All('TsAndCs2');
  
  $coln = 0;
  echo "<form method=post action=TsAndCs2>";
  echo "<div class=tablecont><table id=indextable border width=100% style='min-width:1400px'>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th colspan=2><a href=javascript:SortTable(" . $coln++ . ",'T')>Content</a>\n";
  echo "</thead><tbody>";

  foreach ($TnC as $T) {
    $i = $T['id'];
    echo "<tr><td>$i";
    echo fm_text1('',$T,'Name',1,'','',"Name$i");
    echo "<td>" . fm_textarea('',$T,'Content',4,-4,'','',"Content$i");
  }
  $T = [];
  echo "<tr><td>" . fm_text1('',$T,'Name',1,'','',"Name0");
  echo "<td>" . fm_textarea('',$T,'Content',4,-4,'','',"Content0");
  
  if (Access('SysAdmin')) echo "<tr><td class=NotSide>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea>";  
  echo "</table></div><br>\n";

  echo "<input type=submit name=Update value=Update >\n";
  echo "</form></div>";
  
  dotail();
?>
