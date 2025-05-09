<?php
  include_once("fest.php");
  A_Check('Committee','Venues');

  dostaffhead("Manage Event Types");

  include_once("ProgLib.php");
  include_once("TradeLib.php");
  global $EType_States,$PLANYEAR;

//  echo "<div class='content'><h2>Manage Event Types</h2>\n";
  $Types = Get_Event_Types(1);
  if (UpdateMany('EventTypes','Put_Event_Type',$Types,1)) {
    $Types = Event_Types_ReRead();
    
    $Vens = Get_Active_Venues(1);
    $Vids = [];
    
    if ($Vens) foreach ($Vens as $Ven) $Vids[] = $Ven['VenueId'];
    
    file_put_contents("../cache/VenueList",json_encode($Vids));
    
  }
  $coln = 0;

  echo "Please don't have too many types.<p>\n";
  echo "The only event types that should be not public are Sound Checks and blocked out venues (probably)<br>\n";
  echo "Set Dont List to prevent the events being a category under the public timetables<br>\n";
  echo "Set Inc Type to indicate event type in description if it is not part of the events name.<br>";
  echo "State drives lots: - set to draft to enable the performers to see their own events. Set to complete when all events of given type are in<br>\n";
  echo "State:Draft also allows selected events to be shown, higher ratings show all unless deselected<p>\n";
  echo "Set <b>No Part</b> if event type is valid without any participants.<br>";
  echo "First Year - first year this event type is listed - prevents backtracking.<br>\n";
  echo "If Banner is blank the default will be used<p>";

  echo "Set the Not critical flag for sound checks - means that this event type does not have to be complete for contract signing.<br>";
  echo "Set the <b>Use Imp</b> flag to bring headline particpants to top of an event, they still get bigger fonts.<br>";
  echo "Set Format to drive EventShow rules 0=All Large, 2=Switch to large at Importance-High, 9+=All Small<br>";
  echo "Set The Map Feat to the MapFeature to enable maps of locations 0 = no map<br>";
  echo "Set Concert to surpress publication of indivdual act times<br>";
  echo "Set Sherlock to number to select event by number.  Text string used instead of Sherlock call - eg dance<p>";

  echo "<form method=post action=EventTypes>";
  echo "<div class=Scrolltable><table id=indextable border>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Event Type</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Plural</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Public</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Has<br>Dance</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Has<br>Music</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Has Other</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Not<br>Critical</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Use<br>Imp</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Age<br>Rng</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Fmt</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>State</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Inc<br>Type</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>No<br>Part</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Dont<br>List</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Has Rolls</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>First<br>Year</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Banner</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Map Feat</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Concert</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Imp</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Sherlock</a>\n";
  echo "</thead><tbody>";
  foreach($Types as $t) {
    $i = $t['ETypeNo'];
    echo "<tr><td>$i" . fm_text1("",$t,'SN',1,'','',"SN$i");
    echo          fm_text1("",$t,'Plural',1,'','',"Plural$i");
    echo "<td>" . fm_checkbox('',$t,'Public','',"Public$i");
    echo "<td>" . fm_checkbox('',$t,'HasDance','',"HasDance$i");
    echo "<td>" . fm_checkbox('',$t,'HasMusic','',"HasMusic$i");
    echo "<td>" . fm_checkbox('',$t,'HasOther','',"HasOther$i");
    echo "<td>" . fm_checkbox('',$t,'NotCrit','',"NotCrit$i");
    echo "<td>" . fm_checkbox('',$t,'UseImp','',"UseImp$i");
    echo "<td>" . fm_checkbox('',$t,'AgeRange','',"AgeRange$i");
    echo fm_number1('',$t,'Format','','min=0 max=1000 size=4 maxlength=4',"Format$i");
    echo "<td>" . fm_select($EType_States,$t,'State',0,'',"State$i");
    echo "<td>" . fm_checkbox('',$t,'IncType','',"IncType$i");
    echo "<td>" . fm_checkbox('',$t,'NoPart','',"NoPart$i");
    echo "<td>" . fm_checkbox('',$t,'DontList','',"DontList$i");
    echo "<td>" . fm_checkbox('',$t,'HasRolls','',"HasRolls$i");
    echo fm_number1('',$t,'FirstYear','','min=1980 max=2500 size=4 maxlength=4',"FirstYear$i");
    echo          fm_text1("",$t,'Banner',1,'','',"Banner$i");
    echo fm_number1('',$t,'MapFeatNum','','min=0 max=20',"MapFeatNum$i");
    echo "<td>" . fm_checkbox('',$t,'IsConcert','',"IsConcert$i");
    echo fm_number1('',$t,'Importance','','min=0 max=100',"Importance$i");
    echo          fm_text1("",$t,'Sherlock',1,'','',"Sherlock$i");
    echo "\n";
  }
  echo "<tr><td><td><input type=text name=SN0 >";
  echo "<td><input type=text name=Plural0 >";
  echo "<td><input type=checkbox name=Public0>";
  echo "<td><input type=checkbox name=HasDance0>";
  echo "<td><input type=checkbox name=HasMusic0>";
  echo "<td><input type=checkbox name=HasOther0>";
  echo "<td><input type=checkbox name=NotCrit0>";
  echo "<td><input type=checkbox name=UseImp0>";
  echo "<td><input type=checkbox name=AgeRange0>";
  echo "<td><input type=number min=0 max=1000 size=4 maxlength=4 name=Format0>";
  echo "<td>" . fm_select($EType_States,$t,"State0");
  echo "<td><input type=checkbox name=IncType0>";
  echo "<td><input type=checkbox name=NoPart0>";
  echo "<td><input type=checkbox name=DontList0>";
  echo "<td><input type=checkbox name=HasRolls0>";
//  echo fm_number1('',$t,'FirstYear','','size=5',"FirstYear0");
  echo "<td><input type=number name=FirstYear0 min=1980 max=2500 size=4 maxlength=4 value=$PLANYEAR>";
  echo "<td><input type=text name=Banner0 >";
  echo "<td><input type=number name=MapFeatNum0 min=0 max=20>";
  echo "<td><input type=checkbox name=IsConcert0>";
  echo "<td><input type=number name=Importance0 min=0 max=100 size=5>";
  echo "<td><input type=text name=Sherlock0>";

  echo "</table></div>\n";
  echo "<input type=submit name=Update value=Update >\n";
  echo "</form></div>";

  dotail();

?>
