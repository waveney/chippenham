<?php
/* 
 * Everything to do with logining in, new passwords etc
 * Many mini pages some of which are used in several places
 *
 */

  include_once("fest.php"); 
  include_once("Email.php"); 
  include_once("UserLib.php");
    
function Logon(&$use=0) {
  global $YEAR,$USER,$USERID;
  $Rem = 0;
  if (!$use) {
    $user = $_POST['UserName'];
    if (!ctype_alnum($user))  return 'No Hacking';
    $pwd = $_POST['password'];
    if (isset($_POST['RememberMe'])) $Rem = $_POST['RememberMe'];
    $ans = Get_User($user);
  }
  if ($use || $ans) { // using crypt rather than password_hash so it works on php 3.3
    if (!$use && $ans) {
      $cry = crypt($pwd,'WM');
      if ($cry != $ans['password']) {
        setcookie('FEST2','',-1,'/');
        return "Username/Password Error";
      }
    } else {
      $ans = $use;
    }
    if ($ans['AccessLevel']) {
      $ans['Yale'] = rand_string(40);
      setcookie('FEST2',$ans['Yale'],($Rem ? mktime(0,0,0,1,1,gmdate('Y')+1) : 0),'/' );
      $_COOKIE['FEST2'] = $ans['Yale'];
      Put_User($ans);
      $USER=$ans;
      $USERID = $USER['UserId'];
      include_once ("Staff.php"); // no return wanted
      exit;
    }
    Login("$user no longer has access");
  }
  return "User not known";
}

function ActAs() {
  global $CONF,$USER,$USERID;
  $user = $_REQUEST['i'];
  $ans = Get_User($user);
  if (!empty($CONF['Testing'])) Error_Page('Only on non live systems');
  if ($_REQUEST['k'] != $ans['AccessKey']) Error_Page('Invalid key');

//      $ans['Yale'] = rand_string(40); Don't chaange when acting as
      setcookie('FEST2',$ans['Yale'],mktime(0,0,0,1,1,gmdate('Y')+1),'/' );
      $_COOKIE['FEST2'] = $ans['Yale'];
      Put_User($ans);
      $USER=$ans;
      $USERID = $user;
      include_once ("Staff.php"); // no return wanted
      exit;
}

function Forgot() {
  $rand_hash = rand_string(40);
  $user = $_POST['UserName'];
  if (strlen($user) > 2) {
    if ($ans = Get_User($user)) {
      if ($ans['UserId'] > 9 ) { 
        if ($ans['AccessLevel'] == 0) return "You no longer have access";
        $ans['ChangeSent'] = time();
        $ans['AccessKey'] = $rand_hash;
        Put_User($ans);

        Email_Proforma(4,$ans['UserId'],$ans['Email'],'Login_Forgot_Password', 
            Feature('FestName') . " Staff Access for " . $ans['SN'],'Login_Details',$ans,'LoginLog.txt');
        return "A limited use link has been emailed to you";
      }
    }
  }
  return "Username/Password Error";
}

function Set_Password($user,$msg='') {
  dostaffhead("Set Password");

  $ans = Get_User($user);
// var_dump($ans);exit;
  if ($ans) {
    if ($ans['ChangeSent']+36000 < time()) {
      $rand_hash = rand_string(40);
      $ans['ChangeSent'] = time();
      $ans['AccessKey'] = $rand_hash;
      Put_User($ans);
    } else {
      $rand_hash = $ans['AccessKey'];
    }
 
    if ($msg) echo "<h2 class=ERR>$msg</h2>\n";
    echo "The minimum length is 8.<p>";
    echo "Password must have a digit, a lower case character, an uppercase and a special character.<p>";
    
    echo "<form method=post action=Login>";
    echo "<div class=tablecont><table>";
    echo "<tr><td>Password:<td><input type=password Name=password>\n";
    echo fm_hidden('UserId',$user) . fm_hidden('AccessKey',$rand_hash);
    echo "<tr><td>Confirm:<td><input type=password Name=confirm>\n";
    $_POST['RememberMe'] = 1;
    echo "<tr><td>" . fm_checkbox("Remember Me",$_POST,'RememberMe') . "</table></div><p>\n";
    echo "<input type=submit Name=ACTION value='Set New Password'><p>\n";
    echo "</form></div>\n";

    dotail();
    exit;
  }
  return "User $user not known";
}

function Limited() {
  $who = $_GET['U'];
  $hash = $_GET['A'];

  if ($ans = Get_User($who)) {
    if ($ans['AccessKey'] == $hash && ($ans['ChangeSent']+36000 > time())) {
      return Set_Password($ans['UserId']);
    }
  } else {
    return "Limited use Username/Password Error";
  }
}

function Login($errmsg='', $message='') {
  global $db,$USER,$Access_Type;
  Set_User();
  
  if (isset($USER) && (($USER['AccessLevel'] ?? 0) > $Access_Type['Participant'])) {
    include_once ("Staff.php");  // No return
    exit;
  }
  
  dostaffhead("Staff Login");
  if ($errmsg) echo "<h2 class=ERR>$errmsg</h2>";
  if ($message) echo "<h2>$message</h2>";

  echo "<form method=post action=Login>";
  echo "<div class=tablecont><table class=simpletable><tr><td>User Name or Email:<td><input type=text Name=UserName>\n";
  echo "<tr><td>Password:<td><input type=password Name=password>\n";
  $_POST['RememberMe'] = 1;
  echo "<tr><td>" . fm_checkbox("Remember Me",$_POST,'RememberMe') . "- This will use a cookie";
  echo "</table></div>\n";
  echo "<p><input type=submit Name=ACTION value=Logon><p>\n";

  echo "<input type=submit Name=ACTION value='Lost your password'>\n";
  echo "</form></div>\n";

  dotail();
  exit;
}

function NewPasswd() {
  global $YEAR,$USER,$USERID;
  $user = $_POST['UserId']; 
  if (!$user) $user = $USERID;
  if (!($ans = Get_User($user) )) Login("User not known");
  
  if ($ans['AccessKey'] != $_POST['AccessKey']) Login("Link invalid ");
  
  if ($ans['ChangeSent']+36000 < time()) Login("Link timed out");
  
  if ($_POST['password'] != $_POST['confirm']) Set_Password($user,"Password and Confirm did not match");
  
  if (strlen($_POST['password']) < 8) Set_Password($user,"Password too short");
  
  if (!preg_match('/[0-9]/',$_POST['password']) || 
      !preg_match('/[a-z]/',$_POST['password']) ||
      !preg_match('/[A-Z]/',$_POST['password']) ||
      !preg_match('/\W/',$_POST['password'])) Set_Password($user,"Password must have a digit, a lower case character, an uppercase and a special character");

  // using crypt rather than password_hash so it works on php 3.3
  $hash = crypt($_POST['password'],"WM");
  $ans['password'] = $hash;
  $ans['Yale'] = rand_string(40);
  $USER = $ans;
  $USERID = $user;
  setcookie('FEST2',$ans['Yale'],($_POST['RememberMe'] ? mktime(0,0,0,1,1,gmdate('Y')+1) : 0 ),'/');
  Put_User($ans);
  include ("Staff.php"); // no return wanted
  exit;
}

/* MAIN CODE HERE */
  global $USERID,$CONF;
  Set_User();
  if(!isset($_GET['ACTION'])) {
    if (!isset($_POST['ACTION'])) Login(); // No Return
    $act = $_POST['ACTION'];
  } else {
    $act = $_GET['ACTION'];
  }

//  echo "<!-- " . var_dump($act) . " -->\n";
  switch ($act) {
    case 'Login' :
      Login(); // No Return
    case 'Logon' :
      Login(Logon()); // No Return    
    case 'LOGOUT' :
      $USER = 0;
      setcookie('FEST2',0,1,'/');
      if (@ $CONF['testing']) Login();
      include_once("../index.php"); 
      exit;
    case 'LIMITED' :
      Login(Limited()); // No Return;
    case 'Set New Password' :
      Login(NewPasswd()); // No Return;
    case 'NEWPASSWD' :
      Login(Set_Password($USERID));
    case 'Lost your password' :
      Login(Forgot());
    case 'ActAs' :
      ActAs();
    
  }
  echo "Should not get here  $act ...";
?>
