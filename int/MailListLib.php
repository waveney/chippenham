<?php
  include_once("fest.php");

  include_once("Email.php");

  global $USER,$USERID,$db;
  
$MailStatus = ['Request','Email Confirmed','Accepted','Rejected','Duplicate'];
$StatusMail = array_flip($MailStatus);

define('MAIL_LIST_ADD',1);//Add with no checks - not implemented yet
define('MAIL_LIST_VERIFY',2);//Send verify email
define('MAIL_LIST_EYEBALL',4);//Check with real person
  
function MailForm($Sub=0,$Message='') {
  if ($Message) echo "$Message<p>\n";
  echo "<form method=post action=MailListMgr.php>";
  if (isset($Sub['id'])) {
    $Subid = $Sub['id'];
    Register_AutoUpdate('MailingListRequest',$Subid);
  } else {
    $Sub = [];
    $Subid = 0;
  }
  echo "<h2>Sign Up</h2>All we need is who you are and your Email address.<P>";
  echo "<table border>";
  echo "<tr>" . fm_text('Forename',$Sub,'FirstName',2);
  echo "<tr>" . fm_text('Surname',$Sub,'LastName',2);
  echo "<tr>" . fm_text('Email',$Sub,'Email',2);
  echo "</table>\n";
  
  echo "<input type=submit name=ACTION value=Subscribe>";
  dotail();
}

function CallOctopus($Request,&$Data=0) {

  // Create a new cURL resource
  $ch = curl_init("https://emailoctopus.com/api/1.6/$Request?api_key=" . feature('OctopusAPI'));

  if ($Data) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($Data));
  }

  // Set the content type to application/json
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

  // Return response instead of outputting
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  // Execute the POST request
  $result = curl_exec($ch);

  // Close cURL resource
  curl_close($ch);

  return $result;
}



function ValidateSub(&$Sub) {
  Sanitise($Sub['Email'],'email');
  Sanitise($Sub['FirstName']);
  Sanitise($Sub['LastName']);
  if ((strlen($Sub['FirstName']) + strlen($Sub['LastName'])) < 3) return "Please give a name";
  $Bits = explode('@',$Sub['Email']);
  if ((strlen($Bits[0]) < 1) || (strlen($Bits[1]) < 2)) return "Please give an Email address";
  return '';
}

function MailSub_Details($key,&$Sub) {
  switch ($key) {
  case 'NAME': return $Sub['FirstName'] .' ' . $Sub['LastName'];
  case 'EMAIL': return $Sub['Email'];
  case 'WHEN': return date("j/n/Y", $Sub['SubmitTime']);
  case 'CONFIRM': return "<a href='https://" . $_SERVER['HTTP_HOST'] . "/int/MailListMgr?A=Confirm&i=" . $Sub['id'] . "&k=" . $Sub['AccessKey'] . "'><b>link</b></a>";
  }
}


function Email_Publicity($Sub,$Message) {
  global $PLANYEAR;
  Email_Proforma(6,$Sub['id'],Feature('MailListMgrEmail'),$Message,Feature('FestName') . " $PLANYEAR and " . $Sub['FirstName'] . ' ' . $Sub['LastName'],
    'MailSub_Details',$Sub,'MailingList.txt');
}

function MailFormFilled() {
  global $StatusMail;
  if (!isset($_REQUEST['id'])) {
    Sanitise($_POST['Email'],'email');
    Sanitise($_POST['FirstName']);
    Sanitise($_POST['LastName']);
    $_POST['SubmitTime'] = time();
    $SubId = Insert_db_post('MailingListRequest',$Sub);
  } else {
    $SubId = $_REQUEST['id'];
    $Sub = Gen_Get('MailingListRequest',$SubId);
  }  
  if ($Mess = ValidateSub($Sub)) {
    MailForm($Sub,$Mess);
  }
  
  // Check for unique
  $md5 = md5(strtolower($Sub['Email']));
  
  $CheckRes = CallOctopus("lists/" . Feature('MailListID') . "/contacts/$md5");
  
  if (!isset($CheckRes['error'])) {
    echo "You are already subscribed to the list, thankyou.";
    $Sub['Status'] = $StatusMail['Duplicate'];
    Gen_Put('MailingListRequest',$Sub);
    return;
  }
  Email_Sub($Sub,"MailList_Confirm");
  echo "An Email has been sent to you to confirm your Email address.";
  dotail();
}
 
function MailConfirmed() {
  global $StatusMail;

  $SubId = ($_REQUEST['id'] ?? 0);
  $Key = ($_REQUEST['k'] ?? 0);
  
  if ($SubId == 0) { 
    echo "Invalid Link";
    dotail();
  }
  
  $Sub = Gen_Get('MailingListRequest',$SubId);
  
  if (!Access('Committee','Publicity')) {
    if ($Key == 0 || $Key != $Sub['AccessKey']) {
      echo "Invalid Link";
      dotail();
    }
  }
  
  echo "Recorded for checking";
  Email_Publicity($Sub,"MailList_Add");
  $Sub['Status'] = $StatusMail['Email Confirmed'];
  Gen_Put('MailingListRequest',$Sub);
  echo "Thank you for confirming your Email address.<p>  You should be added to the actual list shortly.<p>";
  dotail();
}

function AcceptForm() {
  global $StatusMail;
  $SubId = ($_REQUEST['id'] ?? 0);
  $Sub = Gen_Get('MailingListRequest',$SubId);
  $Data['fields'] =  ['FirstName' => $Sub['FirstName'] , 'LastName' => $Sub['LastName']];  
  $Data['email_address'] = $Sub['Email'];  
  
  
  $Res = CallOctopus("lists/" . Feature('MailListID') . "/contacts" ,$Data);

var_dump($Res);

  if (isset($Res['error'])) {
    echo "Failed to add to the MailOctopus list because: <b>" . $Res['error'] . "</b>";
  } else {
    echo "<B>" . $Sub['FirstName'] . " " . $Sub['LastName'] . "</b> has been added to the mailing list<p>";
  
    $Sub['Status'] = $StatusMail['Accepted'];
    Gen_Put('MailingListRequest',$Sub);
  }
  
  ListForms('Open');
}

function RejectForm() {
  global $StatusMail;
  
  $SubId = ($_REQUEST['id'] ?? 0);
  $Sub = Gen_Get('MailingListRequest',$SubId);

  $Sub['Status'] = $StatusMail['Rejected'];
  Gen_Put('MailingListRequest',$Sub);
  ListForms('Open');
}

function ListForms($Status) {
  global $MailStatus;
  $coln = 0;


  echo "<form method=post>";
  echo "<div class=Scrolltable><table id=indextable border class=altcolours>\n";
  echo "<thead><tr>";

  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Forename</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Surname</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Email</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'D')>When</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'D')>State</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Actions</a>\n";
  
  $Subs = Gen_Get_All('MailingListRequest');
  
  foreach ($Subs as $Sub) {
    $SubId = $Sub['id'];
    echo "<tr><td>$SubId";
    echo "<td>" . htmlspec($Sub['FirstName']);
    echo "<td>" . htmlspec($Sub['LastName']);
    echo "<td>" . htmlspec($Sub['Email']);
    echo "<td>" . date("j/n/Y", $Sub['SubmitTime']);
    echo "<td>" . $MailStatus[$Sub['Status']];
    echo "<td>";
      switch ($Sub['Status']) {
      case 0: // Request
        echo "<a href=MailListMgr?id=$SubId&A=AcceptForm>Accept</a>, <a href=MailListMgr?id=$SubId&A=RejectForm>Reject</a>, ";
        echo "<a href=MailListMgr?id=$SubId&A=Confirm>Confirm Address</a>";
        break;
      case 1: // Email Confirmed
        echo "<a href=MailListMgr?id=$SubId&A=AcceptForm>Accept</a>, <a href=MailListMgr?id=$SubId&A=RejectForm>Reject</a> ";
        break;
      default: // No actions (currently)
      }
  }
  
  echo "</tbody></table></div>\n";
  dotail();
}




function Mail_List_Action($Action) {

var_dump($_REQUEST);
  switch ($Action) {
  case 'New':
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

  default:
    echo "Unknown Action: $Action";
    
  }
  dotail();
}
 
 
?>

