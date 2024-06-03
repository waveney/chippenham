<?php
  include_once("fest.php");
  A_Check('Committee','Users');

  dostaffhead("Welcome");
  include_once("UserLib.php");
  include_once("Email.php");

  if (isset($_REQUEST['U'])) {
    $uid = $_REQUEST['U'];
    $User = Get_User($uid);

    if (!$User['Email']) {
      Error_Page('No Email Set up for ' . $User['SN']);
    };
    $newpwd = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!~$&()@*=-+') , 0 , 10 );
    $hash = crypt($newpwd,"WM");
    $User['password'] = $hash;
    Put_User($User);
    $User['ActualPwd'] = $newpwd; // Not stored
 
    $subject = "Welcome " . firstword($User['SN']) . " to " . Feature('FestName') . " Staff pages";
    $letter = Email_Proforma(EMAIL_USER,$uid,$User['Email'],'Login_Welcome',$subject,'Login_Details',$User,'LoginLog.txt');
    echo "Email sent:<p>$letter";
  } else {
    echo "No user..."; 
  }
  dotail();
