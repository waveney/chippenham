<?php
  include_once("fest.php");

  dostaffhead("Youth Consent");
  
//  var_dump($_REQUEST);
  $Con = [];
  
  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
      
      case 'Submit':
        echo "Here<p>";
        Insert_db_post('Consent',$Con);
    }
  }
  
  
  if (isset($_REQUEST['id'])) {
    $Cid = $_REQUEST['id'];
    $Con = Gen_Get('Consent',$Cid );
  } else {
    $Cid = 0;
    $Con = [];
  }
  
  echo "<form method=post action=YouthConsent>";
  if ($Cid) {
    Register_AutoUpdate('Consent', $Cid);
    echo fm_hidden('id',$Cid);
  }
  
  
  echo "Registration Form – before leaving your under 18 year old young person at the workshop";
  
  echo "<table border>";
  echo "<tr>" . fm_text("Young Person's full name",$Con,'YoungName',3);
  echo "<tr>" . fm_number("Age",$Con,'Age');
  echo "<tr>" . fm_text("Postcode",$Con,"PostCode",3);
  echo "<tr>" . fm_text("Parent /Guardian’s full name",$Con,"ParentName",3);
  echo "<tr>" . fm_text("Relationship to Young Person",$Con,"Relationship",3);
  echo "<tr>" . fm_text("Email address (parent /guardian)",$Con,'Email',3);
  echo "<tr>" . fm_text("Contact phone number (parent /guardian)",$Con,'ParentPhone',3);
  echo "<tr>" . fm_text("Alternative Emergency Contact Name",$Con,'AltNAme',3);
  echo "<tr>" . fm_text("Alternative Relationship to Young Person",$Con,"AltRelationship",3);
  echo "<tr>" . fm_text("Alt Contact phone number (parent /guardian)",$Con,'AltPhone',3);
  echo "<tr>" . fm_text("Access Needs",$Con,"Mobility",3);
  echo "<tr>" . fm_text("Medical Conditions",$Con,"Medical",3);
  echo "<tr><td colspan=4>" . fm_checkbox("You give us permission to produce
recordings including photographs
during sessions for marketing
including online and social media",$Con,'PhotoConsent');
  echo "<tr><td colspan=4>" . fm_checkbox("You acknowledge that it is the parent
/guardian’s responsibility to update us
of any change in contact details after
completing this form",$Con,'PhotoConsent');
  echo "</table>";
  if (!$Cid) {
    echo fm_submit('ACTION','Submit');
  }
  
  dotail();
?>
