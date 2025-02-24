<?php
// Edit Feature info, dump results to data stored in files - not part of the database, needs to updated as the code is updated

include_once ("fest.php");

global $FHelp;

function Get_Feature_Help() {
  global $FHelp;
  
  $FHelp = Gen_Get_All('FeatureHelp','ORDER BY FeatureGroup, Priority DESC');
  
}

function Save_Feature_Help() { 
  global $FHelp;
  
  $FH = json_encode($FHelp);
  file_put_contents('files/FeatureHelp.json',$FH);
}

function Display_FeatureHelp() {
  global $FHelp;
  $coln = 0;
  Register_AutoUpdate('Generic',0);
  echo "<form method=post><div class=tablecont><table id=indextable border width=800 style='min-width:800'>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'n')>id</a>\n";
  echo "<th colspan=2><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Group</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Priority</a>\n";
  echo "<th colspan=2><a href=javascript:SortTable(" . $coln++ . ",'T')>Default</a>\n";
  echo "<th colspan=4><a href=javascript:SortTable(" . $coln++ . ",'T')>Explanation</a>\n";  
  echo "</thead><tbody>";
  
  foreach($FHelp as $i=>$F) {
    echo "<tr><td>$i" . fm_text1('',$F,'Name',2,'','',"FeatureHelp:Name:$i");
    echo fm_text1('',$F,'FeatureGroup',1,'','',"FeatureHelp:FeatureGroup:$i");
    echo fm_number1('',$F,'Priority','','',"FeatureHelp:Priority:$i");
    echo fm_text1('',$F,'DefaultValue',2,'','',"FeatureHelp:DefaultValue:$i");
    echo "<td>" . fm_basictextarea($F,'Explanation',4,2,'',"FeatureHelp:Explanation:$i");
    
  }
  $F = [];
  $i = 0;
  echo "<tr><td>$i" . fm_text1('',$F,'Name',2,'','',"FeatureHelp:Name:$i");
  echo fm_text1('',$F,'FeatureGroup',1,'','',"FeatureHelp:FeatureGroup:$i");
  echo fm_number1('',$F,'Priority','','',"FeatureHelp:Priority:$i");
  echo fm_text1('',$F,'DefaultValue',2,'','',"FeatureHelp:DefaultValue:$i");
  echo "<td>" . fm_basictextarea($F,'Explanation',4,2,'',"FeatureHelp:Explanation:$i");
  echo "<tr><td class=NotSide>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea>";
  
  
  echo "</table></div>";

  echo fm_submit('Update','Update');
  echo fm_submit('ACTION','Save') . " Use this after a batch of changes.";
  echo "</form>";
}
// Start HERE

dostaffhead('Edit Feature Help');

Get_Feature_Help();

if (isset($_REQUEST['Update'])) {
  if (UpdateMany('FeatureHelp','',$FHelp,1,'','','Name','RemoveMe')) Get_Feature_Help();
}
// function UpdateMany($table,$Putfn,&$data,$Deletes=1,$Dateflds='',$Timeflds='',$Mstr='SN',$MstrNot='',$Hexflds='') {
  

if (isset($_REQUEST['ACTION'])) {
  switch ($_REQUEST['ACTION']) {
    case 'Import':
      $TestOnly = $_REQUEST['TestFull']??0;
      $F = fopen($_FILES["CSVfile"]["tmp_name"],"r");
      $headers = fgetcsv($F);
      foreach($headers as $i=>$d) $hindx[$d] = $i;
      
      while (($bts = fgetcsv($F)) !== FALSE) {
        if (!$bts[0]) continue;
        $stuff=[];
        foreach ($headers as $i=>$d) $stuff[$d] = $bts[$i];
        //var_dump($bts); var_dump($stuff); echo "<p>";
        $rec = [];
        $rec['Name'] = $stuff['Feature'];
        $rec['FeatureGroup'] = $stuff['Group'];
        $rec['DefaultValue'] = $stuff['Default'];
        $rec['Explanation'] =  $stuff['Explanation'];
        
        if (!$TestOnly) Insert_db('FeatureHelp',$rec);
      }
      echo "All imported";
      Get_Feature_Help();
      break;

    case 'Save':
      echo "Saving Data<p>";
      Save_Feature_Help();
      echo "Data Saved<p>";
      break;
  }
} 

Display_FeatureHelp();
  
if (!$FHelp) {
  echo "<h1>No Feature Help Found</h1>";
  echo '<div class="content"><h2>Import Basic Data From CSV</h2>';
  echo '<form method=post enctype="multipart/form-data">';
  echo "<input type=file name=CSVfile><br>";
//  echo "Test Only: <input type=checkbox name=TestFull checked><br>";
  echo "<input type=submit name=ACTION value=Import><br></form>\n";
}

dotail();
