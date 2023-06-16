// Code for Collecting

function SelectWhoCat(e) {
//debugger;
  var WhoCat = (e.target.id.match(/(\d+$)/))[1]; 
  $('#CollectDance').hide();
  $('#CollectVol').hide();
  $('#CollectOther').hide();
  switch (WhoCat) {
  case "0": $('#CollectDance').show(); return;
  case "1": $('#CollectVol').show(); return;
  case "2": $('#CollectOther').show(); return;
  }
}

function SelectDanceSide() {

}

function SelectTeam(e) {
debugger;
  var Team = (e.target.id.match(/(\d+$)/))[1]; 
  $('.CollectTeam').hide();
  $("#Collect" + Team).show();
}

function SelectVolunteer() {

}

function SelectOther() {

}
  
function EnableAssign() {

}
  
function EnableReturn() {

}
  


  /*
  $VolTeams = [];
  foreach($VolCats as $Ci=>$VC) if (!empty($Collectors[$Ci]) ) $VolTeams[$Ci] = $VC['Name'];
  
  echo "<h2>Assign Tins</h2><div class=CollectDiv>\n";
  echo "<h3>Select Who</h3>";
  echo fm_radio('Category',$WhoCats,$_REQUEST,'WhoCat','class=CollectWho1',0,'class=CollectWho2',0,0,'',' oninput=SelectWhoCat()');
  echo "<div class=CollectDance id=CollectDance hidden>" . fm_select($Dance_Sides,$_REQUEST,'SideId',0,' oninput=SelectDanceSide()') . "</div>";
  
  if ($VolTeams) {
    echo "<div class=CollectVol id=CollectVol hidden>";
    echo fm_radio('Team',$VolTeams,$_REQUEST,'VolTeam','class=CollectTeam1',0,'class=CollectTeam2',0,0,'',' oninput=SelectTeam()');
    foreach($VolCats as $Ci=>$VC) if ($VolTeams[$Ci]) {
      echo "<div class=CollectTeam id=Collect$Ci>" . fm_select($VolTeams[$Ci],$_REQUEST,'VolMemb$Ci',0,' oninput=SelectVolunteer()') . "</div>\n";
    }
    echo "</div>\n";
  }
    
  echo "<div class=CollectOther id=CollectOther hidden>";
    echo fm_text('Name',$_REQUEST,'OtherName',2,'',' oninput=SelectOther()');
    echo "</div>\n";
    

  echo "<h3>Select Tin/Bucket/Reader</h3>";
  
  $Tins = Gen_Get_Cond('CollectingUnit', "Status=0 ORDER BY Name");
  $TinNames = [];
  foreach($Tins as $i=>$T) $TinNames[$i] = $T['Name'];
  
  echo fm_select($TinNames,$_REQUEST,'TinId'); // Consider Type then name in the future

  echo "<p><input id=TinTake class=TinNotYet type=submit name=ACTION value='Assign'>";
  */
