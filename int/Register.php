<?php
  include_once("fest.php");
  include_once("DanceLib.php");
  include_once("MusicLib.php");
  include_once("ProgLib.php");
  include_once("PLib.php");
  include_once("DateTime.php");

  global $PerfTypes;
  $Type = 'D';
  $Mess = '';
  if (isset($_GET['T'])) $T = $_GET['T'];
  if (isset($_POST['T'])) $T = $_POST['T'];
  $Perf = 0; 
  foreach ($PerfTypes as $p=>$d) if (Capability("Enable" . $d[2])) if ($d[4] == $T || $d[2] == $T) { $Perf = $p; $Type = $d[2]; };
//var_dump($Type);


  if (isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
      case 'Check':
        $Sname = Sanitise($_REQUEST['SN'],40);
        $Email = Sanitise($_REQUEST['Email'],40);
        $Contact = Sanitise($_REQUEST['Contact'],40);
        $Sides = Find_Perf_Similar($Sname,'AND IsASide=1');
        if ($Sides) {
          foreach ($Sides as $S) {
            if ($Email == $S['Email']) {
              Send_Link2($S);
              echo "An email has been sent to you with a link, if you don't see it, please check your Spam folder.";
              dotail();
            }
            if ($Email == $S['AltEmail']) {
              Send_Link2($S,'Alt');
              echo "An email has been sent to you with a link, if you don't see it, please check your Spam folder.";
              dotail();
            }
          }
         
      
    
    
    }
  }
  
  dohead("Registering", ["/js/Participants.js"]);  
  
  echo "<h1>This is for Dance sides to register an interest in taking part in Chippenham</h1>";
  echo "The first check is to see if you are already in our database.  If so a link will be provided to enable you to update your records.<p>";
  echo "If not, you will be asked to provide some information so we can check you are a real dance side, not an imposter.  Be patient please.<p>";
  
  echo "<form method=post action=Register>";
  echo "<table border><tr>" . fm_text('Your Name',$_POST,'Contact');
  echo "<tr>" . fm_text('Name of Dance Side',$_POST,'SN');
  echo "<tr>" . fm_text('Email Address',$_POST,'Email');
  echo "<tr><td><input type=submit name=ACTION value='Check')";
  echo "</table>";
  
  dotail();
  
  
  if (isset($_POST['SN'])) {
    if (strlen($_POST['SN']) < 3) {
      $Mess = "Name too short";
    } else {
      $similar = Find_Perf_Similar($_POST['SN']);
      if (!isset($_POST['CONTINUE']) && $similar) {
        dostaffhead("Add Performer - already exists");
        echo "<h2> The following already exist:</h2><ul>";
        foreach ($similar as $i=>$side) {
          echo "<li><b><a href=AddPerf?sidenum=" . $side['SideId']  . ">" . $side['SN'] . "</b> is: ";
          $cnt = 0;
          foreach ($PerfTypes as $p=>$d) if (Capability("Enable" . $d[2])) if ($side[$d[0]]) {
            if ($cnt++) echo ", ";
            echo $p;
          }
          if ($side['SideStatus']) {
            if ($side['SideStatus'] == 1) echo " [[No Longer Active]] ";          
            if ($side['SideStatus'] == 2) echo " [[Banned See notes]] ";   
          }       
        }
        echo "</ul>or <form method=post>" . fm_hidden('SN',$_POST['SN']) . fm_hidden('T',$Type) . "<input type=submit name=CONTINUE value=Continue><p>or<p>";  
//        dotail();
      } else { // It is new
        foreach ($PerfTypes as $p=>$d) if (Capability("Enable" . $d[2])) $_POST[$d[0]]=0;
        $_POST[$PerfTypes[$Perf][0]] = 1;
        $_POST['AccessKey'] = rand_string(40);
        
        $snum = Insert_db_post('Sides',$Side);
        $Side = Get_Side($snum);
        $_POST['P'] = $_GET['sidenum'] = $snum;
        $Sidey = Default_SY();

        dostaffhead("Add Performer");
        
        Show_Part($Side,'Side',1,'AddPerf');
        Show_Perf_Year($snum,$Sidey,$YEAR,Access('Staff'));
        echo "<Center><input type=Submit name=Create value='Create' class=Button$BUTTON ></center>\n";
        echo "</form>\n";
        dotail();
      }
    }
  }

  dostaffhead("Add Performer", ["/js/clipboard.min.js", "/js/emailclick.js", "/js/Participants.js"]);

  include_once("MusicLib.php");
  include_once("DateTime.php");
  include_once("PLib.php");

// This is a front end to AddPerf to create the entry after verifying the name is unique then allow for incremental edits to stick
// After verify and save take straight to AddPerf
// 

  if ($Mess) echo "<div class=Err>$Mess</div><p>";
  echo "<form method=post>" . fm_hidden('T',$T);
  echo fm_text('Name',$_POST,'SN',2);
  echo "<input type=submit name=Create value=Create>";
  echo "</form>";
  
  dotail();

?>
