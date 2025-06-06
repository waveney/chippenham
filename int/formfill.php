<?php
  include_once("fest.php");
  $field = $_REQUEST['F'];
  $Value = $_REQUEST['V'];
  $id    = $_REQUEST['I'];
  $type  = $_REQUEST['D'];
  $vfv   = $_REQUEST['vfv']??'';

  global $PLANYEAR,$YEARDATA;
  $match = [];
  $mtch = [];

// var_dump($_REQUEST);
// Special returns @x@ changes id to x, #x# sets feild to x, !x! important error message
  switch ($type) {
  case 'Performer':
    include_once("DanceLib.php");
    if (preg_match('/BandMember(\d*):(\d*)/',$field,$match)) { // Band Members are a special case
      include_once("MusicLib.php");
      if ($match[2]) { // Existing entry
        $CurBand = Get_Band($id);
        $memb = $CurBand[$match[1]];
        if ($Value) {
          $memb['SN'] = $Value;
          Put_BandMember($memb);
        } else {
          db_delete('BandMembers',$memb['BandMemId']);
          echo "@BandMember" . $match[1] . ":0@";
        }
        exit;
      }
      $CurBand = Get_Band($id);
      $memb = Add_BandMember($id,$Value);
      echo "REPLACE_ID_WITH:$id:$memb ";
//      echo "@BandMember" . $match[1] . ":$memb@";
      exit;
    } else if (preg_match('/^Olap.*/',$field)) { // Overlaps are a special case
      $Exist = Get_Overlaps_For($id);
      if (preg_match('/(Olap\D+)(\d+)/',$field,$match)) {
        $OFld = $match[1];
        $ORule = $match[2];
      } elseif (preg_match('/Olap(\d+)(\D*)/',$field,$match)) {
        $OFld = $match[2];
        $ORule = $match[1];
      } else { echo "Undefined Olap format"; exit();
      }

      $O = $StO = (isset($Exist[$ORule]) ? $Exist[$ORule] : ['Sid1'=>$id,'Cat2'=>0]);
      $Other = ($O['Sid1'] == $id)?'Sid2':'Sid1';
      $OtherCat =  ($O['Sid1'] == $id)?'Cat2':'Cat1';
      $O[ ['OlapType' => 'OType',
           'OlapMajor' => 'Major',
           'OlapActive' => 'Active',
           'OlapDays' => 'Days',
           'OlapSide' => $Other,
           'OlapAct' => $Other,
           'OlapOther' => $Other,
           'OlapCat' => $OtherCat,
           'Cat' => $OtherCat][$OFld] ] = $Value;

      if ((isset($O['id'])) && $O['id']) {
        Update_db('Overlaps',$StO,$O);
      } else {
        Insert_db('Overlaps',$O);
      }
      exit;
    } else if (preg_match('/^Perf(\d+)_Side(\d+)/',$field,$match)) { // Overlaps are a special case
      $Exist = Get_Overlaps_For($id);
      $ORule = $match[2];
      $O = $StO = (isset($Exist[$ORule]) ? $Exist[$ORule] : ['Sid1'=>$id,'Cat2'=>0]);
      $Other = ($O['Sid1'] == $id)?'Sid2':'Sid1';
      $O[$Other] = $Value;
      if ((isset($O['id'])) && $O['id']) {
        Update_db('Overlaps',$StO,$O);
      } else {
        Insert_db('Overlaps',$O);
      }
      exit;
    } else if ($field == 'Photo' && (preg_match('/^\s*https?:\/\//i',$Value ))) { // Remote Photos are a special case - look for localisation
      $Perf = Get_Side($id);
      include_once("ImageLib.php");
      preg_match('/\.(jpg|jpeg|gif|png)/i',$Value,$mtch);

      if ($mtch) {
        $sfx = $mtch[1];
        $loc = "/images/Sides/$id.$sfx";
        $res = Localise_Image($Value,$Perf,$loc);
        Put_Side($Perf);
        if ($res) {
          echo "!$res!";
        } else {
          echo "#$loc#PerfThumb#$loc#";
        }
        exit;
      }
      echo "1, Not a recognisable image";
      exit;
    } else if ($field == 'ReleaseDate') {
      include_once("DateTime.php");
      $Value = Date_BestGuess($Value);
    } else if (preg_match('/(Sat|Sun)(Arrive|Depart)/',$field)) {
      include_once("DateTime.php");
      $Value = Time_BestGuess($Value);
    } else if (preg_match('/(\w*):([\d-]*):(\d*):(\w*)/',$field,$mtch )) { //Word + 4 fields
//var_dump($Mtch);
      switch($mtch[1]) {
      case 'CampSite':
        if ($mtch[2]>0) {
          $syid = $mtch[2];
        } else {
          $Perfy = Get_SideYear($id);
          if (empty($Perfy['syId'])) Put_SideYear($Perfy);
//var_dump($Perfy);
          $syid = $Perfy['syId'];
        }
        $ECS = Gen_Get_Cond1('CampUse',"SideYearId=$syid AND CampSite=" . $mtch[3] . " AND CampType=" . $mtch[4]);
        if ($ECS) {
          $ECS['Number'] = $Value;
        } else {
          $ECS = ['SideYearId'=>$syid, 'CampSite'=>$mtch[3], 'Number'=>$Value, 'CampType'=>$mtch[4]];
        }
        echo Gen_Put('CampUse',$ECS);
        return;

      }

    }
//echo "Here";
    // else general cases

    $Perf = Get_Side($id);
    if (isset($Perf[$field])) {
      $Perf[$field] = $Value;
      echo Put_Side($Perf);
      exit;
    }

    $Perfy = Get_SideYear($id);
    $flds = table_fields('SideYear');

    if (!$Perfy) {
      if (isset($flds[$field])) {
        $Perfy = Default_SY($id);
        $Perfy[$field] = $Value;

        echo Put_SideYear($Perfy);
        exit;
      }
    }
    if (isset($Perfy[$field]) || isset($flds[$field])) {
      $Perfy[$field] = $Value;

      if ((1 || !Access('Staff')) && strstr($field,'Performers')) $Perfy['PerfNumChange'] = 1;
//        var_dump($Perfy);
      echo Put_SideYear($Perfy);
      exit;
    }

    $sflds = table_fields('Sides');
    if (isset($sflds[$field])) {
      $Perf[$field] = $Value;
      echo Put_Side($Perf);
      exit;
    }
    // SHOULD never get here... (but it did!)
    trigger_error("Updating a form confused - $field @ $Value @ $id @ $type");
    exit;

  case 'Trader':
    include_once("TradeLib.php");

    $Trad = Get_Trader($id);
    if ($field == 'Photo' && (preg_match('/^\s*https?:\/\//i',$Value ))) { // Remote Photos are a special case - look for localisation
      include_once("ImageLib.php");
      preg_match('/\.(jpg|jpeg|gif|png)/i',$Value,$mtch);
      if ($mtch) {
        $sfx = $mtch[1];
        $loc = "/images/Trade/$id.$sfx";
        $res = Localise_Image($Value,$Trad,$loc);
        Put_Trader($Trad);
        if ($res) {
          echo "!$res!";
        } else {
          echo "#$loc#TradThumb#$loc#";
        }
        exit;
      }
      echo "1, Not a recognisable image";
      exit;
    }


    if (isset($Trad[$field])) {
      $Trad[$field] = $Value;
      echo Put_Trader($Trad);
      exit;
    }
    $Trady = Get_Trade_Year($id);
    if (isset($Trady[$field])) {
      $Trady[$field] = $Value;
      echo Put_Trade_Year($Trady);
      exit;
    }

    $TradFlds = table_fields('Trade');
    if (isset($TradFlds[$field])) {
      $Trad[$field] = $Value;
      echo Put_Trader($Trad);
      exit;
    }

    $TradyFlds = table_fields('TradeYear');
    if ($Trady) {
      if (isset($TradyFlds[$field])) {
        $Trady[$field] = $Value;
        echo Put_Trade_Year($Trady);
        exit;
      }
    } else {
      $flds = table_fields('TradeYear');
      if (isset($TradyFlds[$field])) {
        $Trady = Default_Trade($id);
        $Trady[$field] = $Value;
        echo Put_Trade_Year($Trady);
        exit;
      }
    }

    // SHOULD never get here...
    exit;

  case 'Event':
    include_once("ProgLib.php");
    $Event = Get_Event($id);

    if (preg_match('/^(Start|End|SlotEnd|DoorsOpen)$/',$field)) {
      include_once("DateTime.php");
      $Value = Time_BestGuess($Value);
    } else if (preg_match('/^(Setup|Duration)$/',$field)) {
      include_once("DateTime.php");
      $Value = Time_BestGuess($Value,1);
//    } else if (preg_match('/PerfType\d+/',$field,$res)) {
//      $field = $res[1];
    } else if (preg_match('/Perf\d+_(Side\d+)/',$field,$res)) {
      $field = $res[1];
    }

//    echo "Field=$field Val=$Value<br>";
    if (isset($Event[$field])) { // General case
      $Event[$field] = $Value;
      echo Put_Event($Event);
      exit;
    }

    // SHOULD never get here...
    exit;

  case 'EventSteward':
    $RandWho = $id;
    if (preg_match('/(\w*):(\d*):(.*)/',$field,$mtch)?true:false) {
      $Eid = $mtch[2];
      $Efld = $mtch[1];
      $SE = $mtch[3];
      $ES = Gen_Get_Cond1('EventSteward',"RandId=$id AND EventId=$Eid AND SubEvent=$SE AND Year='$PLANYEAR'");

      if (!isset($ES['id'])) {
        $ES = ['RandId'=>$id, 'EventId'=>$Eid, 'HowMany'=>0, 'HowWent'=>'','Name'=>'', 'SubEvent'=>$SE, 'Year'=>$PLANYEAR];
      }
      $ES[$Efld] = $Value;
      return Gen_Put('EventSteward',$ES);
    }
//    echo "Didn't Match!";
    break;

  case 'Volunteers':
    if (preg_match('/(\w*):(.*?):(\d*)/',$field,$mtch)?true:false) {
 //var_dump($mtch);
      $vfld = $mtch[1];
      $Catid = $mtch[2];
      $Year = $mtch[3];
      switch ($vfld) {
        case 'Status':
        case 'Likes':
        case 'Dislikes':
        case 'Experience':
        case 'Other1':
        case 'Other2':
        case 'Other3':
        case 'Other4':
        case 'VolOrder' :
          $VCY = Gen_Get_Cond1('VolCatYear'," Volid=$id AND Catid=$Catid AND Year=$Year ");
          if (!$VCY) $VCY = ['Volid'=>$id,'CatId'=>$Catid,'Year'=>$Year, 'Props'=>0];
          $VCY[$vfld] = $Value;
          return Gen_Put('VolCatYear',$VCY);
        case 'YStatus':
          $VY = Gen_Get_Cond1('VolYear'," Volid=$id AND Year=$Year ");
          if (!$VY) $VY = ['Volid'=>$id, 'Year'=>$Year];
          $VY['Status'] = $Value;
          return Gen_Put('VolYear',$VY);

        default:
          $VY = Gen_Get_Cond1('VolYear'," Volid=$id AND Year=$Year ");
          if (!$VY) $VY = ['Volid'=>$id, 'Year'=>$Year];
          $VY[$vfld] = $Value;
          return "In VolYear" . Gen_Put('VolYear',$VY);
      }
    }
    break;

  case 'Sponsorships':
    if (preg_match('/(\a*):(\d*)/',$field,$mtch)?true:false) {
      $Spon = Gen_Get('Sponsorship',$mtch[2]);
      if ($Spon) {
        $Spon[$mtch[1]] = $Value;
//var_dump($mtch, $Spon);
        return Gen_Put('Sponsorship',$Spon);
      }
    }
    return "Something wrong $field $Value";

  case 'Sponsorship':
    if (preg_match('/Id(\d*)/',$field,$mtch)?true:false) {
      $field = 'ThingId';
    }
    break;

  case 'FestUsers':
    if (preg_match('/UserCap:(\d*)/',$field,$mtch)?true:false) {
      $Capid = $mtch[1];
      $Cap = Gen_Get_Cond1('UserCap'," User=$id AND Capability=$Capid ");
      if (!$Cap) $Cap = ['User'=>$id,'Capability'=>$Capid];
      $Cap['Level'] = $Value;
      return Gen_Put('UserCap',$Cap);
    }
    break;

  case 'VolCats':
    if ($field != 'Props') break;
    $N = Gen_Get($type,$id);
    $N[$field] = hexdec($Value);
    return Gen_Put($type,$N);

  case 'Generic' : // General case of table:field:id
    if ((preg_match('/(\w*):(\w*):(\d*)/',$field,$mtch)?true:false)) {
      $t = $mtch[1];
      $f = $mtch[2];
      $i = $mtch[3];
      if (($t == 'Ignore') || ($i==0)) exit;
      $N = Gen_Get($t,$i);
      $N[$f] = $Value;
      echo Gen_Put($t,$N);
    }
    if ((preg_match('/Ignore:(\w*):(\w*):(\d*)/',$field,$mtch)?true:false)) {
      exit;
    }
    exit;
    
  default:
    break;
  }
  global $TableIndexes;
  $idx = (isset($TableIndexes[$type])?$TableIndexes[$type]:'id');
  $N = Gen_Get($type,$id,$idx);
  $N[$field] = $Value;
  return Gen_Put($type,$N,$idx);
