<?php
  include_once("fest.php");
// Set a temp cookie on IP address that will pass validation later, if wmffd not set
  dostaffhead("Trader Application",["/js/Participants.js","js/dropzone.js","css/dropzone.css"]);

  include_once("TradeLib.php");
  include_once("PitchMap.php");

  global $USER,$USERID,$db;
  global $Access_Type;

  echo "<div class=content>";
// If access then edit trader info
// If not ask are you a previous trader -> give email to give direct edit link, if found otherwise new trader
// else new Trader - note if new check old records...
  if (isset($_REQUEST['Email'])) {
    $qry = "SELECT * FROM Trade WHERE Email LIKE '%" . $_REQUEST['Email'] . "%' ORDER BY Tid DESC";
    $res = $db->query($qry);
    if ($res->num_rows == 0) {
      echo "<h3>Sorry, that email address is not in the database.</h3>";
      echo "<form >";
      echo "Try Again:" . fm_text1('',$_REQUEST,'Email');
      echo "<input type=submit name=go value=Go>";
      echo "</form>\n";
      echo "Or<p>";
      echo "<h3><a href=Trader?NEW>Please register as a new trader</a></h3>";
//      echo "<h3>THIS HAS A BUG AT THE MOMENT PLEASE TRY LATER</h3>";
      dotail();
      exit;
    }
    $Trad = $res->fetch_assoc();
    if (!isset($Trad['AccessKey']) || strlen($Trad['AccessKey'] != 40)) {
      $Trad['AccessKey'] = rand_string(40);
      Put_Trader($Trad);
    }

    Send_Trader_Simple_Email($Trad,'Trade_Link');
    echo "<h3>A direct link has been emailed to you, you can use this at anytime to book, update your records, ";
    echo "see details of your pitch(es) and other usefull information.</h3>";
    dotail();
    exit;
  }

  if (isset($_REQUEST['NEW'])) {
    Trade_Main(0,'TraderPage',-1);
    dotail();
    exit;
  }

  if (($USER['AccessLevel']??0) == $Access_Type['Participant']) {
    $Tid = $USERID;
    Trade_Main(0,'TraderPage',$Tid);
  } else {
    echo "<h3>Have you filled in an application form for trading at the " . Feature('FestName') . "?</h3>";
    echo "<form >";
    echo "If so please give your email address:" . fm_text1('',$_REQUEST,'Email');
    echo "<input type=submit name=go value=Go>";
    echo "</form>\n";
    echo "And we will email you a link to directly book again and/or edit your details.<p>";

    echo "<h3><a href=Trader?NEW>If not please register as a new trader</a></h3>";
//    echo "<h3>THIS HAS A BUG AT THE MOMENT PLEASE TRY LATER</h3>";
  }
  dotail();

?>
