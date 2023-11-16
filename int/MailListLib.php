<?php
  include_once("fest.php");

  include_once("Email.php");

  global $USER,$USERID,$db;
  
$MailStatus = ['','Request','Email Confirmed','Accepted','Rejected'];
  
function MailForm($Sub=0,$Message='') {
  if ($Message) echo "$Message<p>\n";
  echo "<form method=post action=MailList.php>";
  if (isset($Sub['id'])) {
    $Subid = $Sub['id'];
    Register_AutoUpdate('MailingListRequest',$Subid);
  } else {
    $Sub = [];
    $Subid = 0;
  }

  echo "<table border>";
  echo "<tr>" . fm_text('Forename',$Sub,'FirstName',2);
  echo "<tr>" . fm_text('Surname',$Sub,'LastName',2);
  echo "<tr>" . fm_text('Email',$Sub,'Email',2);
  echo "</table>\n";
  
  echo "<input type=submit name=Action value=Subscribe>";
}

function Subscribe() {
  // Validate
  
  // Send Confirm message
  
  // Save
}

function Mail_List_Action($Action) {
  
  switch ($Action) {
  case 'MailForm':
    MailForm();
    break;
    
  case 'Subscribe':
    MailFormFilled();
    break;
  
  case 'Confirm':
    MailConfirmed();
    break;
  
  case 'ViewForm':
    ViewForm($_REQUEST['id']);
    break;
  
  case 'AcceptForm':
    AcceptForm();
    break;
  
  case 'RejectForm':
    RejectForm();
    break;
  
  case 'ListForms':
    ListForms('Open');
    break;
  
  case 'SendMail':
    SendMailToList(); // For the future
    break;

  }
  dotail();
}
 
 
?>

