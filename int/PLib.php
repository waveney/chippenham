<?php
// Participant Display Lib - Generalises Show_Side 

function Show_Part($Side,$CatT='',$Mode=0,$Form='AddPerf') { // if Cat blank look at data to determine type.  Mode=0 for public, 1 for ctte
  global $Side_Statuses,$Importance,$Surfaces,$Surface_Colours,$Noise_Levels,$Noise_Colours,$Share_Spots,$ADDALL,$PLANYEAR,$YEAR;
  global $OlapTypes,$OlapDays,$PerfTypes,$ADDALL,$PayTypes;
  if ($CatT == '') {
    $CatT = ($Side['IsASide'] ? 'Side' : ($Side['IsAnAct'] ? 'Act' : 'Other')); // Probably needs adding other cats
  }

  $Mstate = ($PLANYEAR == gmdate('Y') && $PLANYEAR == $YEAR);
  $Wide = UserGetPref('WideDisp');

  Set_Side_Help();
  $snum=$Side['SideId'];
//  if ($Side['IsAnAct'] || $Side['IsOther']) Add_Act_Help();
  $Sidey = Get_SideYear($snum);

  $Side['TotalFee'] = (isset($Sidey['TotalFee'])?$Sidey['TotalFee']:0); // This is to make linkemail do the right thing 
  $NotD = 0;
  $PerfTC = 0;
  foreach ($PerfTypes as $p=>$d) {
    if ($Side[$d[0]]) $PerfTC++;
    if (($d[0] != 'IsASide') && $Side[$d[0]]) $NotD = 1;
  }
 // if ( isset($Side['Photo']) && ($Side['Photo'])) echo "<img class=floatright id=PerfThumb src=" . $Side['Photo'] . " height=80>\n";
//  if (Access('SysAdmin')) 
  echo "<input  class=floatright type=Submit name='Update' value='Save Changes' form=mainform>";
  if ($Mode && ((isset($Side['Email']) && strlen($Side['Email']) > 5) || (isset($Side['AltEmail']) && strlen($Side['AltEmail']) > 5)) )  {
    if (Feature('EmailButtons')) {
      if (isset($Side['HasAgent']) && $Side['HasAgent'] && $Side['AgentEmail'] && !$Side['BookDirect']) {
        echo " <button type=button id=Email$snum onclick=ProformaSend('Dance_Blank',$snum,'Email','SendProfEmail',1,'AgentEmail','Invited')>Email Agent</button>"; 
      }
      if ($Side['Email']) echo "<button type=button id=Email$snum onclick=ProformaSend('Dance_Blank',$snum,'Email','SendProfEmail',1,'Email','Invited')>Email</button>"; 
      if ($Side['AltEmail']) 
        echo " <button type=button id=Email$snum onclick=ProformaSend('Dance_Blank',$snum,'Email','SendProfEmail',1,'AltEmail','Invited')>Alt Email</button>"; 
    } else {
//echo "XX5";  
      echo "If you click on the ";
      if (isset($Side['HasAgent']) && $Side['HasAgent'] && !$Side['BookDirect']) {
        echo linkemailhtml($Side,$CatT,'Agent','Agents');
        if (isset($Side['Email'])) echo " or contacts " . linkemailhtml($Side,$CatT,'!!');
      } else {
        if (isset($Side['Email'])) echo linkemailhtml($Side,$CatT,'!!');
      }
      if (isset($Side['AltEmail']) && $Side['AltEmail']) {
        if ($Side['Email']) echo " or ";
        echo linkemailhtml($Side,$CatT,'Alt');
      }
      echo ", press control-V afterwards to paste the <button type=button onclick=Copy2Div('Email$snum','SideLink$snum')>standard link</button>";
//      echo ", press control-V afterwards to paste the <button type=button onclick=CopyDiv('Email$snum')>standard link</button>";
      
// ADD CODE TO ONLY PROVIDE PROGRAMME WHEN AVAIL - Dance only?
//      if ($Side['IsASide']) echo " and programme <button type=button onclick=Copy2Div('Email$snum','SideProg$snum')>programme</button> into message.";
    }
  }
    echo "<div class=NotSide>Link for this performer: <b>https://" . $_SERVER['HTTP_HOST'] . "/int/Direct?t=Perf&id=$snum&key=" . 
          $Side['AccessKey'] . "&Y=$YEAR</b></div>\n";

///echo "XX6";  
  $Adv = '';
  $Imp = '';
  if ($Mode) {
    echo "<span class=NotSide>Fields marked are not visible to participants.</span>";
    echo "  <span class=NotCSide>Marked are visible if set, but not changeable by participants.</span>";
  } else {
    $Adv = ''; // 'class=Adv'; // NEEDS MODING FOR NON DANCE
    if ($Mstate && $Side['IsASide'] && isset($Sidey['Coming']) && $Sidey['Coming']==2 ) {
      echo "<h2 class=floatright id=AllImpsDone>You have <span id=ImpC>0</span> of <span id=ImpT>4</span> <span class=red>Most Important</span> things filled in </h2>";
      $Imp = 'class=Imp';
    }
    echo "Please keep this information up to date, even if you are not coming so we can invite you in the future.";
  }
  
  echo "<div id=ErrorMessage class=ERR></div>";

//********* PUBLIC


  echo "<form method=post id=mainform enctype='multipart/form-data' action=$Form>";
  echo "<div class=tablecont><table width=90% border class=SideTable>\n";
    Register_AutoUpdate('Performer',$snum);
    echo "<tr><th colspan=8><h2><b>Public Information" . Help('PublicInfo') . " <span class=smaller>(Name, Picture, Description, Web, Social Media)</b></b>";
    echo "</h2>";

    echo "<tr>" . fm_text(($Side['IsASide']?'Team Name':'Act Name'), $Side,'SN',3,'','autocomplete=off onchange=nameedit(event) id=SN'); // oninput=nameedit(event)');
      $snx = 'class=ShortName';
      if (((isset($Side['SN'])) && (strlen($Side['SN']) > 20) ) || (isset($Side['ShortName']) && strlen($Side['ShortName']) != 0)) { 
        if (strlen($Side['ShortName']) == 0) $Side['ShortName'] = substr($Side['SN'],0,20);
      } else {
        $snx .= ' hidden';
      }
    if (!$Wide) echo "<tr>";
      echo fm_text('Grid Name', $Side,'ShortName',1,$snx,$snx . " id=ShortName") . "\n";
    if (!$Wide) echo "<tr>";
      echo fm_text('Type', $Side,'Type') . "\n";

    if ($Side['IsASide']) echo "<tr>" . fm_textarea('Costume Description <span id=CostSize></span>',$Side,'CostumeDesc',5,1,
                        "maxlength=150 oninput=SetDSize('CostSize',150,'CostumeDesc')"); 
    echo "<tr>" . fm_textarea('Short Blurb <span id=DescSize></span>',$Side,'Description',5,1,
                        "maxlength=200 oninput=SetDSize('DescSize',200,'Description')"); 
//      echo "<td>" . fm_checkbox("Show One Blurb",$Side,'OneBlurb');
    echo "<tr>" . fm_textarea('Blurb for web',$Side,'Blurb',5,2,'','size=2000' ) . "\n";
    echo "<tr>";
      if (isset($Side['Website']) && strlen($Side['Website'])>1) {
        echo fm_text(weblink(trim($Side['Website'])),$Side,'Website');
      } else {
        echo fm_text('Website',$Side,'Website');
      }
      
      echo "<td>Recent Photo" . fm_DragonDrop(1, 'Photo','Perf',$snum,$Side,$Mode); // TODO  <td><a href=PhotoProcess.php?Cat=Perf&id=$snum>Edit/Change</a>";
    echo "<tr>";
      if (isset($Side['Video']) && $Side['Video'] != '') {
        echo fm_text("<a href=" . videolink($Side['Video']) . ">Recent Video</a>",$Side,'Video',1,$Adv);
      } else {
        echo fm_text('Recent Video',$Side,'Video',1,$Adv);
      }
      echo fm_text(Social_Link($Side,'Facebook' ),$Side,'Facebook');
      if (Access('SysAdmin')) echo fm_text1('Photo Link',$Side,'Photo',1,'class=NotSide','class=NotSide');
    if (!$Wide) echo "<tr>";
      echo fm_text(Social_Link($Side,'Twitter'  ),$Side,'Twitter');
      echo fm_text(Social_Link($Side,'Instagram'),$Side,'Instagram');
/*
      if (!$Wide) echo "<tr>";
      echo fm_text(Social_Link($Side,'Spotify'  ),$Side,'Spotify');
*/

//********* PRIVATE

    if ($Side['IsASide']) {
      if ($NotD) {
         $SmTxt = "Contact(s), PA, Dance Surfaces, Bank, Performers etc";
       } else {
         $SmTxt = "Contact(s), PA, Dance Surfaces etc";      
       }
     } else {
         $SmTxt = "Contact(s), PA, Bank, Performers";     
     }
    echo "<tr><th colspan=8><h2><b>Private Information" . Help('PrivateInfo') . "<span class=smaller>($SmTxt)</span></b></h2>";
    if ($Mode) {
      echo "<tr><td class=NotSide>Id:";//<td class=NotSide>";
        if (isset($snum) && $snum > 0) {
             echo $snum . fm_hidden('SideId',$snum);
          echo fm_hidden('Id',$snum);
        } else {
          echo fm_hidden('SideId',-1);
          echo fm_hidden('Id',-1);
        }
        echo "<td class=NotSide colspan=2>";
        if ($PerfTC < 2 || !$Side['DiffImportance']) echo "Importance:" . fm_select($Importance, $Side,'Importance',0,'','',3);
        if ($PerfTC > 1) echo " " . fm_checkbox("Diff Imp",$Side,'DiffImportance'); 
//        echo " " . fm_text0("Rel Order",$Side,'RelOrder',1,'class=NotSide','class=NotSide size=4');  // Unused
        echo fm_text1('Where found',$Side,'Pre2017',1,'class=NotSide','class=NotSide'); 
        if (($PerfTC > 1) || $Side['HasOverlays']) { 
          include_once("SideOverLib.php");
          echo "<td class=NotSide>" . fm_checkbox("Overlays",$Side,'HasOverlays');
          if ($Side['HasOverlays']) {
            echo "<tr><td class=NotSide>Overlays:" . help('Overlays');
            foreach ($PerfTypes as $p=>$d) {
              if ($Side[$d[0]]) {
                $Ov = Get_Overlay($Side,$d[2]);
                echo "<td class=NotSide><a href=SideOverlay?id=$snum&pc=" . $d[2] . ">" . 
                  ($Ov?'Edit':'Create') . " overlay for: " . $d[2] . "</a>";
              }  
            }
          }
        }
            
//      $IsAs = 0;
//      foreach($PerfTypes as $t=>$p) if ($Side[$p[0]]) $IsAs++;
      if (!$Wide) echo "<tr><td class=NotSide>Performer type:";//<td class=NotSide>";
        echo Help('PerfTypes') . " ";
        echo "<td class=NotSide colspan=2>";
        
        foreach ($PerfTypes as $t=>$p) {
          if (Capability("Enable" . $p[2])) {
            echo fm_checkbox($t,$Side,$p[0]);//, (($Side[$p[0]] && ( $PerfTC == 1) /* && ! Access('SysAdmin')*/ ) ? 'disabled readonly': ' onchange=this.form.submit() ')) . " ";
          }
        }
        echo "<td class=NotSide>State:" . fm_select($Side_Statuses,$Side,'SideStatus') . "\n";
        echo "<td class=NotSide>" . fm_checkbox('Not a Performer',$Side,'NotPerformer') . "\n";
        if ($PerfTC > 1 && $Side['DiffImportance']) {
          echo "<tr><td class=NotSide>Importances:" . help('Importance');
          foreach ($PerfTypes as $p=>$d) {
            if ($Side[$d[0]]) {
              echo "<td class=NotSide>" . $d[2] . ": " . fm_select($Importance, $Side,$d[2] . 'Importance',0,'','',3);
            }
          }
        }
    } else {
      echo fm_hidden('SideId',$snum);
      echo fm_hidden('Id',$snum);
//    }
//    if ($Mode == 0) { //  || !Access('SysAdmin')) {
      // TODO Perf types loop
      echo fm_hidden('IsASide',$Side['IsASide']);
      echo fm_hidden('IsAnAct',$Side['IsAnAct']);
      echo fm_hidden('IsFunny',$Side['IsFunny']);
      echo fm_hidden('IsFamily',$Side['IsFamily']);
      echo fm_hidden('IsOther',$Side['IsOther']);
      echo fm_hidden('IsCeilidh',$Side['IsCeilidh']);
    }


    $AgentTxt = (isset($Side['HasAgent']) && $Side['HasAgent']?"":"hidden");
    if ($NotD) { 
      echo "<tr><td>" . fm_checkbox("Has Agent",$Side,'HasAgent','onchange=AgentChange(event)') . 
           "<td class=AgentDetail>" . fm_checkbox('Book Directly',$Side,'BookDirect');
    }

    echo "<tr class=AgentDetail $AgentTxt>";
      echo fm_text('<span id=AgentLabel>Agent</span>',$Side,'AgentName',1,'','','',($Wide?'':' rowspan=3 '));
      echo fm_text1('Email',$Side,'AgentEmail',2);
    if (!$Wide) echo "<tr class=AgentDetail $AgentTxt>";
      echo fm_text('Phone',$Side,'AgentPhone');
      echo fm_text('Mobile',$Side,'AgentMobile');
      echo "<tr class=AgentDetail $AgentTxt>" . fm_text('Address',$Side,'AgentAddress',3) . fm_text('Post Code',$Side,'AgentPostCode');

    echo "<tr>" . fm_text('<span id=ContactLabel>Contact</span>',$Side,'Contact',1,'','','',($Wide?' rowspan=2':' rowspan=4 '));
      echo fm_text1('Email',$Side,'Email',2);
      if (!$Wide) echo "<tr>";
      echo fm_text('Phone',$Side,'Phone');
      echo fm_text('Mobile',$Side,'Mobile',1,'','onchange=updateimps()','',$Imp) . "\n";
      echo "<tr>" . fm_text('Address',$Side,'Address',3,(Feature('DanceNeedAddress')?$Imp:''),(Feature('DanceNeedAddress')?'onchange=updateimps()':''));
      if (!$Wide) echo "<tr>";
      echo fm_text('Post Code',$Side,'PostCode')."\n";
    echo "<tr $Adv>" . fm_text('Alt Contact',$Side,'AltContact',1,'','','',($Wide?'':' rowspan=2 '));
      echo fm_text1('Alt Email',$Side,'AltEmail',2);
      if (!$Wide) echo "<tr>";
      echo fm_text('Alt Phone',$Side,'AltPhone');
      echo fm_text('Alt Mobile',$Side,'AltMobile')."\n";
//    echo "<tr $Adv>" . fm_text('Alt Address',$Side,'AltAddress',5,$Imp,'onchange=updateimps()');
//      echo fm_text('Alt Post Code',$Side,'AltPostCode')."\n";
    if ($Side['IsASide']) {
      echo "<tr $Adv>" . fm_textarea('Requests',$Side,'Likes',3,1);
        if (!$Wide) echo "<tr>";
        echo fm_text('Animal',$Side,'MorrisAnimal');
      echo "<tr><td>Surfaces:" . help('Surfaces') . "<td colspan=3>";
        for($st=1;$st<=8;$st++) {
          $surf = $Surfaces[$st];
          if (!Access('SysAdmin') && $st >= 6) {
            echo fm_hidden("Surface_$surf",$Side["Surface_$surf"]);  // Surfaces 6-8 only for Richard at the moment
          } else {
            if ($st == 6) echo " ... ";

            echo "<span style='Background:" . $Surface_Colours[$st] . ";padding:4; white-space: nowrap;'>" .fm_checkbox($surf,$Side,"Surface_$surf") . "</span>";
          }
        }
        if (!$Wide) echo "<tr>";
        echo "<td>Shared Spots:<td>" . fm_select($Share_Spots,$Side,'Share');
        if (!isset($Side['NoiseLevel'])) $Side['NoiseLevel']=0;
        echo "<td colspan=2 $Adv>" . fm_radio("Music Volume",$Noise_Levels,$Side,'NoiseLevel','',0,'','',$Noise_Colours);
      echo "<tr $Adv>";
        echo fm_textarea('Workshops',$Side,'Workshops',3,1);

    }

// TODO if (isset($Side['SortCode']) && $Side['SortCode'] replace needbank with js test of fee/op
    if ($ADDALL && !Access('Committee','Finance')) {} // No bank details unless your area or Finance
    else {
      $bankhide = 1;
      if ($snum > 0 && ( $Side['SortCode'] || $Side['Account'] || $Side['AccountName'] || $Side['TotalFee'] )) $bankhide = 0;
      $Bacs = Feature('PayByCheque'); // 0=BACS only, 1=BACS+cheque, 2= LAST Cheque
//    echo $bankhide . "sc:" . $Side['SortCode'] . "ac:" .$Side['Account']. "an:" .$Side['AccountName'] . "tf" . $Side['TotalFee'];
      if ($Bacs > 0) {
        $PayTypes = ['BACS','Cheque'];
        echo "<tr" . ($bankhide?" class='ContractShow' hidden":'') . " id=BankDetail0>";
        echo fm_radio('Payment Type',$PayTypes,$Side,'WantCheque',"onchange=PayTypeSel()");
        if ($Bacs > 1) echo "<td id=ChequeNote colspan=3>Note this the last year when payment by Cheque is an option.";
      }
      echo "<tr" . ($bankhide?" class='ContractShow' hidden":'') . " id=BankDetail>";
        echo "<td rowspan=2>Bank Details:" . help('Bank');
        echo fm_text('Sort Code',$Side,'SortCode');
        echo fm_text('Bank Account Number',$Side,'Account');
      echo "<tr" . ($bankhide?" class='ContractShow' hidden":'') . " id=BankDetail2>";
        echo fm_text('Account Name',$Side,'AccountName',2);
        echo "<td>" . fm_checkbox('Are you VAT registered',$Side,'VATreg');
    }

// PA 
    echo "<tr " . (($Side['IsASide'] && !$Side['IsAnAct'] && !$Side['IsOther'])?$Adv:"") . ">";
      if (($NotD == 0) && (!isset($Side['StagePA']) || ($Side['StagePA'] == ''))) $Side['StagePA'] = 'None';
      echo "<td>PA Requirements:";
      $f = ($Side['StagePA'] == '@@FILE@@');  // This does not use fm_radio as it has many speccial cases
      echo "<td><label for=StagePAtext>Text</label> <input type=radio $ADDALL name=StagePAtext value=1 onchange=setStagePA(1) id=StagePAtext " . ($f?"":"checked") . "> " .
           "<label for=StagePAfile>File</label> <input type=radio $ADDALL name=StagePAtext value=2 onchange=setStagePA(0) id=StagePAfile " . ($f?"checked":"") . ">" .
           Help("StagePA");
      echo "<td id=StagePAtextF colspan=4" . ($f?' hidden':'') . " >" . fm_basictextarea($Side,'StagePA',5,1);

        $files = glob("PAspecs/$snum.*");
        echo fm_DragonDrop(1,'StagePA','Perf',$snum,$Side,$Mode,'',1,'','StagePAFileF',($f?0:1));

// Members
    if ($Side['IsAnAct']) { // May need for Other
      $Band = Get_Band($snum);      
      $BandPerRow=($Wide?6:4); 
      $Curband = $Band? count($Band) : 0;
      $Rows = max(1,ceil($Curband/$BandPerRow));
      $colcnt = 0;
      $row = 0;
      $bi = 0;
      echo "<tr id=BandRow$row><td id=BandMemRow1 rowspan=$Rows>Band Members: <button type=button onclick=AddBandRow($BandPerRow)>+</button>";
      if (is_array($Band)) {
        foreach ($Band as $B) {
            if ($colcnt >= $BandPerRow) {
            $row++;
            echo "<tr id=BandRow$row>";
            $colcnt = 0;
          }
          echo "<td>" . fm_textinput("BandMember$bi:" . $B['BandMemId'],$B['SN'],'onchange=BandChange(event)');
          $colcnt++;
          $bi++;
        }
      }
      while ($colcnt < $BandPerRow) {
        echo "<td>" . fm_textinput("BandMember" . ($bi++) . ":0",'','onchange=BandChange(event)');
        $colcnt++;
      }
      echo "<tr hidden id=AddHere></tr>\n";
    }

// OVERLAPS
    echo "<tr>" . fm_textarea('Shared Performers',$Side,'Overlaps',5,2);
    if ($Mode) { // only ctte can build rule sets
      $olaps = Get_Overlaps_For($snum);
//var_dump($olaps);
      $rows = count($olaps)+1;
 //     $SideList=Sides_All($snum);
 //     $ActList=Act_All();
 //     $OtherList=Other_All();
      $row = 0;
      echo "<tr id=OverlapRow$row class=NotSide><td rowspan=$rows class=NotSide>Overlap Rules: \n" . 
            help('OverlapRules'); //<button type=button onclick=AddOverlapRow()>+</button>\n";

      foreach ($PerfTypes as $p=>$d) if (Capability("Enable" . $d[2])) $SelectPerf[$p] = ($d[0] == 'IsASide'? Sides_All($snum,1): Perf_Name_List(($d[0])));

      $PTypes = [];
      foreach ($PerfTypes as $p=>$d) if (Capability("Enable" . $d[2])) $PTypes[] = $p;

      for ($i = 0; $i < $rows; $i++) {
        $O = (isset($olaps[$i]) ? $olaps[$i] : ['Sid1'=>$snum,'Cat2'=>0,'Sid2'=>0]);
        $Other =  ($O['Sid1'] == $snum)?'Sid2':'Sid1';
        $OtherCat =  ($O['Sid1'] == $snum)?'Cat2':'Cat1';
        if ($i) echo "<tr id=OverlapRow$i class=NotSide>";
        echo "<td colspan=7 class=NotSide>Type: " . fm_select($OlapTypes,$O,'OType',0,'',"OlapType$i") . 
                fm_checkbox("Major",$O,'Major','',"OlapMajor$i") . 
                fm_radio('',$PTypes,$O,$OtherCat,"onchange=EventPerfSel(event,###F,###V)",0,'',"Olap$i" . "Cat");
 
        $sid = $O[$Other];
        $pi = 0;
        foreach ($PerfTypes as $p=>$d) if (Capability("Enable" . $d[2])) {
 
          echo ($SelectPerf[$p]?fm_select($SelectPerf[$p],$O,$Other,1,"id=Perf$pi" . "_Side$i " . ($O[$OtherCat]==$pi?'':'hidden'),"Perf$pi" . "_Side$i") :"");
          if ($sid && ($O[$OtherCat] == $pi) && !isset($SelectPerf[$p][$sid])) {
            $OSide = Get_Side($sid);
            echo "<del><a href=AddPerf?id=$sid>" . $OSide['SN'] . "</a></del> ";               
          }
          $pi++;
        }
               
        echo "&nbsp;On&nbsp;Days: " . fm_select($OlapDays,$O,'Days',0,'',"OlapDays$i") . 
                fm_checkbox("Rule Active",$O,'Active','',"OlapActive$i") . "\n";
        if ($i != ($rows-1)) echo " <button name=Action value=DeleteOlap$i type=submit>Del</a>"; 
      } 

    }

    if ($Mode) {
      echo "<tr>" . fm_text('Location',$Side,'Location',2,'class=NotSide');
      if (Access('SysAdmin')) {
        if (!$Wide) echo "<tr class=NotStaff>";
        echo fm_nontext('Access Key',$Side,'AccessKey',3,'class=NotStaff','class=NotStaff'); 
        if (isset($Side['AccessKey'])) {
          echo "<td class=NotStaff><a href=Direct?id=$snum&key=" . $Side['AccessKey'] . "&Y=$YEAR>Use</a>" . help('Testing');
        }
      }
      echo "<tr>" . fm_textarea('Notes',$Side,'Notes',5,2,'class=NotSide','class=NotSide');
      if (!$Wide) echo "<tr>";
      echo "<td class=NotSide colspan=2>";
      if (file_exists("Store/Performers/$snum")) {
        $files = glob("Store/Performers/$snum/*");
        $fcount = count($files);
        if ($fcount == 1 ) {
          $fname = basename($files[0]);
          echo "File: <a href=ShowFile?l64=" . base64_encode("Store/Performers/$snum/$fname") . ">$fname</a>";
        } else {
          echo "$fcount files are stored ";
        }
      }
      
      echo " <button type=submit formaction='PerformerData?id=$snum&ACTION=LIST'>Manage Files</button>" . help('ManageFiles');
      
      echo "<td class=NotSide><button type=submit formaction='ViewEmailLog?Src=1&id=$snum'>View Email Log</button>" . help('EmailLog');
      
    }
  if (Access('SysAdmin')) {
    echo "<tr><td class=NotStaff>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea><p><span id=DebugPane></span>";
//    echo "<tr><td class=NotSide>Debug<td colspan=5 class=NotSide><textarea id=Debug></textarea><p>" ; //<span id=DebugPane></span>";
  } else {
//    echo "<div hidden><tr><td class=NotSide>Debug:<td colspan=5 class=NotSide><span id=DebugPane></span><p></div>"; 
  }


  echo "</table></div>\n";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function Show_Perf_Year($snum,$Sidey,$year=0,$Mode=0) { // if Cat blank look at data to determine type.  Mode=0 for public, 1 for ctte
  global $YEAR,$PLANYEAR,$YEARDATA,$Invite_States,$Coming_States,$Coming_Colours,$TickBoxes;
  global $Book_State,$Book_States,$Book_Colours,$ContractMethods,$Dance_Comp,$Dance_Comp_Colours,$PerfTypes,$ShowAvailOnly;
  
  if ($year==0) $year=$YEAR;

  $Side=Get_Side($snum);
  Set_Side_Year_Help();
  $Wide = UserGetPref('WideDisp');
  
  $Mstate = ($PLANYEAR == gmdate('Y') && $PLANYEAR == $YEAR);  // TODO?

  if (($year < $PLANYEAR) && !Access('Internal') && !isset($_REQUEST['FORCE'])) { // Then it is historical - no changes allowed
    fm_addall('disabled readonly');
  }

  $NotD = 0;
  foreach ($PerfTypes as $d) if (Capability("Enable" . $d[2])) if (($d[0] != 'IsASide') && $Side[$d[0]]) $NotD = 1;

  $Adv = '';
  $Imp = '';
  if (!$Mode) { // TODO
    $Adv = 'class=Adv';
    if ($Mstate) $Imp = 'class=Imp';
  }
//echo "HERE";
  $Self = ($Mode ? $_SERVER['PHP_SELF'] : "AddPerf"); // TODO
  
// Get_SideYears, OLIst is YEar fields from the years
  $OList = [];
  $years = Get_SideYears($snum);

  if ($years) {
 
//echo var_dump($years);
//var_dump($Sidey);

    foreach ($years as $yr) {
      if (isset($yr['Year'])) $OList[] = $yr['Year'];
    }
    sort($OList);
/* old code
  $OList = [];
  for ($y = 5;$y>0; $y--) {
    if (isknown($snum,$year-$y)) $OList[] = $year-$y;
  }
  if (Get_General($year+1) && (isknown($snum,$year+1) || (($year+1) >= $PLANYEAR))) $OList[] = $year+1;
  if ($year != $PLANYEAR) $OList[] = $PLANYEAR;
    
  sort($OList);
*/
    if (count($OList)) {
      echo "<div class=floatright><h2>";
      $last = -1;
      foreach ($OList as $dv) {
        if ($dv == $last) continue;
        echo " <a href=$Self?sidenum=$snum&Y=$dv>$dv</a> ";
        $last = $dv;
      }
      echo "</h2></div>";
    }
  }

  if ($year < $PLANYEAR && (!isset($Sidey['syId']) || $Sidey['syId'] < 0)) {
    echo "<h2>" . $Side['SN'] . " did not perform in $YEAR</h2>";
    return;
  }

/* TODO this is duff
  if (($Mode == 0) && (($Side['IsASide'] && (!isset($Sidey['Coming']) || $Sidey['Coming'] == 0) && (!isset($Sidey['Invite']) || $Sidey['Invite'] >= $Invite_Type['No'])) ||
                       ($Side['IsASide'] == 0 && $Sidey['YearState'] == 0 && !$ShowAvailOnly))) {
    echo "<h2><a href=DanceRequest?sidenum=$snum&Y=$YEAR>Request Invite for " . substr($YEAR,0,4) . "</a></h2>";
    return;
  }
*/

// Start here
  echo "<h2>Performing in " . substr($year,0,4) . "</h2>";
  
  echo fm_hidden('Year',$year);
  echo fm_hidden('Y',$year);
  if (isset($Sidey['syId']) && ($Sidey['syId'])) echo fm_hidden('syId',$Sidey['syId']);

  echo "<div class=tablecont><table width=90% border class=SideTable>\n";
  
  // Booked by, Release Date, Camping
  if ($Mode) {
    include_once('DocLib.php');
    include_once('BudgetLib.php');
    Contract_State_Check($Sidey,0);
         
    echo "<tr><td class=NotSide>id: " . (isset($Sidey['syId'])?$Sidey['syId']:-1);
      $Perfs = [];
      foreach ($PerfTypes as $t=>$d) if (Capability("Enable" . $d[2])) if ($Side[$d[0]]) $Perfs[] = $d[2];
      $AllMU = Get_AllUsers4Perf($Perfs,$Sidey['BookedBy']);
      echo "<td class=NotSide>Booked By: " . fm_select($AllMU,$Sidey,'BookedBy',1);
      echo fm_date('Release Date',$Sidey,'ReleaseDate','class=NotSide','class=NotSide');     
  }

  // Dance Invites and States
  if ($Side['IsASide']) {
    if ($Mode) {
      echo "<td class=NotSide>Dancing Invite:" . fm_select($Invite_States,$Sidey,'Invite');
      $Coming_States[0] = 'None';
    }
    echo "<tr>";
    echo fm_radio('Dancing Status',$Coming_States ,$Sidey,'Coming','',1,'colspan=3 id=Coming_states','',$Coming_Colours,0,'',
         ' onchange=ComeAnyWarning()'); 
    if ($Mode) echo "<td class=NotSide>" . fm_checkbox("No Dance Events",$Sidey,'NoDanceEvents');
  }
  if (Access('SysAdmin')) echo "<tr>" . fm_textarea('Messages' . Help('Messages'),$Sidey,'Invited',5,2,'class=NotSide','class=NotSide');

  // Performers booking states
  if ($Mode) {
    if ($NotD) {
      echo "<tr>";
    } else { 
      echo "<tr class=ContractShow hidden>";
    }
    if ($Mode || Access('SysAdmin')) {
      echo fm_radio("Contract State",$Book_States,$Sidey,'YearState','class=NotSide',1,'colspan=3 class=NotSide','',$Book_Colours);
      echo "<td class=NotSide>" . fm_checkbox('No Events',$Sidey,'NoEvents');
      if (Access('SysAdmin')) echo fm_number1('Contract Issue',$Sidey,'Contracts', 'class=NotStaff');
    } else {
      echo "<td class=NotSide>Booking State:" . help('YearState') . "<td class=NotSide>" . $Book_States[$Sidey['YearState']];
    }
  } else {
    echo fm_hidden('YearState',$Sidey['YearState']);  
  }
  
  echo fm_hidden('HiddenYearState',$Sidey['YearState']); 
  $DayCount = 0;
  for ($d= $YEARDATA['FirstDay']; $d<= $YEARDATA['LastDay']; $d++) $DayCount++;

  // Dance Spots
  $HereCount = 0;
  if ($Side['IsASide']) {
    $chg = ' onchange=CheckDiscount()';
    if ($Mode == 0 && Feature('RestrictOneProcession',0)) $chg = "onchange=ForceOneProcession(event)";
    $ProcDays = intval(Feature('ProcessDays'));
    if ($YEARDATA['FirstDay'] <= 0) {
      echo "<tr><td rowspan=" . (2*$DayCount-1) . ">" . ((isset($Sidey['Invited']) && $Sidey['Invited']) ? "Dancing on:" : "Would like to dance on:" );
        echo "<td>" . fm_checkbox('Friday',$Sidey,'Fri','onchange=ComeSwitch(event)');
//      echo fm_text1('Daytime Spots',$Sidey,'FriDance',1,'class=ComeFri');
        echo "<td class=ComeFri>" . fm_checkbox('Dance Friday Eve?',$Sidey,'FriEve');
    }
    if ($YEARDATA['FirstDay'] <= 1 && $YEARDATA['LastDay'] > 0) {  
      if (!empty($Sidey['Sat'])) $HereCount++;
      echo "<tr>";
        echo "<td rowspan=2>" . fm_checkbox('Saturday',$Sidey,'Sat','onchange=ComeSwitch(event)');
        echo fm_text1('Daytime &half; hr Spots',$Sidey,'SatDance',0.5,'class=ComeSat onchange=CheckDiscount()');
        if (($ProcDays & 2) && Feature("Procession")) 
          echo "<td class=ComeSat>" . fm_checkbox('Plus the ' . Feature("Procession","Procession"),$Sidey,'ProcessionSat',$chg);
        if ($YEARDATA['LastDay'] > 1)  echo "<td class=ComeSat>" . fm_checkbox('Dance Saturday Eve?',$Sidey,'SatEve');
        echo "<tr>" .fm_text1('Earliest Spot',$Sidey,'SatArrive',0.5,'class=ComeSat');
        echo fm_text1('End of latest Spot',$Sidey,'SatDepart',0.5,'class=ComeSat');  
    }
    if ($YEARDATA['FirstDay'] <= 2 && $YEARDATA['LastDay'] > 1) {  
      if (!empty($Sidey['Sun'])) $HereCount++;
      echo "<tr>";
      echo "<td rowspan=2>" . fm_checkbox('Sunday',$Sidey,'Sun','onchange=ComeSwitch(event)');
      echo fm_text1('Daytime &half; hr Spots',$Sidey,'SunDance',0.5,'class=ComeSun onchange=CheckDiscount()');
      if (($ProcDays & 4) && Feature("Procession")) 
        echo "<td class=ComeSun>" . fm_checkbox('Plus the ' . Feature("Procession","Procession"),$Sidey,'ProcessionSun',$chg);
      if ($YEARDATA['LastDay'] > 2)  echo "<td class=ComeSun>" . fm_checkbox('Dance Sunday Eve?',$Sidey,'SunEve');
      echo "<tr>" .fm_text1('Earliest Spot',$Sidey,'SunArrive',0.5,'class=ComeSun');
      echo fm_text1('End of latest Spot',$Sidey,'SunDepart',0.5,'class=ComeSun');
    }
    if ($YEARDATA['FirstDay'] <= 3 && $YEARDATA['LastDay'] > 2) {  
      if (!empty($Sidey['Mon'])) $HereCount++;
      echo "<tr>";
      echo "<td rowspan=2>" . fm_checkbox('Monday',$Sidey,'Mon','onchange=ComeSwitch(event)');
      echo fm_text1('Daytime &half; hr Spots',$Sidey,'MonDance',0.5,'class=ComeMon onchange=CheckDiscount()');
      if (($ProcDays & 8) && Feature("Procession")) 
        echo "<td class=ComeMon>" . fm_checkbox('Plus the ' . Feature("Procession","Procession"),$Sidey,'ProcessionMon',$chg);
      if ($YEARDATA['LastDay'] > 3)  echo "<td class=ComeMon>" . fm_checkbox('Dance Monday Eve?',$Sidey,'MonEve'); // Not Possible yet
      echo "<tr>" .fm_text1('Earliest Spot',$Sidey,'MonArrive',0.5,'class=ComeMon');
      echo fm_text1('End of latest Spot',$Sidey,'MonDepart',0.5,'class=ComeMon');
    }

    if ($Mode) {
//      echo "<tr><td class=NotSide>" . fm_checkbox('Tuesday',$Sidey,'Tue') . "<td class=NotSide>" . fm_checkbox('Wednesday',$Sidey,'Wed');
//      echo "<td class=NotSide>" . fm_checkbox('Thursday',$Sidey,'Thur') . "<td class=NotSide>" . fm_checkbox('Monday',$Sidey,'Mon');
    } else {
      if (!empty($Sidey['Tue'])) echo fm_hidden('Tue',1);
      if (!empty($Sidey['Wed'])) echo fm_hidden('Wed',1);
      if (!empty($Sidey['Thur'])) echo fm_hidden('Thur',1);
      if (!empty($Sidey['Mon'])) echo fm_hidden('Mon',1);
    }
    if (Feature('DanceComp') && (isset($Side['Type']) && strstr($Side['Type'],'North West'))) {
      echo "<tr>" . fm_radio("Would you be interested taking part in a North West Morris dance competition?", $Dance_Comp,$Sidey,'DanceComp',
                                     'colspan=3',1,'colspan=3','',$Dance_Comp_Colours);
    }
  } 
  
  if ($NotD && Feature('PerformerAvail')) {
    if ($YEARDATA['FirstDay'] <= 0) {
      echo "<tr><td rowspan=$DayCount id=Availability>Availability:";
        echo "<td>" . fm_checkbox(FestDate(0,$format='L',$YEAR) ,$Sidey,'MFri');
        echo fm_text1('Times not available',$Sidey,'FriAvail',2);
    }
    if ($YEARDATA['FirstDay'] <= 1 && $YEARDATA['LastDay'] > 0) {  
      echo "<tr>";
        echo "<td>" . fm_checkbox(FestDate(1,$format='L',$YEAR),$Sidey,'MSat');
        echo fm_text1('Times not available',$Sidey,'SatAvail',2);
    }
    if ($YEARDATA['FirstDay'] <= 2 && $YEARDATA['LastDay'] > 1) {  
      echo "<tr>";
        echo "<td>" . fm_checkbox(FestDate(2,$format='L',$YEAR),$Sidey,'MSun');
        echo fm_text1('Times not available',$Sidey,'SunAvail',2);
    }
    if ($YEARDATA['FirstDay'] <= 3 && $YEARDATA['LastDay'] > 2) {
      echo "<tr>";
        echo "<td>" . fm_checkbox(FestDate(3,$format='L',$YEAR),$Sidey,'MMon');
        echo fm_text1('Times not available',$Sidey,'MonAvail',2);
    }
  }

  // Tickboxes 
//  if ($Side['IsASide']) {
    $str = '';
    $hstr = '';
    foreach ($TickBoxes as $bi=>$box) {
      if (!is_array($box)) continue;
      list($bxtxt,$bxfld,$bxtst,$bxval,$bxuse,$bxSiz) = $box;
      $Doit = 0;
      foreach (str_split($bxuse) as $let) {
        switch ($let) {
        case 'D': if ($Side['IsASide']) $Doit = 1; break;
        case 'M': if ($Side['IsAnAct']) $Doit = 1; break;
        case 'C': if ($Side['IsFunny']) $Doit = 1; break;
        case 'F': if ($Side['IsFamily']) $Doit = 1; break;
        case 'O': if ($Side['IsOther']) $Doit = 1; break;
        case 'H': if ($Side['IsCeilidh']) $Doit = 1; break;
        }
      }
      if (!$Doit) continue;
      
      $show = 0;
      switch ($bxtst) {
      case 'YHAS':
        if (isset($Sidey[$bxfld]) && strstr($Sidey[$bxfld],$bxval)) $show =1;
        break;
      case 'NVAL':
        if (isset($Sidey[$bxfld]) && $Sidey[$bxfld] != $bxval) $show = 1;
      
      }
      if (Access('Staff')) { 
        if ($bxSiz < 3) {
          $str .= "<td class=NotSide>" . fm_checkbox($bxtxt,$Sidey,"TickBox" . ($bi+1));
        } else {
          $str .= fm_text($bxtxt,$Sidey,"TickBox" . ($bi+1),1,'class=NotSide');
        }
      } else {
        if (isset($Sidey['TickBox' . ($bi+1)])) $hstr .= fm_hidden("TickBox" . ($bi+1),$Sidey['TickBox' . ($bi+1)]);
      }
    }
    if ($str) echo "<tr class=NotSide>$str\n";
    if ($hstr) echo $hstr;
//  }
      
  // Wristbands
//  if ( $Side['IsASide'] || $Side['IsAnAct']==0 || Feature('MusicWristBands')) {
    echo "<tr>";    
      switch (Feature('PerformerTickets','Wimb')) {
        case 'Wimb':
          if ($Side['IsASide']) {
            echo fm_text("<span $Imp>How Many Performers Wristbands</span>",$Sidey,'Performers',0.5,'','onchange=updateimps()');
            if ($Mode) {
              if (isset($Sidey['WristbandsSent'])) echo fm_checkbox("Sent",$Sidey,"WristbandsSent"); 
            } else {
              if (isset($Sidey['WristbandsSent']) && $Sidey['WristbandsSent']) {
                $tmp['Ignored2'] = 1; // Makes it visible they have ebeen sent
                echo fm_checkbox('Sent',$tmp,'Ignored2','disabled');
              }
              if (isset($Sidey['WristbandsSent'])) echo fm_hidden('WristbandsSent',$Sidey['WristbandsSent']);
            }
          } else if (isset($Sidey['Performers']) && $Sidey['Performers']) {
            echo fm_text('How Many Performers Wristbands',$Sidey,'Performers',1,'class=NotCSide','class=NotCSide');
          }
          break;
        
        case 'Chip':
          if ($Side['IsASide']) {
            echo "<tr><td colspan=6>Festival Tickets wanted at <span id=TickDiscount>No discount</span> - You will be sent a link to purchase these by the festival. " .
                 "Set Adults to -1 if you don't want any";
            echo "<tr>" . fm_number1('Adults',$Sidey,'Performers','',' onchange=CheckIfTickets() ') . 
                 fm_number1('Youth (10-16)',$Sidey,'PerformersYouth','',' onchange=CheckIfTickets() ') .
                 fm_number1('Child (under 10)',$Sidey,'PerformersChild','',' onchange=CheckIfTickets() ');
            echo "<td class=NOPerfTickets><b>No Tickets Wanted</b>";  
            if ($Mode) echo "<td class=NotSide>" . fm_checkbox('These have changed',$Sidey,'PerfNumChange');
          } 
          
          if ($Side['IsAnAct'] || $Side['IsFunny'] || $Side['IsFamily'] || $Side['IsCeilidh'] || $Side['IsOther'] ) {
            echo "<tr class=NotCSide><td class=NotCSide>Free Tickets:" . fm_number1('Adults',$Sidey,'FreePerf','class=NotCSide') . 
                 fm_number1('Youth (10-16)',$Sidey,'FreeYouth','class=NotCSide') .
                 fm_number1('Child (under 10)',$Sidey,'FreeChild','class=NotCSide');
            if (Access('Staff')) {
              if ($Sidey['TicketsCollected'] ?? 0) {
                $User = Get_User($Sidey['CollectedBy'] ?? 0);
                echo fm_text1("Tickets Collected", $Sidey,'TicketsCollected') . " from " . ($User['SN'] ?? 'Unknown') . "</span>";
              }
            }
          }
          break;
      }   
               
    echo "<td id=ComeAny hidden colspan=2><span class=Err>Don't forget to click Coming above?</span>";
    echo "<td id=WhatDays hidden colspan=2><span class=Err>What Days?</span>";


  $CampMode = Feature('CampControl');
  // Fees etc  TODO Need to make fee/otherpayment open stuff up and then Important - fee drives bank stuff, either for budget and contracts
  if ($Mode) {
    include_once("BudgetLib.php");
    echo "<tr>". fm_number1('Fee',$Sidey,'TotalFee','class=NotCSide',' onchange=CheckContract()');
    echo fm_text('Other payments',$Sidey,'OtherPayment',2,'class=NotCSide',' onchange=CheckContract()');
    echo fm_number1('Cost of this',$Sidey,'OtherPayCost','class=NotSide colspan=1');
    echo "<tr>". fm_number('Accommodation People',$Sidey,'AccomPeople','class=NotCSide',' onchange=CheckContract()');
    echo fm_text('Accommodation Nights',$Sidey,'AccomNights',1,'class=NotCSide',' onchange=CheckContract()');
    echo fm_number1('Cost of Accom',$Sidey,'AccomCost','class=NotSide colspan=1');

    if (!isset($Sidey['BudgetArea']) || $Sidey['BudgetArea']==0) {
      $area = 0;
      foreach ($PerfTypes as $t=>$d) if (Capability("Enable" . $d[2])) {
        if ($Side[$d[0]] && $d[3] && $area==0) $area = FindBudget($d[3]);
      }
      if ($area > 0) $Sidey['BudgetArea'] = $area;
    }
    $Bud = Budget_List();
    $R2Venues = Report_To(1);
// echo "</table>"; var_dump($Bud);
    if ($Bud) {
      echo "<tr class=ContractShow hidden><td class=NotSide>Budget Area:" . help('BudgetArea0') . "<td class=NotSide>" . fm_select($Bud,$Sidey,'BudgetArea');
      echo "<td class=NotSide>Except: " . fm_select($Bud,$Sidey,'BudgetArea2') . fm_number1("Value",$Sidey,'BudgetValue2','class=NotSide','class=NotSide');
      echo "<td class=NotSide>" . fm_select($Bud,$Sidey,'BudgetArea3') . fm_number1("Value",$Sidey,'BudgetValue3','class=NotSide','class=NotSide');
    }
    if (!isset($Sidey['ReportTo']) || $Sidey['ReportTo']==0) $Sidey['ReportTo'] = Feature('DefaultReportPoint',0);
    echo "<tr class='NotCSide ContractShow' hidden>" . fm_textarea('Additional Riders',$Sidey,'Rider',2,1,'class=NotCSide') ."\n";
      if (!$Wide) echo "<tr>";
      echo "<td colspan=2 class=NotCSide>On arrival report to: " . fm_select($R2Venues,$Sidey,'ReportTo') .
           "<td class=NotSide>" . fm_checkbox('Tell about Green Room',$Sidey,'GreenRoom');
      if (empty($Sidey['TotalFee'])) echo "<td class=NotSide>" . 
        fm_checkbox('Need a contract even if no fee',$Sidey,'ContractAnyway',' onchange=CheckContract()');

    $campxtr = $campxtr2 = '';          
    switch ($CampMode) {
    case 0: // None
      break;

    case 2: // Restricted
      $campxtr = " class=NotCSide";
      $campxtr2 = " onchange=CheckContract()"; 
      echo "<tr><td class=NotSide>" . fm_checkbox("Allow Camping",$Sidey,'EnableCamp','onchange="($(\'.CampDay\').toggle())"');           
      // Drop through
      
    case 1: // All
//      if ($CampMode == 1) echo fm_hidden('EnableCamp',$Sidey['EnableCamp']);
      echo "<tr><td $campxtr>Camping numbers:" . 
        fm_number1('Fri',$Sidey,'CampFri', $campxtr,$campxtr2) . 
        fm_number1('Sat',$Sidey,'CampSat', $campxtr,$campxtr2) . 
        fm_number1('Sun',$Sidey,'CampSun', $campxtr,$campxtr2);
      break;
          
    case 3: // Chip style
      $syid = (isset($Sidey['syId'])?$Sidey['syId']:-1);
      $CampSites = Gen_Get_All('Campsites');
      $CampTypes = Gen_Get_All('Camptypes');
      $CampUse = Gen_Get_Cond('CampUse',"SideYearId=$syid");
      $CampU = $Camp = [];
      foreach ($CampUse as $CU) {
        $CampU[$CU['CampSite']][$CU['CampType']] = $CU['Number'];
        $Camp[$CU['CampSite']] = 1;
      }
 
      echo "<tr><td>Camping numbers:<td colspan=4>(numbers of tents, caravans etc not people)";

      foreach ($CampSites as $CS) {
        $Add = '';
        $Csi = $CS['id'];
        if ($CS['Props'] & 2) {
          continue;
          $Add = ' class=NotCSide ';
          if (($Mode == 0) && empty($Camp[$Csi])) continue;
        }

        echo "<tr $Add><td $Add>" . $CS['Name'] . (!empty($CS['Comment']) ? (' (' . $CS['Comment'] . ")"):'');
        foreach($CampTypes as $CT) {
          $CTi =  $CT['id'];
          $Cur['Number'] = (isset($CampU[$Csi][$CTi]) ? $CampU[$Csi][$CTi] : 0);
          echo fm_number1($CT['Name'],$Cur,'Number',$Add,$Add,"CampSite:$syid:$Csi:$CTi");
        }
      }
/*     
      echo "<tr><td>Camping numbers:<td colspan=4>(numbers of tents, caravans etc not people)";
      $syid = (isset($Sidey['syId'])?$Sidey['syId']:0);
      foreach ($CampSites as $CS) {
        $Add = (($CS['Props'] & 2)?' class=NotCSide ':'');
        $Csi = $CS['id'];
        echo "<tr $Add><td $Add>" . $CS['Name'] . (!empty($CS['Comment']) ? (' (' . $CS['Comment'] . ")"):'');
        foreach($CampTypes as $CT) {
          $CTi =  $CT['id'];
          $Cur = Gen_Get_Cond1('CampUse',"CampSite=$Csi AND SideYearId=$syid AND CampType=$CTi");
          if (!isset($Cur['Number'])) $Cur['Number'] = 0;
          echo fm_number1($CT['Name'],$Cur,'Number',$Add,$Add,"CampSite:$syid:$Csi:$CTi");
        }
      }*/
      break;
    }
    
    if (Feature('CampControl')) {

      if ($campxtr) {
        echo "<tr><td $campxtr>Camping numbers:" . fm_number1('Fri',$Sidey,'CampFri',$campxtr," onchange=CheckContract()") . 
                  fm_number1('Sat',$Sidey,'CampSat',$campxtr,) . fm_number1('Sun',$Sidey,'CampSun',$campxtr," onchange=CheckContract()");
      } else {

        $pcamp = " Class=CampDay " . ((isset($Sidey['EnableCamp']) && $Sidey['EnableCamp'])? '' : ' hidden');         
        echo "<td $pcamp>Camping numbers:" . fm_number1('Fri',$Sidey,'CampFri',$pcamp," onchange=CheckContract()") . 
              fm_number1('Sat',$Sidey,'CampSat',$pcamp," onchange=CheckContract()") . fm_number1('Sun',$Sidey,'CampSun',$pcamp," onchange=CheckContract()");
      }
    }
  } else if ($CampMode || $Sidey['TotalFee'] || $Sidey['OtherPayment'] || $Sidey['EnableCamp']) {
    switch ($CampMode) {
    case 0: // None
      break;
      
    case 1:
      echo fm_hidden('EnableCamp',$Sidey['EnableCamp']);
      echo "<tr><td>Camping numbers:" . fm_number1('Fri',$Sidey,'CampFri') . fm_number1('Sat',$Sidey,'CampSat') . fm_number1('Sun',$Sidey,'CampSun');
      break;
          
    case 2:
      echo fm_hidden('EnableCamp',$Sidey['EnableCamp']);
      if ($Sidey['CampFri'] || $Sidey['CampSat'] || $Sidey['CampSun'])  {
        echo "<tr><td>Camping numbers:";
        if ($Sidey['CampFri']) echo "<td>Friday: " . $Sidey['CampFri'];
        if ($Sidey['CampSat']) echo "<td>Saturday: " . $Sidey['CampSat'];
        if ($Sidey['CampSun']) echo "<td>Sunday: " . $Sidey['CampSun'];
      }
      break;   
    case 3: // Chip style
      $syid = (isset($Sidey['syId'])?$Sidey['syId']:-1);
      $CampSites = Gen_Get_All('Campsites');
      $CampTypes = Gen_Get_All('Camptypes');
      $CampUse = Gen_Get_Cond('CampUse',"SideYearId=$syid");
      $CampU = $Camp = [];
      foreach ($CampUse as $CU) {
        $CampU[$CU['CampSite']][$CU['CampType']] = $CU['Number'];
        $Camp[$CU['CampSite']] = 1;
      }
 
      echo "<tr><td>Camping numbers:<td colspan=4>(numbers of tents, caravans etc not people)";

      foreach ($CampSites as $CS) {
        $Add = '';
        $Csi = $CS['id'];
        if ($CS['Props'] & 2) {
          $Add = ' class=NotCSide ';
          if (($Mode == 0) && empty($Camp[$Csi])) continue;
        }

        echo "<tr $Add><td $Add>" . $CS['Name'] . (!empty($CS['Comment']) ? (' (' . $CS['Comment'] . ")"):'');
        foreach($CampTypes as $CT) {
          $CTi =  $CT['id'];
          $Cur['Number'] = (isset($CampU[$Csi][$CTi]) ? $CampU[$Csi][$CTi] : 0);
          echo fm_number1($CT['Name'],$Cur,'Number',$Add,$Add,"CampSite:$syid:$Csi:$CTi");
        }
      }
      break;
    }
    
    if (!empty($Sidey['TotalFee'])) echo "<tr><td>Fee:<td>&pound;" . $Sidey['TotalFee'] . fm_hidden('TotalFee',$Sidey['TotalFee']);
    if (!$Wide) echo "<tr>";
    if (!empty($Sidey['OtherPayment'])) echo fm_text('Other payments',$Sidey,'OtherPayment',1,'disabled readonly');
    if (isset($Sidey['Rider']) && strlen($Sidey['Rider']) > 5)  echo "<tr>" . fm_textarea('Additional Riders',$Sidey,'Rider',2,1,'','disabled') ."\n";
  }
//  echo "<tr><td colspan=8>BEFORE";
  if ((isset($Sidey['TotalFee']) && $Sidey['TotalFee']) || 
      (isset($Sidey['OtherPayment']) && $Sidey['OtherPayment']) || 
      ($Sidey['YearState'] == $Book_State['Contract Sent']) ||
      (isset($Sidey['ContractAnyway']) && $Sidey['ContractAnyway'])) { // Contract if there is a fee

// Events - RO to Act, RW to ctte
//var_dump($snum,$year);
    $Evs = Get_Events4Act($snum,$year);
    $HasPark = '';
    $ParkedLocs = array();
//var_dump($Evs);
//var_dump($Evs);
    if ($Evs) {
      $Venues = Get_Real_Venues(1);
//      $ETs = Get_Event_Types(0);
      echo "<tr class=ContractShow hidden><td colspan=5>Click on the Event Names below for more detailed information.";
      if ($Mode==2) echo "Direct editing of some fields will be possible soon"; //TODO
      echo "<tr class=ContractShow hidden><td>Event Name<td>Date<td>Set up from<td>Start<td>Duration (mins)<td colspan=3>Where\n";
      foreach($Evs as $e) {
        $Detail = ($Mode?"EventAdd":"EventShow");
        $vv = $e['Venue'];
        if ($e['SubEvent'] < 0) { $End = $e['SlotEnd']; } else { $End = $e['End']; }
        if (($e['Start'] != 0) && ($End != 0) && ($e['Duration'] == 0)) $e['Duration'] = timeadd2real($End, - $e['Start']);
        echo "<tr class=ContractShow hidden><td><a href=$Detail?e=" . $e['EventId'] . ">" . $e['SN'] . "</a>";
//        echo "<td>" . $ETs[$e['Type']];
        echo "<td>" . FestDate($e['Day'],'L');
        echo "<td>" . ($e['Start']? ( timecolon(timeadd2($e['Start'],- $e['Setup']) )) : "TBD" ) ;
        echo "<td>" . ($e['Start']?timecolon($e['Start']):"TBD");
        echo "<td>" . ($e['Duration']?$e['Duration']:"TBD"); 
        echo "<td colspan=3>" . ($vv?(Venue_Parents($Venues,$vv) . "<a href=VenueShow?v=$vv>" . SName($Venues[$vv]) . "</a>"):"TBD") . "\n";
        if ($vv && $Venues[$vv]['Parking']) {
          if (!isset($ParkedLocs[$vv])) {
            if ($HasPark) $HasPark .= ", ";
            $ParkedLocs[$vv]++;
            $HasPark .= SName($Venues[$vv]);
          }
        }
      } 
      echo "<tr class=ContractShow hidden><td colspan=7>&nbsp;";
    }
  

// Contract - RO to Act, Confirmed ACT only
// Mode 0 - IF Booked - View Contract, IF Contract Ready - View Contract, Confirm Contract, IF Other & EVs - View DRAFT contract
//              If old contracts, link to old contracts and link to diff old/current, Confirm button -> conf by click
// Mode 1 - If Booked - View Contract, Else view DRAFT Contract
//              If Contract Ready - Confirm by Email radio button
//              If old contracts, link to old contracts and link to diff old/current
//

    $old = 0;
    if (!isset($Sidey['Contracts'])) $Sidey['Contracts']=0;
    switch ($Sidey['YearState']) {
      case $Book_State['Contract Signed']:
        if ($Sidey['Contracts'] >= 1) $old = $Sidey['Contracts'];
        echo "<tr class=ContractShow hidden><td><a href=ViewContract?sidenum=$snum&Y=$YEAR&I=$old>View Contract</a>";

        break;
      case $Book_State['Contract Ready']:
      case $Book_State['Contract Sent']:
        echo "<tr class=ContractShow hidden><td><a href=ViewContract?sidenum=$snum&Y=$YEAR>View Proposed Contract</a>";
        if ($Sidey['Contracts'] >= 1) $old = $Sidey['Contracts'];
        break;
      case $Book_State['Booking']:
        echo "<tr class=ContractShow hidden><td><a href=ViewContract?sidenum=$snum&Y=$YEAR>View DRAFT Contract</a>";
        if ($Sidey['Contracts'] >= 1) $old = $Sidey['Contracts'];
        break;
        
      default:
        break;
      }
    if ($old) {
      echo "<td colspan=2>View earlier contract" . ($old>1?'s':'') . ": ";
      for ($i=1;$i<=$old;$i++) {
        echo "<a href=ViewContract?sidenum=$snum&I=$i>#$i</a> ";
      } 
    }
    switch ($Sidey['YearState']) {
      case $Book_State['Contract Signed']:
        echo "<td>Contract Confirmed " .$ContractMethods[$Sidey['ContractConfirm']] . " on " . date('d/m/y',$Sidey['ContractDate']) . "\n";
        if ($Mode) {
          $E = (($Side['HasAgent'] && !$Side['BookDirect'] )?"'Agent'":"''");
          $Issue = $Sidey['Contracts']+1;
          echo "<td class=NotSide><button type=button id=BContract$snum class=ProfButton onclick=MProformaSend('Music_Contract_Reissue',$snum,"
                 . "'Contract','SendPerfEmail.php',2,$E,$Issue)" . Music_Proforma_Background('Contract') . ">Reissue Contract</button>"; 
        }
//          echo "<td><input type=submit id=orangesubmit name=ReIssue value='Reissue Contract'>";

        break;
      case $Book_State['Contract Ready']:
        $CMess = Contract_Check($snum);
        if ($CMess == '') {
          if ($Mode) {
            echo "<td colspan=2>The Contract is ready to be sent";
          } else {
            echo "<td colspan=2>The Contract is ready to be sent";
          }
        } else {
          echo "<td colspan=3>";
          if ($CMess && $Mode) { 
            echo "<span class=red>" . $CMess . "</span>"; 
          } else { 
            echo "The contract is not yet ready.";
          }
        }
        break;
      case $Book_State['Contract Sent']:
        $CMess = Contract_Check($snum);
        if ($CMess == '') {
          if ($Mode) {
            echo "<td colspan=2><input type=submit id=greensubmit name=Contract value='Confirm Contract by Receipt of Confirmation Email'>";
            echo fm_hidden('ContractDate',time());
            echo "<td colspan=2><input type=submit id=redsubmit name=Decline value='Decline Contract by Clicking Here'>";
            $E = (($Side['HasAgent'] && !$Side['BookDirect'] )?"'Agent'":'');
            $Issue = $Sidey['Contracts']+1;

            echo "<td class=NotSide><button type=button id=BContract$snum class=ProfButton onclick=MProformaSend('Music_Contract_Reissue',$snum,"
                 . "'Contract','SendPerfEmail.php',2,$E,$Issue)" . Music_Proforma_Background('Contract') . ">Reissue Contract</button>";
//            echo "<td><input type=submit id=orangesubmit name=ReIssue value='Reissue Contract'>";
          } else {
            echo "<td colspan=2><input type=submit id=greensubmit name=Contract value='Confirm Contract by Clicking Here'>";
            echo fm_hidden('ContractDate',time());
            echo "<td colspan=2><input type=submit id=redsubmit name=Decline value='Decline Contract by Clicking Here'>";
          }
        } else {
          echo "<td colspan=3>";
          if ($CMess && $Mode) { 
            echo "<span class=red>" . $CMess . "</span>"; 
          } else { 
            echo "The contract is not yet complete, and hence can not be confirmed";
          }
        }
        break;
      case $Book_State['Booking']:
        $CMess = Contract_Check($snum);
        if (1 || $CMess != '') {
          echo "<td colspan=3>";
          if ($Mode) { echo "The contract is not ready because: <span class=red>" . $CMess . "</span>"; }
          else { echo "The contract is not yet complete, and hence can not be confirmed"; }
        }
        break;
    
      default:
        break;
    }
  } else if ($Mode && ($Sidey['YearState'] == $Book_State['Booking'])) {
    echo "<tr><td class=NotDSide colspan=4>No Contract is currently required - you need to either set a fee or to indicate 'Need a contract even if no fee:'";
  }

  // INsurance
  
  if (Feature('PublicLiability')) if (!$ShowAvailOnly) echo fm_DragonDrop(1, 'Insurance','Sides',$snum,$Sidey,$Mode,'',(($NotD || $Mstate || $Mode)),$Imp);

  if (isset($_REQUEST['DUMP'])) {
    echo "<tr><td colspan=6>Dump:";
    var_dump($Side); // Diagnostic code on debug path
    echo "<P>";
    var_dump($Sidey); // Diagnostic code on debug path
  }

  if (($Sidey['SponsoredBy'] ?? 0)) {
    echo "<tr>";
    SponsoredByWho($Sidey,$Side['SN'],3,$snum);
  }
   
  $ntxt = 'Notes (Do <b>NOT</b> use this for questions.<br>if not answered by the ';
  if ($Side['IsASide']) {
    $ntxt .= "<a href=DanceFAQ>Dance FAQ</a>";
    if ($NotD) $ntxt .= "<br> or the ";
  }
  if ($NotD) $ntxt .= "<a href=MusicFAQ>Music FAQ</a>";
  $ntxt .= '<br>please send in an email)';

  echo "<tr>" . fm_textarea($ntxt,$Sidey,'YNotes',8,2);


  if ($Mode) echo "<tr>" . fm_textarea('Private Notes',$Sidey,'PrivNotes',8,2,'class=NotSide','class=NotSide');

  echo "</table></div>\n";
}
/*
1) imp - Problems doing it there is code in js/Participants 
5) Other days revise TODO
8) 

*/
