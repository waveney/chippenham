<?php
  include_once("fest.php");
  A_Check('SysAdmin');
  dostaffhead('Import Volunteer Data');
  global $YEAR,$db;
  include_once("VolLib.php");
  include_once("DateTime.php");


function VolImp_Details($key,&$vol) {
  global $VolCats,$CatStatus;
  switch ($key) {
  case 'WHO': return firstword($vol[0]);

  case 'LINK' : 
    $Data = base64_encode(json_encode($vol));
    return "<a href='https://" . $_SERVER['HTTP_HOST'] . "/int/Volunteers?A=NS2&data=$Data'><b>link</b></a>";
  }
}

function EmailImp_Volunteer(&$vol,$messcat,$whoto) {
  global $PLANYEAR,$USER;
  Email_Proforma(5,0,$whoto,$messcat,Feature('FestName') . " $PLANYEAR and " . $vol[0] . ' ' . $vol[1],'VolImp_Details',$vol,'Volunteer.txt');
}

  $OldVols = Gen_Get_All('Volunteers');

  if (!isset($_FILES['CSVfile'])) {
    echo '<div class="content"><h2>Import Volunteer data</h2>';
    echo '<form method=post enctype="multipart/form-data">';
    echo "<input type=file name=CSVfile><br>";
    echo "Test Only: <input type=checkbox name=TestFull checked><br>";
    echo "<input type=submit name=Import value=Import><br></form>\n";
  } else {
    $TestOnly = $_POST['TestFull'] ?? 0;
    $F = fopen($_FILES["CSVfile"]["tmp_name"],"r");
    $headers = fgetcsv($F);
    foreach($headers as $i=>$d) $hindx[$d] = $i;

    while (($bts = fgetcsv($F)) !== FALSE) {
      $First = $bts[0];
      $Secnd = $bts[1];
      $email = trim($bts[2]);
      foreach($OldVols as $id=>$Vol) 
        if ($Vol['Email'] == $email) {
          echo "$First $Secnd $email is already in the database<br>";
          continue;
        }

      if ($TestOnly) {
        echo "Would Add $First $Secnd $email<br>";
        continue;
      }
      
      EmailImp_Volunteer($bts,'Vol_OlderVol',$email);
    }      
  }

  echo "<h2>All Done</h2>";
  dotail();

?>
