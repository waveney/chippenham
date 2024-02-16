<?php
  include_once("DanceLib.php"); 
  include_once("MusicLib.php"); 
  include_once("ProgLib.php"); 
  include_once("PLib.php");
  include_once("Email.php");
  
function Show_Contract($snum,$mode=0,$ctype=1) { // mode=-2 dummy-1 Draft,0 proposed, 1 freeze reason - see contractConfirm, ctype 0=Side,1=act,2=other
  global $Mess,$Action,$YEARDATA,$Cat_Type,$YEAR,$PLANYEAR,$DayLongList, $Event_Types,$ContractMethods,$USERID,$ReportTo,$PLANYEARDATA,$Months;

  $str = "<div class=content900>\n";
  $Venues = Get_Venues(1);
    
  if ($mode >= -1) {  
    $Side = Get_Side($snum);
    $Sidey = Get_SideYear($snum,$YEAR);
    $Booked = Get_User($Sidey['BookedBy']);
    $kwd = ($mode < 0?'DRAFT':($mode ==0?'Proposed':''));
  } else {
    $str .= "<span class=NotSide>Data that has been filled (other than the list of performances) in as part of the dummy contract are marked this way.</span><p>";
    
    $Side = ['SN' => '<span class=NotSide>Dummy Performer</span>','StagePA'=>'Just a Mike', 
             'SortCode'=>'<span class=NotSide>99 99 99</span>', 'Account'=>'<span class=NotSide>12345678</span>', 
             'AccountName' => '<span class=NotSide>Mystery Products</span>', 'HasAgent'=>0, 'WantCheque'=>0,
             'Contact' => '<span class=NotSide>Freeda Bloggs</span>', 'Email' => '<span class=NotSide>Junk@Spam.net</span>', 
             'Mobile' => '<span class=NotSide>0987 6543 210</span>', 'Address' => '<span class=NotSide>No fixed abode</span>'
            ];
    $Sidey = ['ContractDate'=>time(),
              'Year'=>$YEAR,
              'TotalFee'=>'<span class=NotSide>100', 'OtherPayment'=>'Bottle of Rum</span>',
              'CampSat'=>0, 'CampFri' => 0, 'CampSun'=> 0,'Performers'=>0,
              'Rider' => '<span class=NotSide>If there is any riders on the contract they will appear here</span>',
              'ReportTo' => 0, 'GreenRoom'=> 1,
              'AccomNights' => 1, 'AccomPeople'=> 2,
             ];
    $Booked = Get_User(4);
    $kwd = 'Dummy ';
    if ($Venues) foreach($Venues as $i=>$v) { $Ven = $i; break;}
    $Evs = [['SN' => 'Concert','Type' => 5, 'Day'=> 1, 'Start'=>1900, 'End'=>2000, 'Setup' => 10, 'Venue'=>($Ven??0), 'SubEvent' => 0, 'Duration'=>60]];
  }

// Performances

  if ($mode >= -1) {  
    $Evs = Get_Events4Act($snum,$YEAR);
  } else {
//    $str .= "<span class=NotSide>";  
  }
  
  $ETs = Get_Event_Types(1);
  $evc = $evd = $evv = 0;
  $riders = array();
  $evday = $pkday = [-3=>0,-2=>0,-1=>0,0=>0,1=>0,2=>0,3=>0,4=>0];
  $pkvens = array();
  $SoundChecks = 0;
  $pking = "";
  if (!$Evs) return "No Contract Yet";  // With no events there is no conract, not even a draft

    $Evstr = "<div class=tablecont><table border>";
    if ($ctype == 1) { 
      $Evstr .= "<tr><td>Number<td>Event Name<td>Date<td>Setup From<td>Start<td>Duration<td colspan=3>Where\n";
    } else {
      $Evstr .= "<tr><td>Number<td>Event Name<td>Date<td>Start<td>Duration<td colspan=3>Where\n";
    }
    foreach($Evs as $e) {
      if ($ETs[$e['Type']]['Public'] == 0 || $ETs[$e['Type']]['DontList'] == 1) $SoundChecks = 1;
      $evc++;
      if ($e['SubEvent'] < 0) { $End = $e['SlotEnd']; } else { $End = $e['End']; }
      if (($e['Start'] != 0) && ($End != 0) && ($e['Duration'] == 0)) $e['Duration'] = timeadd2real($End, - $e['Start']);
      $Evstr .= "<tr><td>$evc<td>" . $e['SN'] . "<td>" . FestDate($e['Day'],'L');
      if ($ctype == 1 ) $Evstr .= "<td>" . ($e['Start']? ( timecolon(timeadd2($e['Start'],- $e['Setup']) )) : "TBD" ) ;
      $Evstr .= "<td>" . ($e['Start']?timecolon($e['Start']):"TBD");
      $Evstr .= "<td>" . ($e['Duration']? DurationFormat($e['Duration']) :"TBD"); 
      $evd += $e['Duration'];
      if ($e['Duration'] == 0) $evv = 1;
      $Evstr .= "<td>";
      $evday[$e['Day']]++;
      if ($e['Venue']) {
        if (isset($Venues[$e['Venue']])) {
          $v = $Venues[$e['Venue']];
          $Evstr .= Venue_Parents($Venues, $v['VenueId']) . "<a href=http://" . $_SERVER['HTTP_HOST'] . "/int/VenueShow?v=" . $v['VenueId'] . ">" . $v['SN'] . "</a><br>";
          if ($v['Address']) $Evstr .= $v['Address'] . "<br>" . $v['PostCode'] ."<br>";
          if ($v['MusicRider']) $riders[$v] = 1;
          if ($v['Parking']) {
            $pkday[$e['Day']]++;
            if (!isset($pkvens[$v['VenueId']])) {
              $pkvens[$v['VenueId']] = 1;
              if ($pking) $pking .= ", ";
              $pking .= $v['SN'];
            }
          }
        } else {
          $Evstr .= "Venue Unknown";
        }
      } else {
        $Evstr .= "TBD";
      }
    } 
    $Evstr .= "</table></div>\n";

//  if ($mode < -1) $str .= "</span>";



  $ContractFormat = Feature('ContractType');
  
  switch ($ContractFormat) {
  
  case 'Chip2023':
    $DFrom = ($PLANYEARDATA['DateFri']+$PLANYEARDATA['FirstDay']);
    $DTo = ($PLANYEARDATA['DateFri']+$PLANYEARDATA['LastDay']);
    $DMonth = $Months[$PLANYEARDATA['MonthFri']];

    $str .= "<center>This Document forms the Agreement between<p>";
    
    $str .= "<h2>" . Feature('FestLegalName') . "</h2>(Known as <i>The Festival</i>)<br>and<br>";
    $str .= "<h2>" .$Side['SN'] . "</h2>(known as <i>The Artist</i>)<p>";
  
    $str .= "<i>The Festival</i> and <i>The Artist</i> are to performances and associated sound checks at the Festival in<br>" .
            "$DFrom - $DTo $DMonth $PLANYEAR inclusive (known as <i>The Booking</i>)<p>";
            
    $str .= "In respect of <i>The Booking<i>, <i>The Festival</i> agrees to pay <i>The Artist</i> the sum of<p>";
    
    $str .= "&pound;" . $Sidey['TotalFee'];
    if ($Sidey['OtherPayment']) $str .= " plus " . $Sidey['OtherPayment'];
    if ($Sidey['AccomNights'] && $Sidey['AccomPeople']) $str .= "<br>Plus accommadation for " . 
      Plural($Sidey['AccomPeople'],'','one person ',$Sidey['AccomPeople'] . " people") . ", for " . 
      Plural($Sidey['AccomNights'],'','1 night', $Sidey['AccomNights'] . " nights");
  
    $str .= "<p>No further costs or expenses will be paid unless agreed as a variation to this contract.<p>";
    
    if ($Side['StagePA'] == 'None') {
      $str .= "If you have any PA/Technical requirements, please fill in the relevant section on your online records.<p>\n";
    } else {
      $str .= "Thankyou for filling in your PA/Technical requirements.<p>\n";
    }
  
    $str .= "</center><p><h1>Specific &amp; Detailed Information</h1>";
    $str .= "<table border><tr><td><b>Artist Details</b><td>" . $Side['SN'];
    if (!empty($Side['Contact'])) $str .= "<tr><td>Contact:<td>" . $Side['Contact'];
    if (!empty($Side['Address'])) $str .= "<tr><td>Address:<td>" . $Side['Address'];
    if (!empty($Side['PostCode'])) $str .= "<br>" . $Side['PostCode'];
    if (!empty($Side['Phone'])) $str .= "<tr><td>Phone:<td>" . $Side['Phone'];    
    if (!empty($Side['Mobile'])) $str .= "<tr><td>Mobile:<td>" . $Side['Mobile'];    
    if (!empty($Side['Email'])) $str .= "<tr><td>Email:<td>" . $Side['Email'];    
    
    if ($Side['HasAgent'] && $Side['AgentName']) {
      $str .= "<tr><td><b>Agent:</b>" . $Side['AgentName'];
      if (!empty($Side['AgentAddress'])) $str .= "<tr><td>Address:<td>" . $Side['AgentAddress'] . "<br>" . $Side['AgentPostCode'];
      if (!empty($Side['AgentPhone'])) $str .= "<tr><td>Phone:<td>" . $Side['AgentPhone'];    
      if (!empty($Side['AgentMobile'])) $str .= "<tr><td>Mobile:<td>" . $Side['AgentMobile'];
      if (!empty($Side['AgentEmail'])) $str .= "<tr><td>Email:<td>" . $Side['AgentEmail'];    
    }
  
    $str .= "<tr><td><b>Festival Details</b>";
    $str .= "<tr><td>Name:<td>" . Feature("FestLegalName");
    $str .= "<tr><td>Address:<td>";
    foreach(Feature("FestLegalAddr") as $A) $str .= $A . "<br>";
    $str .= "<tr><td>Telephone:<td>" . Feature("FestPhone");
    $str .= "<tr><td>Email:<td>" . Feature("FestContractEmail");
    
    if ($Sidey['ReportTo']==0) $Sidey['ReportTo'] = Feature('DefaultReportPoint',0);
    if ($Sidey['ReportTo'] == 1 ) { // No actions
    } else if ($Sidey['ReportTo'] != 0 ) {
      $Reporttos = Report_To();
      $str .= "<tr><td><b>ON ARRIVAL</b> Please report to:<td>" . Venue_Parents($Venues, $Sidey['ReportTo']) . "<a href='https://" .  $_SERVER['HTTP_HOST'] . 
             "/int/VenueShow?v=" . ($Sidey['ReportTo']??0) . "'><b>" .
             ($Reporttos[$Sidey['ReportTo']] ?? '') . "</b></a> (click for map and directions)<p>\n";
    }

    $str .= "<tr><td><b>Expected Performance Duration</b><td>" . 
      "$evc performance" . ($evc>1?'s':'') . ", with a total duration of " . ($evv?"at least ":"") . DurationFormat($evd) . "<p>\n";
    $str .= "<tr><td>Current known times<br>See the festival website and/or festival information for more<td>" . $Evstr;

    $PayTypes = ['BACS','Cheque'];
    if (!Feature('PayByCheque')) $Side['WantCheque'] = 0;
    $str .= "<tr><td><b>Payment by</b><td>" . $PayTypes[$Side['WantCheque']];
    if ($Side['WantCheque']) {
      $str .= "<br>Payable to: " . $Side['AccountName'] . "<p>\n";        
    } else {
      $str .= "<br>Sort Code: " . $Side['SortCode'] . " Account Number: " . $Side['Account'] . "<br>Account Name : " . $Side['AccountName'] . "<p>\n";    
    }

    $str .= "</table><p>";
    
    $faq = TnC('PerfTnC');
//var_dump($faq);
    Parse_Proforma($faq);

    $str .= $faq;

    if ($mode > 0) {
      $str .= "This contract was confirmed " . $ContractMethods[$mode] . " on " . date('d/m/y H:i:s',$Sidey['ContractDate']) . "<P>\n";
    }

/*
    if ($mode < -1) {
      $str .= "<p><span class=NotSide>NOTE: A parking statement inserted prior to the changes statement " .
               "to say there is free parking near the venue(s), if appropriate.</span><p>";
      $str .= "<p><span class=NotSide>NOTE2: Additional clause(s) can be added for a particular venue, if appropriate.</span><p>";
    }
*/

    $str .= "</div>";  

    return $str;  
    
  case 'Wimborne':
  default:

  $str .= "<h2>" . Feature('FestName') . " - $kwd Contract</h2>\n";
  if ($kwd) $str .= "<em><b>$kwd contract:</b></em><p>\n";

  $str .= "Standard Agreement between " . ($ctype == 1?"Band/Artist/Performer":"Performer") . " &amp; Employer.<p>\n";

  $str .= "This Agreement made as of " . date('d/m/Y',  ($Sidey['ContractDate']>0?$Sidey['ContractDate']:time())) . 
        " by and between the parties identified below.<p>\n";

  $str .= "In consideration for the following covenants, conditions, and promises, the Employer identified below agrees to
hire the below-identified Artist to perform an engagement and the Artist agrees to provide such performance
services, under the following terms and conditions:<p>\n";

  $str .= "This agreement for performance services is entered into by the performers(s) known as:<br>";

  $str .= "<b>" . (empty($Booked['SN']) ?'' : $Booked['SN']) . "</b> for and on behalf of " . Feature('FestName') . " (now referred to as Employer) and \n";
  $str .= "<b>" . $Side['SN'] . " </b>(now referred to as Artist)<p>";

  $str .= "Performances:<p>";
    
  $str .= $Evstr;

//  if ($mode < -1) $str .= "</span>";

  $str .= "Total of $evc event" . ($evc>1?'s':'') . ", with a total duration of " . ($evv?"at least ":"") . DurationFormat($evd) . "<p>\n";

  $str .= "Total Fee: &pound;" . $Sidey['TotalFee'];
  if ($Sidey['OtherPayment']) $str .= " plus " . $Sidey['OtherPayment'];
  if ($Sidey['AccomNights'] && $Sidey['AccomPeople']) $str .= "<br>Plus accommadation for " . 
    Plural($Sidey['AccomPeople'],'','one person ',$Sidey['AccomPeople'] . " people") . ", for " . 
    Plural($Sidey['AccomNights'],'','1 night', $Sidey['AccomNights'] . " nights");
  $str .= "<p>\n";
  // Extra for supplied camping
  $camp = [];
  for ($day = 0; $day<3; $day++) {
    $dy = "Camp" . DayList($day);
    if ($Sidey[$dy]) $camp[] = $Sidey[$dy] . (($Sidey[$dy]) == 1 ?" person":" people") . " on " . FestDate($day,'L');
  }
  if ($camp) {
    $str .= "Camping will be provided for ";
    if ($mode < -1) $str .= "<span class=NotSide>";
    $str .= FormatList($camp) . " at the <a href=/InfoCamping>Campsite</a>.<p>\n"; 
    if ($mode < -1) $str .= "</span>";
  }

  // Riders for Venues
  foreach ($riders as $v) {
    $str .= "<b>Rider for " . VenName($Venues[$v]) . "</b>:" . $Venues[$v]['MusicRider'] . "<p>\n";
  }

  if (strlen($Sidey['Rider']) > 5) $str .= "<b>Rider:</b> " . $Sidey['Rider'] . "<p>\n";
  
  if ($Sidey['Performers']) $str .= $Sidey['Performers'] . " performer's wristband" . ($Sidey['Performers']>1 ?"s":"") . 
     " will be provided (free entry to all festival events, if space is available).<p>\n";

  if ($Sidey['TotalFee']) {
    if ($Side['SortCode'] || $Side['Account'] || $Side['AccountName']) {
      $str .= "<b>BACS:</b> Sort Code: " . $Side['SortCode'] . " Account Number: " . $Side['Account'] . " Account Name : " . $Side['AccountName'] . "<p>\n";
    }
  }
  

  if ($Sidey['ReportTo']==0) $Sidey['ReportTo'] = Feature('DefaultReportPoint',0);
  if ($Sidey['ReportTo'] == 0 ) {
    $str .= "<b>ON ARRIVAL</b>: Please report to the <b>Information Point in the square. </b>" .
            ($SoundChecks ?"You will need to arrive prior to your soundcheck time to collect wristbands.":"") . "<p>\n";
  } else if ($Sidey['ReportTo'] == 1 ) { // None
  } else {
    $Reporttos = Report_To();
    $str .= "<b>ON ARRIVAL</b>: Please report to " . Venue_Parents($Venues, $Sidey['ReportTo']) . "<a href='https://" .  $_SERVER['HTTP_HOST'] . 
             "/int/VenueShow?v=" . $Sidey['ReportTo'] . "'><b>" .
             $Reporttos[$Sidey['ReportTo']] . "</b></a> (click for map and directions)<p>\n";
  }

  if ($Side['StagePA'] == 'None') {
    $str .= "If you have any PA/Technical requirements, please fill in the relevant section on your Act's personal record.<p>\n";
  } else {
    $str .= "Thankyou for filling in your PA/Technical requirements.<p>\n";
  }
  
  if (isset($Sidey['Insurance']) && $Sidey['Insurance']>0) {
    $str .= "Thankyou for uploading your Insurance.<p>\n";

  } else if (Feature('PublicLiability')) {
    $str .= "Please upload your Insurance before the festival.<p>\n";
  }

/*  
  if ($Sidey['ReportTo'] != 2 && $Sidey['GreenRoom']) {
    $str .= "There is a Green Room, in <a href='https://" .  $_SERVER['HTTP_HOST'] . "/int/VenueShow?v=79'><b>Church House</b></a> " .
            "(In the High Street opposite the Minster Church - click for map and directions).<p> ";
  }
  
  
*/

  switch ($ctype) {
  case 0:
    $faq = "Payment: All payments will be made by BACS, within 48 hours of the end of the Festival. " .
           "Cash will not be used for payments. Any queries should be submitted through the employer.";
    break;
  
  case 1:
    $faq = TnC('PerfTnC');
    Parse_Proforma($faq);
    $faq = preg_replace("/<h2 class=OtherFAQ.*?<\/h2>/",'',$faq);
    if (!$camp) $faq = preg_replace("/<CAMPCLAUSE>.*<\/CAMPCLAUSE>/",'',$faq);
    break;
      
  case 2:
    $faq = TnC('PerfTnC');
    Parse_Proforma($faq);
    $faq = preg_replace("/<h2 class=MusicFAQ.*?<\/h2>/",'',$faq);  
    $faq = preg_replace("/<dt class=MusicFAQ.*?<dt>/s",'<dt>',$faq);
    $faq = preg_replace("/class=OtherFAQ/",'',$faq);
    if (!$camp) $faq = preg_replace("/<CAMPCLAUSE>.*<\/CAMPCLAUSE>/",'',$faq);
    break;  
  }

  if ($pking) {
    $allfree = 1;
    $freon = '';
    for ($i=0;$i<3;$i++) {
      if ($evday[$i] > 0 && $pkday[$i] == 0) $allfree = 0;
      if ($evday[$i] > 0 && $pkday[$i] != 0) {
        if ($freon) $freon .= " and ";
        $freon .= $DayLongList[$i];
      }
    }

    if ($pkingand = preg_replace('/,([^,]*)$/'," and $1",$pking)) $pking = $pkingand;

    if ($allfree) { 
      $ptxt = "<dt>Parking<dd>You may request free parking near $pking.";
      $faq = preg_replace("/<PARKING>/",$ptxt,$faq);
    } if ($freon) {
      $ptxt = "<dt>Parking<dd>On $freon you may book free parking near $pking.<p>";
      $faq = preg_replace("/<PARKING>/",$ptxt,$faq);
    } else {
      // Should never get here
    }
  }
  
  if (!$SoundChecks) $faq = preg_replace("/<SOUNDCHECK>.*<\/SOUNDCHECK>/",'',$faq);

//echo "FAQ = $faq<p>NOT FAQ<p>";

  $str .= $faq;

  if ($mode > 0) {
    $str .= "This contract was confirmed " . $ContractMethods[$mode] . " on " . date('d/m/y at h:m',$Sidey['ContractDate']) . "<P>\n";
  }

  if ($mode < -1) {
    $str .= "<p><span class=NotSide>NOTE: A parking statement inserted prior to the changes statement " .
             "to say there is free parking near the venue(s), if appropriate.</span><p>";
    $str .= "<p><span class=NotSide>NOTE2: Additional clause(s) can be added for a particular venue, if appropriate.</span><p>";
  }

  $str .= "</div>";  
  return $str;
  }
}

?>
