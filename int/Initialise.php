<?php

// Initialise festival system
// create databases
// preload databases as needed - Master_Data and FestUsers
// create and populate directories
// No problem to run again and again
// TODO security if run on a live system

// Note this does not call fest as it must run without a db, it uses Configure.ini and

// IFF Config.ini read it and use, else prompt for data set it up and then use

$CONF = [];
include_once("festfm.php"); // Not db or main fest

function Check_PHP_Config() {
  return;
  if (!strstr(get_include_path(),$_SERVER['DOCUMENT_ROOT'])) {
    echo "The document Root is not part of the php include path -:" . get_include_path() . " LOTS of things depend on this<p>";
    exit;
  }
  // Should check for open_basedir and file size eventually
}


function Get_Config() {
  global $CONF;
  @ $CONF = parse_ini_file("Configuration.ini");
  if ( !$CONF ) {
    $CONF = ['host'=>'localhost','user'=>'','passwd'=>'','dbase'=>'','testing'=>''];
    return 0;
  }
  return 1;
}

function Create_Config() {
  global $CONF;
  if (Get_Config()) return;

  if (!isset($_REQUEST['dbase']) || !isset($_REQUEST['user'])) {
    echo "Set up the database and user, with all privalages, so they can add and change the database later.<p>";

    echo "<html><head><title>Festival System Setup</title></head><body>";
    echo "<form method=post><div class=tablecont><table border>\n";
    echo "<tr>" . fm_text("Host Name - usually localhost",$_REQUEST,'host');
    echo "<tr>" . fm_text("Database Name - must be unique to server",$_REQUEST,'dbase');
    echo "<tr>" . fm_text("Database User - Must be already setup, and be able to add, drop, update and create databases and tables",$_REQUEST,'user');
    echo "<tr>" . fm_text("Database Password (if any)",$_REQUEST,'passwd');
    echo "<tr>" . fm_text("Testing mode - blank for live, 1 for simple test (no emails), an email address to divert all emails too",$_REQUEST,'testing');
    echo "<tr>" . fm_text("Title Prefix - for test/stage/dev sites only",$_REQUEST,'TitlePrefix');
    echo "</table></div><input type=submit></form>\n";
    echo "</body></html>\n";
    exit;
  }
  echo "Now to do the setup<p>";

  $Config = "
[FF]

;;;;;;;;;;;;;;;;;;;
; About Configuration.ini
;;;;;;;;;;;;;;;;;;;
; comments start with ;

; host - usually localhost
host=" . $_REQUEST['host'] . "

; username for the database
user=" . $_REQUEST['user'] . "

; password for the database
passwd='" . $_REQUEST['passwd'] . "'

; database to be used
dbase=" . $_REQUEST['dbase'] . "

; testing - if not set the system will send emails normally
; if it contains an @ it is treated as an email address to send all emails to
; otherwise no emails are sent
testing='" . $_REQUEST['testing'] . "'

; Title Prefix - prepended to Title string - useful for test environments
TitlePrefix=" . $_REQUEST['TitlePrefix'] . "

; everything else is configured from with the festival software itself
";
  if (!file_put_contents("Configuration.ini",$Config)) {
    echo "Could not create configuration file";
    exit;
  }

  if (Get_Config()) return;
  echo "Config file created but reading it failed";
  exit;
}

function Create_Directories() {  // Makes all needed directories and adds .htaccess where appropriate
  global $CONF;

  echo "Checking access<p>";
  $NeedWrite = ['.','int','Schema'];

  foreach($NeedWrite as $D) {
    if (!is_writeable("../" . $D)) {
      echo "../" . $D . " Is NOT writeable  - aborting for now - you can retry once corrected<p>";
      exit;
    }
  }

  echo "Creating directories and links<p>";

  $Dirs = [['int/ArchiveImages',1],  // dir name, access control
           ['int/Contracts',1],
           ['int/Insurance',1],
           ['int/Invoices',1],
           ['int/LogFiles',1],
           ['int/OldStore',1],
           ['int/PAspecs',1],
           ['int/Store',1],
           ['int/Temp',0],
          ];
  $LinkedDirs = ['js','files','cache','images','festfiles'];
  foreach($Dirs as $D) {
    if (!file_exists("../" . $D[0])) {

      mkdir("../" . $D[0],0777,true);
      chmod("../" . $D[0],0777);
      echo "Creating " . $D[0] . "<br>";

      if (!is_writeable("../" . $D[0])) {
        echo "../" . $D[0] . " Is NOT writeable  - aborting for now - you can retry once corrected<p>";
        exit;
      }
    }
    if ($D[1] && !file_exists("../" . $D[0] . "/.htaccess")) file_put_contents("../" . $D[0] . "/.htaccess","order deny,allow\ndeny from all");
  }
  foreach($LinkedDirs as $D) {
    if (!file_exists("../" . $D)) {
      mkdir("../" . $D,0777,true);
      chmod("../" . $D,0777);
    }
    if (!file_exists($D)) symlink ("../" . $D, $D);
  }
  echo "Directories Created<p>";

  // Copy files
  if (!file_exists("../favicon.ico")) {
    if (copy("../images/icons/favicon.ico","../favicon.ico")) {
      echo "Copied the default favicon<br>";
    } else {
      echo "Failed to copy default favicon - aborting for now - you can retry once corrected<p>";
      exit;
    }
  }

  foreach (glob("../images/icons/apple-touch-icon*") as $fn) {
    $dfn = preg_replace('/images\/icons\//','',$fn);
    if (!file_exists($dfn)) {
      if (copy($fn,$dfn)) {
        echo "Copied $fn to $dfn<br>";
      } else {
        echo "Failed to copy $fn to $dfn - aborting for now - you can retry once corrected<p>";
        exit;
      }
    }
  }

  echo "Icon files copied<p>";

  $txt = ":root {\n--main-col:#fcb900;\n";
  $txt .= "--main-col-dark:#993300;\n";
  $txt .= "--main-contrast:#1a0000;\n";
  $txt .= "--header-link:#404040;\n";
  $txt .= "--private_bar:#fff0b3;\n";
  $txt .= '/* Do not edit this file it is dynamically created - edit system data or the Basestyle.css*/\n\n'; 
  // Thats enought for now, rest from System Data

  if (file_put_contents('cache/FestStyle.css',$txt));
    
  $Css = file_get_contents('files/Basestyle.css');

  $txt .= $Css;
  file_put_contents('cache/Style.css',$txt);

  echo "CSS Created<p>";

}

function Create_Databases() {
  global $CONF;
  //  Does the database exist?
  try {
    $db = new mysqli($CONF['host'], $CONF['user'], $CONF['passwd']);
  } catch (\Exception $e) {
    echo $e->getMessage(), PHP_EOL;
    echo "Can't access mysql - aborting for now - you can retry once corrected<p>";
    exit;
  }
  if ($db->select_db($CONF['dbase']) === false) {
    echo "Database to be created .<p>";

    $res = $db->query("CREATE DATABASE IF NOT EXISTS " . $CONF['dbase']);
    if ($db->select_db($CONF['dbase']) === false) {
      echo "Database creation failed " . $db->connect_error;
      exit;
    }
    echo "Database created<br>";

  } else {
    echo "Database already exists.<p>";
  }
}


// Modifys name of database for Skeema to run
function Create_Skeema_local() {
  global $CONF;
//  if (!file_exists("../Schema/.skeema")) {
    $skeema = "schema=" . $CONF['dbase'] . "
default-character-set=utf8mb4
default-collation=utf8mb4_general_ci

host=127.0.0.1
port=3306
user=" . $CONF['user'] . "\n";
    if ($CONF['passwd']) $skeema .= "password='" . $CONF['passwd'] . "'\n";
    file_put_contents("../Schema/.skeema",$skeema);
//  }

  $dbg = ''; // '--debug';
//  chmod("skeema",0755);
  chdir ("../Schema");
  if (file_exists('/usr/bin/skeema')) { // Use systems own copy if available
    echo "About to call Skeema - system version\n";
    system("/usr/bin/skeema $dbg push"); // push for live
  } else {
    echo "About to call Skeema - local version\n";
    system("int/skeema $dbg push"); // push for live
  }
  chdir ("../int");
  echo "Database tables created.<p>";

}

// [Table, id, [data]]

function Preload_Data() {

  global $db,$PLANYEAR,$YEAR,$VERSION;
  include_once("Version.php");

  $Year = gmdate('Y');
  if (empty($PLANYEAR)) $YEAR = $PLANYEAR = $Year;
  // Does not do Email Proformas - see below for them
  $Preloads = [
    ['FestUsers', 1,['Login'=>'system','password'=>'WM/boBz3JdYIA','AccessLevel'=>7,'Roll'=>'Start up']],
    ['FestUsers', 2,['Login'=>'nobody','AccessLevel'=>7,'Roll'=>'Internal Workings']],
    ['FestUsers', 3,['Login'=>'ALL','AccessLevel'=>4,'Roll'=>'Internal Workings','SN'=>'All']],
    ['FestUsers', 4,['Login'=>'dummy','AccessLevel'=>7,'Roll'=>'Dummy Contracts','SN'=>'<span class=NotSide>Dummy Staff Member</span>']],
    ['FestUsers', 5,['Login'=>'reserved']],
    ['FestUsers', 6,['Login'=>'reserved']],
    ['FestUsers', 7,['Login'=>'reserved']],
    ['FestUsers', 8,['Login'=>'reserved']],
    ['FestUsers', 9,['Login'=>'reserved']],
    ['FestUsers', 10,['Login'=>'reserved']],

    ['SystemData',1,['CurVersion'=> $VERSION,'Capabilities'=>
'EnableDocs:1
EnableTLine:0
EnableMusic:1
EnableDance:1
EnableComedy:0
EnableCeilidh:1
EnableFamily:1
EnableOtherPerf:1
EnableTrade:0
EnableEvents:1
EnableMisc:1
EnableFinance:0
EnableAdmin:1
EnableCraft:0
EnableVols:1',
'Features'=>'; Festival
FestName = Festival
ShortName = Fest
ShowYear = ' . $Year . '
PlanYear = ' . $Year . '
HostURL = ' . ($_SERVER['SERVER_NAME'] ?? 'WHAT URL IS THIS?') . '
; There are lots more here to be set up - needs documenting
']],
    ['General',$Year,['Year'=>$Year]],

    ['MapPointTypes',1,['SN'=>'Text','Icon'=>'Text']],
    ['MapPointTypes',2,['SN'=>'Music Venue','Icon'=>'MusicIcon.png']],
    ['MapPointTypes',3,['SN'=>'Car Park','Icon'=>'carparkicon.png']],
    ['MapPointTypes',4,['SN'=>'Toilets','Icon'=>'toileticon.png']],
    ['MapPointTypes',5,['SN'=>'Information','Icon'=>'mapinfo.png']],
    ['MapPointTypes',6,['SN'=>'Dance Venue','Icon'=>'DanceIcon.png']],
    ['MapPointTypes',7,['SN'=>'Bicycle Park','Icon'=>'bicycleicom.png']],
    ['MapPointTypes',8,['SN'=>'Car Charge','Icon'=>'charging.png']],
    ['MapPointTypes',9,['SN'=>'Camping','Icon'=>'Camping.png']],
    ['MapPointTypes',10,['SN'=>'Short Term Car Park','Icon'=>'carparkred.png']],
    ['MapPointTypes',11,['SN'=>'Cup','Icon'=>'tea-hot-icon.png']],
    ['MapPointTypes',12,['SN'=>'Meal','Icon'=>'meal-icon.png']],
    ['MapPointTypes',13,['SN'=>'Beer','Icon'=>'beer-glass-mug-icon.png']],

    ['Directories',1,['SN'=>'Documents', 'Created'=>1, 'Who'=>1, 'Parent'=>1, 'State'=>0, 'AccessLevel'=>0, 'AccessSections'=>'', 'ExtraData'=>'']],
    ['BigEvent',1,['Event'=>-1,'Type'=>'Blank', 'Identifier'=>1,'EventOrder'=>0,'Notes'=>'']],

    ['EventTypes',1,['SN'=>'Dance','Plural'=>'Dances','Public'=>1,'HasDance'=>1,'FirstYear'=>$Year,'Sherlock'=>'int/ShowDanceProg']],
    ['EventTypes',2,['SN'=>'Concert','Plural'=>'Concerts','Public'=>1,'HasMusic'=>1,'FirstYear'=>$Year,'HasRolls'=>1,'IncType'=>1]],
    ['EventTypes',3,['SN'=>'Workshop','Plural'=>'Workshops','Public'=>1,'HasMusic'=>1, 'HasDance'=>1,'FirstYear'=>$Year,'IncType'=>1]],
    ['EventTypes',4,['SN'=>'Session','Plural'=>'Sessions','Public'=>1,'HasMusic'=>1,'FirstYear'=>$Year,'IncType'=>1]],
    ['EventTypes',5,['SN'=>'Ceilidh','Plural'=>'Ceilidhs','Public'=>1,'HasDance'=>1,'HasMusic'=>1,'FirstYear'=>$Year,'HasRolls'=>1,'IncType'=>1]],
    ['EventTypes',6,['SN'=>'Sound Check','Plural'=>'Sound Checks','Public'=>0,'DontList'=>1,'HasMusic'=>1,'NotCrit'=>1, 'FirstYear'=>$Year,'IncType'=>1,'NoPart'=>1]],
    ['EventTypes',7,['SN'=>'Music','Plural'=>'Music','Public'=>1,'HasMusic'=>1,'FirstYear'=>$Year,'IncType'=>1]],
    ['EventTypes',8,['SN'=>'Venue Blocked Out','Plural'=>'Venue Blocked Out','DontList'=>1,'Public'=>0,'FirstYear'=>$Year,'NoPart'=>1]],
    ['EventTypes',9,['SN'=>'Folk Dance','Plural'=>'Folk Dances','Public'=>1,'HasDance'=>1,'HasMusic'=>1,'FirstYear'=>$Year,'HasRolls'=>1]],

    ['PerformerTypes',1,['SN'=>'Dance','FullName'=>'Dance Displays']],
    ['PerformerTypes',2,['SN'=>'Music','FullName'=>'Music']],
    ['PerformerTypes',3,['SN'=>'Comedy','FullName'=>'Comedy']],
    ['PerformerTypes',4,['SN'=>'Family','FullName'=>'Family and Community']],
    ['PerformerTypes',5,['SN'=>'Ceilidh','FullName'=>'Ceilidhs and Folk Dances']],
    ['PerformerTypes',6,['SN'=>'Other','FullName'=>'Other']],

    ['Galleries',1,['SN'=>'All Galleries', 'Level'=>1]],
    ['MainMenu',1,['Menu'=>'']], // Fudge for later 

  ];

  // Now call festdb
  include_once("festdb.php");
  Feature_Reset();
  global $db,$TableIndexes;

  echo "Checking database settings:<p>";
  $res = $db->query('SELECT @@sql_mode');
//  $res=$db->query('SELECT @@hostname');
  if ($res) {
    $ans = $res->fetch_assoc();
//    var_dump($res,$ans);
//    echo "Dumpped<p>";
    if (strstr($ans['@@sql_mode'],'STRICT_TRANS_TABLES')) {
      echo "You have STRICT_TRANS_TABLES set in the database.<br>" .
           'edit /etc/mysql/my.cnf (or /etc/my.cnf) add the line:<br>
            sql_mode = ""<br>
            or if it exists make it empty.';
      exit;
    }
  }

// var_dump($TableIndexes);exit;
  foreach($Preloads as $P) {
    $indx = (isset($TableIndexes[$P[0]])? $TableIndexes[$P[0]] : 'id');
    echo "Checking " . $P[0] . ": " . $P[1] . "<br>";
    if (db_get($P[0],"$indx=" . $P[1])) continue; // already in - skip
    $qry = "INSERT INTO " . $P[0] . " SET ";
    $bits = [];
    $bits[] = " $indx=" . $P[1];
    foreach($P[2] as $k=>$v) $bits[] = " $k='$v' ";
    $qry .= implode(", ",$bits);
    echo "SQL is : $qry<br>";
    $db->query($qry);
  }

  echo "Main Menu Creation<p>";
  $Menus = json_decode(file_get_contents('festfiles/DumpMenu.json'),1);
//  var_dump($Menus);
  foreach($Menus as $M) Gen_Put('MainMenu',$M);

// Email proformas - lots of these read from munged sql dump
  echo "About to Create Email Proformas<p>";

  include_once("Email.php");
  $Pros = Gen_Get_All('EmailProformas');

  $Profs = json_decode(file_get_contents('festfiles/DumpEmails.json'),1);

  foreach ($Profs as $P) {
    foreach($Pros as $Pr) if ($Pr['SN'] == $P['SN']) continue 2;
    unset($P['id']);
    Gen_Put('EmailProformas',$P);
    echo "Added Email Proforma - " . $P['SN'] . "<Br>";
  }

  echo "About to Create Ts And Cs <p>";

  $Ts=Gen_Get_All('TsAndCs2');

  $Cs = json_decode(file_get_contents('festfiles/DumpTsNCs.json'),1);

  foreach ($Cs as $C) {
    foreach($Ts as $T) if ($T['Name'] == $C['Name']) continue 2;
    unset($C['id']);
    Gen_Put('TsAndCs2',$C);
    echo "Added TnC Proforma - " . $C['Name'] . "<Br>";
  }


  echo "About to Import raw features<p>";

  $RFeats = base64_decode(file_get_contents('festfiles/RawFeatures'));

  $RFeatures = parse_ini_string($RFeats);

  $CSys = Gen_Get('SystemData',1);
  $CFeats = $CSys['Features'];

  $CFeatures =  parse_ini_string($CFeats);

  if (strlen($RFeats) > strlen($CFeats)) { // Raw is bigger
    foreach ($CFeatures as $CF=>$CV) {
      if (isset($RFeatures[$CF])) {
        if ($RFeatures[$CF] == $CV) continue; //
        $RFeats = preg_replace("/($CF)( *)?\=.*?$/", "$CF = $CV", $RFeats);
      } else {
        $RFeats .= "$CF = $CV\n";
      }
    }
    $CSys['Features'] = $RFeats;
    Gen_Put('SystemData',$CSys);
  } else { // Current is bigger
    foreach ($RFeatures as $RF=>$RV) {
      if (isset($CFeatures[$RF])) {
        if ($CFeatures[$RF] == $RV) continue; //
        $CFeats = preg_replace("/($RF)( *)?\=.*?$/", "$RF = $RV", $CFeats);
      } else {
        $CFeats .= "$RF = $RV\n";
      }
    }
    $CSys['Features'] = $CFeats;
    Gen_Put('SystemData',$CSys);
  }
  Feature_Reset();
  echo "System data now has Raw Features<p>";
}

function Create_htaccess() {
    $DocRoot = $_SERVER['DOCUMENT_ROOT'];
  if (file_exists("../.htaccess")) {
    // Read ht access, if it does not have rewriteengine on append it, do the same with the rule.  If change write file back
    $htaccess = file_get_contents("../.htaccess");
    $htac_changed = 0;

    if (!strstr($htaccess,"Options FollowSymLinks")) {
      $htaccess .= "Options FollowSymLinks\n";
      $htac_changed = 1;
    }

    if (!strstr($htaccess,"RewriteEngine on")) {
      $htaccess .= "RewriteEngine on\n";
      $htac_changed = 1;
    }

    if (!strstr($htaccess,'RewriteRule ^([^.?]+)$ %{REQUEST_URI}.php [L]')) {
      $htaccess .= 'RewriteRule ^([^.?]+)$ %{REQUEST_URI}.php [L]' . "\n";
      $htac_changed = 1;
    }

    if (!strstr($htaccess,'<Files ~ "\.ini$">')) {
    $htaccess .= '
<Files ~ "\.ini$">
    Order allow,deny
    Deny from all
</Files>';
      $htac_changed = 1;
    }

    if (!strstr($htaccess,'php_value include_path')) {
      $htaccess .= 'php_value include_path "' . get_include_path() . ":" . $DocRoot . "\"\n";
      $htac_changed = 1;
    }

    if ($htac_changed) {
      if (is_writable("../.htaccess")) {
        file_put_contents("../.htaccess",$htaccess);
        echo "htaccess modified<p>";
      } else {
        echo "htaccess needs modification but can't be writen to by Initialise<p>";
        return 0;
      }
    }
  } else {
    $htac = 'Options FollowSymLinks
RewriteEngine on
RewriteRule ^([^.?]+)$ %{REQUEST_URI}.php [L]
' . 'php_value include_path "' . get_include_path() . ":" . $DocRoot . "\"\n";
    $htac .= '
<Files ~ "\.ini$">
    Order allow,deny
    Deny from all
</Files>';

    if (file_put_contents("../.htaccess",$htac)) {
      echo "htaccess created<p>";
    } else {
      echo "htaccess needs Creation but can't be writen to by Initialise<p>";
      return 0;
    }
  }
  return 1;
}

// Updating code - not yet written
function BringUptoDate($oldversion) {



}

function Check_Sysadmin() {

  include_once("DocLib.php");
  include_once("UserLib.php");
  global $Access_Type;

  $Users = Get_AllUsers(2);
  $isasys = 0;

  foreach($Users as $U) if ($U['AccessLevel'] == $Access_Type['SysAdmin']) $isasys = 1;

  if ($isasys) return;  // There is a sysadmin setup - skip

  echo "<form method=post><h2>Setup a sysadmin account</h2>";
  echo "<div class=tablecont><table><tr>" . fm_text("Login",$_REQUEST,'login');
  echo "<tr>" . fm_text("Password",$_REQUEST,'password');
  echo "<tr>" . fm_text("Full Name",$_REQUEST,'SN');
  echo "</table><div><p>";
  echo "<input type=submit name=SETUPSYS value=SETUP>";
  exit;
}

function Setup_Sysadmin() {
  global $Access_Type,$YEAR;
  include_once("UserLib.php");
  include_once("DocLib.php");

  $Users = Get_AllUsers(2);
  $isasys = 0;
  $ans = [];

  if ($Users) foreach($Users as $U) if ($U['AccessLevel'] == $Access_Type['SysAdmin']) $isasys = 1;
  if ($isasys) return;  // There is a sysadmin setup - skip

  $user = ['Login'=>$_REQUEST['login'], 'AccessLevel'=> $Access_Type['SysAdmin'], 'password'=> crypt($_REQUEST['password'],"WM"), 'SN'=>$_REQUEST['SN']];
  $userid = Insert_db('FestUsers',$user,$ans);
  echo "SysAdmin setup.<p>";
  $ans['UserId'] = $userid;
  $ans['Yale'] = rand_string(40);
  $USER = $ans;
  $USERID = $userid;
  setcookie('FEST2',$ans['Yale'], mktime(0,0,0,1,1,$YEAR+1),'/');
  Put_User($ans);
}

function Setup_Map_Data() {
  global $PLANYEAR;
  include_once("MapLib.php");
  Update_MapPoints();

  echo "Map Cache set up<p>";

}

if (isset($_REQUEST['SETUPSYS'])) {
  include_once("fest.php");
  Setup_Sysadmin();
} else {
  Check_PHP_Config();
  Create_Directories();
  Create_Config();
  Create_Databases();
  Create_Skeema_local();
  Preload_Data();
  Setup_Map_Data();
  if (!Create_htaccess()) {
    echo "Please fix and re-run";
    exit;
  };

  include_once("fest.php");
  Check_Sysadmin();

}

echo "All done<p><h2><a href=Staff.php>Now Login</a> Then go to System Data (under admin, and click Update (even if you don't change anything</h2>";


/*

TODO
  chmod
  improve skemea paths
  sql.mode = ""
  php.ini
  check errors on create entries in db
  .php handling


*/

?>
