<?php
  include_once("fest.php");
  include_once("DanceLib.php");
  include_once("ProgLib.php");
  include_once("DateTime.php");
  include_once("Email.php");

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
  }
}

function Send_MgrMessage(&$Reg,$Msg) {
  global $MgrMsg,$FESTSYS;
  $MgrMsg = $Msg;
  $DanceEmailsFrom = Feature('DanceEmailsFrom','Dance');
  $too = [['to',$DanceEmailsFrom . '@' . $FESTSYS['HostURL'],$FESTSYS['ShortName'] . ' ' . $DanceEmailsFrom]];

  echo Email_Proforma(1,$Reg['id'],$too,'Dance_Register','Dance Side Registering','Register_Email_Details',$Reg,$logfile='Dance');
}

function Send_DanceMessage($Side,$Mess,$To='') {
  global $FESTSYS;
  $DanceEmailsFrom = Feature('DanceEmailsFrom','Dance');
  $too = [['to', $To, $Side['Contact']],
          ['from',$DanceEmailsFrom . '@' . $FESTSYS['HostURL'],$FESTSYS['ShortName'] . ' ' . $DanceEmailsFrom],
          ['replyto',$DanceEmailsFrom . '@' . $FESTSYS['HostURL'],$FESTSYS['ShortName'] . ' ' . $DanceEmailsFrom]];

  echo Email_Proforma(1,$Side['SideId'],$too,$Mess,'Dance Side Registering','Register_Email_Details',$Side,$logfile='Dance');
}

function Thankyou() {
  echo "<h2>Thankyou</h2>";
  echo "Your details and request have been stored and sent to the Dance managers to check.  Please be patient they are busy people.<p>";
  dotail();
}

function WhoYouAre($Mess='') {
  global $FESTSYS;
  $RedStar = " <span class=Red><b>*</b></span>";
  echo "This will be checked to ensure you are who you say you are and that " . $_REQUEST['SN'] . " is apropriate for " . $FESTSYS['FestName'] . "<p>";
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

//show details

// Action buttons
}

function ListReg() {


}

// **********************************************************************
// START HERE
// **********************************************************************
  $AllPosts = ['SN','Email:40:email','Contact','Website','YouTube','Mobile','Phone','VerifyReason:1000','Description:2000','SideId:10:num','RegId:10:num'];
  SanitiseAll($AllPosts);
  dostaffhead("Registering", ["/js/Participants.js"]);  
  $RedStar = " <span class=Red><b>*</b></span>";

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
        $_REQUEST['SubmitDate'] = time();
        $id = Insert_db_post('SideRegister',$Reg);
        Send_MgrMessage($Reg,"Potential new side");
        Thankyou();
    
    case 'NoEmail':
        $_POST['State'] = 2;
        $_POST['SubmitDate'] = time();
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
        $_POST['State'] = 3;
        $_POST['SubmitDate'] = time();
        $id = Insert_db_post('SideRegister',$Reg);
        Send_MgrMessage($Reg,"New Email address for side");
        Thankyou();

    case 'NowMeAlt':
        $_POST['State'] = 4;
        $_POST['SubmitDate'] = time();
        $id = Insert_db_post('SideRegister',$Reg);
        Send_MgrMessage($Reg,"New Alternative Email address for side");
        Thankyou();
    
    case 'View':
        $Regid = $_REQUEST['id'];
        ViewReg($Regid);
        dotail();
    
    case 'List':
        RegList();
        dotail();
    
    }
  }
  
  
  echo "<h1>This is for Dance sides to register to take part in " . $FESTSYS['FestName'] . "</h1>";
  echo "The first check is to see if you are already in our database.  If so a link will be provided to enable you to update your records.<p>";
  echo "If not, you will be asked to provide some information so we can check you are a real dance side, not an imposter.<p>" .
       "Be patient please, there are humans in the loop.<p>";
  
  echo "<form method=post action=Register>";
  echo "<table border><tr>" . fm_text('Your Name',$_POST,'Contact',4);
  echo "<tr>" . fm_text('Name of Dance Side',$_POST,'SN',4);
  echo "<tr>" . fm_text('Email Address',$_POST,'Email',4);
  echo "</table>";
  echo "<input type=submit name=ACTION value='Check'></form>";

  
  dotail();
  


?>
