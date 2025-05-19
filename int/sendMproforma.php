<?php 
// Set fields in data
include_once("fest.php");
include_once("DanceLib.php");
include_once("MusicLib.php");
include_once("Email.php");
global $PLANYEAR,$USER,$USERID,$PerfTypes;
// Send an Email Proforma for a performer other than dance
$id = $_REQUEST['I'];
$proforma = $_REQUEST['N'];

$Side = Get_Side($id);
$Sidey = Get_SideYear($id);
$ReplyTo = '';
$ReplyName = '';

$IsAC = 0;
  set_user();

foreach ($PerfTypes as $t=>$p) {
  if ($Side[$p[0]]) {
    $IsAC++;
    $EmailsFrom = Feature($p[1] . 'EmailsFrom','');
    if ($IsAC > 1) break;
    if ($EmailsFrom == 'USER') { $IsAC+=2; break; }
    if (!empty($EmailsFrom)) $ReplyTo = $EmailsFrom;
  }
}

if (empty($Sidey['BookedBy'])) {
  $ReplyTo = ($USER['FestEmail']?$USER['FestEmail']:$USER['Email']);
} else if ($IsAC > 1 || empty($ReplyTo)) {
  if ($USERID == $Sidey['BookedBy']) {
    if (!empty($USER['FestEmail'])) {
      $ReplyTo =($USER['FestEmail']?$USER['FestEmail']:$USER['Email']);
      if (!strstr($ReplyTo,'@')) $ReplyTo .= '@' . Feature('HostURL');
    }
  } else {
    $User = Gen_Get('FestUsers',$Sidey['BookedBy'],'UserId');
    $ReplyTo = ($USER['FestEmail']?$USER['FestEmail']:$USER['Email']);
    if (!strstr($ReplyTo,'@')) $ReplyTo .= '@' . Feature('HostURL');
  }
}

$subject = Feature('FestName') . " $PLANYEAR and " . $Side['SN'];

  $proforma = (isset($_REQUEST['N'])?$_REQUEST['N']:'');
  $label = (isset($_REQUEST['L'])?$_REQUEST['L']:"");
  $Atts = (isset($_REQUEST['ATTS'])?json_decode($_REQUEST['ATTS'],true):[]);

//  var_dump($Atts);
  $Side = Get_Side($id);
  $Sidey = Get_SideYear($id);
  $subject = Feature('FestName') . " $PLANYEAR and " . $Side['SN'];
  $To = $Side['Email'];
  $Contact = $Side['Contact'];
  if (empty($Contact)) $Contact = $Side['SN'];
  
  if (empty($_REQUEST['E'])) {
    if ($Side['HasAgent'] && !$Side['BookDirect']) $_REQUEST['E'] = 'Agent';
  }
  

  if (isset($_REQUEST['E'])) switch ($_REQUEST['E']) {
    case 'Agent':
      $To = $Side['AgentEmail'];
      if (!empty($Side['AgentName'])) $Contact = $Side['AgentName'];
      if (strstr($proforma,'Contract') && !strstr($proforma,'Agent')) $proforma .= "_Agent";
      break;
    case 'Alt':
      $To = $Side['AltEmail'];
      if (!empty($Side['AltContact'])) $Contact = $Side['AltContact'];
      break;

    default:

      break;
  }
  $Mess = (isset($_REQUEST['Message'])?$_REQUEST['Message']:(Get_Email_Proforma($proforma))['Body']);

  $too = [['to',$To,$Contact],
          ['replyto',$ReplyTo,Feature('ShortName')]];
            
  $Atts = [];

  Email_Proforma(EMAIL_DANCE,$id, $too,$proforma,$subject,'Dance_Email_Details',[$Side,$Sidey],'Performer',$Atts);
  Dance_Email_Details_Callback($proforma,[$Side,$Sidey]);

  $Sidey = Get_SideYear($id); // Need to refetch as callback has overwritten it
  $prefix = '';

  
    if ($proforma) $prefix .= "<span " . Music_Proforma_Background($proforma) . ">$proforma:";
    $prefix .= date('j/n/y');
    if ($proforma) $prefix .= "</span>";
    if (strlen($Sidey['Invited'])) {
      $Sidey['Invited'] = $prefix . ", " . $Sidey['Invited'];
    } else {
      $Sidey['Invited'] = $prefix;  
    }
    Put_SideYear($Sidey);
//echo $Res;
