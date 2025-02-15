<?php
  include_once("fest.php");
  A_Check('Committee','News');

  dostaffhead("Manage Front Page Articles",["js/dropzone.js","css/dropzone.css"]);
  global $Importance,$SHOWYEAR,$ArticleFormats;

  include_once("NewsLib.php");
  include_once("Uploading.php");
  include_once("DateTime.php");

//var_dump($_REQUEST);
  $Dates = array('StartDate','StopDate','RestartDate');

  if (isset($_REQUEST['ACTION'])) { /* Response to create/update button */
    Parse_DateInputs($Dates,1);

    if (isset($_REQUEST['Image']) && $_REQUEST['Image']) {
      $img = $_REQUEST['Image'];
      $mtch = [];
      if (preg_match('/^https?:\/\//i',$img)) {
        $stuff = getimagesize($img);
      } else if (preg_match('/^\/(.*)/',$img,$mtch)) {
        if (file_exists($mtch[1])) {
          $stuff = getimagesize($mtch[1]);
        } else {
          $stuff = [0,0];
        }
      } else {
        $stuff = getimagesize($img);
      }
      if ($stuff) {
        $_REQUEST['ImageWidth'] = $stuff[0];
        $_REQUEST['ImageHeight'] = $stuff[1];
      }
    }

    if ($_REQUEST['ACTION'] == 'UPDATE') {
      $id = $_REQUEST['id'];
      $Art = Get_Article($id);
      Update_db_post('Articles',$Art);
    } elseif ($_REQUEST['ACTION'] == 'STOP') {
      $id = $_REQUEST['id'];
      $Art = Get_Article($id);
      Update_db_post('Articles',$Art);
      $Art['StopDate'] = time() - 60;
      Put_Article($Art);
    } elseif ($_REQUEST['ACTION'] == 'START') {
      $id = $_REQUEST['id'];
      $Art = Get_Article($id);
      Update_db_post('Articles',$Art);
      $Art['StartDate'] = time();
      Put_Article($Art);
    } elseif ($_REQUEST['ACTION'] == 'COPY') {
      $id = $_REQUEST['id'];
      $Art = Get_Article($id);
      $_REQUEST['StartDate'] = time()+7*24*3600;
      unset($Art['id']);
      $_REQUEST['SN'] .= " COPY OF ";
      $id = Insert_db_post('Articles',$Art);
    } elseif ($_REQUEST['ACTION'] == 'DELETE') {
      $id = $_REQUEST['id'];
      db_delete('Articles',$id);
      include('ListArticles.php');
    } elseif ($_REQUEST['ACTION'] == 'REOPEN') {
      $id = $_REQUEST['id'];
      $Art = Get_Article($id);
      Update_db_post('Articles',$Art);
      $Art['StopDate'] = 0;
      $Art['StartDate'] = (isset($_REQUEST['RestartDate'])?$_REQUEST['RestartDate']:0);
      Put_Article($Art);
    } elseif ($_REQUEST['ACTION'] == 'CREATE') {
      if (empty($_REQUEST['StartDate'])) $_REQUEST['StartDate'] = time()+7*24*3600;
      $id = Insert_db_post('Articles',$Art);
    }
  } elseif (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
    $Art = Get_Article($id);
  } else {
    $id = -1;
    $Art = [];
  }

//  var_dump($Art);
  echo "To limit when article will appear give a start and/or end date.<p>Do NOT use a facebook image as a link - they are transient.<br>\n";
  echo "Set Title as @[Dance|Music| etc]_[Imp,Many] to have a random important Performer or a random performer along with a count<br>\n";
  echo "Set Title as @Perf 23 to do Performer 23, @Event will be used to highlight event - Not Implemented yet<p>\n";
  echo "The Banner formats go across the entire page<p>";
  echo "You <b>MUST</b> create an article before you can upload an image.<p>";

  echo "<form method=post>";
  if (isset($Art['id'])) Register_AutoUpdate('Articles',$Art['id']);
  echo "<div class=tablecont><table border>\n";
  echo "<tr>" . fm_text("Title",$Art,'SN',2);
    echo "<td>" . fm_checkbox('Hide Title',$Art,'HideTitle');
    echo fm_text1('Title Colour',$Art,'TitleColour',2) . " Black if not set";
//    echo "<td>" . fm_checkbox('Red Title',$Art,'RedTitle');
  echo "<tr>" . fm_text("Usage",$Art,'UsedOn');
/*    echo "<td colspan=2 rowspan=5>";
    if ( isset($Art['Image']) && $Art['Image']) {
      echo "<img src=" . $Art['Image'] . " height=200>";
    } else {
      echo "No Image";
    }*/
    if (isset($Art['id'])) echo "<td colspan=3 rowspan=4><table border><tr>" . fm_DragonDrop(1, 'Image','Article',$Art['id'],$Art) . "</table>";
  echo "<tr><td>Format:<td>" . fm_select($ArticleFormats,$Art,'Format');
  echo "<tr><td>Importance:<td>" . fm_select($Importance,$Art,'Importance');
  echo "<tr>" , fm_text("Relative Order",$Art,'RelOrder');
  echo "<tr>" . fm_date("Start Date",$Art,'StartDate') . fm_date("Stop Date",$Art,'StopDate');
  echo "<tr>" . fm_text("Link - may be blank",$Art,'Link') . "<td>" . fm_checkbox('Remote Link',$Art,'ExternalLink') . "<td>";
    if ($id > 0) echo fm_hidden('id',$id) . "id: $id";
    echo fm_text1("Image",$Art,'Image') . " Click Update if manually changed";
  echo "<tr>" . fm_textarea("Text:<br>(some html)", $Art,'Text',6,10);
  if (Access('SysAdmin')) echo "<tr><td class=NotSide>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea>";
  echo "</table></div>";

  if ($id > 0) {
    echo "<input type=submit name=ACTION value=UPDATE>";
    echo "<input type=submit name=ACTION value=STOP>";
    echo "<input type=submit name=ACTION value=DELETE>";
    echo "<input type=submit name=ACTION value=COPY>";
    echo "<input type=submit name=ACTION value=START>";

    if ($Art['StopDate']) {
      echo "<input type=submit name=ACTION value=REOPEN>" . fm_text1('on',$Art,'RestartDate');
    }
  } else {
    echo "<input type=submit name=ACTION value=CREATE> . Create this in the future, check it and only then bring the start date forward or remove it.";
  }

  echo "</form><p>\n";

  echo "<h2><a href=ListArticles>List Articles</a>, <a href=/index?F=7>Top Page in a Week</a></h2>\n";

  dotail();

?>
