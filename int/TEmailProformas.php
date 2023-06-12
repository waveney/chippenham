<?php
  include_once("fest.php");
  A_Check('Staff');

  dostaffhead("Manage Email Proformas");

  include_once("Email.php");
  
function Edit_Proforma(&$Proforma) {
  if (empty($Proforma['id'])) {
    echo "<h2>Create New Email Proforma</h2>";
  } else {
    echo "<h2>Edit Email Proforma</h2>";
  }
  echo "The Prefix of a name (the bit before the first _) has to have set values, do not introduce new ones without consulting Richard " .
        "(They have to match a capability)<p>";  
  echo "<form method=post action=TEmailProformas><table border>\n";
  if (!empty($Proforma['id'])) {
    Register_Autoupdate('EmailProformas',$Proforma['id']);
    echo "<tr><td>ID:<td>" . $Proforma['id'] . fm_hidden('i',$Proforma['id']) ;
  }
  
  echo "<tr>" . fm_text('Name',$Proforma,'SN',4);
  echo "<tr>" . fm_textarea('Message',$Proforma,'Body',4,30);
  echo "</table>\n";
  
  if (empty($Proforma['id'])) {
    echo "<input type=submit name=ACTION value=Create>\n";
  } else {
    echo "<input type=submit name=ACTION value=Delete>\n";  
  }
  
  echo "<h2><a href=TEmailProformas>Back to the list</a></h2>";
  dotail();
}
  
 
  $prefixes = ['BB'=>Capability("EnableMisc"),'Dance'=>Capability('EnableDance'),'Finance'=>Capability('EnableFinance'),'LNL'=>Capability("EnableMisc"),'Login'=>1,
               'Trade'=>Capability("EnableTrade"),'lol'=>Capability("EnableMisc"), 'Stew'=>Capability("EnableOldVols"),'Vol'=>Capability("EnableVols"),
               'Invoice'=>(Capability('EnableFinance') || Capability('EnableTrade')), 'ART'=>Capability('EnableArt'), 'Music'=>Capability('EnableMusic')];
  
  Replace_Help();
  echo "<P>";

  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
    case 'New':
      $Prof = [];
      Edit_Proforma($Prof);
      break;
    case 'Create':
    // validate Prefix TODO
    
      $Prof = [];
      Insert_db_post('EmailProformas', $Prof);
      echo "<h2>Proforma created</h2>\n";
      
      Edit_Proforma($Prof);
      break;
    case 'Delete':
      $Pid = $_REQUEST['i'];
      $Prof = Get_Email_Proforma($Pid);
      
      db_delete('EmailProformas',$Pid);
      echo "<h2>Proforma: " . $Prof['SN'] . " deleted.</h2>\n";
      break;
      
    case 'Edit':
      $Pid = $_REQUEST['i'];
      $Prof = Get_Email_Proforma($Pid);
      Edit_Proforma($Prof);
      break;
    }
  }

  echo "<div class='content'><h2>Manage Email Proformas</h2>\n";

  $Edit = Access('SysAdmin');
     
  echo "These are the proforma messages.  You cannot change them (too many problems in the past), email changes to Richard/SysAdmin.<p>";

  fm_addall('disabled readonly');
  $Pros=Get_Email_Proformas(1);

  $coln = 0;
//  echo "<form method=post action=TEmailProformas>";
  echo "<div class=tablecont><table id=indextable border>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Index</a>\n";
  echo "<th colspan><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Body of Message</a>\n";
  echo "</thead><tbody>";
  if ($Pros) foreach($Pros as $t) {
    $nam = $t['SN'];
    preg_match('/(.*?)_/',$nam,$res);
    if (!isset($prefixes[$res[1]])) {
      echo "Message $nam has an unknown prefix - Richard can fix<p>";
      continue;
    }
    if (!Access('Internal') && (!isset($res[1]) || !$prefixes[$res[1]])) continue;
    $i = $t['id'];
    echo "<tr><td>$i<td>" . ($Edit?"<a href=TEmailProformas?ACTION=Edit&i=$i>":'') . $t['SN'] . ($Edit?'</a>':'');
    echo "<td>" . fm_basictextarea($t,'Body',6,3); // ,'',"Body$i");
    echo "\n";
  }
/*  if ($Edit) {
    echo "<tr><td><td colspan=2><input type=text name=SN0 size=32>";
    echo "<td><textarea name=Body0 rows=6 cols=120></textarea>";
  } */
  echo "</table></div>\n";
  if ($Edit) echo "<h2><a href=TEmailProformas?ACTION=New>New Proforma</a></h2>"; // "<input type=submit name=Update value=Update>\n";
  
//  echo "</form>";
  echo "</div>";

  dotail();

?>
