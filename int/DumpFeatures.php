<?php
  include_once("fest.php");
  A_Check('SysAdmin');

  dostaffhead("Dunp Emails");
  include_once("Email.php");

  // Anything not listed will be copied as is
  // name=>  xxx - replace with xxx
  $Rules =['FestName'=>'', 
           'ShortName'=>'',
           'FestPhone'=>'',
           'HostURL'=>'',
           'CopyTradeEmailsName'=>'',
           'DanceOrg'=>'Dance Team',
           'GoogleAPI'=>'',
           'EmailReplyTo'=>'No-Reply@example.com',
           'SMPTserver'=>'',
           'SMTPuser'=>'',
           'SMTPpwd'=>'',
           'WebsiteBanner'=>'/images/icons/DefaultLogo.png',
           'WebsiteBanner2'=>'/images/icons/DefaultLogo.png',
           'DefaultPageBanner'=>'/images/icons/ChipBan1440.png',
           'MailChimp_apikey'=>'',
           'MailChimp_listid'=>'',
           'MailChimp_server'=>'',

           ];
  global $FESTSYS;
  
  $Feats = $FESTSYS['Features'];
  $SFeats = [];
  foreach(explode("\n",$Feats) as $line) {
    if (!isset($line[0]) || ($line[0] == ';')) {
      $SFeats []= $line;
      continue;
    }
    if (preg_match('/(.+)=(.*?)(;.*)?$/',$line,$match)) {
      $key = trim($match[1]);
      $rslt = trim($match[2]);
      $com = trim($match[3]??'');
      
      if (isset($Rules[$key])) $rslt = $Rules[$key];
      $SFeats []= "$key = $rslt" . ($com? (' ' . $com) : '');
    } else {
      $SFeats []= $line;  
    }
  }
    
  file_put_contents('festfiles/RawFeatures',base64_encode(implode("\n",$SFeats))); 
  echo "Features Dumped<p>";
  dotail();
?>
