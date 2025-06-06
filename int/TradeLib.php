<?php

// For Book -> Confirm -> Deposit ->Pay , if class begins with a - then not used/don't list
$Trade_States = ['Not Submitted','Declined','Refunded','Cancelled','Submitted','Quoted','Accepted','Deposit Paid',
                  'Balance Requested','Fully Paid','Wait List','Requote','Change Aware','Refund Needed'];
$Trade_State = array_flip($Trade_States);
//$Trade_StateClasses = array('TSNotSub','TSDecline','-TSRefunded','TSCancel','TSSubmit','TSInvite','TSConf','TSDeposit','TSInvoice','TSPaid','TSWaitList','TSRequote');
// Put a - in front of colour to surpress it
$Trade_State_Colours = ['white','red','grey','grey','yellow','lightyellow','cyan','lightblue','darkseagreen','LightGreen','#ffb380','#e6d9b2','Coral','Gold'];
$Trade_State_Props = [3,0,0,0,3,2,2,2,0,0,3,2,0,0];

$TS_Actions = ['Submit,Invite,Invite Better',
                'Resend,Submit',
                'Resend',
                'Resend,Submit',
                'Resend,Quote,Accept,Invite,Decline,Hold,Cancel,Invite Better,Dates,FestC', // submitted
                'Resend,Quote,Invite,Accept,Decline,UnQuote,LastWeek,Dates,FestC', // quoted
                'Resend,Cancel,Dates,FestC', // ,Send All acceptted
                'Pitch Assign,Pitch Change,Moved,Resend,Send Bal,Cancel,Dates,FestC', // Dep Paid
                'Pitch Assign,Pitch Change,Moved,Resend,Chase,Cancel,Dates,FestC', // Bal Req
                'Pitch Assign,Pitch Change,Moved,Resend,Cancel,Dates,FestC,Pay More', // Fully Paid
                'Resend,Accept,Decline,Cancel,FestC',
                'Resend,Quote,Cancel,Dates,FestC',
                'Resend,Accept,Decline,Cancel,FestC',
                'Resend,Cancel',
                ];

$Trader_Status = ['Alive','Banned','Not trading'];
$Trader_State = array_flip($Trader_Status);
$ButExtra = [
 //       'Accept'=>'title=>"Accept the quote/invite"',
        'Decline'=>'title="Decline this trader, if in doubt Hold Them"',
        'Submit'=>'title="Submit application"',
        'Hold'=>'title="Hold for space available"',
        'Dep Paid'=>'title="Deposit Paid"', // Not Used
        'Send Bal'=>'title="Send Balance Request"', // PAYCODE
        'Invoice'=>'title="Send Balance Request"', // Invoice
        'Paid'=>'title="Full Fees Paid"', // Not Used
        'Quote'=>'title="Send or repeat Quote email"',
        'Invite'=>'title="Send or repeat the Invitation Email"',
        'Balance Requested'=>'title="Final Invoice Sent"',
        'Cancel'=>'onClick="javascript:return confirm(\'are you sure you want to cancel this?\');"',
        'Resend'=>'title="Resend last email to trader"',
        'Invite Better'=>'title="Send an Invitation to a better location"',
        'Artisan Invite'=>'title="Send an Artisan Invite"',
        'UnQuote'=>'title="Remove Quote or Invitation"',
        'Chase'=>'title="Chase email for final payment"',
        'Pitch'=>'title="Change of Pitch Number"',
        'Moved'=>'title="Pitch Moved"',
        'Balance'=>'title="Send Balance Payment Request',
        'LastWeek'=>'title="Last week of Quote"',
        'Dates'=>'title="Festival Changed Dates"',
        'FestC'=>'title="Festival Cancelled this year"',
        'Overdue'=>'title="Total payment needed now"',
        'Send All'=>'title="Send Deposit reminder and Balance request in one message"',
        'Pay More'=>'title="Pay more - eg for extra power"',
        ];

$ButTraderTips = [ // Overlay of diferent tips for traders
        'Accept'=>'title="The invitation or quote"',
        'Decline'=>'title="Decline this invitation"',
        'Resend'=>'title="Resend current state"',
        ];


$ButTrader = array('Submit','Accept','Decline','Cancel','Resend'); // Actions Traders can do
$ButAdmin = array('Paid','Dep Paid');
$RestrictButs = array('Paid','Dep Paid'); // If !AutoInvoice or SysAdmin
$Trade_Days = array('All','Saturday only','Sunday only','Saturday and Sunday','Monday','Saturday and Monday','All'); // TODO
$Prefixes = array ('in','in the','by the');
// $TaxiAuthorities = array('East Dorset','Poole','Bournemouth','BCP','Dorset');
$TradeMapPoints = ['Trade','Other']; //,'text']; // text TODO

$BizProps = ['IsTrader'=>1,'IsSponsor'=>2,'IsAdvertiser'=>4,'IsSupplier'=>0,'IsOther'=>0]; // bit 0 = Image, 2=Logo, 3=Advert
$SponTypes = ['General','Venue','Event','Performer'];
$SponStates = ['Raised','Invoiced','Paid','Paid in Kind'];
$TradeTypeStates = ['Private','Open','Closed']; // Private - not shown on site
$LocTypes = ['Trade','Infra','Other'];
$ObjectTypes = ['rect','text','circle','arrow','image'];

function Get_Trade_Locs($tup=0,$Cond='') { // 0 just names, 1 all data
  global $db;
  $full = $short = [];
  $res = $db->query("SELECT * FROM TradeLocs $Cond ORDER BY SN ");
  if ($res) {
    while ($typ = $res->fetch_assoc()) {
      $short[$typ['TLocId']] = $typ['SN'];
      $full[$typ['TLocId']] = $typ;
    }
  }
  if ($tup) return $full;
  return $short;
}

function Get_Trade_Loc($id) {
  global $db;
  $res=$db->query("SELECT * FROM TradeLocs WHERE TLocId=$id");
  if ($res) {
    $ans = $res->fetch_assoc();
    return $ans;
  }
  return 0;
}

function Put_Trade_Loc(&$now) {
  $e=$now['TLocId'];
  $Cur = Get_Trade_Loc($e);
  return Update_db('TradeLocs',$Cur,$now);
}

function Get_Trade_Pitches($loc='',$Year=0) {
  global $db,$YEAR;
  if ($Year == 0) $Year=$YEAR;
  $full = [];
//  var_dump("SELECT * FROM TradePitch " . ($loc?"WHERE Loc=$loc ":"") . " AND Year='Year' ORDER BY Posn ");

  $res = $db->query("SELECT * FROM TradePitch " . ($loc?"WHERE Loc=$loc ":"") . " AND Year='$Year' ORDER BY Posn ");
  if ($res) {
    while ($ptch = $res->fetch_assoc()) {
      $full[$ptch['Posn']] = $ptch;
    }
  }
  return $full;
}

function Get_Location() {
  $Loc = ($_REQUEST['l'] ?? Feature('TradeBaseMap'));
  if (is_numeric($Loc)) return $Loc;
  $Loc = preg_replace('/_/', ' ',$Loc);
  $Locs = Gen_Get_Cond1('TradeLocs',"SN='$Loc'");
  if ($Locs) return $Locs['TLocId'];
  return Feature('TradeBaseMap');
}

function Get_Trade_Pitch($id) {
  global $db;
  $res = $db->query("SELECT * FROM TradePitch WHERE id=$id");
  if ($res) return $res->fetch_assoc();
  return [];
}

function Put_Trade_Pitch(&$now) {
  $e=$now['id'];
  $Cur = Get_Trade_Pitch($e);
  return Update_db('TradePitch',$Cur,$now);
}



function Get_Trade_Types($tup=0) { // 0 just base names, 1 all data
  global $db;
  $full = array();
  if ($tup) {
    $res = $db->query("SELECT * FROM TradePrices ORDER BY ListOrder");
    if ($res) while ($tt = $res->fetch_assoc()) $full[$tt['id']] = $tt;
  } else {
    $res = $db->query("SELECT * FROM TradePrices WHERE Addition=0 ORDER BY ListOrder");
    if ($res) while ($tt = $res->fetch_assoc()) $full[$tt['id']] = $tt['SN'];
  }
  return $full;
}

$TradeTypeData = Get_Trade_Types(1);
$TradeLocData = Get_Trade_Locs(1);

function Get_Trade_Type($id) {
  global $db;
  $res=$db->query("SELECT * FROM TradePrices WHERE id=$id");
  if ($res) return $res->fetch_assoc();
  return 0;
}

function Put_Trade_Type(&$now) {
  $e=$now['id'];
  $Cur = Get_Trade_Type($e);
  return Update_db('TradePrices',$Cur,$now);
}

function Old_Get_Sponsors($tup=0) { // 0 Current, 1 all data
  global $db,$YEAR,$YEARDATA;
  $full = [];
  if ($tup == 0) {
    $res = $db->query("SELECT * FROM Sponsors WHERE Year='$YEAR' ORDER BY SN ");
    if (!$res && !empty($YEARDATA['PrevFest'])) {
      $res = $db->query("SELECT * FROM Sponsors WHERE Year='" . $YEARDATA['PrevFest'] . "' ORDER BY SN ");
    }
  } else {
    $res = $db->query("SELECT * FROM Sponsors ORDER BY SN ");
  }
  if ($res) while ($spon = $res->fetch_assoc()) $full[] = $spon;
  return $full;
}

function Get_Sponsor_Names($tup=0) {
  $ans = [];
  $data = Old_Get_Sponsors($tup);
  foreach ($data as $sp) $ans[$sp['id']]=$sp['SN'];
  return $ans;
}

function Get_Sponsor($id) {
  global $db;
  $res=$db->query("SELECT * FROM Sponsors WHERE id=$id");
  if ($res) return $res->fetch_assoc();
  return 0;
}

function Put_Sponsor(&$now) {
  $e=$now['id'];
  $Cur = Get_Sponsor($e);
  return Update_db('Sponsors',$Cur,$now);
}

function Get_WaterRefills($tup=0) { // 0 Current, 1 all data
  global $db,$PLANYEAR;
  $full = array();
  $yr = ($tup ?"" :" WHERE Year='$PLANYEAR' ");
  $res = $db->query("SELECT * FROM Water $yr ORDER BY SN ");
  if ($res) while ($spon = $res->fetch_assoc()) $full[] = $spon;
  if ($tup==0 && empty($full)) {
    $yr = " WHERE Year='" . ($PLANYEAR-1) . "'";
    $res = $db->query("SELECT * FROM Water $yr ORDER BY SN ");
    if ($res) while ($spon = $res->fetch_assoc()) $full[] = $spon;
  }
  return $full;
}

function Get_WaterRefill($id) {
  global $db;
  $res=$db->query("SELECT * FROM Water WHERE id=$id");
  if ($res) return $res->fetch_assoc();
  return 0;
}

function Put_WaterRefill(&$now) {
  $e=$now['id'];
  $Cur = Get_WaterRefill($e);
  return Update_db('Water',$Cur,$now);
}

function Get_Trader($who) {
  global $db;
  $res = $db->query("SELECT * FROM Trade WHERE Tid='$who'");
  if (!$res || $res->num_rows == 0) return 0;
  $data = $res->fetch_assoc();
  return $data;
}

function Get_Trader_All($who) {
  global $db,$YEAR;
  $res = $db->query("SELECT t.*,y.* FROM Trade AS t, TradeYear AS y WHERE t.Tid='$who' AND t.Tid=y.Tid AND y.YEAR=$YEAR");
  if (!$res || $res->num_rows == 0) return 0;
  $data = $res->fetch_assoc();
  return $data;
}

function Get_TraderByName($who) {
  global $db;
  $res = $db->query("SELECT * FROM Trade WHERE SN LIKE '$who'");
  if (!$res || $res->num_rows == 0) return 0;
  $data = $res->fetch_assoc();
  return $data;
}

function Get_Traders_Coming($type=0,$SortBy='SN') { // 0=names, 1=all
  global $db,$YEAR,$Trade_State;
  $data = array();
  $qry = "SELECT t.*, y.* FROM Trade AS t, TradeYear AS y WHERE t.Tid = y.Tid AND y.Year='$YEAR' AND ((y.BookingState>=" .
               $Trade_State['Deposit Paid'] . " AND y.BookingState<" . $Trade_State['Wait List'] . ") OR y.ShowAnyway) ORDER BY $SortBy";
  $res = $db->query($qry);
  if (!$res || $res->num_rows == 0) return 0;
  while ($tr=$res->fetch_assoc()) {
    $data[$tr['Tid']] = ($type?$tr:preg_replace('/\|/','',$tr['SN']));
  }
  return $data;
}

function Get_All_Traders($type=0) { // 0=names, 1=all
  global $db;
  $data = array();
  $qry = "SELECT * FROM Trade WHERE Status=0 AND IsTrader=1 ORDER BY SN";
  $res = $db->query($qry);
  if (!$res || $res->num_rows == 0) return 0;
  while ($tr=$res->fetch_assoc()) {
    $data[$tr['Tid']] = ($type?$tr:preg_replace('/\|/','',$tr['SN']));
  }
  return $data;
}

function Get_All_Businesses($type=0) { // 0=names, 1=all
  global $db;
  $data = array();
  $qry = "SELECT * FROM Trade WHERE Status=0 AND IsTrader=0 ORDER BY SN";
  $res = $db->query($qry);
  if (!$res || $res->num_rows == 0) return 0;
  while ($tr=$res->fetch_assoc()) {
    $data[$tr['Tid']] = ($type?$tr:preg_replace('/\|/','',$tr['SN']));
  }
  return $data;
}


function Put_Trader(&$now) {
//  debug_print_backtrace();
  $e=$now['Tid'];
  $Cur = Get_Trader($e);
  if ($Cur) return Update_db('Trade',$Cur,$now);
}

function Get_Trade_Years($Tid) {
  global $db;
  $Years = array();
  $res = $db->query("SELECT * FROM TradeYear WHERE Tid='$Tid'");
  if (!$res) return 0;
  while ($yr = $res->fetch_assoc()) {
    $y = $yr['Year'];
    $Years[$y] = $yr;
  }
  return $Years;
}

function Get_Trade_Year($Tid,$year=0) {
  global $db,$YEAR;
  if (!$year) $year=$YEAR;
  $qry = "SELECT * FROM TradeYear WHERE Tid='" . $Tid . "' AND Year='" . $year . "'";
  $res = $db->query($qry);
  if (!$res || $res->num_rows == 0) return 0;
  return $res->fetch_assoc();
}

function Put_Trade_Year(&$now) {
  $e=$now['Tid'];
  $Cur = Get_Trade_Year($e,$now['Year']);
  if ($Cur) return Update_db('TradeYear',$Cur,$now);
  Insert_db('TradeYear',$now);
}

function Set_Trade_Help() {
  static $t = [
        'Website'=>'If you would like to be listed on the Folk Festival Website, please supply your website (if you have one) and an Image and tick the box',
        'GoodsDesc'=>'Describe your goods and business.  Essential for Traders, optional otherwise.  At least 20 words please, but not more than 500 characters.
For traders, this is used both to decide whether to accept a Traders booking and as words to accompany your Image on the festival website.',
        'PitchSize'=>'If you want more than 1 pitch, give each pitch size, a deposit will be required for each.
If you attempt to setup a pitch larger than booked you may be told to leave',
        'Power'=>'Some locations can provide power, some only support lower power requirements.
There will be an additional fee for power, that will be added to your final invoice.',
        'Photo'=>'Give URL of Image to use or upload one (landscape is prefered)',
        'TradeType'=>'Fees depend on trade type, pitch size and location',
//        'BookingState'=>'ONLY change this if you are fixing a problem, use the state change buttons',
        'PublicInfo'=>'Information in this section may be used on the public website',
        'PrivateInfo'=>'Information in this section is only visible to you and the revelent members of the festival, you can amend this at any time',
        'PublicHealth'=>'Please give the NAME of the local authority your registered with',
        'IsTrader'=>'Used to indicate the business is a trader (useful for finance) do not touch (normally)',
        'BankDetails'=>'Needed for suppliers, very rarely needed for others when doing a refund',
        'Extras'=>'Some locations have extras',
        'NumberTickets'=>'Traders may request up to 2 tickets, which give access to the showers at the Olympiad',
        'NumberCarPass'=>'Traders may request up to 2 passes to park near the Olympiad after they have set up their pitches',
        'CampNeed'=>'For '

  ];
  Set_Help_Table($t);
}

function Pitch_Size_Def($type) {
  global $TradeTypeData;
  $DefPtch = (isset($TradeTypeData[$type]['DefaultSize'])?$TradeTypeData[$type]['DefaultSize']:'');
  if (!$DefPtch) $DefPtch = Feature('DefaultPitch','3Mx3M');
  return $DefPtch;
}

function Default_Trade($id,$type=1) {
  global $YEAR;
  return array('Year'=>$YEAR,'Tid'=>$id,'PitchSize0'=>Pitch_Size_Def($type),'Power0'=>1,'BookingState'=>0,'ExtraPowerCost'=>0, 'Fee'=>0);
}

// OLD CODE DELETE
function PayCodeGen($Type,$TYid) { // Type = DEP, BAL, PAY
  $digits = (string)($TYid*123) . "000000000000";
  // 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
  $even_sum = ord($Type[0]) + ord($Type[2]) + $digits[1] + $digits[3] + $digits[5] + $digits[7] + $digits[9] + $digits[11];
  // 2. Multiply this result by 3.
  $even_sum_three = $even_sum * 3;
  // 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
  $odd_sum = ord($Type[1]) + $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8] + $digits[10];
  // 4. Sum the results of steps 2 and 3.
  $total_sum = $even_sum_three + $odd_sum;

  $check_digit = chr(($total_sum%26) + ord('A'));
  return "$Type$TYid$check_digit";
}

function PowerCost(&$Trady) {
  static $TradePower = [];
  if (!$TradePower) $TradePower = Gen_Get_All("TradePower");
  $TotPowerCost = 0;

  for ($i = 0; $i < 3; $i++) {
    if (($Trady["Power$i"]??0) && isset($TradePower[$Trady["Power$i"]]))  $TotPowerCost += $TradePower[$Trady["Power$i"]]['Cost'];
  }
  return $TotPowerCost;
}

function TableCost(&$Trady) {
  $Tables = 0;

  for ($i = 0; $i < 3; $i++) {
    if ($Trady["Tables$i"]??0) $Tables += $Trady["Tables$i"];
  }
  return $Tables * Feature('TableCost');
}

function Show_Trader($Tid,&$Trad,$Form='Trade',$Mode=0) { // Mode 1 = Ctte, 2=Finance, 3=Biz general
  global $ADDALL,$Trader_Status,$TradeTypeData,$BizProps;
  Set_Trade_Help();
  $Props = 0;

  foreach ($BizProps as $p=>$m) if (!empty($Trad[$p])) $Props |= $m;

//  if (isset($Trad['Photo']) && $Trad['Photo']) echo "<img class=floatright id=TradThumb src=" . $Trad['Photo'] . " height=80>\n";
  if (Access('SysAdmin') && ($Tid > 0)) echo "<input  class=floatright type=Submit name='Update' value='Save Changes' form=mainform>";
  if ($Mode && isset($Trad['Email']) && strlen($Trad['Email']) > 5) {
    echo "If you click on the " . linkemailhtml($Trad,'Trade');
    echo ", press control-V afterwards to paste the standard link." ;// <button type=button onclick=Copy2Div('Email$Tid','SideLink$Tid')>standard link</button>";
    echo "<p>\n";
  }
  if (Access('Committee','Trade') && !empty($Trad['AccessKey'])) {
    echo "This traders link: <b><span class=NotSide>https://" . $_SERVER['HTTP_HOST'] . "/int/Direct?t=Trade&id=$Tid&key=" . $Trad['AccessKey'] .
         "</b></span><br>";
  }

  $Adv = '';
  $Imp = '';
  if ($Mode ==1) {
    echo "<span class=NotSide>Fields marked are not visible to Business.</span>";
    echo "  <span class=NotCSide>Marked are visible if set, but not changeable by Business.</span>";
  } else {
    $Adv = 'class=Adv';
  }
  echo "<div id=ErrorMessage class=ERR></div>";
//********* PUBLIC

  if (!isset($Trad['TradeType']) || ($Trad['TradeType'] == 0)) $Trad['TradeType'] = 1;

  echo "<form method=post id=mainform enctype='multipart/form-data' action=$Form>";
  if ($Tid>0) {
    Register_AutoUpdate('Trader',$Tid);
    echo "<input type=submit hidden>";
  }
  if (isset($_REQUEST['ORGS'])) echo fm_hidden('ORGS',1);
  echo "<div class=tablecont><table width=90% border class=SideTable>\n";
    echo "<tr><th colspan=8><b>Public Information</b>" . Help('PublicInfo');
    echo "<tr>" . fm_text('Business Name', $Trad,'SN',2,'','autocomplete=off id=SN') .
                  fm_text('Trading name if different', $Trad,'BizName',2,'','autocomplete=off id=SN');
    echo "<tr>";
      if (isset($Trad['Website']) && strlen($Trad['Website'])>1) {
        echo fm_text(weblink($Trad['Website']),$Trad,'Website',2);
      } else {
        echo fm_text('Website<br>Leave blank if none',$Trad,'Website',2);
      }
      if ($Props &1) {
        if ($Tid >0) {
          echo "<tr><td>Recent Photo:" . fm_DragonDrop(1, 'Photo','Trade',$Tid,$Trad,$Mode); // TODO  <td><a href=PhotoProcess.php?Cat=Perf&id=$snum>Edit/Change</a>";
        } else {
          echo "<td colspan=3>You can upload a photo once you have created your record\n";
        }
        if (Access('SysAdmin')) echo fm_text('Photo',$Trad,'Photo');
      }
      if ($Props &2) {
        if ($Tid >0) {
          echo "<tr><td>Logo:" . fm_DragonDrop(1, 'Logo','Trade',$Tid,$Trad,$Mode); // TODO  <td><a href=PhotoProcess.php?Cat=Perf&id=$snum>Edit/Change</a>";
        } else {
          echo "<td colspan=3>You can upload a Logo once you have created your record\n";
        }
        if (Access('SysAdmin')) echo fm_text('Logo',$Trad,'Logo');

      }
/*
      if ($Props &4) {
        if ($Tid >0) {
          echo "<tr><td>Advert:" . fm_DragonDrop(1, 'Advert','TradeYear',$Tid,$Trad,$Mode); // TODO  <td><a href=PhotoProcess.php?Cat=Perf&id=$snum>Edit/Change</a>";
        } else {
          echo "<td colspan=3>You can upload an Advert once you have created your record\n";
        }
      }
*/


    if ($Mode != 2) echo "<tr>" . fm_textarea('Products <span id=DescSize></span>',$Trad,'GoodsDesc',7,2,
                        'maxlength=500 oninput=SetDSize("DescSize",500,"GoodsDesc")');

//********* PRIVATE

    echo "<tr><th colspan=8><b>Private Information</b>" . Help('PrivateInfo');
    if (($Trad['IsTrader'])??0) {
      echo "<tr>";
        echo "<td>Trade Type:" . help('TradeType') . "<td colspan=7>";
        foreach ($TradeTypeData as $i=>$d) {
          if ($d['Addition']) continue;
          if (($d['TOpen'] != 1) && ($Trad['TradeType'] != $i) && ($Mode==0)) continue;
          echo " <div class=KeepTogether style='background:" . $d['Colour'] . ";'>" . $d['SN'] . ": ";
          echo " <input type=radio name=TradeType $ADDALL value=$i ";
          if ($Trad['TradeType'] == $i) echo " checked";
          echo " onclick='SetTradeType(" . $d['NeedPublicHealth'] . "," . $d['NeedCharityNum'] . "," .
                                          $d['NeedInsurance'] . "," . $d['NeedRiskAssess'] . ',"' . $d['Description'] . '","' .
                                          $d['Colour'] . '","' . Pitch_Size_Def($i) . '")\''; // not fm-Radio because of this line
          echo " id=TradeType$i oninput=AutoRadioInput('TradeType',$i) ";
          echo ">&nbsp;</div>\n ";
        }
        echo "<br clear=all><div id=TTDescription style='background:" . $TradeTypeData[$Trad['TradeType']]['Colour'] . ";'>" .
          $TradeTypeData[$Trad['TradeType']]['Description'] . "</div>\n";
    }
    echo "<tr>" . fm_text('<span id=ContactLabel>Contact Name</span>',$Trad,'Contact');
      echo fm_text1('Email',$Trad,'Email',2);
      echo fm_text('Phone',$Trad,'Phone');
      echo fm_text('Mobile',$Trad,'Mobile',1,$Imp,'onchange=updateimps()') . "\n";
    echo "<tr>" . fm_text('Address',$Trad,'Address',5,$Imp,'onchange=updateimps()');
      echo fm_text('Post Code',$Trad,'PostCode')."\n";

// Other Contacts to be added here at some point


    if ($Trad['IsTrader']??0) {
      echo "<tr class=PublicHealth " . ($TradeTypeData[$Trad['TradeType']]['NeedPublicHealth']?'':'hidden') . ">" ;
      echo fm_text("Registered with which Local Authority ",$Trad,'PublicHealth',2,'colspan=2');
      if (Feature('TradeBID') || Feature('TradeChamberCommerce')) {
        echo "<tr><td>Are a <td>" . (Feature('TradeBID')?(fm_checkbox('BID Levy Payer',$Trad,'BID') . "<td>"):'') .
                                      (Feature('TradeChamberCommerce')?(fm_checkbox('Chamber of Commerce Member',$Trad,'ChamberTrade') . "<td>"):'');
        }
      if ($Mode) echo fm_checkbox('Previous Festival Trader',$Trad,'Previous');
      echo fm_text('Charity Number',$Trad,'Charity',1,'class=Charity ' . ($TradeTypeData[$Trad['TradeType']]['NeedCharityNum']?'':'hidden'));

      if ($Mode) echo "<tr><td class=NotSide colspan=2>" . fm_radio("",$Trader_Status,$Trad,'Status','',0);
    }
    if (Access('SysAdmin') && isset($Trad['AccessKey'])) {
      echo "<tr>";
        if ($Tid > 0) echo "<td class=NotSide>Id: $Tid";
        echo fm_nontext('Access Key',$Trad,'AccessKey',3,'class=NotSide','class=NotSide');
        if (isset($Trad['AccessKey'])) {
          echo "<td class=NotSide><a href=Direct?id=$Tid&t=trade&key=" . $Trad['AccessKey'] . ">Use</a>" . help('Testing');
        }
        echo "  <td class=NotSide><button name=Action value=Delete onClick=\"javascript:return confirm('are you sure you want to delete this?');\">Delete</button>\n";
    }
    if ($Mode && Capability("EnableFinance")) {
      echo "<tr><td class=NotSide>" . fm_checkbox("Is a Trader",$Trad,'IsTrader',' onchange=this.form.submit() ') .
           "<td class=NotSide>" . fm_checkbox("Is a Sponsor",$Trad,'IsSponsor',' onchange=this.form.submit() ') .
           "<td class=NotSide>" . fm_checkbox("Is an Advertiser",$Trad,'IsAdvertiser',' onchange=this.form.submit() ') .
           "<td class=NotSide>" . fm_checkbox("Is a Supplier",$Trad,'IsSupplier',' onchange=this.form.submit() ') .
           "<td class=NotSide>" . fm_checkbox("Is Other",$Trad,'IsOther',' onchange=this.form.submit() ');
      if ($Tid > 0 && Feature('NeedSageCode') && Access('Committee',"Finance")) {
        include_once("InvoiceLib.php");
        if (isset($Trad['SN'])) Sage_Code($Trad);
        echo fm_text("Sage Code",$Trad,'SageCode',1,'class=NotSide','class=NotSide');
      }
      if ($Tid > 0 && ($Trad['IsSponsor'] ?? 0)) echo "<td class=NotSide>" . fm_checkbox("Show Name and Logo",$Trad,'IandT');
    } else {
      echo fm_hidden('IsTrader',$Trad['IsTrader']);
    }
    if ($Mode || (isset($Trad['SortCode']) && $Trad['SortCode'])) {
      echo "<tr><td>Bank Details" . Help('BankDetails') .
           fm_text('Sort Code',$Trad,'SortCode') . fm_text('Account No',$Trad,'Account') . fm_text('Account Name',$Trad,'AccountName',2);
    }
    echo fm_hidden("Tid", $Tid);
    echo fm_hidden("Id", $Tid);

    if ($Mode) {
      echo "<tr>" . fm_textarea('Notes',$Trad,'Notes',7,2,'class=NotSide','class=NotSide');
    }
  echo "</table></div>";
}

function Show_Trade_Year($Tid,&$Trady,$year=0,$Mode=0) {
  global $YEAR,$PLANYEAR,$YEARDATA,$Trade_States,$ADDALL,$Trade_State_Colours,$Trade_State,$Trade_Days,$TradeTypeData;
  global $Trade_State_Props;
  $Trad = Get_Trader($Tid);
  if ($year==0) $year=$YEAR;
  $CurYear = date("Y");
  if ($year != $PLANYEAR) { // Then it is historical - no changes allowed
    fm_addall('disabled readonly');
  }
  $Self = $_SERVER['PHP_SELF'];
  if ($year != $CurYear) {
    if ($Mode && Get_Trade_Year($Tid,$CurYear))
      echo "<div class=floatright><h2><a href=$Self?id=$Tid&Y=$CurYear>$CurYear</a></h2></div>";
    echo "<h2>Trading in $year</h2>";
  } else if ($year == $PLANYEAR) {
    $Prev = $YEARDATA['PrevFest'];
    if ($Mode && Get_Trade_Year($Tid,$Prev))
      echo "<div class=floatright><h2><a href=$Self?id=$Tid&Y=$Prev>$Prev</a></h2></div>";
    echo "<h2>Trading in $year</h2>";
  } else {
    if ($Mode) echo "<div class=floatright><h2><a href=$Self?id=$Tid>$PLANYEAR</a></h2></div>";
    echo "<h2>Details for $year</h2>";
  }
  echo fm_hidden('Year',$year);
  if (isset($Trady['TYid']) && $Trady['TYid']) echo fm_hidden('TYid',$Trady['TYid']);

  $TradeLocs = Get_Trade_Locs(0,'WHERE InUse=1 AND NoList=0');
  $TradeLocFull = Get_Trade_Locs(1);
  echo fm_hidden('TradeLocData',json_encode($TradeLocFull));
  $Trade_Prop = $Trade_State_Props[$Trady['BookingState'] ?? 0];
  $TradePower = Gen_Get_All("TradePower");
  $Powers = [];
  $PowerOpts = [];
  foreach ($TradePower as $i=>$P) {
    if ($P['Cost'] >=0) {
      $PowerOpts[$i] = $Powers[$i] = $P['Name'] . (($P['Cost']??0)? " (£" . $P['Cost'] . ")" :'');
    } else {
      $PowerOpts[$i] = $P['Name'];
    }
  }
//  var_dump($Powers);

  echo "<div class=tablecont><table width=90% border class=SideTable>\n";
  echo fm_hidden('Year',$year);
  if (isset($Trady['TYid'])) echo fm_hidden('TYid',$Trady['TYid']);

  if ($Mode) {
    $Field = (Access('SysAdmin')?'BookingState':'BookingStatus');
    echo "<td class=NotCSide>Booking State:" . help('BookingState') . "<td colspan=3 class=NotCSide>";
      foreach ($Trade_States as $i=>$ts) {
        if( preg_match('/^-/',$Trade_State_Colours[$i])) continue;
        $cls = " style='background:" . $Trade_State_Colours[$i] . ";padding:4; white-space: nowrap;'";
        echo " <div class=KeepTogether $cls>$ts: ";
        echo " <input type=radio name=$Field $ADDALL value=$i ";
        if (!Access('SysAdmin')) echo " readonly disabled ";
        if (isset($Trady['BookingState']) && ($Trady['BookingState'] == $i)) echo " checked";
        echo ">&nbsp;</div>\n ";
      }
      echo "<td class=NotSide>" . fm_checkbox('Show Trader in anystate',$Trady,'ShowAnyway');
      if (!Access('SysAdmin')) echo fm_hidden('BookingState',$Trady['BookingState']);
//    echo fm_radio("Booking State",$Trade_States,$Trady,'BookingState','class=NotCSide',1,'colspan=2 class=NotCSide');
    if ($TradeTypeData[$Trad['TradeType']]['NeedPublicHealth']) echo "<td class=NotSide>" . fm_checkbox('Local Auth Checked',$Trady,'HealthChecked');
    if (Access('Internal')) echo ($Trady['TYid'] ?? -1);
    echo "<td class=NotSide>";
    if ($Trady['BookingState'] == $Trade_State['Quoted']) {
      if ($Trady['DateRemind']) {
        echo "Reminded on: " . date('D d M', $Trady['DateRemind']);
      } else {
        echo "Quoted on: " . date('D d M', $Trady['DateQuoted']);
      }
    }
  } else {
    $stat = $Trady['BookingState'];
    if (!$stat) $stat = 0;
    echo fm_hidden('BookingState',$stat);
    if ($stat == $Trade_State['Fully Paid'] && ($Trady['Insurance'] == 0 || $Trady['RiskAssessment'] == 0)) {
      echo "<td>Booking State:" . help('BookingState') . "<td id=BookState class=TSNoInsRA>Fully Paid";
      if ($Trady['Insurance'] ==0) echo ", no Insurance";
      if ($Trady['RiskAssessment'] ==0) echo ", no Risk Assessment";
    } else {
        echo "<td>Booking State:" . help('BookingState') . "<td id=BookState ";
        if ($stat == $Trade_State['Fully Paid'] && ($Trady['Insurance'] == 0 || $Trady['RiskAssessment'] == 0)) {
          echo " class=TSNoInsRA>Paid";
          if ($Trady['Insurance'] ==0) echo ", no Insurance";
          if ($Trady['RiskAssessment'] ==0) echo ", no Risk Assess";
        } else {
          echo " style='background:" . $Trade_State_Colours[$stat] . ";padding:4; white-space: nowrap;'>" . $Trade_States[$stat];
        }
    }
  }

  if (Feature('TradeDays')) echo "<tr><td>Days:<td>" . fm_select($Trade_Days,$Trady,'Days');
  echo "<tr><td>Requested Pitch Sizes, (WxD) <span class=DefaultPitch>" . Pitch_Size_Def($Trad['TradeType'] ?? 1) . "</span> is default" . Help('PitchSize');

  echo "<td>Location";
  if ($Trade_Prop & 1) {
    echo " Requested";
  } else if ($Trady['PitchLoc0'] ?? 0) {
  } else {
    echo " (When Assigned)";
  }

  if (Feature("TradePower")) echo "<td colspan=2>Power" . Help('Power');
  if (Feature("TradeExtras")) echo "<td colspan=1>Extras" . Help('Extras');

  echo "<td>Pitch Number";
  $TotPowerCost = PowerCost($Trady);
  $TableCost = TableCost($Trady);
  if (($Trady['Fee']??0) < 0) $TotPowerCost = $TableCost = 0;

  for ($i = 0; $i < 3; $i++) {
    $Prop = ($TradeLocFull[$Trady["PitchLoc$i"]?? 0]['Props'] ?? 0);
    $Hideme = (($i>0) && (empty($Trady["PitchLoc$i"])) && (empty($Trady["PitchSize$i"])));
    $More = (($i<2) && (empty($Trady["PitchLoc" . ($i+1)])) && (empty($Trady["PitchSize" . ($i+1)])));

    if (empty($Trady["Power$i"]))$Trady["Power$i"] = 1;
    echo "<tr id=Stall$i " . ($Hideme?'hidden':'') . ">" .
      fm_text1("",$Trady,"PitchSize$i",1,(!$Mode && ($Trady['Fee']??0))?" onchange=CheckReQuote($Tid)":"");
      if (!$Mode && ($Trady['Fee']??0)) echo "<br>Changing will result in a new quote.  Be patient.";
      if (($i < 2) && $More) {
        echo "<div class=TradeMore><button type=button class=TradeMore onclick=MoreStalls($i)>" . ($i?'3rd Stall':'2nd Stall') . "</button></div>";
      }

    if ($Mode) { // Festival
      echo "<td id=PowerFor$i>" . fm_select($TradeLocs,$Trady,"PitchLoc$i",1,"onchange=UpdatePower($i," . ($Trady['Fee'] ?? 0) .")"); //
    } else if ($Trade_Prop & 1) { // Change Pos
      echo "<td>" . fm_select($TradeLocs,$Trady,"PitchLoc$i",1, "onchange=UpdatePower($i," . ($Trady['Fee'] ?? 0) .")");
    } else if ($Trady["PitchLoc$i"] ?? 0) {  // Assigned
      echo "<td>" . ($TradeLocs[$Trady["PitchLoc$i"]]??'');
    } else { // Not assigned
      echo "<td>";
    }

    if (Feature("TradePower")) {
      echo fm_radio('',$Powers,$Trady,"Power$i"," colspan=2 onchange=UpdatePower($i," . ($Trady['Fee'] ?? 0) .")",3); // Add actions to propgate cost
    }

    if (Feature('TradeExtras')) {
      echo fm_number1('Table &amp;<br>2 chairs (£' . Feature('TableCost',9) . ')',$Trady,"Tables$i", '' ,
         " min=0 max=4 onchange=UpdatePower($i," . ($Trady['Fee'] ?? 0) .")" );
    }

    if ($Mode) {

      echo fm_text1("",$Trady,"PitchNum$i",1,'class=NotCSide','class=NotCSide onchange=PitchNumChange(' . ($Trady["PitchNum$i"]??0) . ')');
      if (isset($Trady["PitchLoc$i"]) && $Trady["PitchLoc$i"]) echo " <a href=TradeStandMap?t=2&l=" . $Trady["PitchLoc$i"] . ">Map</a>";
    } else {
//      echo "<td>";
      if (isset($Trady["PitchLoc$i"])  && $Trady["PitchLoc$i"]) {
        echo ($TradeLocs[$Trady["PitchLoc$i"]]??'');
        echo fm_hidden("PitchLoc$i",($Trady["PitchLoc$i"]??0));
        echo "<td>";
        if ($Trady["PitchNum$i"]) echo $Trady["PitchNum$i"] . " <a href=TradeStandMap?t=2&l=" . $Trady["PitchLoc$i"] . ">Map</a>"; // TODO Trade State testing for partial
      } else {
        echo "<td>";
      }
    }
  }


  if ($Mode) {
    $Xtra = '';
    if (empty($Trady['ExtraPowerDesc'])) {
      echo "<tr class=XtraPower><td><button type=button onclick=EnableXtraPower()>Extra Power!</button>";
      $Xtra = 'hidden';
    }
    echo "<tr class='NotCSide XtraPower' $Xtra>";
    echo  fm_text('Extra Power Description',$Trady,'ExtraPowerDesc',2,'class=NotCSide') .
          fm_text('Cost',$Trady,'ExtraPowerCost',1,'class=NotCSide', 'class=NotCSide onchange=FeeChange(0,' . ($Trady['Fee'] ?? 0) .")");
  } else if (($Trady['ExtraPower']??0) > 1) {
    echo "<tr class=NotCSide>";
    echo "<td class=NotCSide>Extra Power - Type:<td>" . $PowerOpts[$Trady['ExtraPower']] . "<td>Number:<td>" . $Trady['ExtraPowerNumber'] .
          "<td>Cost:<td id=ExtraPowerCost>£" . $Trady['ExtraPowerCost'];
  }

  if (isset($Trady['Fee']) && $Trady['Fee'] > 0 ) {
    include_once("InvoiceLib.php");
    if (Feature('TradeInvoicePay')) { // Paycodes - nasty mandyism
      $Pay = Pay_Code_Find(1,$Tid);
      if ($Pay && ($Pay['State']==0) && ($Trady)) {
      echo "<tr><td>Payment due for<td colspan=5><b>" . $Pay['Reason'] . "</b><br>Due " . date('j/n/Y',$Pay['DueDate']) .
        "<br>Please pay " . Print_Pence($Pay['Amount']) . " to:<br>" .
        Feature("FestBankAdr") . "<br>Sort Code: " . Feature("FestBankSortCode") . "<br>Account No: " . Feature("FestBankAccountNum") . 
        "<p>Quote Reference: " . $Pay['Code'];
      }
    } else { // Our own invoices
      $Invs = Invoice_Find(1,$Tid);
//var_dump($Invs);
      if ($Invs && ($Trady)) {
        $inv = array_shift($Invs);
//var_dump($inv);
        echo "<tr><td>Payment due for<td colspan=5><b>" . $inv['Reason'] . "</b><br>Due " . date('j/n/Y',$inv['DueDate']) .
             "<br>Please pay " . Print_Pence($inv['Total']) . " to:<br>" .
            Feature("FestBankAdr") . "<br>Sort Code: " . Feature("FestBankSortCode") . "<br>Account No: " . Feature("FestBankAccountNum") . 
            "<p>Quote Reference: " . $inv['OurRef'] . "/" . $inv['id'];
      }
    }
  }

  echo "<tr>";
    if ($Mode) {
      echo fm_text("Pitch Fee, put -1 for free",$Trady,'Fee',1,'class=NotCSide','class=NotCSide onchange=FeeChange(0,' . ($Trady['Fee'] ?? 0) .")");
      if ($TotPowerCost || $TableCost || ($Trady['ExtraPowerCost']??0) || ($Trady['Fee']??0) ) {
        echo "<td class=Powerelems >Total Fee:<td  class=Powerelems id=PowerFee>£" .
          (($Trady['Fee'] ?? 0) +  ($Trady['ExtraPowerCost']??0) + $TotPowerCost + $TableCost);
      } else {
        echo "<td class=Powerelems hidden >Total Fee:<td  class=Powerelems id=PowerFee hidden >";
      }
      echo fm_text("Paid so far",$Trady,'TotalPaid',1,'class=NotCSide','class=NotCSide');
    } else {
      echo "<td>Pitch Fee:<td>";
      if (!isset($Trady['Fee']) || $Trady['Fee'] == 0 ) {
        echo "To be set";
      } else if ($Trady['Fee']<0) {
        echo "Free";
      } else  {
        echo "&pound;" . $Trady['Fee'];
        if ($TotPowerCost || $TableCost || ($Trady['ExtraPowerCost']??0)) {
          echo "<td class=Powerelems >Total Fee:<td  class=Powerelems id=PowerFee>£" .
            (($Trady['Fee'] ?? 0) + $TotPowerCost +  ($Trady['ExtraPowerCost']??0) + $TableCost);
        } else {
          echo "<td class=Powerelems hidden >Total Fee:<td  class=Powerelems id=PowerFee hidden >";
        }
        echo "<td>Paid so far: &pound;" . $Trady['TotalPaid'];
      }
    }

// Notes, Insurance upload, Risk Assess inline/upload, download, Deposit Required,
// State (Requesting, Accepted, Declined, Invoiced, Deposit Paid, Rejected, Paid, Ammended) Store when Accept
// Email link, and confamation, have means to request new link (use email address known), import existing dataZZ

// Insurance

  echo fm_DragonDrop(1,'Insurance','Trade',$Tid,$Trady,$Mode,"You <b>must</b> have a copy available with you during the festival");

// Risc Assessment function fm_DragonDrop($Call, $Type,$Cat,$id,&$Data,$Mode=0,$Mess='',$Cond=1,$tddata1='',$tdclass='',$hide=0) {
  echo fm_DragonDrop(1,'RiskAssessment','Trade',$Tid,$Trady,$Mode);

  echo "<tr>" . fm_text('Arival day/time',$Trady,'ArrivalTime');
  if (Feature('TraderPasses')) echo fm_number('Trader Passes',$Trady,'NumberTickets','',' min=0 max=4');
  if (Feature('TradeCarpark')) echo fm_number('Carpark passes',$Trady,'NumberCarPass','',' min=0 max=2');

  if (Feature('TradeCamping')) {
    include_once 'VolLib.php';
    global $CampType;
      $camps = Get_Campsites('Trade',1);
      unset($camps[1]);
//var_dump($camps);exit;
      echo "<tr>" . fm_radio("Do you want camping?",$camps,$Trady,'CampNeed','',3,' colspan=4','',
        0,0,''," onchange=CampingTradeSet()");
      echo "<tr id=CampPUB>" . fm_radio("If so for what?" ,$CampType,$Trady,'CampType','',1,' colspan=4');
      echo "<tr id=CampREST>" . fm_text('Please describe the footprint you need.<br>For example 1 car one tent /<br>one car one tent and a caravan etc ',
                    $Trady,'CampText',4);
      Register_OnLoad('CampingTradeSet');
  }

// Notes - As Sides
  echo "<tr>" . fm_textarea('Notes/Requests',$Trady,'YNotes',6,2);
  if ($Mode) echo "<tr>" . fm_textarea('Private Notes',$Trady,'PNotes',6,2,'class=NotSide','class=NotSide');
  if ($Mode) {
    if (Access('SysAdmin')) {
      echo "<tr>" . fm_textarea('History',$Trady,'History',6,2,'class=NotSide',"class='NotSide ScrollEnd'");
    } else {
      $hist = ($Trady['History']?? '');
      echo "<tr><td class=NotSide>History:<td colspan=8 class=NotSide>";
      if ($hist) {
        $hist = preg_replace('/\n/','<br>\n"',$hist);
        echo $hist . fm_hidden("History",$hist);
      }
    }
  }
  if ($Mode) {
    if (isset($Trady['SentInvite']) && $Trady['SentInvite']) {
      echo "<tr>";
      echo fm_date('Invite Sent',$Trady,'SentInvite');
    }
  }

  if (1 || Access('SysAdmin')) echo "<tr><td class=NotSide>Debug<td colspan=6 class=NotSide><textarea id=Debug></textarea>";
  echo "</table></div>\n";
}

function Get_Trade_Details(&$Trad,&$Trady) {
  global $Trade_Days,$TradeLocData,$TradeTypeData,$YEARDATA,$EType_States;
  $Pwr = PowerCost($Trady);
  $Power = Gen_Get_All('TradePower');
  if ($Trady['Fee']<0) $Pwr = 0;

  $Body = "\nBusiness: " . preg_replace('/\|/','',$Trad['SN']) . "\n";
  $Body .= "Goods: " . $Trad['GoodsDesc'] . "\n\n";
  $Body .= "Type: " . $TradeTypeData[$Trad['TradeType']]['SN'] . "\n\n";
  if (isset($Trad['Website']) && $Trad['Website']) $Body .= "Website: " . weblink($Trad['Website'],$Trad['Website']) . "\n\n";
  $Body .= "Contact: " . $Trad['Contact'] . "\n";
  if (isset($Trad['Phone']) && $Trad['Phone']) $Body .= "Phone: " . $Trad['Phone'] . "\n";
  if (isset($Trad['Mobile']) && $Trad['Mobile']) $Body .= "Mobile: " . $Trad['Mobile'] . "\n";
  $Body .= "Email: <a href=mailto:" . $Trad['Email'] . ">" . $Trad['Email'] . "</a>\n";
  $Body .= "Address: " . $Trad['Address'] . "\n";
  $Body .= "PostCode: " . $Trad['PostCode'] . "\n\n";
  if (isset($Trad['Charity']) && $Trad['Charity']) $Body .= "Charity: " . $Trad['Charity'] . "\n";
  if (isset($Trad['PublicHealth']) && $Trad['PublicHealth']) $Body .= "Local Authority: " . $Trad['PublicHealth'] . "\n";
  if (isset($Trad['BID']) && $Trad['BID']) $Body .= "BID Member: Yes\n";
  if (isset($Trad['ChamberTrade']) && $Trad['ChamberTrade']) $Body .= "Chamber of Trade Member: Yes\n";
  if (isset($Trad['Previous']) && $Trad['Previous']) $Body .= "Previous Trader: Yes\n";
  $Body .= "\n\n";

  if (isset($Trady['Year'] ) ) $Body .= "For " . $Trady['Year'] .":\n";
  if (isset($Trade_Days[$Trady['Days']] ) ) $Body .= "Days: " . $Trade_Days[$Trady['Days']] . "\n";
  if (isset($Trady['PitchSize0'] ) ) $Body .= "Pitch:" . $Trady['PitchSize0'];
  $Partial = (array_flip($EType_States))['Partial'];
  if ($Trady['PitchLoc0']) $Body .= " at " . $TradeLocData[$Trady['PitchLoc0']]['SN'];
  if ($YEARDATA['TradeState']>= $Partial && $Trady['PitchNum0']) $Body .= "Pitch Number "  . $Trady['PitchNum0'];
  if (!empty($Trady['Power0']) || ($Trady['Power0']>1)) $Body .= " with " . ($Trady["Power0"]> 0 ? $Power[$Trady['Power0']]['Amps'] . " Amps\n" : " own Euro 4 silent generator\n");
  if (!empty($Trady['QuoteSize0']) && ($Trady['QuoteSize0'] != $Trady['PitchSize0'])) $Body .= "<b>WAS " . $Trady['QuoteSize0'] . "</b>\n";
  if ($Trady['Tables0'] ?? 0) $Body .= "Request Table and 2 chairs\n";

  if ($Trady['PitchSize1']) {
    $Body .= "\nPitch 2:" . $Trady['PitchSize1'];
    if ($Trady['PitchLoc1']) $Body .= " at " . $TradeLocData[$Trady['PitchLoc1']]['SN'];
    if ($YEARDATA['TradeState']>= $Partial && $Trady['PitchNum1']) $Body .= "Pitch Number "  . $Trady['PitchNum1'];
    if (!empty($Trady['Power1']) || ($Trady['Power1']>1)) $Body .= " with " . $Power[$Trady['Power1']]['Amps'] . " Amps\n";
    if (!empty($Trady['QuoteSize1']) && ($Trady['QuoteSize1'] != $Trady['PitchSize1'])) $Body .= "<b>WAS " . $Trady['QuoteSize1'] . "</b>\n";
    if ($Trady['Tables1'] ?? 0) $Body .= "Request Table and 2 chairs\n";
    }
  if ($Trady['PitchSize2']) {
    $Body .= "\nPitch 3:" . $Trady['PitchSize2'];
    if ($Trady['PitchLoc2']) $Body .= " at " . $TradeLocData[$Trady['PitchLoc2']]['SN'];
    if ($YEARDATA['TradeState']>= $Partial && $Trady['PitchNum2']) $Body .= "Pitch Number "  . $Trady['PitchNum2'];
    if (!empty($Trady['Power2']) || ($Trady['Power2']>1)) $Body .= " with " . $Power[$Trady['Power2']]['Amps'] . " Amps\n";
    if (!empty($Trady['QuoteSize2']) && ($Trady['QuoteSize2'] != $Trady['PitchSize2'])) $Body .= "<b>WAS " . $Trady['QuoteSize1'] . "</b>\n";
    if ($Trady['Tables2'] ?? 0) $Body .= "Request Table and 2 chairs\n";
    }

  if ($Trady['ExtraPowerDesc']) {
    $Body .= "Extra Power of " .  $Trady['ExtraPowerDesc'] . " at a cost of £" . $Trady['ExtraPowerCost'];
  }

  if ($Trady['Fee']) {
    if ($Trady['Fee'] < 0 ) {
      $Body .= "\nFee: None.\n";
      $Dep = 0;
    } else {
      $Dep = T_Deposit($Trad);
      if ($Pwr) $Body .= "Power Cost: &pound;$Pwr\n";
      $Body .= "\nDeposit: &pound;$Dep\nBalance: &pound;" . ($Trady['Fee'] +$Pwr - $Dep) . "\nTotal: &pound;" . ($Trady['Fee'] + $Pwr) . "\n\n";
    }
  }

  $Body .= "*PAYCODES*\n";

  if ($Trady['YNotes']) $Body .= "Notes: " . $Trady['YNotes'] . "\n";
  if ($Trady['Insurance']) $Body .= "Insurance already uploaded\n";
  if ($Trady['RiskAssessment']) $Body .= "Risk Assessment already uploaded\n";

  $Body = preg_replace('/\n/',"<br>\n",$Body);
  return $Body;
}

function Trade_Finance(&$Trad,&$Trady) { // Finance statement as part of statement
  $Invs = Get_Invoices(" OurRef='" . Sage_Code($Trad) . "'"," IssueDate DESC ");
  if (!$Invs) return "";
  $PaidSoFar = (isset($Trady['TotalPaid']) ? $Trady['TotalPaid'] : 0);
  $Pwr = PowerCost($Trady) +  $Trady['ExtraPowerCost'];

  $Str = "Paid so far: &pound;$PaidSoFar<br>";
  $Dep = ($Trady['Fee']>0?T_Deposit($Trad):0);
  if ($Trady['Fee']<0) $Pwr = 0;

  if ($Dep) $Str .= "The deposit is: &pound;$Dep<br>";
  if ($PaidSoFar) {
    if ($PaidSoFar < ($Trady['Fee'] + $Pwr)) $Str .= "There will be a balance of: &pound;" . ($Trady['Fee'] + $Pwr - $PaidSoFar) . "<br>";
  } else {
    $Str .= "There will be a balance of: &pound;" . ($Trady['Fee'] + $Pwr - $Dep) . "<br>";
  }

  $Str .= "*PAYCODES*";

  if ($Invs[0]['PayDate']) {
    $Str .= "The most recently paid invoice is attached for your records.<p>";
  } else {
    $Str .= "There is an outstanding invoice for " . Print_Pence($Invs[0]['Total']) . " (attached)<p>";
  }
  return $Str;
}

function Trader_Details($key,&$data,$att=0) {
  global $TradeLocData,$Prefixes,$YEAR;
  $Trad = &$data[0];
  if (isset($data[1])) $Trady = &$data[1];
  $host = "https://" . $_SERVER['HTTP_HOST'];
  $Tid = $Trad['Tid'];
  $mtch = [];

  switch ($key) {
  case 'WHO':  return $Trad['Contact']? UpperFirstChr(firstword($Trad['Contact'])) : preg_replace('/\|/','',$Trad['SN']);
  case 'LINK': return "<a href='$host/int/Direct?t=Trade&id=$Tid&key=" . $Trad['AccessKey'] . "'  style='background:lightblue;'><b>link</b></a>";
  case 'WMFFLINK':
  case 'FESTLINK' : return "<a href='$host/int/Trade?id=$Tid'><b>link</b></a>";
  case 'HERE':
  case 'REMOVE': return "<a href='$host/int/Remove?t=Trade&id=$Tid&key=" . $Trad['AccessKey'] . "'><b>remove</b></a>";
  case 'BIZ': return $Trad['BizName'] ?? $Trad['SN'] ?? "you";
  case 'LOCATION':
    $Locs = Get_Trade_Locs(1);
    $Location = '';
    if ($Trady['PitchLoc0']) $Location = $Trady['PitchSize0'] . " in " . $Locs[$Trady['PitchLoc0']]['SN'];
    if ($Trady['PitchLoc1']) {
      if ($Trady['PitchLoc2']) { $Location .= ", " .  $Trady['PitchSize1'] . " in " . $Locs[$Trady['PitchLoc1']]['SN']; }
      else { $Location .= " and " .  $Trady['PitchSize1'] . " in " . $Locs[$Trady['PitchLoc1']]['SN']; }
    }
    if ($Trady['PitchLoc2']) { $Location .= " and " .  $Trady['PitchSize2'] . " in " . $Locs[$Trady['PitchLoc2']]['SN']; }
    return $Location;
  case 'PRICE':
    $Price = $Trady['Fee'];
    if ($Price < 0) return "Free";
    if ($Price ==0) return "Not Known";
    return "&pound;" . $Price;
  case 'DEPOSIT': return T_Deposit($Trad);
  case 'BALANCE': return ($Trady['Fee'] + PowerCost($Trady) + $Trady['ExtraPowerCost'] + TableCost($Trady) - $Trady['TotalPaid']);
  case 'DETAILS': return Get_Trade_Details($Trad,$Trady);
  case 'PAIDSOFAR': return $Trady['TotalPaid'];
  case 'STATE': return ['No application has been made',
                        'Invitation/Quote has been declined',
                        'A refund has been made',
                        'The application has been cancelled',
                        'The application has been submitted',
                        'A price has been quoted',
                        'The application has been accepted, no deposit paid',
                        'The deposit has been paid',
                        'Final balacing payment has been requested but not paid',
                        'Fully Paid',
                        'On a wait list',
                        'Awaiting a requote after change'][$Trady['BookingState']] . "<P>";
  case 'BACSREF':
    preg_match('/(\d*)\.pdf/',$att,$mtch);
    return Sage_Code($Trad) . "/" . (isset($mtch[1]) ? $mtch[1] : '0000' );
  case 'FINANCIAL': return Trade_Finance($Trad,$Trady);
  case 'PAYDAYS' : return Feature('PaymentTerms',30);
  case 'TRADEMAP':
    $MapLinks = '';
    for ($i=0; $i<3; $i++) {
      if ($Trady["PitchLoc$i"] && $Trady["PitchNum$i"]) {
        $plural = (strchr(',',$Trady["PitchNum$i"])?"Pitches numbered ":"Pitch number ");
        $MapLinks .= "You have been assigned $plural " . $Trady["PitchNum$i"] . " " .
                     $Prefixes[$TradeLocData[$Trady["PitchLoc$i"]]['prefix']] . " " . $TradeLocData[$Trady["PitchLoc$i"]]['SN'] .
                     " please see this <a href='$host/int/TradeStandMap?l=" . $Trady["PitchLoc$i"] . "&t=2' style='background:lightblue;'>map</a> " .
                     "<p>" .
                     "Note the formatting of the business names on this should be improved soon<p>";
      }
    }
    if (!$MapLinks) return "";
    return "<b>Pitch assignments</b>.  The layouts of many areas are for health and safety reasons and are not negotiable.<p> " . $MapLinks;
  case 'WEBSITESTUFF':
    $webstuff = '';
    if (!$Trad['Photo']) {
      $webstuff = "If you would like a photo to appear on our website, please use the *LINK* to upload one.  ";
    }
    $webstuff .= "If you would like to revise the description of what sell or you do, please use the *LINK* to revise it (this will appear on our website).  ";
    return "$webstuff<p>";
  case 'DEPCODE': return $Trady['DepositCode'];
  case 'BALCODE': return $Trady['BalanceCode'];
  case 'OTHERCODE': return $Trady['OtherCode'];
  case 'PAIDSOFAR': return $Trady['TotalPaid'];
  case 'PAYCODES':
    include_once("InvoiceLib.php");
    $Pay = Pay_Code_Find(1,$Tid);
    if ($Pay && $Pay['State']==0) {
      return "<b>Payment due</b><br>For: <b>" . $Pay['Reason'] . "</b><br>Due: " . date('j/n/Y',$Pay['DueDate']) .
          "<br>Please pay: " . Print_Pence($Pay['Amount']) . " to:<br>" .
          Feature("FestBankAdr") . "<br>Sort Code: " . Feature("FestBankSortCode") . "<br>Account No: " . Feature("FestBankAccountNum") . "<p>Quote Reference: " .
          $Pay['Code'] . "<p>";
    }
    return "";
  case 'VAT': if (Feature('FestVatNumber')) {
      return "Prices include VAT at " . Feature('VatRate') . "%<p>";
    } else {
      return "";
    }

  case 'TRADEORG':
    return Feature('TradeOrg');

  case (preg_match('/TICKBOX(.*)/',$key,$mtch)?true:false):
    $bits = preg_split('/:/',$mtch[1],3);
    $box = 1;
    $txt = 'Click This';
    if (isset($bits[1])) $box = $bits[1];
    if (isset($bits[2])) {
      $txt = preg_replace('/_/',' ',$bits[2]);
    }
    return "<a href='$host/int/Access?t=t&i=$Tid&TB=$box&k=" . $Trad['AccessKey'] . "&Y=$YEAR'><b>$txt</b></a>\n"; // NOT Y=YEAR?


/* TODO DUFF
  case 'DUEDATE' return
    $tc = Trade_Date_Cutoff();
    if ($tc) return $tc;
    return Feature('PaymentTerms',30);
*/
  default: return "UNKNOWN CODE $key UNKNOWN UNKNOWN";
  }
}

function Trader_Admin_Details($key,&$data,$att=0) {
  $Trad = &$data[0];
  $Trady = &$data[1];
  $res = Trader_Details($key,$data,$att);
  if ($key == 'DETAILS') {
    if ($Trad['Status'] == 1) $res = "THIS IS FROM A BANNED TRADER<P>" . $res;
    if ($Trad['Notes']) $res .= "<p>PRIVATE NOTES:<br>" . $Trad['Notes'] . "<p>";
    if ($Trady['PNotes']) $res .= "<p>PRIVATE NOTES:<br>" . $Trady['PNotes'] . "<p>";
  }
  return $res;
}

function Send_Trader_Email(&$Trad,&$Trady,$messcat='Link',$att='') {
  global $PLANYEAR;
  include_once("Email.php");
  $bccto = Feature('CopyTradeEmailsTo');
  $bcc=[];
  $from = Feature('SendTradeEmailFrom');
  if ($from) $from .= "@" . Feature('HostURL');
  if ($bccto) $bcc = ['bcc' , "$bccto@" . Feature('HostURL'),Feature('CopyTradeEmailsName')];
  Email_Proforma(EMAIL_TRADE,$Trad['Tid'],[['to',$Trad['Email'],$Trad['Contact']],$bcc],
    $messcat,Feature('FestName') . " $PLANYEAR and " . preg_replace('/\|/','',$Trad['SN']),'Trader_Details',[&$Trad,&$Trady],'TradeLog',$att,0,$from);
}

function Send_Trader_Simple_Email(&$Trad,$messcat='Link',$att='') {
  global $PLANYEAR;
  include_once("Email.php");
  $from = Feature('SendTradeEmailFrom');
  if ($from) $from .= "@" . Feature('HostURL');
  Email_Proforma(EMAIL_TRADE,$Trad['Tid'],[$Trad['Email'],$Trad['Contact']],
    $messcat,Feature('FestName') . " $PLANYEAR and " . preg_replace('/\|/','',$Trad['SN']),'Trader_Details',[&$Trad],'TradeLog',$att,0,$from);
}

function Send_Trade_Finance_Email(&$Trad,&$Trady,$messcat,$att=0) {
  global $PLANYEAR;
  include_once("Email.php");

  Email_Proforma(EMAIL_TRADE,$Trad['Tid'],"treasurer@" . Feature('HostURL'),
    $messcat,Feature('FestName') . " $PLANYEAR and " . preg_replace('/\|/','',$Trad['SN']),'Trader_Details',[&$Trad,&$Trady],'TradeLog',$att);
}

function Send_Trade_Admin_Email(&$Trad,&$Trady,$messcat,$att=0) {

  global $PLANYEAR;
  include_once("Email.php");

  Email_Proforma(EMAIL_TRADE,$Trad['Tid'],"trade@" . Feature('HostURL'),
    $messcat,Feature('FestName') . " $PLANYEAR and " . preg_replace('/\|/','',$Trad['SN']),'Trader_Admin_Details',[&$Trad,&$Trady],'TradeLog',$att);
}

//  Mark as submitted, email fest and trader, record data of submission
function Submit_Application(&$Trad,&$Trady,$Mode=0) {
  global $PLANYEAR,$USER;
  $Trady['Date'] = time();
  if (!isset($Trady['History'])) $Trady['History'] = '';
  $Trady['History'] .= "Action: Submit on " . date('j M Y H:i:s') . " by " . ($Mode?$USER['Login']:'Trader') . ".<br>";
  if ($Trady['TYid']) {
    Put_Trade_Year($Trady);
  } else { // Its new...
    $Trady['Year'] = $PLANYEAR;
    Insert_db_post('TradeYear',$Trady);
    $Trady = Get_Trade_Year($Trad['Tid']); // Read data to get all the 0's in place
  }

  Send_Trader_Email($Trad,$Trady,'Trade_Submit');
  Send_Trade_Admin_Email($Trad,$Trady,'Trade_NewSubmit');

  echo "<h3>Your application has been submitted</h3>\nAn email has been sent to you with a summary of your submission and a link to enable you to update it.\n<p>";

  echo "<b>IF</b> you do not see the email, Please check your SPAM folder and mark the message as <b>Not SPAM</b>, " .
       "otherwise you will not see any subsequent message from us.<p>";
}

function Validate_Trade($Mode=0) { // Mode 1 for Staff Submit, less stringent
  global $TradeTypeData,$Trade_State;
  $Orgs = isset($_REQUEST['ORGS']);
      $proc = 1;
      if (!isset($_REQUEST['SN']) || strlen($_REQUEST['SN']) < 3 ) {
        echo "<h2 class=ERR>No Business Name Given</h2>\n";
        $proc = 0;
      }

      if ($Orgs==0 && $Mode == 0 && ($TradeTypeData[$_REQUEST['TradeType']]['TOpen'] == 0)) {
        if ($_REQUEST['BookingState'] < $Trade_State['Quoted'] || $_REQUEST['BookingState'] > $Trade_State['Fully Paid']) {
          echo "<h2 class=ERR>Sorry that category is full for this year</h2>\n";
          $proc = 0;
        }
      }

      if (!isset($_REQUEST['Contact']) || strlen($_REQUEST['Contact']) < 4 ) {
        echo "<h2 class=ERR>No Contact Name Given</h2>\n";
        $proc = 0;
      }
      if ($Orgs==0 && (!isset($_REQUEST['Phone']) && !isset($_REQUEST['Mobile'])) || (strlen($_REQUEST['Phone']) < 6 && strlen($_REQUEST['Mobile']) < 6)) {
        echo "<h2 class=MERR>No Phone/Mobile Numbers Given</h2>\n";
        if (!$Mode) $proc = 0;
      }
      if (!isset($_REQUEST['Email']) || strlen($_REQUEST['Email']) < 8) {
        echo "<h2 class=MERR>No Email Given</h2>\n";
        if (!$Mode) $proc = 0;
      }
      if ($Orgs==0 && !isset($_REQUEST['Address']) || strlen($_REQUEST['Address']) < 10) {
        echo "<h2 class=MERR>No Address Given</h2>\n";
        if (!$Mode) $proc = 0;
      }
      if ($Orgs==0 ) {
      } else if (!isset($_REQUEST['GoodsDesc'])) {
        echo "<h2 class=ERR>No Products Description Given</h2>\n";
        $proc = 0;
      } else if ((strlen($_REQUEST['GoodsDesc']) < 30) && ($Mode == 0)){
        echo "<h2 class=ERR>The Product Description is too short</h2>\n";
        $proc = 0;
      }
      if ($Orgs==0 && (!isset($_REQUEST['PublicHealth']) || strlen($_REQUEST['PublicHealth']) < 4) && ($TradeTypeData[$_REQUEST['TradeType']]['NeedPublicHealth']) && ($Mode == 0)) {
        echo "<h2 class=ERR>No Public Health Authority Given</h2>\n";
        $proc = 0;
      }
  return $proc;
}

function Trader_Name($Tid) {
  $Trad = Get_Trader($Tid);
  return preg_replace('/\|/','',$Trad['SN']);
}

function T_Deposit(&$Trad) {
  global $TradeTypeData;
  return $TradeTypeData[$Trad['TradeType']]['Deposit'];
}

function Validate_Pitches(&$CurDat) {
  return ''; // TODO Completely wrong...

// Pitches not only trade -
// Lists of Pitches

  global $db,$PLANYEAR,$TradeLocData;
  for ($pn=0; $pn<3; $pn++) {
    if ($_REQUEST["PitchLoc$pn"] != $CurDat["PitchLoc$pn"] || $_REQUEST["PitchNum$pn"] != $CurDat["PitchNum$pn"]) {
      if ($_REQUEST["PitchLoc$pn"]) {
        if ($_REQUEST["PitchNum$pn"]) { // Loc & NUm set, lets check them
          $pl = $_REQUEST["PitchLoc$pn"];
          $ln = $_REQUEST["PitchNum$pn"];
          if ($CurDat['Days'] == 0) {
            $DayTest = '';
          } else if ($CurDat['Days'] == 1) {
            $DayTest = " AND ( Days!=2 ) ";
          } else {
            $DayTest = " AND ( Days!=1 ) ";
          }
          $qry = "SELECT * FROM TradeYear WHERE Year='$PLANYEAR' AND (( PitchLoc0=$pl AND PitchNum0=$ln ) || (PitchLoc1=$pl AND PitchNum1=$ln) " .
                 " || (PitchLoc2=$pl AND PitchNum2=$ln)) $DayTest";
          $res = $db->query($qry);
          if ($res->num_rows != 0) {
            $dat = $res->fetch_assoc();
            return "Pitch " . ($pn+1) . " already in use by " . Trader_Name($dat['Tid']);
          }
          if ($ln > $TradeLocData[$pl]['Pitches']) return "Pitch Number " . ($pn+1) . " Out of range (1-" . $TradeLocData[$pl]['Pitches'] . ")";
        }
      }
    }
  }
  return '';
}

function Trade_TickBox($Tid,&$Trad,&$Trady,$TB) {
  global $PLANYEAR;
  $Mode = 0;
  if (Access('Committee')) $Mode = 1;

  switch ($TB) {
  case '1': // Move - Happy with new dates
    Trade_Action('DateHappy',$Trad,$Trady,$Mode);
    break;

  case '2': // Move - Unable to do new dates
    Trade_Action('DateUnHappy',$Trad,$Trady,$Mode);
    break;

  case '3': // move - Recieved
    Trade_Action('DateAck',$Trad,$Trady,$Mode);
    break;

  case '4': // Cancel - Happy on new year
    Trade_Action('CancelHappy1',$Trad,$Trady,$Mode);

    $NextY = $PLANYEAR; //$YEARDATA['NextFest'];
// echo "Next year is $NextY <p>"; exit;
    $NTrady = Get_Trade_Year($Tid,$NextY);
    if (!$NTrady) $NTrady = $Trady;
    $NTrady['Year'] = $NextY;
    $NTrady['DateChange'] = 0;
    $NTrady['TYid'] = 0;
//var_dump($NTrady); exit;
    Put_Trade_Year($NTrady);

    Trade_Action('CancelHappy2',$Trad,$NTrady,$Mode);
//var_dump($NTrady); exit;
    return $NTrady;

  case '5': // Cancel - Unable to do new dates
    Trade_Action('CancelUnHappy',$Trad,$Trady,$Mode);
    break;

  case '6': // Cancel - Recieved
    Trade_Action('CancelAck',$Trad,$Trady,$Mode);
    break;

  }
  return 0;
}

function Trade_Main($Mode,$Program,$iddd=0) {
// Mode 0 = Traders, 1 = ctte, 2 = Finance (for other invoices) Program = Trade/Trader $iddd if set starts it up, with that Tid

  global $YEAR,$PLANYEAR,$Mess,$Action,$Trade_State,$Trade_States,$USER,$TS_Actions,$ButExtra,$ButTraderTips, $ButTrader,$ButAdmin,$RestrictButs;
  global $TradeTypeData,$TradeLocData;
  include_once("DateTime.php");
  echo "<div class=content><h2>Add/Edit " . ($Mode<2?'Trade Stall Booking':'Business or Organisation') . "</h2>";

//  var_dump($_REQUEST);
/*
  $file = fopen("LogFiles/moeslog",'a+');
  fwrite($file,json_encode($_REQUEST));
  fclose($file);
*/

  $Orgs = isset($_REQUEST['ORGS']);

  $Action = 0;
  $Mess = '';
  if (isset($_REQUEST['Action'])) {
    include_once("Uploading.php");
    $Action = $_REQUEST['Action'];
    switch ($Action) {
    case 'PASpecUpload':
      $Mess = Upload_PASpec();
      break;
    case 'Insurance':
      $Mess = Upload_Insurance('Trade');
      break;
    case 'Photo':
      $Mess = Upload_Photo('Trade');
      break;
    case 'Delete':
      if (Access('SysAdmin')) {
        $Tid = $_REQUEST['Tid'];
        db_delete('Trade',$Tid);
        include_once("Staff.php");  // No return
      }
      break;
    default:
      $Mess = "!!!";
    }
  }

  if ($iddd != 0) {
    unset($_REQUEST['Tid']);
    if ($iddd > 0) {
      $_REQUEST['id'] = $iddd;
    } else {
      unset($_REQUEST['id']);
    }
  }

  if (isset($_REQUEST['Tid'])) { /* Response to update button */
    $Tid = $_REQUEST['Tid'];

//    A_Check('Participant','Trader',$Tid); // Check Surpressed until access resolved

    if (!$Orgs) {
//      if (Feature("TradePower")) for ($i=0;$i<3;$i++) if (($_REQUEST["PowerType$i"]??0)==1) $_REQUEST["Power$i"] = -1; // TODO
      Clean_Email($_REQUEST['Email']);
//    Clean_Email($_REQUEST['AltEmail']);
      $proc = Validate_Trade($Mode);
    }

//echo "Trade Validation: $proc <br>;
    if ($Tid > 0) {                                 // existing Trader
      $Trad = Get_Trader($Tid);
      if ($Trad) {
        if (!$Orgs) {
          $Tradyrs = Get_Trade_Years($Tid);
          if (isset($Tradyrs[$PLANYEAR])) $Trady = $Tradyrs[$PLANYEAR];
        }
      } else {
        echo "<h2 class=ERR>Could not find Trader $Tid</h2>\n";
      }

      if (isset($_REQUEST['NewAccessKey'])) $_REQUEST['AccessKey'] = rand_string(40);

      if (!isset($_REQUEST['ACTION']) || ($_REQUEST['ACTION'] == 'Save') ) Update_db_post('Trade',$Trad);
      if (Feature('LogAllTrade')) Report_Log('Trade');
      if ($Mode < 2 && !$Orgs && isset($_REQUEST['Year'])) {
        if ($_REQUEST['Year'] == $PLANYEAR) {
          $same = 1;
          if (isset($Trady) && $Trady) {
            $OldFee = $Trady['Fee'];
            if ($Mode) {
              if (isset($Trady['BookingState'])) {
                if (isset($_REQUEST['BookingState']) && ($Trady['BookingState'] != ($_REQUEST['BookingState']??0))) {
                  $_REQUEST['History'] .= "Action: " . $Trade_States[$_REQUEST['BookingState']] . " on " . date('j M Y H:i') .
                                       " by " . $USER['Login'] . ".<br>";
                }
                if (($_REQUEST['Fee'] < 0) && (($_REQUEST['BookingState']??0) == $Trade_State['Deposit Paid'])) {
                  $_REQUEST['BookingState'] = $Trade_State['Fully Paid'];
                  $_REQUEST['History'] .= "Action: " . $Trade_States[$_REQUEST['BookingState']??0] . " on " . date('j M Y H:i') .
                    " by " . $USER['Login'] . ".<br>";
                }
              }
              if ($_REQUEST['PitchLoc0'] != $Trady['PitchLoc0'] || ($_REQUEST['PitchLoc1']??0) != $Trady['PitchLoc1'] || ($_REQUEST['PitchLoc2']??0) != $Trady['PitchLoc2'] ||
                  $_REQUEST['PitchNum0'] != $Trady['PitchNum0'] || ($_REQUEST['PitchNum1']??0) != $Trady['PitchNum1'] || ($_REQUEST['PitchNum2']??0) != $Trady['PitchNum2'] ) {
                $Mess = Validate_Pitches($Trady);
                if ($Mess) echo "<h2 class=Err>$Mess</h2>";
              }
            }

            $same=1;
            foreach(["PitchSize0","PitchSize1","PitchSize2","Days"] as $cc) if ($Trady[$cc] != ($_REQUEST[$cc]??0)) $same = 0;
            if ($Trad['TradeType'] != $_REQUEST['TradeType']) $same = 0;
            foreach(["Power0","Power1","Power2"] as $cc) if ($Trady[$cc] && $_REQUEST[$cc] && $Trady[$cc] != ($_REQUEST[$cc]??0)) $same = 0; // TODO THIS NEEDS CHANGE

            if (!$Mess) {
               if (!isset($_REQUEST['ACTION']) || ($_REQUEST['ACTION'] == 'Save') ) Update_db_post('TradeYear',$Trady);
            }
            if (!$Mess && $same == 0 && $Trady['BookingState'] > $Trade_State['Submitted']) {
              Send_Trade_Admin_Email($Trad,$Trady,'Trade_Changes');
              $Trady['BookingState'] = $Trade_State['Requote'];
              Put_Trade_Year($Trady);
            }
            if (!Feature('AutoInvoices',1) && $Trady['Fee'] >=0 && $OldFee != $Trady['Fee'] && $Trady['BookingState'] >= $Trade_State['Accepted'])
                  Send_Trade_Finance_Email($Trad,$Trady,'Trade_UpdateBalance');
          } else {
            $chks = ['Insurance','RiskAssessment','PitchSize0','PitchSize1','PitchSize2','Power0','Power1','Power2','YNotes','BookingState','Submit',
                     'Days','Fee','PitchLoc0','PitchLoc1','PitchLoc2','ACTION'];
            foreach($chks as $c) if (isset($_REQUEST[$c]) && $_REQUEST[$c]) {
              if ($c == 'PitchSize0' && $_REQUEST[$c] == "3Mx3M") continue; // This is the only non blank default
              if (isset($_REQUEST['Fee']) && ($_REQUEST['Fee'] < 0) && ($_REQUEST['BookingState'] >= $Trade_State['Accepted'])) $_REQUEST['BookingState'] = $Trade_State['Fully Paid'];
              $_REQUEST['Year'] = $PLANYEAR;
              Insert_db_post('TradeYear',$Trady);
              $Trady = Get_Trade_Year($Trad['Tid']);
              break;
            }
          }
        }
        if ($proc && isset($_REQUEST['ACTION'])) Trade_Action($_REQUEST['ACTION'],$Trad,$Trady,$Mode);
      } else { // Mode ==2 || Orgs
//        if (isset($_REQUEST['ACTION'])) Invoice_Action($_REQUEST['ACTION'],$Trad);
      }
    } else { // New trader

 //     var_dump($_REQUEST);exit;
      $_REQUEST['AccessKey'] = rand_string(40);
      $Tid = Insert_db_post('Trade',$Trad);
      if ($Tid && !$Orgs && $Trad['IsTrader'] ) {
        Insert_db_post('TradeYear',$Trady);
        $Trady = Get_Trade_Year($Trad['Tid']);
        if (empty($Trady)) $Trady = Default_Trade($Tid,$Trad['TradeType']);
      }

      if ($Mode == 2 || $Orgs) {
//        if (isset($_REQUEST['ACTION'])) Invoice_Action($_REQUEST['ACTION'],$Trad);
      } else {
        if ($proc && isset($_REQUEST['ACTION'])) Trade_Action($_REQUEST['ACTION'],$Trad,$Trady,$Mode);
      }
    }
    if ($Mode !== 2 && $proc && isset($_REQUEST['Submit'])) Submit_Application($Trad,$Trady,$Mode);

  } elseif (isset($_REQUEST['id'])) { // Link from elsewhere
    $Tid = $_REQUEST['id'];
    $Trad = Get_Trader($Tid);
    if ($Trad && $Trad['IsTrader'] && !$Orgs) {
      $Tradyrs = Get_Trade_Years($Tid);
      if (isset($Tradyrs[$YEAR])) {
        $Trady = $Tradyrs[$YEAR];
      } else {
        $Trady = Default_Trade($Tid,$Trad['TradeType']);
      }
    } elseif (!$Trad) {
      echo "<h2 class=ERR>Could not find Trader $Tid</h2>\n";
    }
  } elseif ($Mode != 2 && !$Orgs) {
    $Tid = -1;
    $Trad = ['TradeType' => 1, 'IsTrader' => 1];
  } else {
    $Tid = -1;
    $Trad = ['TradeType' => 1, 'IsTrader' => 0];
  }

  if (!isset($Trady)) $Trady = Default_Trade($Tid,($Trad['TradeType']??0));

  if (isset($_REQUEST['TB'])) {
    $Ans = Trade_TickBox($Tid,$Trad,$Trady,$_REQUEST['TB']);
    if ($Ans) $Trady = $Ans;
    $DYear = $Trady['Year'];
// echo "<p>CCC $YEAR $DYear";
  } else {
    $DYear = $YEAR;
  }

// echo "<p>Trady " . $Trady['Year'] . "<p>";

  Show_Trader($Tid,$Trad,$Program,$Mode);
  if ($Mode < 2 && !$Orgs) {
    if (($Mode==0) && (Feature('TradeStatus',1) ==0)) {
      echo "<h2>The Trading system is still being set up.  When ready, you will be able to complete your booking here.</h2>";
    } else {
      Show_Trade_Year($Tid,$Trady,$DYear,$Mode);
    }
  }

  if ($Mode == 0 && !$Orgs) {
    echo "<h2>The action buttons to click on are below the Terms and conditions</h2>";
 /*   echo "<Center>";
    if (Access('SysAdmin')) echo "<input type=Submit name='Update' value='Save Changes'>";
    echo "</Center>";    */
    echo TnC('TradeTnC');
    echo TnC('TradeTimes');
    echo TnC('TradeFAQ');
  }

  if ($Tid > 0) {
    if (Access('Committee','Trade')) echo "<input type=submit name=Save value=Save> - Only needed in a few obscure cases";
    if ($Mode < 2 && !$Orgs) {
      if (!isset($Trady['BookingState'])) { $Trady['BookingState'] = 0; $Trady['Fee'] = 0; }
      if (Access('SysAdmin')) {
        echo "<div class=floatright>";
        echo "<input type=Submit id=smallsubmit name='NewAccessKey' value='New Access Key'>";
        if (!Feature("AutoInvoices",1) && $Trady['BookingState'] >= $Trade_State['Accepted'])
          echo "<input type=Submit id=smallsubmit name='ACTION' value='Resend Finance'>";
        echo "</div>\n";
      }
    }
    echo "<Center>";
    if (Access('Committee','Finance')) {
      echo "<input type=Submit name='NewInvoice' title='Send a NON TRADE Invoice to this trader' value='New Invoice' " .
           "formaction='InvoiceManage?ACTION=NEWFOR&Tid=$Tid'>\n";
    }
//    if (!isset($Trady['BookingState']) || $Trady['BookingState']== 0) echo "<input type=Submit name=Submit value='Save Changes and Submit Application'>";

    $Act = (($Mode < 2 && !$Orgs)? $TS_Actions[$Trady['BookingState']] :"");
    if ($Act ) {
      $Acts = preg_split('/,/',$Act);
//      if ($TradeTypeData[$Trad['TradeType']]['ArtisanMsgs']) {
//        if ($TradeLocData[$Trady['PitchLoc0']]['ArtisanMsgs']) $dummy=1;
//      }
//echo $Trad['TradeType'];
      if ($TradeTypeData[$Trad['TradeType']]['ArtisanMsgs'] && isset($Trady['PitchLoc0']) && $Trady['PitchLoc0'] &&
         $TradeLocData[$Trady['PitchLoc0']]['ArtisanMsgs']) $Acts[] = 'Artisan Invite';
      foreach($Acts as $ac) {
        if ($Mode==0 && !in_array($ac,$ButTrader)) continue;
        if ($Mode==1 && !Access('Committee','Trade') && !in_array($ac,$ButAdmin)) continue;
        if (!isset($Trady['Fee'])) $Trady['Fee'] = 0;
        if (Feature('AutoInvoices',1) && !Access('SysAdmin') && in_array($ac,$RestrictButs)) continue;  // Normal people cant hit Paid have to be through the invoice
        $xtra = '';
        switch ($ac) {
          case 'Quote':
            $xtra = " id=QuoteButton " . ($Trady['Fee']?'':'hidden');
            break;
          case 'Artisan Invite':
            $xtra = " id=ArtInviteButton " . ($Trady['Fee']?'':'hidden');
            break;
          case 'Invite':
            $xtra = " id=InviteButton " . ($Trady['Fee']?'':'hidden');
            break;
          case 'Invite Better':
            if (!Feature('InviteBetter')) continue 2;
            $xtra = " id=InviteBetterButton " . ($Trady['Fee']?'':'hidden');
            break;
          case 'Accept':
            if ($Trady['Fee'] == 0) continue 2;
            break;
          case 'Dep Paid':
            if ($Trady['Fee'] == 0 || Feature('AutoInvoices',1)) continue 2;
            break;
          case 'Paid':
            if ($Trady['Fee'] == 0 || Feature('AutoInvoices',1)) continue 2;
            break;
          case 'Invoice':
          case 'Bal Request':
            if ($Trady['PitchLoc0'] == 0 || $Trady['Fee'] == 0) continue 2;
            break;
          case 'Send All':
            if (1) break;
          
          case 'FestC' :
            if (!Feature('EnableCancelMsg')) continue 2;
            break;
          case 'Dates' :
            if (!Feature('EnableDateChange')) continue 2;
            break;
          case 'Submit' :
            if (Feature('TradeStatus',1) == 0) continue 2;
            break;
          case 'Moved' :
            if (($Trady['PitchNum0'] ??0) == 0) continue 2;
            $xtra = " id=MoveButton hidden ";
            break;
          case 'Pitch Assign' :
            if (($Trady['PitchNum0'] ??0) == 0) continue 2;
            $xtra = " id=PitchAssignButton hidden ";
            break;
          case 'Pitch Change' :
            if (($Trady['PitchNum0'] ??0) == 0) continue 2;
            $xtra = " id=PitchChangeButton hidden ";
            break;

          case 'LastWeek' :
            if (($Trady['DateQuoted'] == 0) || ($Trady['DateRemind'] != 0) ||
                (($Trady['DateQuoted'] + Feature('TradeLastWeek',14)*86400) > time())) continue 2;
            break;

          case 'UnQuote' :
            if (($Trady['DateRemind'] == 0) || (($Trady['DateRemind'] + Feature('TradeUnQuote',14)*86400) > time())) continue 2;
            break;

          case 'Cancel' :
            if (!$Mode) $ac .= " Booking";
            break;
            
          case 'Pay More':
            $TotPowerCost = PowerCost($Trady);
            $TableCost = TableCost($Trady);
            if (($Trady['Fee']??0) < 0) $TotPowerCost = $TableCost = 0;
            $totchg = (($Trady['Fee'] ?? 0) +  ($Trady['ExtraPowerCost']??0) + $TotPowerCost + $TableCost);
// var_dump($totchg,$Trady);
            if ($Trady['TotalPaid'] >= $totchg ) continue 2;
//var_dump("here");
            break;
            
          default:
        }
//        var_dump($Mode, $ButTraderTips[$ac]);
        if (!$Mode && !empty($ButTraderTips[$ac])) $ButExtra[$ac] = $ButTraderTips[$ac];
        echo "<input type=submit name=ACTION value='$ac' " . ($ButExtra[$ac] ??'') . " $xtra onlick=PreventDouble()>";
      }
    }
    if ($Mode == 0) {
      include_once("InvoiceLib.php");
      $Invs = Get_InvoicesFor($Tid);
      if ($Invs) echo "<input type=submit name=ACTION value='Invoices'>";
    }

    echo "</center>\n";
  } else {
    echo "<Center>";
    echo "<input type=Submit name=ACTION value='Create'>\n";
    if (($Mode < 2) && Feature('TradeBooking')) echo "<input type=Submit name=ACTION value='Create and Submit Application'>";
    echo "</center>\n";
  }
  echo "</form>\n";

  if ($Mode==1 && $Tid>0) {
    $Invs = Get_Invoices(" OurRef='" . Sage_Code($Trad) . "'"," IssueDate DESC ");
    echo "<h2><a href=ListCTrade>List Traders Coming</a> ";
//    var_dump($Invs);
    if ($Invs) echo ", <a href=InvoiceManage?FOR=$Tid>Show All Invoices for " . preg_replace('/\|/','',$Trad['SN']) . "</a>";
    echo "</h2>";
  }
}

function Trade_Date_Cutoff() { // return 0 - normal, 30, full payment (normal duration), >0 = Days left to trade stop (full payment)
  global $YEARDATA;
  $Now = time();
  $PayTerm = Feature('PaymentTerms',30);
  if (!$YEARDATA['TradeMainDate']) return $PayTerm;
  if (!$YEARDATA['TradeLastDate']) return $PayTerm;
  if ($YEARDATA['TradeMainDate'] > $Now) return 0;
  if ($Now >= $YEARDATA['TradeLastDate']) return 2;
  $DaysLeft = intdiv(($YEARDATA['TradeLastDate'] - $Now),24*60*60);
  if ($DaysLeft > $PayTerm) $DaysLeft = $PayTerm;
  if ($DaysLeft < 2) $DaysLeft = 2;
  return $DaysLeft;
}

function Trade_Invoice_Code(&$Trad,&$Trady) {
  global $TradeLocData,$TradeTypeData;
  $InvCode = 0;
  if ($Trady['PitchLoc0']) $InvCode = $TradeLocData[$Trady['PitchLoc0']]['InvoiceCode'];
  if ($InvCode == 0) $InvCode = $TradeTypeData[$Trad['TradeType']]['SalesCode'];
//  echo "<p>Returning Invoice Code $InvCode<p>";
  return $InvCode;
}

function Trade_Deposit_Invoice(&$Trad,&$Trady,$Full='Full',$extra='',$Paid=0) {
  global $PLANYEAR;
  if (! Feature("AutoInvoices",1)) return 0;

  $Dep = ($Trady['Fee']>0?T_Deposit($Trad):0);
  $InvPay = Feature('TradeInvoicePay');
  if (!$InvPay ) {
    $PaidSoFar = (isset($Trady['TotalPaid']) ? $Trady['TotalPaid'] : 0);
    if ($PaidSoFar) {
      $Dep -= $PaidSoFar;
      if ($Dep < 0) $Dep = 0;
    }
  }
  $InvCode = Trade_Invoice_Code($Trad,$Trady);
  $DueDate = Trade_Date_Cutoff();
  if ($DueDate == 0 || $InvPay ) {
//      if (Now < Main invoice date, Due = 30, else invoice full amount (if Now < 30 before cut date, Due = 30, else Due = CutDate - now
    $ipdf = New_Invoice($Trad,
                        ["Deposit for trade stand at the " . substr($PLANYEAR,0,4) . " festival",$Dep*100],
                        'Trade Stand Deposit',
                        $InvCode,1,-1,0,0,$Paid);
  } else {
    $details = [["$Full fees for trade stand at the " . substr($PLANYEAR,0,4) . " festival",$Trady['Fee']*100]];
    $Pwr = PowerCost($Trady) +  $Trady['ExtraPowerCost'];
    if ($Trady['Fee']<0) $Pwr = 0;

    if ($Pwr) $details[]= ['Plus Power',$Pwr*100];
    $TableCost = TableCost($Trady);
    if ($TableCost) $details[]= ['Plus Tables',$TableCost*100];
    if ($extra) $details[]=$extra; // Probably wrong
    $ipdf = New_Invoice($Trad,
                        $details,
                        'Trade Stand Full Charge',
                        $InvCode, 1, $DueDate,0,0,$Paid);
  }
  return $ipdf;
}

// Highly recursive set of actions - some trigger others amt = paid amount (0 = all)
function Trade_Action($Action,&$Trad,&$Trady,$Mode=0,$Hist='',$data='', $invid=0) {
  global $Trade_State,$TradeTypeData,$USER,$TradeLocData,$PLANYEAR,$Trade_States;
  include_once("InvoiceLib.php");
  $Tchng = $Ychng = 0;
  $PaidSoFar = (isset($Trady['TotalPaid']) ? $Trady['TotalPaid'] : 0);
  $CurState = $NewState = (isset($Trady['BookingState']) ? $Trady['BookingState'] : 0);
  $xtra = '';
  $InvPay = Feature("TradeInvoicePay"); // Switch Invoice or Just Paycodes

//echo "<p>DOING Trade_ACtion $Action<p>";
//var_dump($Trady);

  $SaveAction = $Action;
  switch ($Action) {
  case 'Create' :
    break;

  case 'Create and Submit Application':
  case 'Submit' :
    if (isset($Trady['Fee']) && $Trady['Fee']) {
      Trade_Action('Accept',$Trad,$Trady,$Mode,"$Hist $Action");
      return;
    } else {
      if ($CurState >= $Trade_State['Submitted']) {
        echo "<h3>This has already been Submitted</h3>";
        return;
      }

      echo "This takes a few seconds, please be patient.<p>";
      $NewState = $Trade_State['Submitted'];
      Submit_Application($Trad,$Trady,$Mode);
    }
    break;

  case 'Accept' :
    if ($CurState == $Trade_State['Change Aware']) {
      Trade_Action('DateHappy',$Trad,$Trady,"$Hist $Action");
      return;
    }

//    var_dump($CurState);
    if ($CurState >= $Trade_State['Accepted'] && $CurState < $Trade_State['Wait List']) {
      echo "<h3>This has already been accepted</h3>";
      return;
    }
    $Fee = $Trady['Fee'];
    $Dep = ($Fee>0?T_Deposit($Trad):0);
    $NewState = $Trade_State['Accepted'];
    if ($Dep <= $PaidSoFar) {
      Trade_Action('Dep Paid',$Trad,$Trady,$Mode,"$Hist $Action");
      Send_Trader_Email($Trad,$Trady,'Trade_AcceptNoDeposit');
      return;
    }

    $ProformaName = (($TradeTypeData[$Trad['TradeType']]['ArtisanMsgs'] && $TradeLocData[$Trady['PitchLoc0']]['ArtisanMsgs']) ? "Trade_Artisan_Accept" : "Trade_Accepted");
    $Pwr = PowerCost($Trady) +  $Trady['ExtraPowerCost'];
    if ($Trady['Fee']<0) $Pwr = 0;

    if ($InvPay) { // Only for when Mandy was being awkward
      $DueDate = Trade_Date_Cutoff();
      if ($DueDate) { // Single Pay Request
        $Code = Pay_Rec_Gen("PAY",( $Fee + $Pwr)*100,1,$Trad['Tid'],preg_replace('/\|/','',$Trad['SN']),'Trade Stand Full Payment',$DueDate);
        Send_Trader_Email($Trad,$Trady,$ProformaName . "_FullInvoice");
      } else { // Deposit
        $Code = Pay_Rec_Gen("DEP",$Dep*100,1,$Trad['Tid'],preg_replace('/\|/','',$Trad['SN']),'Trade Stand Deposit',Feature('PaymentTerms',30));
        Send_Trader_Email($Trad,$Trady,$ProformaName . "_DepositPayment");
      }
    } else {
      $ipdf = Trade_Deposit_Invoice($Trad,$Trady);

      if ($ipdf) {
        $DueDate = Trade_Date_Cutoff();
        Send_Trader_Email($Trad,$Trady,$ProformaName . ($DueDate?"_FullInvoice":"_Invoice"),$ipdf);
      } else {
        Send_Trader_Email($Trad,$Trady,$ProformaName);
        Send_Trade_Finance_Email($Trad,$Trady,'Trade_RequestDeposit');
      }
    }
    if ($Trady['DateChange']) $Trady['DateChange'] = 3;
    $xtra = "Fee: $Fee " . " Size:" . $Trady['PitchSize0'];
    break;

  case 'Send Bal': // Send requests for final payments - PAYCODES
  case 'Invoice': // Invoices
    if (!Feature('TradeInvoicePay')) {
      if ($CurState >= $Trade_State['Balance Requested']) {
        echo "<h3>This has already been Invoiced</h3>";
        return;
      }

      $Pwr = PowerCost($Trady) + $Trady['ExtraPowerCost'];
      if ($Trady['Fee']<0) $Pwr = 0;

      if ($CurState == $Trade_State['Fully Paid']) break; // should not be here...
      $Fee = $Trady['Fee'];
      if (($Fee + $Pwr) <= $PaidSoFar) { // Fully paid on depoist invoice - needs final invoice
        $NewState = $Trade_State['Fully Paid']; // Should not be here...
        break;
      }

      if (Feature("AutoInvoices",1)) {
         $ProformaName = (($TradeTypeData[$Trad['TradeType']]['ArtisanMsgs'] && $TradeLocData[$Trady['PitchLoc0']]['ArtisanMsgs']) ?
            "Trade_Artisan_Final_Invoice" : "Trade_Final_Invoice");
        $InvCode = Trade_Invoice_Code($Trad,$Trady);
        $DueDate = Trade_Date_Cutoff();
        $details = [["Balance payment to secure trade stand at the " . substr($PLANYEAR,0,4) . " festival",$Fee*100]];
        if ($details && $Pwr) $details[]= ['Plus Power',$Pwr*100];
        $TableCost = TableCost($Trady);
        if ($details && $TableCost) $details[]= ['Plus Tables',$TableCost*100];
        
        $details[]= ["Less your deposit payment",-$PaidSoFar*100];
        $ipdf = New_Invoice($Trad, $details, 'Trade Stand Balance Charge',
                             $InvCode, 1, ($DueDate?$DueDate:30) );
        Send_Trader_Email($Trad,$Trady,$ProformaName,$ipdf);
        $NewState = $Trade_State['Balance Requested'];
      }
      break;
    } else { // Paycodes
      if ($CurState == $Trade_State['Fully Paid']) break; // should not be here...
      $Fee = $Trady['Fee'];
      $Pwr = PowerCost($Trady) + $Trady['ExtraPowerCost'];
      if ($Trady['Fee']<0) $Pwr = 0;

      if ($Fee <= $PaidSoFar) { // Fully paid on depoist invoice - needs final invoice
        $NewState = $Trade_State['Fully Paid']; // Should not be here...
        break;
      }
      if ($CurState == $Trade_State['Deposit Paid']) {
        $DueDate = Trade_Date_Cutoff();
        $ProformaName = (($TradeTypeData[$Trad['TradeType']]['ArtisanMsgs'] && $Trady['PitchLoc0'] && $TradeLocData[$Trady['PitchLoc0']]['ArtisanMsgs']) ?
                        "Trade_Artisan_FinalPayment" : "Trade_FinalPayment");
        $Code = Pay_Rec_Gen("BAL",($Trady['Fee'] + $Pwr - $PaidSoFar)*100,1,$Trad['Tid'],preg_replace('/\|/','',$Trad['SN']),'Trade Stand Balance Payment',$DueDate);

        Send_Trader_Email($Trad,$Trady,$ProformaName);
      }
      $NewState = $Trade_State['Balance Requested'];
      break;
    }

  case 'LastWeek' : // Send Last week message
    Send_Trader_Email($Trad,$Trady,'Trade_Quote_WeekLeft');
    $Trady['DateRemind'] = time();
    $Ychng = 1;
    break;

  case 'Resend Finance':
    Send_Trade_Finance_Email($Trad,$Trady,'Trade_RequestDeposit');  // Only used when no auto invoices
    break;

  case 'Decline' :
    if ($CurState == $Trade_State['Change Aware']) {
      Trade_Action('DateUnHappy',$Trad,$Trady,"$Hist $Action");
      return;
    }

    if ($CurState == $Trade_State['Declined']) {
      echo "<h3>This has already been Declined</h3>";
      return;
    }

    Pay_Code_Remove(1,$Trad['Tid']);

    $NewState = $Trade_State['Declined'];
    $att = 0;
    if ($InvPay) {
      Invoice_RemoveCode(PayCodeGen("DEP",$Trady['TYid']));
      Invoice_RemoveCode(PayCodeGen("BAL",$Trady['TYid']));
      Invoice_RemoveCode(PayCodeGen("PAY",$Trady['TYid']));
    } else {
      if ($CurState == $Trade_State['Accepted']) { // Should not be here ...
        // Is there an invoice ? If so credit it and attach credit note
        $Invs = Get_Invoices(" PayDate=0 AND OurRef='" . Sage_Code($Trad) . "'"," IssueDate DESC ");
        foreach ($Invs as $Inv) $att = Invoice_Credit_Note($Inv);
      }
    }
    Send_Trader_Email($Trad,$Trady,'Trade_Decline',$att);
    break;

  case 'Hold' :
    if ($CurState == $Trade_State['Wait List']) {
      echo "<h3>This has already been Wait Listed</h3>";
      return;
    }

    $NewState = $Trade_State['Wait List'];
    Send_Trader_Email($Trad,$Trady,'Trade_Hold');
    break;

  case 'Dep Paid' :
    $Pwr = PowerCost($Trady) + $Trady['ExtraPowerCost'];
    if ($Trady['Fee']<0) $Pwr = 0;

    if ($Trady['Fee'] < 0 || ($Trady['Fee'] + $Pwr) <= $PaidSoFar) {
      Trade_Action('Paid',$Trad,$Trady,$Mode,"$Hist $Action");
      return;
    } else  { // Should not need anything
      $Dep = T_Deposit($Trad);
      if (!$data) $data = $Dep;
      $Trady['TotalPaid'] += $data;
      $Ychng = 1;

      $xtra = " of $Dep ";
      if ($Trady['TotalPaid'] >= $Dep) {
        $NewState = $Trade_State['Deposit Paid'];
        $DueDate = Trade_Date_Cutoff();
        if ($DueDate) Trade_Action('Send Bal',$Trad,$Trady,$Mode,"$Hist $Action");
      }
    }
    break;

  case 'PPaid': // Paid Clicked from Payment page
    $Trady['TotalPaid'] += $data;
    $Fee = $Trady['Fee'];
    $Pwr = PowerCost($Trady) + $Trady['ExtraPowerCost'];
    if ($Trady['Fee']<0) $Pwr = 0;

    $InvCode = Trade_Invoice_Code($Trad,$Trady);
    $Ychng = 1;
    $xtra = $data;
    if ($CurState == $Trade_State['Accepted']) {
      $NewState = $Trade_State['Deposit Paid'];
      $ipdf = Trade_Deposit_Invoice($Trad,$Trady,'Deposit','',1);
      Send_Trader_Email($Trad,$Trady,'Trade_Deposit_Paid_Invoice',$ipdf);
      // mark paid, get invoice email invoice
    } else if ($CurState == $Trade_State['Balance Requested']) {
      $NewState = $Trade_State['Fully Paid'];
      $DueDate = Trade_Date_Cutoff();
      if ($PaidSoFar) {
        $details = [["Balance payment to secure trade stand at the " . substr($PLANYEAR,0,4) . " festival",$Fee*100]];
        if ($Pwr) $details[]= ['Plus power',$Pwr*100];
        $TableCost = TableCost($Trady);
        if ($TableCost) $details[]= ['Plus Tables',$TableCost*100];
        
        $details[]= ["Less your deposit payment",-$PaidSoFar*100];
        $ipdf = New_Invoice($Trad,
                           $details,
                           'Trade Stand Balance Charge',
                           $InvCode, 1, ($DueDate?$DueDate:-1),0,0,1 );
        Send_Trader_Email($Trad,$Trady,'Trade_Fully_Paid_Invoice',$ipdf);
      } else {
        $details = [["Full payment to secure trade stand at the " . substr($PLANYEAR,0,4) . " festival",$Fee*100]];
        if ($Pwr) $details[]= ['Plus power',$Pwr*100];
        $TableCost = TableCost($Trady);
        if ($TableCost) $details[]= ['Plus Tables',$TableCost*100];
        
        $ipdf = New_Invoice($Trad,
                           $details,
                           'Trade Stand Full Charge',
                           $InvCode, 1, ($DueDate?$DueDate:-1),0,0,1 );
        Send_Trader_Email($Trad,$Trady,'Trade_Fully_Paid_Invoice',$ipdf);
      }
      // Mark Fully Paid, get invoice, email invoice

    } else { // error report
      Send_SysAdmin_Email('Payment Paid in wrong state',$Trady);
    }
    break;

  case 'PDiff' :
    $PaidBefore = $Trady['TotalPaid'];
    $Trady['TotalPaid'] += $data;
    $PaidSoFar = $Trady['TotalPaid'];
    $Ychng = 1;
    $Dep = T_Deposit($Trad);
    $Fee = $Trady['Fee'];
    if ($Fee<0) $Dep = 0;
    $Pwr = ($Fee<0 ? PowerCost($Trady) + $Trady['ExtraPowerCost'] : 0);
    $InvCode = Trade_Invoice_Code($Trad,$Trady);
    $xtra = $data;
/*
  if Paid < Deposit, nothing
  Paid < Full, Invoice Depost (Plus what was paid) leave as dep paid
  if Paid == Full, Invoice Full, State -> Fully Paid
  if Paid > Full, Invoice Full, State -> Fully Paid, sys message

*/
    if ($PaidSoFar < $Dep) {
      Send_Trader_Email($Trad,$Trady,"Trade_Partial_Payment");
    } elseif ($PaidSoFar == $Dep) {
      if ($CurState == $Trade_State['Accepted']) {
        $NewState = $Trade_State['Deposit Paid'];
        $ipdf = Trade_Deposit_Invoice($Trad,$Trady,'Deposit','',1);
        Send_Trader_Email($Trad,$Trady,'Trade_Deposit_Paid_Invoice',$ipdf);
      } else {
        Send_SysAdmin_Email('Trader Paid when not expected',$Trady);
      }
    } elseif ($PaidSoFar < ($Fee + $Pwr)) {
      if ($CurState == $Trade_State['Accepted']) {
        $NewState = $Trade_State['Deposit Paid'];
        $ipdf = Trade_Deposit_Invoice($Trad,$Trady,'Deposit','',1);
        Send_Trader_Email($Trad,$Trady,'Trade_Deposit_Paid_Invoice',$ipdf);
      } elseif ($CurState != $Trade_State['Balance Requested']) {
        Send_SysAdmin_Email('Trader Paid when not expected',$Trady);
      }
    }  else {
      if ($PaidSoFar > ($Fee + $Pwr)) {
        Send_SysAdmin_Email('Trader Paid more than fee!',$Trady);
      }
      if ($CurState == $Trade_State['Accepted']) {
        $NewState = $Trade_State['Fully Paid'];
        $details = [["Full payment to secure trade stand at the " . substr($PLANYEAR,0,4) . " festival",$Fee*100]];
        if ($Pwr) $details[]= ['Plus power',$Pwr*100];
        $TableCost = TableCost($Trady);
        if ($TableCost) $details[]= ['Plus Tables',$TableCost*100];
        
        $ipdf = New_Invoice($Trad,$details,
                           $InvCode, 1,-1,0,0,1 );
        Send_Trader_Email($Trad,$Trady,'Trade_Fully_Paid_Invoice',$ipdf);
      } elseif ($CurState == $Trade_State['Balance Requested']) {
        $NewState = $Trade_State['Fully Paid'];
        $details = [["Balance payment to secure trade stand at the " . substr($PLANYEAR,0,4) . " festival",$Fee*100]];
        if ($Pwr) $details[]= ['Plus power',$Pwr*100];
        $TableCost = TableCost($Trady);
        if ($TableCost) $details[]= ['Plus Tables',$TableCost*100];
        $details[]= ["Less your deposit payment",-$PaidBefore*100];

        $ipdf = New_Invoice($Trad,$details,
                           'Trade Stand Balance Charge',
                           $InvCode, 1,-1,0,0,1 );
        Send_Trader_Email($Trad,$Trady,'Trade_Balance_Paid_Invoice',$ipdf);
      } else {
        Send_SysAdmin_Email('Trader Paid when not expected',$Trady);
      }
    }
    break;


  case 'Paid' :
    $fee = $Trady['Fee'];
    $Dep = T_Deposit($Trad);
    $Pwr = PowerCost($Trady) + $Trady['ExtraPowerCost'];
    if ($fee<0) $Dep = $Pwr = 0;
    if (($fee > 0) && (($fee + $Pwr) > $PaidSoFar)) {
      if (!$data) $data = $fee + $Pwr -$Dep;
//var_dump($data);
      $Trady['TotalPaid'] += $data;
      $Ychng = 1;
//var_dump($Trady);
    }
    $xtra = $data;
    if ($Trady['TotalPaid'] >= ($fee + $Pwr) ) {
      $NewState = $Trade_State['Fully Paid'];  // if paid > invoiced amend invoice to full
      if ($invid) {
        Update_Invoice($invid,["Balance of Fees for trade stand at the " . substr($PLANYEAR,0,4) . " festival",($fee+$Pwr-$Dep)*100],0);
        $inv = Get_Invoice($invid);
        $att = Get_Invoice_Pdf($invid,'',$inv['Revision']);
        Send_Trader_Email($Trad,$Trady,'Trade_Statement',$att);
      }
    } else if ($Trady['TotalPaid'] >= $Dep && $CurState == $Trade_State['Accepted']) {
      $NewState = $Trade_State['Deposit Paid'];
      $Action = "Deposit Paid";
    }
    break;

  case 'Local Auth Checked' :
    $Trady['HealthChecked'] = 1;
    $Ychng = 1;
    break;

  case 'Ins Checked' :
    $Trady['Insurance'] = 2;
    $Ychng = 1;
    break;

  case 'RA Checked' :
    $Trady['RiskAssessment'] = 2;
    $Ychng = 1;
    break;

  case 'Cancel' : // If invoiced - credit note, free up fee and locations if set email moe need a reason field
  case 'Cancel Booking' :
    if ($CurState == ($Trade_States['Change_Aware']??0)) {
      Trade_Action('DateUnHappy',$Trad,$Trady,"$Hist $Action");
      return;
    }

    if ($CurState == $Trade_State['Cancelled']) {
      echo "<h3>This has already been Cancelled</h3>";
      return;
    }

    $att = 0;
    $Tid = $Trad['Tid'];

    Pay_Code_Remove(1,$Tid);

    // Is there an invoice ? If so credit it and attach credit note
    $Invs = Get_Invoices(" PayDate=0 AND OurRef='" . Sage_Code($Trad) . "'"," IssueDate DESC ");
    if ($Invs) $att = Invoice_Credit_Note($Invs[0],$data);  // TODO BUG
    $NewState = $Trade_State['Cancelled'];
    Send_Trader_Email($Trad,$Trady,'Trade_Cancel',$att);
    Send_Trade_Admin_Email($Trad,$Trady,'Trade_Cancel_Admin');

    $xtra .= "Fee was " . $Trady['Fee'] . ", Pitch was " . $Trady['PitchLoc0'] . ", Number was " . $Trady['PitchNum0'] . "\n";
    $Trady['Fee'] = 0;
    $Trady['PitchLoc0'] = $Trady['PitchLoc1'] = $Trady['PitchLoc2'] = '';
    $Trady['PitchNum0'] = $Trady['PitchNum1'] = $Trady['PitchNum2'] = '';
    $Ychng = 1;
    break;

  case 'Change' :
    $NewState = $Trade_State['Requote'];
    Send_Trader_Email($Trad,$Trady,'Trade_Changes');
    Send_Trade_Admin_Email($Trad,$Trady,'Trade_Changes');
    break;

  case 'Invite' :
    $Fee = $Trady['Fee'];
    if ($Fee) {
      for($i=0;$i<3;$i++) $Trady["QuoteSize$i"] = $Trady["PitchSize$i"];
      $Ychng = 1;
      $NewState = $Trade_State['Quoted'];
      Send_Trader_Email($Trad,$Trady,'Trade_Invitation');
      $xtra = "Fee: $Fee " . " Size:" . $Trady['PitchSize0'];
    }
    $Trady['DateQuoted'] = time();
    break;

  case 'Artisan Invite' :
    $Fee = $Trady['Fee'];
    if ($Fee) {
      for($i=0;$i<3;$i++) $Trady["QuoteSize$i"] = $Trady["PitchSize$i"];
      $Ychng = 1;
      $NewState = $Trade_State['Quoted'];
      Send_Trader_Email($Trad,$Trady,'Trade_Artisan_Invite');
      $xtra = "Fee: $Fee " . " Size:" . $Trady['PitchSize0'];
    }
    $Trady['DateQuoted'] = time();
    break;

  case 'Invite Better' :
    $Fee = $Trady['Fee'];
    if ($Fee) {
      for($i=0;$i<3;$i++) $Trady["QuoteSize$i"] = $Trady["PitchSize$i"];
      $Ychng = 1;
      $NewState = $Trade_State['Quoted'];
      Send_Trader_Email($Trad,$Trady,'Trade_InvitationBetter');
      $xtra = "Fee: $Fee " . " Size:" . $Trady['PitchSize0'];
    }
    $Trady['DateQuoted'] = time();
    break;

  case 'Quote' :
  /*
    if (!requote) just quote (Quoted)
    else if free then fully paid and message (fully Paid)
    else if dep not paid and not due and dep not changed Statement (Accepted)
    else if dep not paid and due and not invoiced Issue full invoice (Invoiced)
    else if dep not paid and due and invoice credit and new invoice (Invoiced)
    else if dep paid and not due - statement (Dep Paid)
    else if no invoice issue balance invoice (Invoiced)
    else if not yet paid - additional invoice (Invoiced)
    if paid new invoice for extra (Invoiced)

    if dep not paid and dep not changed { if due

  */


    for($i=0;$i<3;$i++) $Trady["QuoteSize$i"] = $Trady["PitchSize$i"];
//var_dump($Trady);
    $Ychng = 1;
    $Fee = $Trady['Fee'];
    $Dep = ($Fee>0 ?T_Deposit($Trad):0);
  if ($CurState != $Trade_State['Requote'] || (($Trady['Fee'] > 0) && ($Trady['TotalPaid'] < $Trady['Fee']))){
      $NewState = $Trady['BookingState'] = $Trade_State['Quoted'];
      Send_Trader_Email($Trad,$Trady,'Trade_Quote');
    } elseif ($Trady['Fee'] <0) {
      $NewState = $Trady['BookingState'] = $Trade_State['Fully Paid'];
      Send_Trader_Email($Trad,$Trady,'Trade_AcceptNoDeposit');
    } else {
      $Invs = Get_Invoices(" OurRef='" . Sage_Code($Trad) . "'"," IssueDate DESC ");
      $InvoicedTotal = 0;
      foreach ($Invs as $inv) $InvoicedTotal += $inv['Total'];

      $DueDate = Trade_Date_Cutoff();
      if ($Invs) $invoice = Get_Invoice_Pdf($Invs[0]['id']);
      if ($PaidSoFar < $Dep && $DueDate==0) {  // Need a deposit
        if ($Invs && $PaidSoFar==0 && $InvoicedTotal>=$Dep) { // For info no action required, existing deposit fine, repeat it
          $NewState = $Trady['BookingState'] = $Trade_State['Quoted'];
          Send_Trader_Email($Trad,$Trady,'Trade_Quote',$invoice);
        } elseif (!$Invs) {
          $ProformaName = (($TradeTypeData[$Trad['TradeType']]['ArtisanMsgs'] && $TradeLocData[$Trady['PitchLoc0']]['ArtisanMsgs']) ?
             "Trade_Artisan_Accept" : "Trade_Accepted");
          $ipdf = Trade_Deposit_Invoice($Trad,$Trady);
          if ($ipdf) Send_Trader_Email($Trad,$Trady,$ProformaName . ($DueDate?"_FullInvoice":"_Invoice"),$ipdf);
        } else {
          $NewState = $Trady['BookingState'] = $Trade_State['Deposit Paid'];
          Send_Trader_Email($Trad,$Trady,'Trade_Statement');  // For info no action required
        }
      } elseif ($DueDate) { // Issue /update final invoice
        $Fee = $Trady['Fee'];
        $Pwr = PowerCost($Trady) + $Trady['ExtraPowerCost'];
        if ($Trady['Fee']<0) $Pwr = 0;

        if (Feature("AutoInvoices",1)) {
          $ProformaName = (($TradeTypeData[$Trad['TradeType']]['ArtisanMsgs'] && $TradeLocData[$Trady['PitchLoc0']]['ArtisanMsgs']) ?
                            "Trade_Artisan_Final_Invoice" : "Trade_Final_Invoice");
          $InvCode = Trade_Invoice_Code($Trad,$Trady);
          $details = [["Full payment to secure your trade stand at the " . substr($PLANYEAR,0,4) . " festival",$Fee*100]];
          if ($Pwr) $details[]= ['Plus Power',$Pwr*100];
          $TableCost = TableCost($Trady);
          if ($TableCost) $details[]= ['Plus Tables',$TableCost*100];
          if ($InvoicedTotal) $details[] = ["Less previous invoice(s)",$InvoicedTotal];
          $type = "Full";
          if ($InvoicedTotal > $Dep) $type = "Change ";

          $ipdf = New_Invoice($Trad,$details, "Trade Stand $type", $InvCode, 1, ($DueDate?$DueDate:30));
          $NewState = $Trady['BookingState'] = $Trade_State['Balance Requested'];
          Send_Trader_Email($Trad,$Trady,$ProformaName,$ipdf);
        } else { // Old case - not right
          $NewState = $Trady['BookingState'] = $Trade_State['Quoted'];
          Send_Trader_Email($Trad,$Trady,'Trade_Quote');
        }
      } else { // No need for a deposit - send update to trader
        $NewState = $Trady['BookingState'] = $Trade_State['Deposit Paid'];
        Send_Trader_Email($Trad,$Trady,'Trade_Statement');  // For info no action required
      }
    }
    $xtra = "Fee: $Fee " . " Size:" . $Trady['PitchSize0'];
    $Trady['DateQuoted'] = time();
    break;

  case 'Resend' :
    $att = 0;
    $Invs = Get_Invoices(" OurRef='" . Sage_Code($Trad) . "'"," IssueDate DESC ");
    if ($Invs) $att = Get_Invoice_Pdf($Invs[0]['id']);

    Send_Trader_Email($Trad,$Trady,'Trade_Statement',$att);
    echo "<h3>An Email has been sent " . (Access('Staff')?'':'to you') . " with a statement of where your booking is</h3>";
    break;

  case 'UnQuote' :
    Pay_Code_Remove(1,$Trad['Tid']);
    $NewState = $Trade_State['Declined'];
    Send_Trader_Email($Trad,$Trady,'Trade_UnQuote');
    break;

  case 'Invoices' :
    $Tid = $Trad['Tid'];
    $Invs = Get_InvoicesFor($Tid);

    if ($Invs) {
      $Now = time();
      $coln = 0;
      echo "<div class=Scrolltable><table id=indextable border>\n";
      echo "<thead><tr>";
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Our Ref</a>\n";
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'D')>Date Raised</a>\n";
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'D')>Date Due</a>\n";
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'D')>Date Paid</a>\n";
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Amount (left)</a>\n";
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>View</a>\n";
      echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Download</a>\n";
      echo "</thead><tbody>";
      foreach($Invs as $i=>$inv) {
        $id = $inv['id'];
        echo "<tr><td>" . $inv['OurRef'] . '/' . $inv['id'];
        echo "<td>" . date('j/n/Y',$inv['IssueDate']);
        echo "<td>";
        if ($inv['Total'] > 0) {
          if  ($inv['DueDate'] < $Now && $inv['PaidTotal']<$inv['Total']) {
            echo "<span class=red>" . date('j/n/Y',$inv['DueDate']) . "</span>";
          } else {
            echo date('j/n/Y',$inv['DueDate'] );
          }
        }
        echo "<td>" . ($inv['PayDate']>0? date('j/n/Y',abs($inv['PayDate'])) : ($inv['PayDate']<0? "NA": ""));
        echo "<td>" . Print_Pence($inv['Total']);
        if ($inv['PaidTotal'] > 0 && $inv['PaidTotal'] != $inv['Total']) echo " (" . Print_Pence($inv['Total'] - $inv['PaidTotal']) . ")";
        $Rev = ($inv['Revision']?"R" .$inv['Revision']:"");
        echo "<td><a href=ShowFile?l=" . Get_Invoice_Pdf($id,'',$Rev) . ">View</a>";
        echo "<td><a href=ShowFile?D=" . Get_Invoice_Pdf($id,'',$Rev) . ">Download</a>";
        echo "\n";
      }
      echo "</table></div><p>";
      echo "<h2><a href=TraderPage?id=$Tid>Back to Trade Details</a></h2>";
      dotail();
    } else {
      echo "<h3>No Invoices Found</h3>";
    }
    break;
  case 'UnPaid':
    $PaidSoFar -= $data;
    $Trady['TotalPaid'] -= $data;
    $Ychng = 1;
    $Dep = T_Deposit($Trad);
    $fee = $Trady['Fee'];
    $Pwr = PowerCost($Trady) + $Trady['ExtraPowerCost'];
    if ($Trady['Fee']<0) $Pwr = 0;

    $xtra = $data;
    if ($Trady['TotalPaid'] >= ($fee+$Pwr)) { // No change?
    } else if ($Trady['TotalPaid'] >= $Dep) {
      $NewState = $Trade_State['Deposit Paid'];
    } else {
      $NewState = $Trade_State['Accepted'];
    }
    break;

  case 'Chase':
    $att = 0;
    $Invs = Get_Invoices(" OurRef='" . Sage_Code($Trad) . "'"," IssueDate DESC ");
    if ($Invs) $att = Get_Invoice_Pdf($Invs[0]['id']);

    Send_Trader_Email($Trad,$Trady,'Trade_Chase1',$att);
    break;

  case 'Pitch Assign':
    Send_Trader_Email($Trad,$Trady,'Trade_PitchAssign');
    break;

  case 'Pitch Change':
    Send_Trader_Email($Trad,$Trady,'Trade_PitchChange');
    break;

  case 'Moved':
    Send_Trader_Email($Trad,$Trady,'Trade_PitchMoved');
    break;

  case 'Dates':
    Send_Trader_Email($Trad,$Trady,'Trade_Change_Dates');
    $NewState = $Trade_State['Change Aware'];
    $Trady['DateChange'] = 1;
    break;

  case 'FestC':
    Send_Trader_Email($Trad,$Trady,($PaidSoFar?'Trade_Cancel_Fest_Paid':'Trade_Cancel_Fest_NotPaid'));
    $NewState = $Trade_State['Change Aware'];
    $Trady['DateChange'] = 11;
    break;

  case 'DateHappy' :
    $Trady['DateChange'] = 3;
    $Dep = T_Deposit($Trad);
    $Pwr = PowerCost($Trady) + $Trady['ExtraPowerCost'];
    if ($Trady['Fee']<0) $Pwr = 0;

    $Ychng = 1;
    if ($Dep <= $PaidSoFar) {
      if ($PaidSoFar >= ($Trady['Fee']+$Pwr)) {
        $NewState = $Trade_State['Fully Paid'];
      } else {
        $NewState = $Trade_State['Deposit Paid'];
      }
    } else {
      $NewState = $Trade_State['Accepted'];
      // TODO Resend Deposit Message
    }
    break;

  case 'DateUnHappy' : // MANDY
    $Trady['DateChange'] = 4;
    $Ychng = 1;
    if ($PaidSoFar) {
      $NewState = $Trade_State['Refund Needed'];
      $att = 0;
      $Invs = Get_Invoices(" OurRef='" . Sage_Code($Trad) . "'"," IssueDate DESC ");
      if ($Invs) $att = Get_Invoice_Pdf($Invs[0]['id']);
      Send_Trade_Finance_Email($Trad,$Trady,'Trade_DC_Refund',$att);
      Send_Trader_Email($Trad,$Trady,'Trade_DC_Refund_Ack');
    }
    break;

  case 'DateAck' :
    $Trady['DateChange'] = 2;
    $Ychng = 1;
    Send_Trader_Email($Trad,$Trady,'Trade_DC_Ack');
    break;

  case 'CancelHappy1' : // Actions on old year
    $Trady['DateChange'] = 13;
    $Ychng = 1;
    break;

  case 'CancelHappy2' : // Actions on new year
    $Dep = T_Deposit($Trad);
    $Pwr = PowerCost($Trady) + $Trady['ExtraPowerCost'];
    if ($Trady['Fee']<0) $Dep = $Pwr = 0;
    if ($Dep <= $PaidSoFar) {
      if ($PaidSoFar >= ($Trady['Fee']+$Pwr)) {
        $NewState = $Trade_State['Fully Paid'];
      } else {
        $NewState = $Trade_State['Deposit Paid'];
      }
    } else {
      $NewState = $Trade_State['Accepted'];
    }

    $Ychng = 1;
    Send_Trader_Email($Trad,$Trady,'Trade_Cancel_Happy');
    break;

  case 'CancelUnHappy' :
    $Trady['DateChange'] = 14;
    $Ychng = 1;
    if ($PaidSoFar) {
      $NewState = $Trade_State['Refund Needed'];
      $att = 0;
      $Invs = Get_Invoices(" OurRef='" . Sage_Code($Trad) . "'"," IssueDate DESC ");
      if ($Invs) $att = Get_Invoice_Pdf($Invs[0]['id']);
      Send_Trade_Finance_Email($Trad,$Trady,'Trade_DC_Refund',$att); // These work for both move and cancel
      Send_Trader_Email($Trad,$Trady,'Trade_DC_Refund_Ack');
    }

  case 'CancelAck' :
    $Trady['DateChange'] = 12;
    $Ychng = 1;
    Send_Trader_Email($Trad,$Trady,'Trade_Cancel_Ack');
    break;

  case 'Pay More' : // Extra charges after fully paid
    if (!Feature('TradeInvoicePay')) {
      $Pwr = PowerCost($Trady) + $Trady['ExtraPowerCost'];
      $TableCost = TableCost($Trady);
      $Fee = $Trady['Fee'];
      if ($Fee <0) $TableCost = $Pwr = 0;
      if (($Fee + $Pwr + $TableCost) <= $Trady['TotalPaid']) { // Fully paid already
        $NewState = $Trade_State['Fully Paid']; // Should not be here...
        break;
      }
      
      if (Feature("AutoInvoices",1)) {
        $ProformaName = (($TradeTypeData[$Trad['TradeType']]['ArtisanMsgs'] && $TradeLocData[$Trady['PitchLoc0']]['ArtisanMsgs']) ?
          "Trade_Artisan_Extra_Invoice" : "Trade_Extra_Invoice");
        $InvCode = Trade_Invoice_Code($Trad,$Trady);
        $DueDate = Trade_Date_Cutoff();
        $details = [["Extra payment to secure trade stand at the " . substr($PLANYEAR,0,4) . " festival",$Fee*100]];
        if ($details && $Pwr) $details[]= ['Plus Power',$Pwr*100];
        if ($details && $TableCost) $details[]= ['Plus Tables',$TableCost*100];
        
        $details[]= ["Less your currant payments",-$Trady['TotalPaid']*100];
        $ipdf = New_Invoice($Trad, $details, 'Trade Stand Extra Charge', $InvCode, 1, ($DueDate?$DueDate:30) );
        Send_Trader_Email($Trad,$Trady,$ProformaName,$ipdf);
        $NewState = $Trade_State['Balance Requested'];
      }
      break;
    } else { // Paycodes    
      // Not Coded
    }
    

  default:
    break;
  }
/* TODO
   Need schedualled events:
     Send final invoices
     Overdue Invoices
   */


// var_dump($Ychng,$CurState,$NewState);

  if ($Tchng && $Action) Put_Trader($Trad);
// var_dump($Action,$Ychng,$CurState, $NewState,$Trady);
  if (($SaveAction || $Action) && ($Ychng || $CurState != $NewState )) {
    $Trady['BookingState'] = $NewState; // Action test is to catch the moe errors
    $By = (isset($USER['Login'])) ? $USER['Login'] : 'Trader';
    $Trady['History'] .= "Action: $Hist $SaveAction $xtra " . $Trade_States[$Trady['BookingState']] . " on " . date('j M Y H:i') . " by $By.<br>";
    Put_Trade_Year($Trady);
  }
}

function Get_Taxis() {
  global $db;
  $cs = array();
  $res = $db->query("SELECT * FROM TaxiCompanies ORDER BY Authority,SN");
  if ($res) while ($c = $res->fetch_assoc()) $cs[] = $c;
  return $cs;
}

function Get_Taxi($id) {
  global $db;
  $res = $db->query("SELECT * FROM TaxiCompanies WHERE id=$id");
  if ($res) while($c = $res->fetch_assoc()) return $c;
}

function Put_Taxi($now) {
  $e=$now['id'];
  $Cur = Get_Taxi($e);
  return Update_db('TaxiCompanies',$Cur,$now);
}

function Get_OtherLinks($xtra='') {
  global $db;
  $cs = array();
  $res = $db->query("SELECT * FROM OtherLinks $xtra");
  if ($res) while($c = $res->fetch_assoc()) $cs[] = $c;
  return $cs;
}

function Get_OtherLink($id) {
  global $db;
  $res = $db->query("SELECT * FROM OtherLinks WHERE id=$id");
  if ($res) while($c = $res->fetch_assoc()) return $c;
}

function Put_OtherLink($now) {
  $e=$now['id'];
  $Cur = Get_OtherLink($e);
  return Update_db('OtherLinks',$Cur,$now);
}

function Trade_F_Action($Uid,$Action,$xtra='',$invid=0) { // Call from Invoicing
  $PCRec = [];
  if (is_numeric($Uid)) {
    $Trad = Get_Trader($Uid);
    $Trady = Get_Trade_Year($Uid);
    Trade_Action($Action,$Trad,$Trady,1,'', $xtra,$invid); // OLD CODE
  } else if (preg_match('/(\D*)(\d*)\D$/',$Uid,$PCRec)) {
    $Tid = $PCRec[1];
    $Trad = Get_Trader($Tid);
    $Trady = Get_Trade_Year($Tid);
    Trade_Action($Action,$Trad,$Trady,1,'', $xtra,$invid);

  } else {
    // Unrecognised Uid
  }
}

function Trade_P_Action($Tid,$action,$xtra='') { // Call From Payment
  $Trad = Get_Trader($Tid);
  $Trady = Get_Trade_Year($Tid);
  Trade_Action($action,$Trad,$Trady,1,'', $xtra);
}

function Get_Traders_For($loc,$All=0 ) {
  global $db, $Trade_State,$YEAR;
  $qry = "SELECT t.*, y.* FROM Trade AS t, TradeYear AS y WHERE " .
        ($All? ("y.BookingState>= " . $Trade_State['Submitted'] ) :
          ( "(y.BookingState=" . $Trade_State['Accepted'] . " OR y.BookingState=" . $Trade_State['Deposit Paid'] .
            " OR y.BookingState=" . $Trade_State['Balance Requested'] . " OR y.BookingState=" . $Trade_State['Fully Paid'] . ")" ) ) .
         " AND t.Tid = y.Tid AND y.Year='$YEAR' AND (y.PitchLoc0=$loc OR y.PitchLoc1=$loc OR y.PitchLoc2=$loc ) ORDER BY SN";

  $res = $db->query($qry);
  $Traders = [];
  if ($res) while ($trad = $res->fetch_assoc()) $Traders[] = $trad;
  return $Traders;
}
