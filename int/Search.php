<?php
  include_once("fest.php");
  A_Check('Staff','Docs');

  dostaffhead("Document Search");
  global $db;

  include_once("DocLib.php");
/* Search Loc and Restrict
*/
  $table = 0;
  $lsted = array();
  if (isset($_REQUEST['Search'])) {
    $xtr = '';
    $from = $until = '';
    if ($_REQUEST['Who']) $xtr = ' AND Who=' . $_REQUEST['Who'];
    if ($_REQUEST['From']) $from = Extract_Date($_REQUEST['From']);
    if ($_REQUEST['Until']) $until = Extract_Date($_REQUEST['Until']);
    $targ = $_REQUEST['Target'];
    if (isset($_REQUEST['Titles']) || !isset($_REQUEST['Cont'])) {
      if ($from) $xtr .= " AND Created>$from ";
      if ($until) $xtr .= " AND Created<$until ";
      if ($_REQUEST['Search_Loc']) $xtr .= " AND Dir=" . $_REQUEST['Search_Dir'];
      $qry = "SELECT * FROM Documents WHERE SN COLLATE UTF8_GENERAL_CI LIKE '%$targ%' $xtr";
      $res = $db->query($qry);
      if ($res && $res->num_rows) {
        Doc_Table_Head(1);
        $table = 1;
        while($doc = $res->fetch_assoc()) { // Need Restrict
          Doc_List($doc,1);
          $lsted[$doc['DocId']]=1;
        }
      }
    }

    $greplst = [];
    if (isset($_REQUEST['Cont'])) {
      if ($_REQUEST['Search_Loc']) {
        $path = Dir_FullPName($_REQUEST['Search_Dir'],32);
        exec("grep -lir '" . $targ . "' Store/$path", $greplst);
      } else {
        exec("grep -lir '" . $targ . "' Store", $greplst);
      }
      if ($greplst) {
        foreach($greplst as $file) {
          $doc = Find_Doc_For($file);
          if (!$doc) continue;
//echo "from = $from until = $until now =" . time() . "doc= ". var_dump($doc) . "<P>";
          if ($_REQUEST['Who']) if ($doc['Who'] != $_REQUEST['Who']) continue;
          if ($from) if ($doc['Created'] < $from) continue;
          if ($until) if ($doc['Created'] > $until) continue;
          if (isset($lsted[$doc['DocId']])) continue;
          if (!$table) {
            Doc_Table_Head(1);
            $table = 1;
          }
          Doc_List($doc,1); // Need Restrict
          $lsted[$doc['DocId']]=1;
        }
      }
    }

    if($table) {
      echo "</tbody></table>\n";
    } else {
      echo "<h2>Not found</h2>\n";
    }
  }

  SearchForm();

  dotail();
?>

