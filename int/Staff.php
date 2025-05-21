<?php
  include_once("fest.php");
  /* Remove any Participant overlay */
  if (isset($_COOKIE['FESTD'])) {
    unset($_COOKIE['FESTD']);
    setcookie('FESTD','',1,'/');
  }
  A_Check('Upload');
  $host= "https://" . $_SERVER['HTTP_HOST'];

  dostaffhead("Staff Pages", ["/js/jquery.typeahead.min.js", "/css/jquery.typeahead.min.css", "/js/Staff.js"]);

  global $YEAR,$PLANYEAR,$YEARDATA,$Event_Types,$VolCats,$VERSION,$FESTSYS;
  include_once("ProgLib.php");
  include_once("TradeLib.php");
  include_once("VolLib.php");
  $Years = Get_Years();
  $Days = array('All','Fri','Sat','Sun','Mon');
  $Heads = [];

  $ETypes = [];
  foreach($Event_Types as $eti => $ET) $ETypes[$eti] = $ET['Plural'];
  $VolTeams = [0=>''];
  foreach($VolCats as $V) $VolTeams[$V['id']] = $V['Name'];


  function StaffTable($Section,$Heading,$cols=1) {
    global $Heads;
    static $ColNum = 3;
    $txt = '';
    if ($Section != 'Any' && !Capability("Enable$Section")) return '';
    $Heads[] = $Heading;
    if ($ColNum+$cols > 3) {
      $txt .= "<tr>";
      $ColNum =0;
    }
    $hnam = preg_replace("/[^A-Za-z0-9]/", '', $Heading);
    $txt .= "<td class=Stafftd colspan=$cols >";
    $txt .= "<h2 id='Staff$hnam'>$Heading</h2>";
    $ColNum+=$cols;
//var_dump($Heads);
    return $txt;
  }

  global $ErrorMessage;
  if (!empty($ErrorMessage)) echo "<h2 class=ERR>$ErrorMessage</h2>";

//echo php_ini_loaded_file() . "<P>";

  echo "<h2>";
  echo "<div style=float:left; width: 33%; text-align: left>Staff Pages - $YEAR</div>";
  if (isset($Years[$YEARDATA['PrevFest']]))
    echo " <div style='float:left; width: 33%; text-align:center; font-weight:normal;'> For other years select &gt;&gt;&gt;</div>";
  echo "<div style=float:right; width: 33%; text-align: right>";
  if (isset($Years[$YEARDATA['PrevFest']])) {
    $Lookback = Feature('LookBackYears',1);
    if ($Lookback == 1) {
      echo "<a href=Staff?Y=" . $YEARDATA['PrevFest'] .">" . $YEARDATA['PrevFest'] . "</a> &nbsp; ";
    } else {
      $BackData = Gen_Get_All('General');
      $BDidx = [];
      foreach ($BackData as $i=>$BD) $BDidx[$BD['Year']] = $i;
      $List = [];

      $Prev = $YEARDATA['PrevFest'];
//      $Previ = $BDidx[$Prev];
      while (Count($List) < $Lookback) {
        $List[] = "<a href=Staff?Y=$Prev>$Prev</a> &nbsp; ";
        $Prev = $BackData[$BDidx[$Prev]]['PrevFest'];
        if (empty($Prev)) break;
      }
//      var_dump($List);
      foreach(array_reverse($List) as $L) echo $L;
    }
  }
  if (isset($Years[$YEARDATA['NextFest']]) && ($YEARDATA['NextFest'] != $YEAR)) echo "<a href=Staff?Y=" . $YEARDATA['NextFest'] .">" . $YEARDATA['NextFest'] . "</a>\n";
  echo "</div>";
  echo "</h2>\n";

  $txt = "<div class=tablecont><table border width=100% class=Staff style='min-width:800px'>\n";

  if ($x = StaffTable('Docs','Document Storage')) {
    $txt .= $x;
      $txt .= "<ul>\n";
      if (Access('Staff')) {
        $txt .= "<li><a href=Dir>View Document Storage</a>\n";
        $txt .= "<li><a href=Search>Search Document Storage</a>\n";
      }
      $txt .= "<p>";
//      $txt .= "<li><a href=ProgrammeDraft1.pdf>Programme Draft</a>\n";
      $txt .= "<li><a href=StaffHelp>General Help</a>\n";

      if (Access('SysAdmin')) {
        $txt .= "<p>";
        $txt .= "<li class=smalltext><a href=DirRebuild?SC>Scan Directories - Report File/Database discrepancies</a>";
//      $txt .= "<li><a href=DirRebuild?FI>Rebuild Directorys - Files are YEARDATA</a>";
//      $txt .= "<li><a href=DirRebuild?DB>Rebuild Directorys - Database is YEARDATA</a>";
      }
      $txt .= "</ul>\n";
    }

// *********************** TIMELINE ****************************************************
  if ($x = StaffTable('TLine','Timeline')) {
    $txt .= $x;
    $txt .= "<ul>\n";
    $txt .= "<li><a href=TimeLine?Y=$YEAR>Time Line Management</a>\n<p>";
    $txt .= "<li><a href=TLHelp>Timeline Help</a>\n";
//    $txt .= "<li>Timeline Stats\n";
    if (Access('SysAdmin')) {
      $txt .= "<p>";
//      $txt .= "<li class=smalltext><a href=TLImport1>Timeline Import 1</a>\n";
      }
    $txt .= "</ul><p>\n";
  }

// *********************** DANCE ****************************************************
  if ($x = StaffTable('Dance','Dance',2)) {
    $txt .= $x;
    if (Access('SysAdmin')) $txt .= "<div class=tablecont><table class=FullWidth><tr><td>";
    $txt .= "<ul>\n";
    if (Access('Staff','Dance')) {
      $txt .= "<li><a href=InviteDance?Y=$YEAR>Invite Dance Sides</a>\n";
      $txt .= "<li><a href=InviteDance?Y=$YEAR&INVITED>List Ongoing Dance Sides</a>\n";
    }
    if (Access('Staff')) {

      $txt .= "<li><a href=ListDance?SEL=Coming&Y=$YEAR>List Dance Sides Coming</a>\n";
      $txt .= "<li><a href=DanceSummary?Y=$YEAR>Dance Sides Summary</a>\n";
    }
    if (Access('Staff','Dance')) $txt .= "<li><a href=CreatePerf?T=Dance&Y=$YEAR>Add Dance Side to Database</a>";

    if (Access('Staff'))  $txt .= "<li><a href=ListDance?SEL=ALL&Y=$YEAR>List All Dance Sides in Database</a>\n";
    $txt .= "<li><a href=DanceFAQ>Dance FAQ</a>\n";
    if (Access('Staff','Dance')) {
      if ($YEAR == $PLANYEAR) {
        $txt .= "<li><a href=NewDanceProg?Cond=1&Pub=0&Y=$YEAR>Edit Dance Programme</a>";
      } else {
        $txt .= "<li><a href=NewDanceProg?Y=Cond=1&Pub=0&$YEAR&SAND>Edit $YEAR Dance Programme in Sandbox</a>";
      }
    }
    $txt .= "<li><a href=ShowDanceProg?Pub=0&Y=$YEAR>View Dance Programme</a>";
    $txt .= "<li><a href=/Map?F=3>Dance Location Map</a>";
    $txt .= "<li><a href=/LineUp?T=Dance&FORCE&Y=$PLANYEAR>Dance Lineup</a> (Even if not public)";
    if (Access('Staff','Dance')) $txt .= "<li><a href=Register?ACTION=List>Side Registrations</a>";
    $txt .= "<li><a href=ListDance?SEL=TinList&Y=$YEAR>Just a list of sides and days</a>\n";

    if (Access('SysAdmin')) {
      $txt .= "<td><ul>";
//      $txt .= "<li><a href=ShowDanceProg?Y=$YEAR>View Dance Programme</a>";

      $txt .= "<li class=smalltext><a href=ShowDanceProg?Cond=1&Y=$YEAR>Condensed Dance Programme</a>";
      $txt .= "<li class=smalltext><a href=DanceCheck?Y=$YEAR>Dance Checking</a>";
      $txt .= "<li class=smalltext><a href=DanceTypes>Set Dance Types</a>";
//      $txt .= "<li class=smalltext><a href=LineUpDance?MIN&Y=$YEAR>Picture free List of Dance Sides Coming</a>\n";
//      $txt .= "<li class=smalltext><a href=ModifyDance2>Modify Dance Structure #2</a>\n";
      $txt .= "<li class=smalltext><a href=WhereDance?Y=$YEAR>Where did Dance Sides Come from</a>\n";
//      $txt .= "<td width=300px>";
      $txt .= "<li class=smalltext><a href=PrintLabels?Y=$YEAR>Print Address Labels</a>";
      $txt .= "<li class=smalltext><a href=CarPark?Y=$YEAR>Car Park Tickets</a>";
      if ($YEAR == $PLANYEAR) $txt .= "<li class=smalltext><a href=WristbandsSent>Mark Wristbands Sent</a>";
      $txt .= "<li class=smalltext><a href=ShowDanceProg?Cond=1&Pub=1&Y=$YEAR>Public Dance Programme</a>";
//      $txt .= "<li class=smalltext><a href=FixBug3?Y=$YEAR>Create/Copy missing SideYear records after Date Change</a>";
//      $txt .= "<li class=smalltext><a href=FixBug2?Y=$YEAR>Change order of message records</a>";
//      $txt .= "<td width=300px>";
      $txt .= "<li class=smalltext><a href=ShowDanceProg?Head=0&Day=Sat&Y=$YEAR>Dance Programme - Sat - no headers</a>";
      $txt .= "<li class=smalltext><a href=ShowDanceProg?Head=0&Day=Sun&Y=$YEAR>Dance Programme - Sun - no headers</a>";
      $txt .= "<li class=smalltext><a href=ShowDanceProg?Head=0&Day=Mon&Y=$YEAR>Dance Programme - Mon - no headers</a>";
      $txt .= "<li class=smalltext><a href=CheckDuplicates?Y=$YEAR>Check for Duplicate Year Tables Entries</a>";
//      $txt .= "<li class=smalltext><a href=ImportDance2>Import Appalachian List</a>"; // Should never be needed again
//      $txt .= "<li class=smalltext><a href=CheckAccessKeys>Check and fix Blank Access Keys</a>";
      $txt .= "<li class=smalltext><a href=ResetImageSizes>Scan and save all Perf Image sizes</a>";
      $txt .= "</ul></table></div>\n";
    }
    $txt .= "</ul>\n";
  }

// *********************** MUSIC ****************************************************
  if ($x = StaffTable('Music','Music')) {
    $txt .= $x;
    $txt .= "<ul>\n";
    $txt .= "<li><a href=MusicFAQ>Music FAQ</a>\n";
    if (Access('Staff')) {
      $txt .= "<li><a href=ListMusic?SEL=Avail&Y=$YEAR&T=M>List Music Acts Available</a>\n";
      $txt .= "<li><a href=ListMusic?SEL=Booking&Y=$YEAR&T=M>List Music Acts Booking</a>\n";
      $txt .= "<li><a href=ListMusic?SEL=BookingLastYear&Y=$YEAR&T=M>List Music Acts Booking Last Year</a>\n";
      $txt .= "<li><a href=ListMusic?SEL=ALL&Y=$YEAR&T=M>List All Music Acts in Database</a>\n";
//      $txt .= "<li>Music Acts Summary"; //<a href=MusicSummary?Y=$YEAR>Music Acts Summary</a>\n";
    }
    $txt .= "<li><a href=/LineUp?T=Music&FORCE&Y=$PLANYEAR>Music Lineup</a> (Even if not public)";
    if (Access('Staff','Music')) {
//      $txt .= "<li>Invite Music Acts\n";
      $txt .= "<li><a href=CreatePerf?T=Music&Y=$YEAR>Add Music Act to Database</a>";
/*
//      if ($YEAR == $PLANYEAR) $txt .= "<li><a href=MusicProg?>Edit Music Programming</a>";
*/
//      $txt .= "<li>Edit Music Programming";
      if (Access('SysAdmin')) {
        $txt .= "<li><a href=ShowMusicProg?Y=$YEAR>View Music Programming\n</a>";
      } else {
//        $txt .= "<li>View Music Programming\n";
      }
    } else {
//      $txt .= "<li><a href=ShowMusicProg?Y=$YEAR>View Music Programme</a>";
    }
    if (Access('SysAdmin')) {
      $txt .= "<p><div class=tablecont><table><tr><td>";
      $txt .= "<li class=smalltext><a href=ShowMusicProg?Pub=1&Y=$YEAR>Public Music Programme</a>";
      $txt .= "<li class=smalltext><a href=MusicTypes>Set Music Types</a>";
//      $txt .= "<li class=smalltext><a href=ResetImageSizes?PERF>Scan and save Image sizes</a>";
//      $txt .= "<li class=smalltext><a href=CopyActYear>Copy all ActYear data to SideYear</a>";
//      $txt .= "<li class=smalltext><a href=FixBug5?Y=$YEAR>Create/Copy missing Music SideYear records after Date Change</a>";
//      $txt .= "<li class=smalltext><a href=CopyLast2This?Y=$YEAR>Create/Copy Last years music acts to this year</a>";
      $txt .= "</table></div><p>\n";
    }
    $txt .= "<li><a href=ContractView?t=1>Dummy Music Contract</a>";
//    $txt .= "<li><a href=LiveNLoudView?Y=$YEAR>Show Live N Loud applications</a>";
//    $txt .= "<li><a href=BuskersBashView?Y=$YEAR>Show Buskers Bash applications</a>";
//    if (Access('SysAdmin')) $txt .= "<li class=smalltext><a href=LiveNLoudEmail>Send LNL bulk email</a>";
    $txt .= "</ul>\n";
  }

// *********************** Comedy, Childrens Ent, Other Perf
  if ($x = StaffTable('Comedy','Comedy')) {
    $txt .= $x;
    $txt .= "<ul>\n";
    if (Access('Staff')) {
      $txt .= "<li><a href=ListMusic?SEL=ALL&Y=$YEAR&T=C>List All Comedy Performers in Database</a>\n";
      $txt .= "<li><a href=ListMusic?SEL=Booking&Y=$YEAR&T=C>List Comedy Performers Booking</a>\n";
    }
    if (Access('Staff','Comedy')) {
      $txt .= "<li><a href=CreatePerf?T=C&Y=$YEAR>Add Comedy Performer to Database</a>";
    }
    $txt .= "<li><a href=/LineUp?T=Comedy&FORCE&Y=$PLANYEAR>Comedy Lineup</a> (Even if not public)";

    $txt .= "</ul>\n";
  }
  if ($x = StaffTable('Ceilidh','Ceilidhs and Dances')) {
    $txt .= $x;
    $txt .= "<ul>\n";
    if (Access('Staff')) {
      $txt .= "<li><a href=ListMusic?SEL=ALL&Y=$YEAR&T=H>List All Ceilidh Performers in Database</a>\n";
      $txt .= "<li><a href=ListMusic?SEL=Booking&Y=$YEAR&T=H>List Ceilidh Performers Booking</a>\n";
    }
    if (Access('Staff','Ceilidh')) {
      $txt .= "<li><a href=CreatePerf?T=H&Y=$YEAR>Add Ceilidh Performer to Database</a>";
    }
    $txt .= "<li><a href=/LineUp?T=Ceilidh&FORCE&Y=$PLANYEAR>Ceilidh Lineup</a> (Even if not public)";

    $txt .= "</ul>\n";
  }
  if ($x = StaffTable('Family',"Children's Entertainers")) {
    $txt .= $x;
    $txt .= "<ul>\n";
    if (Access('Staff')) {
      $txt .= "<li><a href=ListMusic?SEL=ALL&Y=$YEAR&T=Y>List All Children's Entertainers in Database</a>\n";
      $txt .= "<li><a href=ListMusic?SEL=Booking&Y=$YEAR&T=Y>List Children's Entertainers Booking</a>\n";
    }
    if (Access('Staff','Family')) {
      $txt .= "<li><a href=CreatePerf?T=Y&Y=$YEAR>Add Children's Entertainers to Database</a>";
    }
    $txt .= "<li><a href=/LineUp?T=Family&FORCE&Y=$PLANYEAR>Family Lineup</a> (Even if not public)";

    $txt .= "</ul>\n";
    $txt .= "<h2>Youth</h2>";
    $txt .= "<ul>\n";
    if (Access('Staff')) {
      $txt .= "<li><a href=ListMusic?SEL=ALL&Y=$YEAR&T=U>List All Youth Activity Organisers in Database</a>\n";
      $txt .= "<li><a href=ListMusic?SEL=Booking&Y=$YEAR&T=U>List Youth Activity Organisers Booking</a>\n";
    }
    if (Access('Staff','Youth')) {
      $txt .= "<li><a href=CreatePerf?T=U&Y=$YEAR>Add Youth Activity Organiser to Database</a>";
    }
    $txt .= "<li><a href=/LineUp?T=Youth&FORCE&Y=$PLANYEAR>Youth Lineup</a> (Even if not public)";

    $txt .= "</ul>\n";

  }
  if ($x = StaffTable('OtherPerf', 'Other Performers')) {
    $txt .= $x;
    $txt .= "<ul>\n";
    if (Access('Staff')) {
      $txt .= "<li><a href=ListMusic?SEL=ALL&Y=$YEAR&T=O>List All Other Performers in Database</a>\n";
      $txt .= "<li><a href=ListMusic?SEL=Booking&Y=$YEAR&T=O>List Other Performers Booking</a>\n";
    }
    if (Access('Staff','OtherPerf')) {
      $txt .= "<li><a href=CreatePerf?T=O&Y=$YEAR>Add Other Performer to Database</a>";
    }
    $txt .= "<li><a href=/LineUp?T=Other&FORCE>Other Lineup</a> (Even if not public)";
    if (Access('Staff','OtherPerf')) {
      $txt .= "<p><li><a href=ListMusic?SEL=ALL&Y=$YEAR&T=Z>List All Acts without Performer Types set</a>\n";
    }

    $txt .= "</ul>\n";
  }

// *********************** STALLS   ****************************************************
  if ($x = StaffTable('Trade','Trade',2)) {
    $txt .= $x;
    $Tlocs = Get_Trade_Locs(0,"WHERE InUse=1");
    $ld = ['l'=>Feature('TradeBaseMap')];
    $txt .= "<table><tr><td><ul>\n";
    $txt .= "<li><a href=ListCTrade?Y=$YEAR>List Active Traders This Year</a>\n";
    $txt .= "<li><a href=ListTrade?Y=$YEAR>List All Traders</a>\n";
    $txt .= "<li><a href=TradeFAQ>Trade FAQ</a>\n";
    $txt .= "<li><a href=ListCTrade?Y=$YEAR&SUM>Traders Summary</a>\n";

    $txt .= "<li><form method=Post action=TradeStandMap?STAFF class=staffform>";
      $txt .= "<input type=submit name=l value='Trade Stand Map' id=staffformid>" .
                fm_hidden('Y',$YEAR) .
                fm_select($Tlocs,$ld,'l',0," onchange=this.form.submit()") . "</form>\n";

    $txt .= "<li><a href=TradeShow>Trade Show</a>\n";
    if (Access('Committee','Trade')) {
      $txt .= "<li><a href=Trade?Y=$YEAR>Add Trader</a>\n";
      $txt .= "<li><form method=Post action=TradeAssign class=staffform>";
      $txt .= "<input type=submit name=ll value='Trade Pitch Assign' id=staffformid>" .
                fm_hidden('Y',$YEAR) .
                fm_select($Tlocs,$ld,'l',0," onchange=this.form.submit()") . "</form>\n";

      $txt .= "<li><a href=TradeLocs?Y=$YEAR>Trade Locations</a>\n";
      $txt .= "</ul><td><ul>";
      $txt .= "<li><a href=TradeShow?STAFF>Trade Show</a> (even if not public)\n";
      if (Access('SysAdmin')) $txt .= "<li><a href=TradeTypes>Trade Types and base Prices</a>\n";
      if (Access('SysAdmin')) $txt .= "<li><a href=TradePower>Trade Power</a>\n";
      if (Access('SysAdmin')) $txt .= "<li><a href=EmailTraders>Email Groups of Traders</a>\n"; // Old code needs lots of changes
//      if (Access('SysAdmin')) $txt .= "<li><a href=TradeDateChange>Bump Trade Year Data to new dates</a>\n";

//      if (Access('SysAdmin')) $txt .= "<li><a href=TradeImport3>Fix Access Keys</a>\n";
      if (Access('SysAdmin')) $txt .= "<li><a href=Trade2CSV?Y=$YEAR>Traders as CSV</a>\n";
    }
    $txt .= "<li><a href=TradePowerList?T=Power>Show all power</a>\n";
    $txt .= "<li><a href=TradePowerList?T=Tables>Show all tables</a>\n";
    $txt .= "<li><a href=TradePowerList?T=FireEx>Show all Fire Ex</a>\n";
    $txt .= "<li><a href=TradeStandMaps>Show all Maps (Pagenated)</a>\n";
    $txt .= "<li><a href=ListCTrade?Y=$YEAR&TOPRINT=1>List Active Traders For Setup</a>\n";


    if (Capability('EnableTrade') && !Capability('EnableFinance')) $txt .= "<li><a href=InvoiceManage?Y=$YEAR>Invoice/Payment Management</a>\n";
    if (Access('SysAdmin')) {
      $txt .= "<p><div class=tablecont><table><tr><td>";
      $txt .= "<li class=smalltext><a href=TradeAccessKeys>Fix Missing Access Keys</a>\n";
//      $txt .= "<li class=smalltext><a href=TradeImport4>Merge ATMs Trade Data</a>\n";
      $txt .= "<li class=smalltext><a href=ResetImageSizes?TRADE>Scan and save Image sizes</a>";
//      $txt .= "<li class=smalltext><a href=FixBug4>Fix unsaved states</a>";
//      $txt .= "<li class=smalltext><a href=ListBTrade>Special List for Brian</a>";
      if (Feature('EnableCancelMsg')) $txt .= "<li class=smalltext><a href=CopyTradeYear?Y=$YEAR>Copy Trade Year to New Years</a>";
      if (Feature('EnableCancelMsg')) $txt .= "<li class=smalltext><a href=CopyTradeYear2?Y=$YEAR>Copy Trade Year to New Years Bug Fix</a>";
      $txt .= "</table></div><p>\n";
    }
    $txt .= "</ul></table>\n";
  }

// *********************** VENUES & EVENTS *******************************************************
  $_REQUEST['DAYS'] = 0; $_REQUEST['Pics'] = 1;
  if ($x = StaffTable('Events','Events',2)) {
    $txt .= $x;
    $Vens = Get_AVenues();
    $txt .= "<ul>\n";
    $txt .= "<li><a href=EventList?Y=$YEAR>List All Events</a>\n";
    if (Access('Staff','Events') && $YEAR==$PLANYEAR) $txt .= "<li><a href=EventAdd>Create Event(s)</a>";

    $txt .= "<li><form method=Post action=EventList class=staffform>";
      $txt .= "<input type=submit name=a value='List Events at' id=staffformid>" .
                fm_hidden('Y',$YEAR) .
                fm_select($Vens,0,'V',0," onchange=this.form.submit()") . "</form>\n";

    $txt .= "<li><form method=Post action=VenueShow?Mode=1 class=staffform>";
      $txt .= "<input type=submit name=a value='Show Events at' id=staffformid>" .
                fm_hidden('Y',$YEAR) .
                fm_select($Vens,0,'v',0," onchange=this.form.submit()") . " - A public view of events even if they are not public</form>\n";
    $txt .= "<li><form method=Post action=../Sherlock?SHOWALL=1 class=staffform>";
      $txt .= "<input type=submit name=a value='Timetable For' id=Posterid>" .
                fm_hidden('Y',$YEAR) .
                fm_select($ETypes,0,'t',0," onchange=this.form.submit()") . "even if not public" .
                "</form> \n";

    if (Access('Staff','Events')) $txt .= "<li><a href=EventTypes>Event Types</a>\n";

    $txt .= "<li><a href=StewList?Y=$YEAR>List Stewarding Events</a>\n";
    $txt .= "<li><a href=EventSummary?Y=$YEAR>Event Summary</a>\n";
    $txt .= "<li><form method=Post action=PAShow class=staffform>";
      $txt .= "<input type=submit name=a value='PA Requirements for' id=staffformid>" .
                fm_hidden('Y',$YEAR) .
                fm_select($Vens,0,'pa4v',0," onchange=this.form.submit()") . "</form>\n";

    $txt .= "<li><form method=Post action=StewardShow class=staffform>";
        $txt .= "<input type=submit name=a value='Event Sheets for' id=staffformid>" .
                fm_hidden('Y',$YEAR) .
                fm_select($Vens,0,'pa4v',0," onchange=this.form.submit()") . "</form>\n";

    $txt .= "<li><form method=Post action=StewardResults class=staffform>";
        $txt .= "<input type=submit name=a value='Event Sheet Results for' id=staffformid>" .
                fm_hidden('Y',$YEAR) .
                fm_select($Vens,0,'pa4v',0," onchange=this.form.submit()") . "</form>\n";


//    if (Access('SysAdmin')) $txt .= "<li><a href=BusTimes>Fetch and Cache Bus Times</a>\n";
//    if (Access('SysAdmin')) $txt .= "<li><a href=ConvertEvents>Convert Old Format Events to New Format Events</a>\n";
    $txt .= "<li><form method=Post action=/WhatsOnNow class=staffform>";
      $txt .= "<input type=submit name=a value='Whats On At ' id=staffformid>" .
                fm_hidden('Y',$YEAR) . fm_text0('',$_REQUEST,'AtTime') .' on ' . fm_text0('',$_REQUEST,'AtDate') . "</form>\n";
    if (Access('SysAdmin')) {
      $txt .= "<li class=smalltext><a href=CopyEvent2This?Y=$YEAR>Create/Copy Last years music Events to this year</a>";
      $txt .= "<li class=smalltext><a href=SubEvWoParents?Y=$YEAR>Find Subevents Without Parents</a>";
    }
    $txt .= "</ul>\n";
  }
// *********************** Venues *****************************************************************
  $_REQUEST['DAYS'] = 0; $_REQUEST['Pics'] = 1;
  if ($x = StaffTable('Events','Venues')) {
    $txt .= $x;
    $Vens = Get_AVenues();
    $txt .= "<ul>\n";
    $txt .= "<li><a href=VenueList?Y=$YEAR>List Venues</a>\n";
    if (Access('Staff','Venues')) $txt .= "<li><a href=VenueComplete?Y=$YEAR>Mark Venues as Complete</a>\n";
    if (Access('Committee','Venues')) $txt .= "<li><a href=MapPoints>Additional Map Points</a>\n";
    if (Access('SysAdmin')) $txt .= "<li><a href=MapPTypes>Map Point Types</a>\n";
    if (Access('SysAdmin')) $txt .= "<li><a href=AddVenue?NEWACCESS onClick=\"javascript:return confirm('are you sure you update these?');\">" .
                                    "Generate New Access Keys for Venues</a>\n";
    if ($YEAR == $PLANYEAR && Access('Staff')) $txt .= "<li><a href=VenueActive>Refresh Active Venue List</a>\n";
    if (Access('SysAdmin')) {
      $txt .= "<li><a href=FoodDrink>Food and Drink</a>\n";
      $txt .= "<li><a href=WaterManage>Water Refills</a>\n";
    }

    $txt .= "<li><a href=Infra?Y=$YEAR>Infrastructure</a>\n";

    $txt .= "<li><a href=TradeStandMap?t=6&Y=$YEAR>Infrastructure Map</a>\n";
    $txt .= "<li><a href=TradeStandMap?t=0&Y=$YEAR>Island Park General Map</a>\n";
    $txt .= "<li><a href=TradeStandMap?t=5&Y=$YEAR>Island Park EMP Map</a>\n";


    $txt .= "</ul>\n";
  }

// *********************** Tickets *****************************************************************
  if ($x = StaffTable('Events','Tickets')) {
    $txt .= $x;
    $txt .= "<ul>\n";
    $txt .= "<li><a href=ListPerfTickets?SEL=ALL&Y=$YEAR>List All Performer Tickets Wanted</a>\n";
    $txt .= "<li><a href=ListPerfTickets?SEL=ALL&Y=$YEAR&COL=1>Record Performer Ticket Collection</a><p>\n";

    $AllTeams = $VolTeams;
    $AllTeams[-1] = 'All Volunteers';
    $txt .= "<li><form method=Post action=Volunteers?ACTION=TicketList class=staffform>" .
                fm_hidden('Y',$YEAR) .
                "<input type=submit name=a value='Record Volunteer Ticket Collection ' id=staffformid>" .
                fm_select($AllTeams,0,'Cat',0," onchange=this.form.submit()") . "</form>\n";

//    $txt .= "<li><a href=ListPerfTickets?SEL=ALL&Y=$YEAR&COL=1>Record Volunteer Ticket Collection</a><p>\n";

    if (Access('SysAdmin')) $txt .= "<p><li><a href=TicketEvents?Y=$YEAR>List Ticketed Events</a>\n";
    if (0 && Access('SysAdmin')) {
      $txt .= "<p><li><a href=Volunteers?A=CompAdd>Add Festival team and Complimentary tickets</a>\n";
      $txt .= "<li><a href=Volunteers?A=CompList>List Festival team and Complimentary tickets</a>\n";
    }
   
    $txt .= "</ul><h2>Collecting</h2><ul>";
    $txt .= "<p><li><a href=Collecting?Y=$YEAR>General Collecting</a>\n";
    $txt .= "<li><a href=Collecting?ACTION=Records&Y=$YEAR>Tin Records</a>\n";
    $txt .= "<li><a href=Collecting?ACTION=IO&$YEAR>Tins in and out</a>\n";
//    $txt .= "<li><a href=ListPerfTickets?SEL=ALL&Y=$YEAR&COL=1>Record Performer Ticket Collection</a><p>\n";


    $txt .= "</ul>\n";
  }


// *********************** Publicity *****************************************************************
  if ($x = StaffTable('Events','Publicity')) {
    $txt .= $x;
    $txt .= "<ul>\n";
    $txt .= "<li><form method=Post action=VenueShow?Poster=1 class=staffform>";
    $XDays = $Days;
    $XDays[]= 'Fri+Sat';
    $XDays[]= 'Sun+Mon';
      $txt .= "<input type=submit name=a value='Poster For' id=Posterid>" .
                fm_hidden('Y',$YEAR) .
                fm_select($Vens,0,'v',0," onchange=this.form.submit()") . "<br>" .
                fm_radio('',$XDays,$_REQUEST,'DAYS','',0) . fm_checkbox('Pics',$_REQUEST,'Pics') .
                "</form>\n";

    $txt .= "<p>";
    $txt .= "<li class=smalltext><a href=PaperProg?ALPHA=1&$YEAR>Lineups for Printed Program</a> (Even if not public)";
    $txt .= "<li class=smalltext><a href=PaperTime?$YEAR>Events for Printed Program</a> (Even if not public)";
    $txt .= "<li class=smalltext><a href=ShowDanceProg?Head=0&Pub=1&Links=0&Cond=1&NoBackground=1&Day=Sat&$YEAR&Print=1>Sat Dance Grid for Printed Program</a>";
    $txt .= "<li class=smalltext><a href=ShowDanceProg?Head=0&Pub=1&Links=0&Cond=1&NoBackground=1&Day=Sun&$YEAR&Print=1>Sun Dance Grid for Printed Program</a>";
    $txt .= "<li class=smalltext><a href=ShowDanceProg?Head=0&Pub=1&Links=0&Cond=1&NoBackground=1&Day=Mon&$YEAR&Print=1>Mon Dance Grid for Printed Program</a>";

    $txt .= "<p class=smalltext>";
    $txt .= "<li><a href=/PerfChanges?$YEAR>Performer Changes since programme went to print</a>";
    $txt .= "<li><a href=/EventChanges?$YEAR>Event Changes since programme went to print</a>";
    if (Access('SysAdmin')) $txt .= "<li class=smalltext><a href=PerfEventPrint?$YEAR>Event and Performer Changes to be printed</a>";
    if (Access('SysAdmin')) $txt .= "<li class=smalltext><a href=EventUpdateEdit?$YEAR>Edit Event Changes</a>";
    if (Access('SysAdmin')) $txt .= "<li class=smalltext><a href=PerfUpdateEdit?$YEAR>Edit Performer Changes</a>";
    if (Access('SysAdmin')) $txt .= "<li class=smalltext><a href=Analytics>Analyse Google Analytics</a>";
    $txt .= "<li><a href=QRMake>Generate a QR code</a><p>\n";

    $txt .= "<li><a href=MailListMgr?A=ListForms>Mailing List Manager</a><p>\n";
    $txt .= "<li><a href=PerformerList?$YEAR>List of Performers</a><p>\n";
    $txt .= "</ul>\n";
  }


// *********************** Misc *****************************************************************
  if ($x = StaffTable('Misc','Misc')) {
    $txt .= $x;
    $txt .= "<ul>\n";
    $txt .= "<h2>Volunteers</h2>";
//    $txt .= "<li><a href=StewardView>Stewarding Applications (old)</a>\n";
    $txt .= "<li><a href=Volunteers?A=New>Volunteering Application Form</a>\n";
    $txt .= "<li><a href=Volunteers?A=List>List Volunteers</a>\n";
    $txt .= "<li><a href=VolCats>Volunteer Categories</a>\n";
    $txt .= "<li><a href=VolRates>Volunteer Signup Rates</a>\n";
    
//    $txt .= "<li><a href=VolGroups>Volunteer Groups</a>\n";
    $txt .= "<li><form method=Post action=Volunteers?ACTION=TeamList class=staffform>" .
                fm_hidden('Y',$YEAR) .
                "<input type=submit name=a value='Volunteer Details for ' id=staffformid>" .
                fm_select($VolTeams,0,'Cat',0," onchange=this.form.submit()") . "</form>\n";
    if (Access('Staff','Photos')) {
      $txt .= "<h2>Photos</h2>";
      $txt .= "<p><li><a href=PhotoUpload>Photo Upload</a>";
      $txt .= "<li><a href=PhotoManage>Photo Manage</a>";
      $txt .= "<li><a href=GallManage>Gallery Manage</a>";
    }

//    $txt .= "<li><a href=LaughView?Y=$YEAR>Show Laugh Out Loud applications</a>";
    if (Access('Committee')) {
      $txt .= "<h2>Campsites</h2>";
      $txt .= "<li><a href=Campsites?Y=$YEAR>Campsites</a>\n";
      $txt .= "<li><a href=CampTypes?Y=$YEAR>Camping Types</a>\n";
      $txt .= "<li><a href=CampUse?Y=$YEAR>Camping Use</a>\n";
    }
    $txt .= "<p>";

//    if (Access('SysAdmin')) $txt .= "<li><a href=VolImport>Import Older Volunteers</a>";
//    if (Access('SysAdmin')) $txt .= "<li><a href=CampsiteUse?Y=$YEAR>Manage Wimborne Style Campsite Use</a>\n";
//    if (Access('SysAdmin')) $txt .= "<li><a href=CarerTickets?Y=$YEAR>Manage Carer / Partner Tickets</a>\n";
//    if (Access('SysAdmin','Sponsors')) $txt .= "<li><a href=TaxiCompanies>Manage Taxi Company List</a>\n";
//    if (Access('SysAdmin')) $txt .= "<li><a href=ConvertPhotos>Convert Archive Format</a>";

    if (Access('SysAdmin')) {
      $txt .= "<li><a href=CheckWebLinks>Check all Weblinks</a>";
    }
    $txt .= "</ul>\n";
  }

// *********************** Finance **************************************************************
  if ($x = StaffTable('Finance','Finance and Sponsors')) {
    $txt .= $x;
    $txt .= "<ul>\n";
    if (Access('Committee','Finance')) {
      $txt .= "<li><a href=BudgetManage?Y=$YEAR>Budget Management</a>\n";
      $txt .= "<li><a href=InvoiceManage?Y=$YEAR>Invoice/Payment Management</a>\n";
      $txt .= "<li><a href=InvoiceManage?ACTION=NEW>New Invoice</a>\n";
      $txt .= "<li><a href=InvoiceCodes?Y=$YEAR>Invoice Codes</a>\n";
      $txt .= "<li><a href=InvoiceSummary?Y=$YEAR>Invoice Summary</a>\n";
      $txt .= "<li><a href=OtherPaymentSummary?Y=$YEAR>Other Payment Summary</a>\n";
      $txt .= "<li><a href=Payments?Y=$YEAR>List All Performer Payments</a>\n";
      $txt .= "<li><a href=TradeRecieve?Y=$YEAR>List Trader Payments</a>\n";
    } elseif (Access('Committee')) {
      $txt .= "<li><a href=BudgetManage?Y=$YEAR>Budget View</a>\n";
      $txt .= "<li><a href=InvoiceManage?Y=$YEAR>Invoice Management</a>\n";
    }

    if (Access('Committee','Finance') || Access('Committee','Biz')) {
      $txt .= "<p><li><a href=ListBiz>Businesses and Organistaions List (Not traders)</a>\n";
      $txt .= "<li><a href=Biz?ACTION=AllSponList>All Sponsorships </a> (new code)\n";
      $txt .= "<li><a href=Biz?ACTION=ReHash>Set up Cached Data</a> (Run after changes)\n";


//      $txt .= "<p><li><a href=ListTrade?ORGS>Businesses and Organistaions List</a> (Old code)\n";
      $txt .= "<li><a href=Trade?ORGS>New Business or Organistaion</a>\n";
//      $txt .= "<li><a href=Sponsors>Sponsors</a> (old code)\n"; */
    }

    if (Access('SysAdmin')) {
//      $txt .= "<p>";
//      $txt .= "<li class=smalltext><a href=ImportDebtorCodes>Import Debtor Codes</a>";
//      $txt .= "<li class=smalltext><a href=ImportProgAds>Import Programme ads</a>\n";
//      $txt .= "<p>";


//      $txt .= "<li><a href=ImportOldInvoice>Import Old Invoices</a>\n";
    }
    $txt .= "</ul>\n";
  }

// *********************** Art & Craft *********************************************************
  if ($x = StaffTable('Craft','Art and Craft')) {
    $txt .= $x;
    $txt .= "<h2></h2>\n";
    $txt .= "<ul>\n";
    $txt .= "<li><a href=ArtForm>Art Application Form</a>\n";
    $txt .= "<li><a href=ArtView>Show Art Applications</a>\n";
    $txt .= "</ul>";
  }

// *********************** Users  **************************************************************
  if ($x = StaffTable('Any','Users')) {
    $txt .= $x;
    $txt .= "<ul>\n";
    $txt .= "<li><a href=Login?ACTION=NEWPASSWD>New Password</a>\n";
    if (Access('Committee','Users')) {
      $txt .= "<li><a href=AddUser>Add User</a>";
      $txt .= "<li><a href=ListUsers?FULL>List Committee/Group Users</a>";
      $txt .= "<li><a href=UserDocs>Storage Used</a>";
      $txt .= "<li><a href=ContactCats>Contact Categories</a>";
    } else {
      $txt .= "<li><a href=ListUsers>List Committee/Group Users</a>";
    }
    if (Access('SysAdmin') && !Capability("EnableFinance")) {
//      $txt .= "<p>";
//      $txt .= "<li><a href=Capabilities>Capabilities</a>";
//      $txt .= "<li class=smalltext><a href=ImportDebtorCodes>Import Debtor Codes</a>";
//      $txt .= "<li class=smalltext><a href=ImportProgAds>Import Programme ads</a>\n";
//      $txt .= "<p>";
      $txt .= "<p>";
      $txt .= "<li><a href=Sponsors>Sponsors</a>\n";
      $txt .= "<li><a href=WaterManage>Water Refills</a>\n";

//      $txt .= "<li><a href=ImportOldInvoice>Import Old Invoices</a>\n";
    }
    if (Access('Committee','Finance') && !Capability("EnableFinance")) {
      $txt .= "<li><a href=Payments?Y=$YEAR>List All Performer Payments</a>\n";
    }
    $txt .= "</ul><p>\n";
  }

// *********************** GENERAL ADMIN *********************************************************
  if ($x = StaffTable('Any','General Admin')) {
    $txt .= $x;
    $txt .= "<ul>\n";

    if (Capability('EnableAdmin') && Access('Committee','News')) {
//      $txt .= "<li><a href=NewsManage>News Management</a>";
      $txt .= "<li><a href=ListArticles>Front Page Article Management</a>";

    }
    if (0 && Access('Steward')) {
      $txt .= "<li><a href=AddBug>New Bug/Feature request</a>\n";
      $txt .= "<li><a href=ListBugs>List Bugs/Feature requests</a><p>\n";
    }

    if (Access('Staff')) $txt .= "<li><a href=TEmailProformas>EMail Proformas</a>";
    if (Access('Staff')) $txt .= "<li><a href=AdminGuide>Admin Guide</a> \n";
    if (Access('SysAdmin')) {
      $Match = [];

      preg_match('/(\d*)\.(\d*)/',$VERSION,$Match);
      $Version = $Match[2];
      $xtra = '';
      if ($Version != ($FESTSYS['CurVersion'] ?? 0)) {
        foreach(glob("../Schema/*.sql") as $sql) {
          if (filemtime($sql) > $FESTSYS['VersionDate']) {
            $xtra = " style='color:red;font-size:28;font-weight:bold;'";
            break;
          }
        }
      }
//      $txt .= "<li><a href=BannerManage>Manage Banners</a> \n";
      if (Feature('Donate')) $txt .= "<li><a href=DonateTypes?Y=$YEAR>Donation Buttons Setup</a> \n";
      $txt .= "<li><a href=PerformerTypes?Y=$YEAR>Performer Types</a> \n";
      $txt .= "<li><a href=TsAndCs2?Y=$YEAR>Terms, Conditions, FAQs etc</a> \n";
      $txt .= "<li><a href=YearData?Y=$YEAR>General Year Settings</a> \n";
      if ($xtra) {
        $txt .= "<li><a href=UpdateSystem $xtra>Update the system after pull</a> \n";
        $txt .= "<li class=smalltext><a href=UpdateSystem?MarkDone>Just mark done</a><p> \n";
      }
      $txt .= "<li><a href=SystemData>Festival System Data Settings</a><p> \n";

      $txt .= "<li><a href=RareAdmin>Rare Admin Tasks</a> \n";
    }
    $txt .= "</ul>\n";
  }

// *********************** Development ONLY *********************************************************
  if ($x = StaffTable('Development','Development Tools')) {
    $txt .= $x;
    $txt .= "<ul>\n";

    $txt .= "<li><a href=DumpEmails>Dump Emails</a>";
    $txt .= "<li><a href=DumpTnC>Dump Ts and Cs</a>\n";
    $txt .= "<li><a href=DumpFeatures>Dump Features</a>\n";
    $txt .= "<li><a href=DumpMenu>Dump Menu</a>\n";
    $txt .= "<li><a href=EditFeatureHelp>Edit Feature Help</a>\n";
    $txt .= "</ul>\n";
  }

  $txt .= "</table></div>\n";

  echo "<h3>Jump to: ";
  $d = 0;
  foreach ($Heads as $Hd) {
    $hnam = preg_replace("/[^A-Za-z0-9]/", '', $Hd);
    $Hd = preg_replace("/ /",'&nbsp;',$Hd);
//    if ($d++) echo ", ";
    echo "&gt;&nbsp;<a href='#Staff$hnam'>$Hd</a> ";
  }
  echo "</h3><br>";
  echo $txt;
  dotail();
