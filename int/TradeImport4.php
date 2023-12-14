<?php
  include_once("fest.php");
  A_Check('SysAdmin');
  dostaffhead('Import Old Trade Data');
  global $YEAR,$db;
  include_once("TradeLib.php");
  include_once("DateTime.php");

  $TradTypes = Get_Trade_Types();
  $TradType = array_flip($TradTypes);

  $Oldstate = array('confirmed'=>3,'unconfirmed'=>1,'paid'=>5,'deposit'=>4,'declined'=>2,'denied'=>2);

  $Ttype = 1;
  if (!isset($_FILES['CSVfile'])) {
    echo '<div class="content"><h2>Import Trader Data From ATM CSV</h2>';
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
      $stuff=array();
      $brack='';
      foreach ($headers as $i=>$d) $stuff[$d] = $bts[$i];
//var_dump($bts);
    $rec = array();
    $yr = array();
    $rec['TradeType'] = $stuff['Type'];
    if (!empty($stuff['Email'])) {
      $em = $stuff['Email'];
      $res = "SELECT * FROM Trade WHERE Email LIKE '%$em%'";
      $q = $db->query($res);
      if ($q->num_rows) {
	$orec = $q->fetch_assoc();
	if ($orec['Previous'] == 0) {
	  $orec['Previous'] = 1;
	  Put_Trader($orec);
	  echo "Updated " . $orec['SN'] . "<br>";
	} else {
	  echo "Trader " . $stuff['Trading Name'] . " already in database.<br>";
	}
      } else {
	$rec['SName'] = $stuff['Trading Name'];
	$rec['Contact'] = $stuff['First Name'] . ' ' . $stuff['Last Name'] ;
	$rec['Email'] = $stuff['Email'];
	$rec['Website'] = $stuff['Web'];
	$rec['Address'] = $stuff['Address'];
	$rec['PostCode'] = $stuff['Post Code'];
	$rec['GoodsDesc'] = $stuff['Trade Type'];
//        if (preg_match('/^07/',$stuff['Contact No.'])) {
          $rec['Mobile'] = $stuff['SMS OR Landline'];
//        } else {
//          $rec['Phone'] = $stuff['Contact No.'];
//        }
        $rec['GoodsDesc'] = $stuff['Trade Type'];
 //       $rec['Previous'] = 1;

/* 
        if (!$TestOnly) $Tid = Insert_db('Trade',$rec);
        $yr['Tid'] = $Tid;
        $yr['Year'] = 20;
        $yr['PitchSize0'] = $stuff['Pitch Size'];

        $yr['BookingState'] = 5;
        if (!$TestOnly) $TYid = Insert_db('TradeYear',$yr);

/*
echo $rec['SName'] . " ";
var_dump($rec);
var_dump($yr);
echo "<p>";
*/
      if ($TestOnly) {
        echo "Would Add " . $rec['SName'] . "<br>";      
      } else {
        Gen_Put('Trade',$rec);
        echo "Added " . $rec['SName'] . "<br>";
      }
      }
/*    } else {
      if ($stuff['Activity Type'] == 'Food') $Ttype = 2;
      if ($stuff['Activity Type'] == 'Non-Food') $Ttype = 1;
      if ($stuff['Activity Type'] == 'Artisan') $Ttype = $TradType['Artisan'];
      if ($stuff['Activity Type'] == "Children's Activities") $Ttype = $TradType["Children's Activities"];
      if ($stuff['Activity Type'] == 'Charity Sunday') $Ttype = $TradType['Local Charity'];
      if ($stuff['Activity Type'] == 'Face Painting') $Ttype = $TradType['Face Painting'];
      if ($stuff['Activity Type'] == 'Street Pedlars') $Ttype = $TradType['Street Pedlars'];
      echo "Ignoring (for now) : " . $stuff['Activity Type'] . "<br>";*/
    }


  }
  }

  dotail();

?>
