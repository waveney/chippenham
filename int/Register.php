<?php
  include_once("fest.php");
  include_once("DanceLib.php");
  include_once("ProgLib.php");
  include_once("DateTime.php");
  include_once("Email.php");

$RegStates = ['','New Side','No Email','Now Me','Alt Now Me',
              10=>'Confirmed', 11=>'New Side - Confirmed',12=>'No Email - Confirmed',13=>'Now Me - Confirmed',14=>'Alt Now Me - Confirmed',
              20=>'Rejected', 21=>'New Side - Rejected',22=>'No Email - Rejected',23=>'Now Me - Rejected',24=>'Alt Now Me - Rejected'];

function Disguise($txt) {
  [$User,$Domain] = explode('@',$txt,2);
  $DUser = preg_replace('/(.)./','$1*',$User);
  $DDomain = preg_replace('/(.)./','$1*',$Domain);
  return "$DUser@$DDomain";
}


function Register_Email_Details($key,&$data,$att=0) {
  global $MgrMsg,$YEAR;
  $Reg = &$data;
// var_dump($Reg);
  $host = "https://" . $_SERVER['HTTP_HOST'];
  switch ($key) {
  case 'WHO' :
  case 'CONTACT':  return $Reg['Contact']? firstword($Reg['Contact']) : $Reg['SN'];
  case 'REGLINK': return "<a href='$host/int/Register?ACTION=View&id=" . $Reg['id'] . "'><b>Register link</b></a>  " ;
  case 'LINK':
    $Side = Get_Side($Reg['SideId']);
    return "<a href='$host/int/Direct?t=Perf&id=" . $Side['SideId'] . "&key=" . $Side['AccessKey'] . "&Y=$YEAR'><b>Link</b></a>  " ;
  case 'EMAIL': return $Reg['Email'];
  case 'SIDENAME': return $Reg['SN'];
  case 'MGRMSG' : return $MgrMsg;
  case 'DANCEORG': return Feature('DanceOrg','Richard Proctor');
  case 'REJECTREASON': return (empty($Reg['Reason'])?'':"Because: " . $Reg['Reason']);
  }
}

function Send_MgrMessage(&$Reg,$Msg) {
  global $MgrMsg;
  $MgrMsg = $Msg;
  $DanceEmailsFrom = Feature('DanceEmailsFrom','Dance');
  $too = [['to',$DanceEmailsFrom . '@' . Feature('HostURL'),Feature('ShortName') . ' ' . $DanceEmailsFrom]];

  Email_Proforma(EMAIL_DANCE,$Reg['id'],$too,'Dance_Register','Dance Side Registering','Register_Email_Details',$Reg,$logfile='Dance');
}

function Send_DanceMessage($Side,$Mess,$To='') {
  $DanceEmailsFrom = Feature('DanceEmailsFrom','Dance');
  $too = [['to', $To, ((!empty($Side['AltEmail'])) && ($To == $Side['AltEmail'])?$Side['AltContact'] : $Side['Contact'])],
          ['from',$DanceEmailsFrom . '@' . Feature('HostURL'),Feature('ShortName') . ' ' . $DanceEmailsFrom],
          ['replyto',$DanceEmailsFrom . '@' . Feature('HostURL'),Feature('ShortName') . ' ' . $DanceEmailsFrom]];

  Email_Proforma(EMAIL_DANCE,$Side['SideId'],$too,$Mess,'Dance Side Registering','Register_Email_Details',$Side,$logfile='Dance');
}

function Thankyou() {
  echo "<h2>Thankyou</h2>";
  echo "Your details and request have been stored and sent to the Dance managers to check.  Please be patient they are busy people.<p>";
  dotail();
}

function WhoYouAre($Mess='') {
  $RedStar = " <span class=Red><b>*</b></span>";
  echo "This will be checked to ensure you are who you say you are and that " . $_REQUEST['SN'] . " is apropriate for " . Feature('FestName') . "<p>";
  if ($Mess) echo "<span class=Red>$Mess</span<p>";
  echo "<form method=post action=Register?ACTION=NewSide><table border>";
  echo fm_hidden('Email',$_REQUEST['Email']) . fm_hidden('Contact',$_REQUEST['Contact']) . fm_hidden('SN',$_REQUEST['SN']);
  echo "<tr>" . fm_text('Dance Style - eg Northwest, Cotswold',$_REQUEST,'Type',-2);
  echo "<tr>" . fm_textarea("Short Description$RedStar",$_REQUEST,'Description',4,-4);
  echo "<tr>" . fm_text('Website',$_REQUEST,'Website',-2);
  echo "<tr>" . fm_text("Video link $RedStar (Youtube or equivalent)",$_REQUEST,'YouTube',-2);
  echo "<tr>" . fm_text('Phone Number',$_REQUEST,'Phone',-2);
  echo "<tr>" . fm_text("Mobile Number$RedStar",$_REQUEST,'Mobile',-2);
  echo "</table>";
  echo "<input type=submit value='Submit Application'></form><p>\n";
  dotail();
}

function ViewReg($id) {
  global $RegStates;
  $Reg = Gen_Get('SideRegister',$id);
  if (empty($Reg['id'])) {
    echo "Entry $id Not found";
    dotail();
  }

  echo "<form method=post action=Register>";
  Register_AutoUpdate('SideRegister',$id,'id');
  echo "<table border>";
  echo "<tr><td>Id:$id<tr><td>Contact:<td>" . $Reg['Contact'];
  echo "<tr><td>Email:<td>" . $Reg['Email'];
  echo "<tr><td>State: " . fm_select($RegStates,$Reg,'State');
  if ($Reg['SN']) {
    echo "<tr><td>Side Name<td>" . $Reg['SN'];
    if (!empty($Reg['SideId'])) echo "<td><a href=AddPerf?id=" . $Reg['SideId'] . " target=_blank>View in new tab</a>";
  }
  if ($Reg['Type']) echo "<tr><td>Type:<td>" . $Reg['Type'];
  if ($Reg['Mobile']) echo "<tr><td>Mobile:<td>" . $Reg['Mobile'];
  if ($Reg['Phone']) echo "<tr><td>Phone:<td>" . $Reg['Phone'];
  if ($Reg['Website']) echo "<tr><td>Website:<td>" . $Reg['Website'] . "<td>" . weblinksimple($Reg['Website']) . "Use in a new tab</a>";
  if ($Reg['YouTube']) echo "<tr><td>YouTube:<td>" . $Reg['YouTube'] . "<td><a href=" . videolink($Reg['YouTube']) . " target=_blanks>Use in new tab</a>";
  if ($Reg['Description']) echo "<tr><td colspan=5>Description:<br>" . $Reg['Description'];
  if ($Reg['VerifyReason']) echo "<tr><td colspan=5>Reasons to Verify:<br>" . $Reg['VerifyReason'];
  echo "<tr>" . fm_textarea('Reject Reason',$Reg,'Reason',5,2);
   if ($Reg['DateSubmitted']) echo "<tr><td>Submitted on:<td>" . date("j M Y",$Reg['DateSubmitted']);
  echo "</table>";

  if ($Reg['State'] < 10) {
    echo "<input type=submit name=ACTION value='Confirm'>";
    echo "<input type=submit name=ACTION value='Reject'>";
    echo "<input type=submit name=ACTION value='Ignore'>";
  } else if ($Reg['State'] > 10) {
    echo "<input type=submit name=ACTION value='Confirm after all'>";
  }

//show details

// Action buttons

  echo "</form>";
  echo "<h2><a href=Register?ACTION=Edit&id=$id>Edit</a></h2>";
  echo "<h2><a href=Register?ACTION=List>List</a></h2>";

  dotail();
}

function EditReg($id) {
  global $RegStates;
  $Reg = Gen_Get('SideRegister',$id);
  if (empty($Reg['id'])) {
    echo "Entry $id Not found";
    dotail();
  }

  echo "<form method=post action=Register>";
  Register_AutoUpdate('SideRegister',$id,'id');
  echo "<table border>";
  echo "<tr><td>Id:$id<tr>" . fm_text('Contact',$Reg,'Contact');
  echo "<tr>" . fm_text('Email',$Reg,'Email');
  echo "<tr><td>State: " . fm_select($RegStates,$Reg,'State');
  if ($Reg['SN']) {
    echo "<tr>" . fm_text('Side Name',$Reg,'SN',4);
    if (!empty($Reg['SideId'])) echo "<td><a href=AddPerf?id=" . $Reg['SideId'] . " target=_blank>View in new tab</a>";
  }
  if ($Reg['Type']) echo "<tr>" . fm_text('Type',$Reg,'Type',2);
  if ($Reg['Mobile']) echo "<tr>" . fm_text('Mobile',$Reg,'Mobile',2);
  if ($Reg['Phone']) echo "<tr>" . fm_text('Phone',$Reg,'Phone',2);
  if ($Reg['Website']) echo "<tr>" . fm_text('Website',$Reg,'Website',2) . "<td>" . weblinksimple($Reg['Website']) . "Use in a new tab</a>";
  if ($Reg['YouTube']) echo "<tr>" . fm_text('YouTube',$Reg,'YouTube',2) . "<td><a href=" . videolink($Reg['YouTube']) . " target=_blanks>Use in new tab</a>";
  if ($Reg['Description']) echo "<tr>" . fm_textarea('Description',$Reg, 'Description',5,3);
  if ($Reg['VerifyReason']) echo "<tr>" . fm_textarea('Reason to Verify',$Reg,'VerifyReason',5,3);
  if (Access('SysAdmin')) echo "<tr>" . fm_textarea('History',$Reg,'History',5,3);
  echo "<tr>" . fm_textarea('Reject Reason',$Reg,'Reason',5,2);
  if ($Reg['DateSubmitted']) echo "<tr><td>Submitted on:<td>" . date("j M Y",$Reg['DateSubmitted']);
  echo "</table>";
  if (Access('SysAdmin')) echo "<input type=submit name=ACTION value=Delete><br>\n";
  echo "</form>";
  echo "<h2><a href=Register?ACTION=View&id=$id>Back to View and Actions</a></h2>";
  echo "<h2><a href=Register?ACTION=List>List</a></h2>";
  dotail();
}


function ListReg() {
  global $RegStates;

  echo "<button class='floatright FullD' onclick=\"($('.FullD').toggle())\">All Applications</button>" .
       "<button class='floatright FullD' hidden onclick=\"($('.FullD').toggle())\">Curent Aplications</button> ";

  echo "<h2>List of Outstanding Registrations</h2>";
  $Regs = Gen_Get_All('SideRegister');

  if ($Regs) {

    $coln = 0;
    echo "<form method=post action=Register?ACTION=View>";
    echo "<div class=Scrolltable><table id=indextable border>\n";
    echo "<thead><tr>";

    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Id</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Contact</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Side</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>State</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Email</a>\n";
    echo "<th><a href=javascript:SortTable(" . $coln++ . ",'D')>Date</a>\n";
    echo "</thead><tbody>";

    foreach($Regs as $id=>$R) {
      echo "<tr " . ($R['State'] < 11?'':'class=FullD hidden') . "><td>$id<td><a href=Register?ACTION=View&id=$id>" . $R['Contact'] .
           "</a><td><a href=Register?ACTION=View&id=$id>" . $R['SN'] . "</a><td>" . $RegStates[$R['State']] .
           "<td>" . $R['Email'] . "<td>" . ( ($R['DateSubmitted']) ?date("j M Y",$R['DateSubmitted']):'');
    }
    echo "</table></div><br>";
  } else {
    echo "None are stored at present.";
  }
  dotail();
}

function Confirm($id,$Override=0) {
  global $RegStates,$USER;
  $Reg = Gen_Get('SideRegister',$id);
  if (empty($Reg['id'])) {
    echo "Entry $id Not found";
    echo "<h2><a href=Register?ACTION=List>List other Registrations</a></h2>";
    dotail();
  }
  if (($Override == 0) && ($Reg['State']>10)) {
    echo "<h2>This request is in state: " . $RegStates[$Reg['State']] . "</h2>";
    echo "<h2><a href=Register?ACTION=View&id=$id>View and Actions</a></h2>";

    echo "<h2><a href=Register?ACTION=List>List other Registrations</a></h2>";
    dotail();
  }
  switch ($Reg['State']%10) {
    case 1: // New Side
      $Side = $Reg;
      unset($Side['id']);
      if (strlen($Reg['Description']) > 150) {
        $Side['Blurb'] = $Reg['Description'];
        $Side['Description'] = '';
      }
      $Side['AccessKey'] = rand_string(40);
      $Side['IsASide']=1;
      $Reg['SideId'] = $Side['SideId'] = Insert_db('Sides',$Side);
      $Reg['State'] = ($Reg['State']%10)+10;
      $Reg['History'] .= "Confirmed by " . $USER['Login'] . " on " . date("j M Y") . "\n";
      Gen_Put('SideRegister',$Reg);
      Send_DanceMessage($Side,'Dance_Welcome',$Side['Email']);
      echo $Side['SN'] . " is now in the database as a side and an email confirmation has been sent to" . $Side['Contact'] . ".<p>";
      break;

    case 2: // No Email
      $Side = Get_Side($Reg['SideId']);
      if (empty($Side['id'])) {
        echo "Side " . $Reg['SideId'] . " - " . $Reg['SN'] . " NOT FOUND.<p>";
        echo "<h2><a href=Register?ACTION=List>List other Registrations</a></h2>";
        dotail();
      }
      $Side['AccessKey'] = rand_string(40);
      $Side['Contact'] = $Reg['Contact'];
      $Side['Email'] = $Reg['Email'];
      Put_Side($Side);
      $Reg['State'] = ($Reg['State']%10)+10;
      $Reg['History'] .= "Confirmed by " . $USER['Login'] . " on " . date("j M Y") . "\n";
      Gen_Put('SideRegister',$Reg);
      Send_DanceMessage($Side,'Dance_Welcome',$Side['Email']);
      echo $Side['SN'] . " has an email and contact set up. An email confirmation has been sent to" . $Side['Contact'] . ".<p>";
      break;

    case 3: // Replace Contact
      $Side = Get_Side($Reg['SideId']);
      if (empty($Side['id'])) {
        echo "Side " . $Reg['SideId'] . " - " . $Reg['SN'] . " NOT FOUND.<p>";
        echo "<h2><a href=Register?ACTION=List>List other Registrations</a></h2>";
        dotail();
      }
      $Side['AccessKey'] = rand_string(40);
      $Side['Contact'] = $Reg['Contact'];
      $Side['Email'] = $Reg['Email'];
      Put_Side($Side);
      $Reg['State'] = ($Reg['State']%10)+10;
      $Reg['History'] .= "Confirmed by " . $USER['Login'] . " on " . date("j M Y") . "\n";
      Gen_Put('SideRegister',$Reg);
      Send_DanceMessage($Side,'Dance_Welcome',$Side['Email']);
      echo $Side['SN'] . " has the contact changed. An email confirmation has been sent to" . $Side['Contact'] . ".<p>";
      break;

    case 4: // Replace Alt Contact
      $Side = Get_Side($Reg['SideId']);
      if (empty($Side['id'])) {
        echo "Side " . $Reg['SideId'] . " - " . $Reg['SN'] . " NOT FOUND.<p>";
        echo "<h2><a href=Register?ACTION=List>List other Registrations</a></h2>";
        dotail();
      }
      $Side['AltContact'] = $Reg['Contact'];
      $Side['AltEmail'] = $Reg['Email'];
      Put_Side($Side);
      $Reg['State'] = ($Reg['State']%10)+10;
      $Reg['History'] .= "Confirmed by " . $USER['Login'] . " on " . date("j M Y") . "\n";
      Gen_Put('SideRegister',$Reg);
      Send_DanceMessage($Side,'Dance_Welcome',$Side['AltEmail']);
      echo $Side['SN'] . " has the alternative contact set up. An email confirmation has been sent to" . $Reg['Contact'] . ".<p>";
      break;

    default:
  }
  echo "<h2><a href=Register?ACTION=List>List other Registrations</a></h2>";
  dotail();
}

function Reject($id) {
  global $RegStates,$USER;
  $Reg = Gen_Get('SideRegister',$id);
  if (empty($Reg['id'])) {
    echo "Entry $id Not found";
    dotail();
  }
  if ($Reg['State']>10) {
    echo "<h2>This request is in state: " . $RegStates[$Reg['State']] . "</h2>";
    echo "<h2><a href=Register?ACTION=View&id=$id>View and Actions</a></h2>";
    dotail();
  }

  $Reg['State'] = ($Reg['State']%10)+20;
  $Reg['History'] .= "Rejected by " . $USER['Login'] . " on " . date("j M Y") . "\n";
  Gen_Put('SideRegister',$Reg);
  Send_DanceMessage($Reg,'Dance_Rejected',$Reg['Email']);
  echo $Reg['SN'] . " was rejected and an email message has been sent to" . $Reg['Contact'] . ".<p>";

   echo "<h2><a href=Register?ACTION=List>List other Registrations</a></h2>";
  dotail();
}

function Ignore($id) {
  global $RegStates,$USER;
  $Reg = Gen_Get('SideRegister',$id);
  if (empty($Reg['id'])) {
    echo "Entry $id Not found";
    dotail();
  }
  if ($Reg['State']>10) {
    echo "<h2>This request is in state: " . $RegStates[$Reg['State']] . "</h2>";
    echo "<h2><a href=Register?ACTION=View&id=$id>View and Actions</a></h2>";
    dotail();
  }

  $Reg['State'] = ($Reg['State']%10)+20;
  $Reg['History'] .= "Ignored by " . $USER['Login'] . " on " . date("j M Y") . "\n";
  Gen_Put('SideRegister',$Reg);
  echo $Reg['SN'] . " is now silently ignored.<p>";

   echo "<h2><a href=Register?ACTION=List>List other Registrations</a></h2>";
  dotail();
}

// **********************************************************************
// START HERE
// **********************************************************************
  $AllPosts = ['SN','Email:40:email','Contact','Website','YouTube','Mobile','Phone','VerifyReason:1000','Description:2000','SideId:10:num','RegId:10:num'];
  SanitiseAll($AllPosts);
  dostaffhead("Registering", ["/js/Participants.js"]);
  $RedStar = " <span class=Red><b>*</b></span>";
  $Reg = [];

  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
      case 'Check':
        $SName = $_REQUEST['SN'];
        $Email = $_REQUEST['Email'];
        $Contact = $_REQUEST['Contact'];
        $Sides = Find_Perf_Similar($SName,'AND IsASide=1');
        $SCount = count($Sides);
        if ($SCount) {

// Check for match emails, before offering choice
          foreach ($Sides as $S) {
            if ($Email == $S['Email']) {
              Send_DanceMessage($S,'Dance_Blank',$S['Email']);
              echo "An email has been sent to you with a link, if you don't see it, please check your Spam folder.";
              dotail();
            }
            if ($Email == $S['AltEmail']) {
              Send_DanceMessage($S,'Dance_Blank',$S['AltEmail']);
              echo "An email has been sent to you with a link, if you don't see it, please check your Spam folder.";
              dotail();
            }
          }

// Choices

          if (count($Sides) > 1) {
            echo "We have $SCount dance sides that match that name.<p>";
          } else {
            echo "We have a dance side that matches that name.<p>";
          }
          echo "</table><tr>";
          foreach ($Sides as $S) {
            if ($SCount > 1) echo "<h2>For: " . $S['SN'] . "</h2>\n";
            if (strlen($S['Email']) > 5) {
              $Hidden = Disguise($S['Email']);
              echo "<hr>The stored email address looks a bit like? $Hidden <br>\n";
              echo "<form method=post action=Register?ACTION=SendMe>";
              echo fm_hidden('Email',$Email) . fm_hidden('Contact',$Contact) . fm_hidden('SideId',$S['SideId']) . fm_hidden('SN',$S['SN']);
              echo "<input type=submit value='Yes thats also me, send me a link'></form><p>\n";

              echo "<hr><form method=post action=Register?ACTION=NowMe>";
              echo fm_hidden('Email',$Email) . fm_hidden('Contact',$Contact) . fm_hidden('SideId',$S['SideId']) . fm_hidden('SN',$S['SN']);
              echo "<input type=submit value='That was valid but it has now changed to me'><p>\n";

              echo "Please either get the orginal contact to inform us, or be patient while we check " .
                   "(if you are listed on your website as the contact this is straightforward.<p>" .
                   "Please give us info to help verify this:<br>";
              echo fm_textarea('',$_REQUEST,'VerifyReason',1,-3);
              echo "</form><p>";

              if (strlen($S['AltEmail']) > 5) {
                $Hidden = Disguise($S['AltEmail']);
                echo "<hr>The Second stored email address looks a bit like? $Hidden <br>\n";
                echo "<form method=post action=Register?ACTION=SendMeAlt>";
                echo fm_hidden('Email',$Email) . fm_hidden('Contact',$Contact) . fm_hidden('SideId',$S['SideId']) . fm_hidden('SN',$S['SN']);
                echo "<input type=submit value='Yes thats also me, send me a link'></form><p>\n";

                echo "<hr><form method=post action=Register?ACTION=NowMeAlt>";
                echo fm_hidden('Email',$Email) . fm_hidden('Contact',$Contact) . fm_hidden('SideId',$S['SideId']) . fm_hidden('SN',$S['SN']);
                echo "<input type=submit value='That was valid but it has now changed to me'><p>\n";

                echo "Please either get the orginal contact to inform us, or be patient while we check " .
                     "(if you are listed on your website as the contact this is straightforward.<p>" .
                     "Please give us info to help verify this:<br>";
                echo fm_textarea('',$_REQUEST,'VerifyReason',1,-3);
                echo "</form><p>";
              }

            } else {
              echo "<hr><h2>Your Side is in the system, but without an email address</h2>";
              echo "<form method=post action=Register?ACTION=NoEmail>";
              echo fm_hidden('Email',$Email) . fm_hidden('Contact',$Contact). fm_hidden('SideId',$S['SideId'])  . fm_hidden('SN',$S['SN']);

              echo "Please give us info to help verify that you are the contact for $SName.  " .
                   "If you are listed on your website as the contact this is straightforward.<p>";
              echo fm_textarea('',$_REQUEST,'VerifyReason',1,-3);
              echo "<input type=submit name=Fred value='Register me as the Contact'><p>\n";
              echo "</form><p>";
            }
            echo "</table>";
          }
          echo "<hr><h2>None of the above, please give us some details</h2>";
        } else {
          echo "<h2>Please give us some details</h2>";
        }
        WhoYouAre();

    case 'NewSide':
        if (empty($_REQUEST['Description']) || strlen($_REQUEST['Description']) < 10) WhoYouAre('We need a description');
        if (empty($_REQUEST['YouTube']) || strlen($_REQUEST['YouTube']) < 10 ) WhoYouAre('We need a Video');
        if (empty($_REQUEST['Mobile']) || strlen($_REQUEST['Mobile']) < 11) WhoYouAre('We need a Mobile Phone number');

        $_REQUEST['State'] = 1;
        $_REQUEST['DateSubmitted'] = time();
        $id = Insert_db_post('SideRegister',$Reg);
        Send_MgrMessage($Reg,"Potential new side");
        Thankyou();

    case 'NoEmail':
        $_REQUEST['State'] = 2;
        $_REQUEST['DateSubmitted'] = time();
        $id = Insert_db_post('SideRegister',$Reg);
        Send_MgrMessage($Reg,"Email address for side without one");
        Thankyou();

    case 'SendMe':
        $S = Get_Side($_REQUEST['SideId']);
        Send_DanceMessage($S,'Dance_Blank',$S['Email']);
        echo "An email has been sent to you with a link, if you don't see it, please check your Spam folder.";
        dotail();

    case 'SendMeAlt':
        $S = Get_Side($_REQUEST['SideId']);
        Send_DanceMessage($S,'Dance_Blank',$S['AltEmail']);
        echo "An email has been sent to you with a link, if you don't see it, please check your Spam folder.";
        dotail();

    case 'NowMe':
        $_REQUEST['State'] = 3;
        $_REQUEST['DateSubmitted'] = time();
        $id = Insert_db_post('SideRegister',$Reg);
        Send_MgrMessage($Reg,"New Email address for side");
        Thankyou();

    case 'NowMeAlt':
        $_REQUEST['State'] = 4;
        $_REQUEST['DateSubmitted'] = time();
        $id = Insert_db_post('SideRegister',$Reg);
        Send_MgrMessage($Reg,"New Alternative Email address for side");
        Thankyou();

    case 'View':
        A_Check('Staff','Dance');
        $Regid = $_REQUEST['id'];
        ViewReg($Regid);
        dotail();

    case 'Edit':
        A_Check('Staff','Dance');
        $Regid = $_REQUEST['id'];
        EditReg($Regid);
        dotail();

    case 'List':
        A_Check('Staff','Dance');
        ListReg();
        dotail();

    case 'Confirm':
        A_Check('Staff','Dance');
        Confirm($_REQUEST['id']);
        dotail();

    case 'Reject' :
        A_Check('Staff','Dance');
        Reject($_REQUEST['id']);
        dotail();

    case 'Ignore' :
        A_Check('Staff','Dance');
        Ignore($_REQUEST['id']);
        dotail();

    case 'Confirm after all':
        A_Check('Staff','Dance');
        Confirm($_REQUEST['id'],1);
        dotail();

    case 'Delete' :
        A_Check('Staff','Dance');
        db_delete('SideRegister',$_REQUEST['id']);
        echo "Entry Deleted from records.<p>";
        ListReg();
        dotail();
    }
  }


  echo "<h1>This is for Dance sides to register to take part in " . Feature('FestName') . "</h1>";
  echo "The first check is to see if you are already in our database.  If so a link will be provided to enable you to update your records.<p>";
  echo "If not, you will be asked to provide some information so we can check you are a real dance side, not an imposter.<p>" .
       "Be patient please, there are humans in the loop.<p>";

  echo "<form method=post action=Register>";
  echo "<table border><tr>" . fm_text('Your Name',$_REQUEST,'Contact',4);
  echo "<tr>" . fm_text('Name of Dance Side',$_REQUEST,'SN',4);
  echo "<tr>" . fm_text('Email Address',$_REQUEST,'Email',4);
  echo "</table>";
  echo "<input type=submit name=ACTION value='Check'></form>";


  dotail();



?>
