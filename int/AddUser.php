<?php
  include_once("fest.php");

  A_Check('Committee','Users');

  dostaffhead("Add/Change User");
  include_once("UserLib.php");
  global $Sections,$CONF;

  Set_User_Help();

  echo "<h2>Add/Edit Fest Con Users</h2>\n";
  echo "<form method=post action='AddUser'>\n";
  if (isset($_POST['UserId'])) { /* Response to update button */
    $unum = $_POST['UserId'];
    if ($unum > 0) {                                 // existing User
    
      $Stypes = [];
      $Ttypes = Gen_Get_All('UserCap',"WHERE User=$unum");

      foreach($Ttypes as $T) $Stypes[$T['Capability']] = $T['Level'];
    
      $User = Get_User($unum);
      if (isset($_POST['ACTION'])) {
        switch ($_POST['ACTION']) {
        case 'Set Password' :
          $hash = crypt($_POST['NewPass'],"WM");
          $User['password'] = $hash;
          $a = Put_User($User);
          break;
        case 'Remove Access' :
          $User['AccessLevel'] = 0;
          $User['password'] = 'impossible2guess'; // that is not a valid password
          $User['FestEmail'] = '';
          $User['Roll'] = 'No Access' . date(' j/m/Y');
          $User['Contacts'] = 0;
          foreach ($Sections as $sec) $User[$sec] = 0;
          $a = Put_User($User);                 
        }
      } else {
        Update_db_post('FestUsers',$User);
      }
    } else { /* New User */
      $proc = 1;
      if (!isset($_POST['SN'])) {
        echo "<h2 class=ERR>NO NAME GIVEN</h2>\n";
        $proc = 0;
      }
      if ($proc && !isset($_POST['Login'])) {
        echo "<h2 class=ERR>NO login GIVEN</h2>\n";
        $proc = 0;
      }
      $Exist = Gen_Get_Cond('FestUsers',"Login='" . $_POST['Login'] ."'",'UserId');
      if ($proc && !empty($Exist)) {
        echo "<h2 class=ERR>Username not unique</h2>\n";
        $proc = 0;      
      }
      $unum = Insert_db_post('FestUsers',$User,$proc);
    }
  } elseif (isset($_GET['usernum']) && $_GET['usernum']) {
    $unum = $_GET['usernum'];
    $User = Get_User($unum);
  } else {
    $unum = -1;
  }

  $Stypes = [];
  if ($unum > 0) {                                 // existing User   
    $Ttypes = Gen_Get_All('UserCap',"WHERE User=$unum");
    foreach($Ttypes as $T) $Stypes[$T['Capability']] = $T['Level'];
  }
// var_dump($Stypes);

//  echo "<!-- " . var_dump($User) . " -->\n";
  echo "<div class=tablecont><table width=90% border>\n";
    echo "<tr><td>User Id:<td>";
      if (isset($unum) && $unum > 0) {
        echo $unum . fm_hidden('UserId',$unum);
        Register_Autoupdate('FestUsers',$unum);
      } else {
        echo fm_hidden('UserId',-1);
        $User['AccessLevel'] = $Access_Type['Committee'];
      }
    echo "<tr>" . fm_text('Name', $User,'SN',3,'','autocomplete=off');
    echo "<tr>" . fm_text('Abrev', $User,'Abrev',1,'','autocomplete=off');
    echo "<tr>" . fm_text('Personal Email',$User,'Email',3,'','autocomplete=off');
//    echo "<tr>" . fm_text('ClassEmail',$User,'ClassEmail',3,'','autocomplete=off') . "<td>Festival level email to send replies to if booking";
    echo "<tr>" . fm_text('Phone',$User,'Phone',1,'','autocomplete=off');
    echo "<tr>" . fm_text(Feature('ShortName') . " Email",$User,'FestEmail',1,'','autocomplete=off');
    echo "<tr>" . fm_text('Login',$User,'Login');
    echo "<tr>" . fm_text('Roll',$User,'Roll',3);
    echo "<tr>" . fm_text('Relative Order',$User,'RelOrder',3);
    echo "<tr><td>No Tasks (test users only) " . fm_checkbox('',$User,'NoTasks');
    echo "<tr><td>Access Level<td>" . fm_select($Access_Levels,$User,'AccessLevel',0,'','',$USER['AccessLevel']);
    echo "<tr>" . fm_text('Image', $User,'Image',3);
    echo "<tr>" . fm_radio('Show on Contacts Page',$User_Public_Vis,$User,'Contacts');
    $r = 0;
    if ($unum > 0) {
      foreach($Sections as $Si => $sec) {
        if ($Si == 0) continue;
        if ((($r++)&1) == 0) echo "<tr>";
        echo fm_radio("Change " . $sec ,$Area_Levels,$Stypes,$Si,'',1,'',"UserCap:$Si");
      }
    }
    if (isset($User['LastAccess'])) echo "<tr><td>Last Login:<td>" . ($User['LastAccess']? date('d/m/y H:i:s',$User['LastAccess']):'Never');
    if (Access('SysAdmin')) {
      echo "<tr>" . fm_text('Change Sent',$User,'ChangeSent',1,'','readonly');
      if ($unum > 0) { 
        if (!isset($User['AccessKey']) || $User['AccessKey'] == '') {
          $User['AccessKey'] = rand_string(40);
          Put_User($User);
        }
        echo "<tr>" . fm_text('Access Key',$User,'AccessKey',1,'','readonly'); 
        if (!empty($CONF['testing'])) {
          echo "<td><a href=Login.php?ACTION=ActAs&i=$unum&k=" . $User['AccessKey'] . ">Use</a>";
        }
      }
      echo "<tr>" . fm_textarea('Prefs',$User,'Prefs',6,2);
      echo "<tr><td>Log Use" . fm_checkbox('',$User,'LogUse');
    }
  if (Access('SysAdmin')) echo "<tr><td class=NotSide>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea>";  
    echo "</table></div>\n";

  if ($unum > 0) {
    echo "<Center><input type=Submit name='Update' value='Update'>\n";
    echo "</center>\n";
    echo "</form><form method=post action=AddUser>" . fm_hidden('UserId',$unum);
    echo " <input type=text name=NewPass size=10>";
    echo "<input type=submit name=ACTION value='Set Password'>\n";

    echo "<input type=submit name=ACTION value='Remove Access' " .
                  "onClick=\"javascript:return confirm('are you sure you want to remove this user?');\"></form> "; 

    echo "<h2><a href=Welcome?U=$unum>Send Welcome Email with New Password Link</a> , \n";
  } else { 
    echo "<Center><input type=Submit name=Create value='Create'></center>\n";
    echo "</form>\n<h2>";
  }
  echo "<a href=ListUsers?FULL>List Users</a> , \n";
  if ($unum >0) echo "<a href=AddUser>Add Another User</a>\n";
  echo "</h2>";

  dotail();
?>
