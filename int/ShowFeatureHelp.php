<?php
include_once("fest.php");


dostaffhead("Festival Data Settings Help");
 
$FHelp = Gen_Get_All('FeatureHelp','ORDER BY FeatureGroup, Priority DESC');

if (!file_exists("files/FeatureHelpUpdate") || filemtime('files/FeatureHelp.json') > filemtime('files/FeatureHelpUpdate')) {
  
  foreach ($FHelp as $F) db_delete('FeatureHelp',$F['id']);
  
  $FH = file_get_contents('files/FeatureHelp.json');
   
//  echo "Data read<p>";
  
  $FHelpNew = json_decode($FH,1);
  
//  var_dump("Json", $FHelpNew); 
  
  foreach ($FHelpNew as $F) {
    unset($F['id']);
    Gen_Put('FeatureHelp',$F);
  }
    
  file_put_contents('files/FeatureHelpUpdate',time());
  
  $FHelp = Gen_Get_All('FeatureHelp','ORDER BY FeatureGroup, Priority DESC');
}
  
  echo "<h1>Help on System Features</h1>";
  echo "These control lots of aspects to the system.<p>Most are set at the system level.<p>Some can be set in the Festival year data.<p>" .
    "Anything set at the year, overrides the global value.<p>Many (All?) of the data set up in festival year will move to this format eventually.<p>" .
    "Data is in php.ini format.  Comments start with ';'.<br>" .
    "All entries are of the form: name = value on each line, use quotes if complex values.<br>" .
    "Some data can be an array that has the form: name[] = value.<p>" .
    "You MUST click UPDATE after System Data changes.  This is crutial data, if screwed everything may fail - you have been warned.<p>";
  
  $coln = 0;
  echo "<div class=tablecont><table id=indextable border width=800 style='min-width:800'>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Group</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Default</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Explanation</a>\n";
  echo "</thead><tbody>";
  
  foreach($FHelp as $i=>$F) {
    echo "<tr><td>" . $F['Name'] . "<td>" . $F['FeatureGroup'] . "<td>" . $F['DefaultValue'] . "<td>" . $F['Explanation'];
  }
  
  echo "</table></div>";

  dotail();
