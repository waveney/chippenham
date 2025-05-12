<?php
// Send Bespoke email bassed on a proforma email Finance Version
include_once("fest.php");
include_once("InvoiceLib.php");
include_once("Email.php");
global $PLANYEAR,$CONF;

A_Check("Staff","Finance");

$id = $_REQUEST['id'];

//$label = (isset($_REQUEST['L'])?$_REQUEST['L']:"");

$inv = Get_Invoice($id);
$subject = Feature('FestName') . " $PLANYEAR and " . $inv['BZ'];
$Mess = (isset($_REQUEST['Message'])?$_REQUEST['Message']:$inv['CoverNote']);
if (strlen($Mess) >40) {
  $inv['CoverNote'] = $Mess;
} else {
  $Mess = (Get_Email_Proforma($Mess))['Body'];
  if (!$Mess) {
    dostaffhead("Problem...");
    echo "<h2 class=err>Proforma " . $_REQUEST['Message'] . " not found</h2>";
    dotail();
  }
}

if (isset($_REQUEST['CANCEL'])) {  echo "<script>window.close()</script>"; exit; }
if (isset($_REQUEST['SEND'])) {
  $too = [['to',$inv['Email'],$inv['Contact']],
          ['from','Finance@' . Feature('HostURL'),Feature('ShortName') . ' Finance'],
          ['replyto','Finance@' . Feature('HostURL'),Feature('ShortName') . ' Finance']];
  $pdf = Get_Invoice_Pdf($id,'',$inv['Revision']);
  echo Email_Proforma(EMAIL_INVOICE,$inv['SourceId'], $too,$Mess,$subject,'Invoice_Email_Details',$inv,$logfile='Invoices',$pdf);

  $inv['EmailDate'] = time();
  Put_Invoice($inv);
  echo "<script>window.close()</script>";
  exit;
}

if (isset($_REQUEST['SAVE'])) {
  Put_Invoice($inv);
}

dominimalhead("Email for " . $inv['BZ'],["cache/FestStyle.css","css/festconstyle.css"]);
echo "<h2>Email for " . $inv['BZ'] . " - " . $inv['Contact'] . "</h2>";
if (isset($_REQUEST['PREVIEW'])) {
  echo "<p><h3>Preview...</h2>";
  $MessP = $Mess;
  Parse_Proforma($MessP,$helper='Invoice_Email_Details',$inv);
  echo "<div style='background:white;border:2;border-color:blue;padding:20;margin:20;width:90%;height:50%;overflow:scroll' >$MessP</div>";
}
echo "<h3>Edit the message below, then click Preview, Send or Cancel</h3>";
echo "Put &lt;p&gt; for paras, &lt;br&gt; for line break, &lt;b&gt;<b>Bold</b>&lt;/b&gt;, &amp;amp; for &amp;, &amp;pound; for &pound; <p> ";

echo "<form method=post>" . fm_hidden('id',$id) . fm_hidden('ACTION','BESPOKE');// . fm_hidden('L',$label);
echo "<div style='width:90%;height:70%'>
      <textarea name=Message id=OrigMsg style='background:white;border:2;border-color:blue;padding:20;margin:20;width:100%;height:100%'
       onchange=UpdateHtml('OrigMsg','ActMsg'))>" .  htmlspec($Mess) . "</textarea></div><p><br><p>\n";

echo " <input type=submit name=PREVIEW value=Preview> <input type=submit name=SEND value=Send> <input type=submit name=SAVE value=Save> <input type=submit name=CANCEL value=Cancel><p>\n";

echo "</form><p>";

?>
