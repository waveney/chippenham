<?php
  include_once("fest.php");
  A_Check('Committee','Photos');

  dostaffhead("Manage A Gallery");

/* List all photos with edit boxes and thumb nails
   comment a photo may be part of more than one gallery
   import from a directory all files
   no upload, remove file to remove from gallery - SN = filename??
*/


  include_once("ImageLib.php");
  include_once("TradeLib.php");
  $mtch = [];

// var_dump($_REQUEST);

  $ThumbSize = $_REQUEST['Thumb'] ?? 75;
  $Gals = Get_Gallery_Names(1);
  $Galid = (isset($_REQUEST['g'])? $_REQUEST['g']:0 );
  if (strlen($Galid) > 4) $Galid=0;
  $GalName = $Gals[$Galid];

  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
    case 'Move': // Move to other gallery
      $Tgt = $_REQUEST['MoveTo'];
      $Count = 0;
//echo "<p>EEP";
      foreach ($_REQUEST as $R=>$V) {
//echo "<p>doing $R<br>";
        if (preg_match('/Sel(\d*)/',$R,$mtch)) {
          $pid = $mtch[1];
          if ($Tgt) {
            $Photo = Get_Gallery_Photo($pid);
            $Photo['Galid'] = $Tgt;
            Put_Gallery_Photo($Photo);
          } else {
            db_delete('GallPhotos',$pid);
          }
          $Count++;
        }
      }
      if ($Tgt) {
        echo "Moved $Count to " . $Gals[$Tgt] . "<p>";
      } else {
        echo "Deleted $Count photos from Gallery - they are still stored on the server<p>";
      }

      break;

    case 'Copy': // Copy Photo in another gallery - source is used for both
      $Tgt = $_REQUEST['CopyTo'];
      if (!$Tgt) break;
      $Count = 0;
      foreach ($_REQUEST as $R=>$V) {
        if (preg_match('/Sel(\d*)/',$R,$mtch)) {
          $pid = $mtch[1];
          $Photo = Get_Gallery_Photo($pid);
          $Photo['Galid'] = $Tgt;
          unset($Photo['id']);
          Gen_Put('GallPhotos',$Photo);
          $Count++;
        }
      }
      echo "Copied $Count to " . $Gals[$Tgt] . "<p>";
      break;

    default:
      break;
    }

  }
  if (isset($_REQUEST['IMPORT'])) {
    $Prefix = $_REQUEST['FilePrefix'];
    $ImpLog = '';
    $ImpCount = 0;
    if (is_dir("../$Prefix")) { // Directory
      $handle = opendir("../$Prefix");
      while (false !== ($entry = readdir($handle))) {
        if (preg_match('/^\./',$entry)) continue;
        $suf = pathinfo($entry,PATHINFO_EXTENSION);
        $lcsuf = strtolower($suf);

        if ($lcsuf == 'jpg' || $lcsuf == 'jpeg' || $lcsuf == 'png') {
          $file = $Prefix . '/' . $entry;
          $strfile = $Prefix . '/' . pathinfo($entry,PATHINFO_FILENAME) . '.' . $lcsuf;
          Image_Convert($file,800,536,$strfile);
          if ($file != $strfile) unlink($file);
          if (!db_get('GallPhotos',"Galid=$Galid AND File='$strfile'")) { // not already in gallery
            $dat = array('File'=>$strfile,'Galid'=>$Galid);
              Insert_db('GallPhotos',$dat);
            $ImpCount++;
          } else {
            $ImpLog .= "Ignoring $strfile - already in Gallery<br>";
          }
        }
      }
      closedir($handle);
    } else { // Just a prefix
      $globs = glob("../$Prefix*");
      foreach($globs as $fil) {
        $file = preg_replace('/^\.\.\//','',$fil);
        echo "Importing $file<br>";

        if (is_file($fil)) {
          $suf = pathinfo($file,PATHINFO_EXTENSION);
          if ($suf == 'jpg' || $suf == 'jpeg' || $suf == 'png') {
            if (!db_get('GallPhotos',"Galid=$Galid AND File='$file'")) { // not already in gallery
              $dat = array('File'=>$file,'Galid'=>$Galid);
                Insert_db('GallPhotos',$dat);
              $ImpCount++;
            } else {
              $ImpLog .= "Ignoring $file - already in Gallery<br>";
            }
          }
        } else if (is_dir($fil)) { // Globbed dir
          $dir = $file;
          $handle = opendir("../$dir");
          while (false !== ($entry = readdir($handle))) {
            if (preg_match('/^\./',$entry)) continue;
            $suf = pathinfo($entry,PATHINFO_EXTENSION);
            if ($suf == 'jpg' || $suf == 'jpeg' || $suf == 'png') {
              $file = $dir . '/' . $entry;
              if (!db_get('GallPhotos',"Galid=$Galid AND File='$file'")) { // not already in gallery
                $dat = array('File'=>$file,'Galid'=>$Galid);
                  Insert_db('GallPhotos',$dat);
                $ImpCount++;
              } else {
                $ImpLog .= "Ignoring $file - already in Gallery<br>";
              }
            }
          }
          closedir($handle);
        } else {
          $ImpLog .= "Don't know what to do with $file<br>";
        }
      }
    }

    echo "Imported $ImpCount files<br>";
    if ($ImpLog) echo $ImpLog;
    echo "<p>\n";
  }

  $Gallery = Gen_Get('Galleries',$Galid);
  echo "<h2>Manage Gallery - $GalName</h2>\n";
  echo "<form method=post action=GallCManage>";
    Register_AutoUpdate('Galleries',$Galid);
    echo fm_hidden('g',$Galid);
    echo "<h3>Import Photos</h3>Give Name of Directory (All Images will be imported) or Full Prefix (Any File with that Prefix will be imported): ";
    echo fm_textinput('FilePrefix',isset($_REQUEST['FilePrefix'])?$_REQUEST['FilePrefix']:"");
    echo "<input type=submit name=IMPORT value=Import>";
    echo "</form><p>";

  echo "<h3>Current Gallery</h3>\n";
  echo "<table border>";
  echo "<tr>" . fm_textarea("Description",$Gallery,'Description',6,3);
  echo "<tr>" . fm_text('Credits',$Gallery,'Credits',4);
  echo "<tr>" . fm_text('Image Number',$Gallery,'Image') . fm_text('Banner',$Gallery,'Banner') ;
  echo "<td>" . fm_number('Level',$Gallery,'Level') . fm_text('Gallery Set',$Gallery,'GallerySet');
  if (Access('SysAdmin')) {
    echo "<tr><td class=NotStaff>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea><p><span id=DebugPane></span>";
  }


  echo "</table>";

  Cancel_AutoUpdate();  // REst is a different form handled by Updatemany
  $Gal = Get_Gallery_Photos($Galid);
  if (UpdateMany('GallPhotos','Put_Gallery_Photo',$Gal,0)) $Gal = Get_Gallery_Photos($Galid);

  echo "If used Order controls the order of pictures appearing, two pictures of the same Order value may appear in any order.<p>\n";
  echo "To delete a photo move the entry to the blank gallery.<p>\n";

  $coln = 0;

//  $GalSize = count($Gal);

  echo "<form method=post action=GallCManage>";

//  $Page = 0;
//  if (isset($_REQUEST['Page'])) $Page = $_REQUEST['Page'];
//  echo fm_hidden('Page',$Page);


  echo fm_hidden('g',$Galid);
  echo "<div class=Scrolltable><table id=indextable border>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Sel</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Seq</a>\n";
  echo "<th colspan=2><a href=javascript:SortTable(" . $coln++ . ",'T')>File</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Caption</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Order</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Thumbnail</a>\n";
  echo "</thead><tbody>";

//  $GCount = $Skip = 0;
  $Seq = 0;
  foreach($Gal as $g) {
//    if ($Page > 0 && ($Skip < ($Page*100)) continue;
    $i = $g['id'];
    echo "<tr><td>" . fm_checkbox('',$g,'Select','',"Sel$i") . "<td>$i<td>" . $Seq++;
    echo fm_text1("",$g,'File',2,'','',"File$i") . "</a>";
    echo fm_text1("",$g,'Caption',1,'','',"Caption$i") . "</a>";
    echo fm_number1("",$g,'RelOrder','','',"RelOrder$i") . "</a>";
    echo fm_hidden("Galid$i",$Galid);
    echo "<td><img src=\"" . $g['File'] . "\" height=$ThumbSize>";
    echo "\n";
  }
  echo "<tr><td><td><td><input type=text name=File0 >";
  echo "<td><input type=text name=Captions0 >";
  echo "<td><input type=number name=RelOrder0 >";
  echo "</table></div>\n";
  echo "<input type=submit name=Update value=Update>\n";
  echo "<input type=submit name=ACTION value=Move> selected to: " . fm_select($Gals,$_REQUEST,'MoveTo',1);
  echo "<input type=submit name=ACTION value=Copy> selected to: " . fm_select($Gals,$_REQUEST,'CopyTo',1);
  echo "</form>";
  echo "<h2><a href=GallManage>Back to Galleries</a>, <a href=ShowGallery?g=$Galid>Show Gallery</a></h2><p>\n";

  dotail();

?>
