function blurbedit(ev) { // Not currently used
//  setTimeout( function(ev) {
   var stuff = document.getElementById('Blurb').value;
   var onefifty = stuff.substring(0,150);
   var m = onefifty.match(/^([\s\S]*?[.?!])\s/);
   var sent = (m ? m[1] : onefifty);
   document.getElementById('FirstBlurb').textContent = sent;   
//  }, 5000);
}

function nameedit(ev) {

//  if (document.getElementById("SN").value.length > 20 ) {
  var v = $("#SN").val();
  if ($('#ShortName')) {
    if (v.length > 20 ) {
      var r = v.substring(0,20);
      if ($('#ShortName').attr("hidden")) {
          $('#ShortName').val(r);
      }
      $(".ShortName").removeAttr("hidden");
    }
  }
}

function ShowAdv(ev) {
  if ($('#ShowMore').text() == 'More features') {
    $('.Adv').show();
    $('#ShowMore').text('Less features');
  } else {
    $('.Adv').hide();
    $('#ShowMore').text('More features');
  }
}

// Where el is the DOM element you'd like to test for visibility
function isHidden(el) {
  return (el === null || el.offsetParent === null)
}

function updateimps() {
//  debugger;
  if (document.getElementById('ImpC') == undefined) return; 
  var imps=0;
  var impt=4;
  if (document.getElementsByName('Mobile')[0].value.length > 8) imps++;
  if (document.getElementsByName('Address')[0].value.length > 12) imps++;
  if (document.getElementsByName('Performers')[0] != undefined) if (document.getElementsByName('Performers')[0].value > 0) imps++;
  if (document.getElementsByName('Ignored')[0] != undefined) if (document.getElementsByName('Ignored')[0].checked) imps++; // Insurance TODO Check
  if (0 && !isHidden(document.getElementById('BankDetail'))) {
    $impt++;
    if ( (document.getElementsByName('SortCode')[0].value.length >= 6) && (document.getElementsByName('Account')[0].value.length >= 8) &&
         (document.getElementsByName('AccountName')[0].value.length >= 6)) imps++;
  }

  document.getElementById('ImpC').innerHTML = imps;
  document.getElementById('ImpT').innerHTML = impt;
  if (imps == impt) { 
    $('#AllImpsDone').hide();
    $('.Imp').css('color','Black');
  } else {
    $('#AllImpsDone').show()
    $('.Imp').css('color','red');
  };
}

function AgentChange(ev) {
//  debugger;
  if ($(".AgentDetail").length) {
    var txt = ( $("[Name=HasAgent]").is(":checked"))? 'Direct Contact': 'Contact';
    document.getElementById('ContactLabel').innerHTML = txt;
    if ($("[Name=HasAgent]").is(":checked")) {
      $(".AgentDetail").show();
      document.getElementById("Help4Contact").title = "Direct Performer Contact Name";
    } else {
      $(".AgentDetail").hide();
      document.getElementById("Help4Contact").title = "Main Contact Name";
    }
  }
}

function CheckContract() {
//  debugger;
  if ((document.getElementById('TotalFee') && document.getElementById('TotalFee').value > 0 ) || 
       (document.getElementById('CampFri') && document.getElementById('CampFri').value > 0 ) || 
       (document.getElementById('CampSat') && document.getElementById('CampSat').value > 0 ) || 
       (document.getElementById('CampSun') && document.getElementById('CampSun').value > 0 ) ||
       (document.getElementById('OtherPayment') && document.getElementById('OtherPayment').value !='' )) { 
//    if ((document.getElementById('OtherPayment') && document.getElementById('OtherPayment').value !='' )) 

    $('.ContractShow').show()
  } else { $('.ContractShow').hide() }

  updateimps();
}

function ComeAnyWarning() {
  var Come = $("input[name='Coming']:checked");
  if (Come && Come.val() != 2 && ($("[Name=Fri]").is(":checked") || $("[Name=Sat]").is(":checked") || $("[Name=Sun]").is(":checked") || 
     $("[Name=Mon]").is(":checked"))) { $('#ComeAny').show(); $('#WhatDays').hide() } 
  else if (Come && Come.val() == 2 && (!$("[Name=Fri]").is(":checked") && !$("[Name=Sat]").is(":checked") && !$("[Name=Sun]").is(":checked")  && 
     !$("[Name=Mon]").is(":checked"))) { $('#ComeAny').hide(); $('#WhatDays').show() } 
  else { $('#ComeAny').hide(); ; $('#WhatDays').hide() }
} 

function CheckDiscount() {
  debugger;
//  if ($("input[name='FreeAdultTicket']:checked")) return;
  var Events = 0;
  
  ['Sat','Sun','Mon'].forEach((day) => {
    if ($("[Name=" + day + "]").is(":checked")) {
      if (s = $('#' + day + 'Dance')) {
        Events += +s.val();
      }
      if (s = $("[Name=Procession" + day +"]").is(":checked")) Events++;
    }
  });
  
  var Disc=0;
  if (Events >=3 && Events <= 6) Disc = 25;
  if (Events >= 7) Disc = 50;
  
  $('#TickDiscount').text(Disc + '% discount');
}

$(document).ready(function() {
  $(".Adv").hide();

  if(! $("[Name=Fri]").is(":checked")) $('.ComeFri').hide();
  if(! $("[Name=Sat]").is(":checked")) $('.ComeSat').hide();
  if(! $("[Name=Sun]").is(":checked")) $('.ComeSun').hide();
  if(! $("[Name=Mon]").is(":checked")) $('.ComeMon').hide();
  if(! $("[Name=MFri]").is(":checked")) $('.ComeMFri').hide();
  if(! $("[Name=MSat]").is(":checked")) $('.ComeMSat').hide();
  if(! $("[Name=MSun]").is(":checked")) $('.ComeMSun').hide();
  if(! $("[Name=MSun]").is(":checked")) $('.ComeMMon').hide();
  AgentChange();
  CheckContract();
  ComeAnyWarning();
  CheckDiscount();
} );

function ComeSwitch(ev) {
  var day =ev.target.name;
  if($("[Name=" + day + "]").is(":checked")) {
    $(".Come" + day).show();
    var come = document.getElementById('Coming_states');
    come.value = 2;
  } else {
    $(".Come" + day).hide();
  }
  ComeAnyWarning(); 
  CheckDiscount(); 
}

function CopyAndSubmit(name) {
  document.getElementById(name + 'Upload').value = document.getElementById(name + 'Form').value;
  document.forms[name].submit();
}

function setStagePA(ev) {
  var yearval = (document.getElementById('Year') ? (document.getElementById('Year').value || 0) : 0);
  var typeval = document.getElementById('AutoType').value;
  var refval = document.getElementById('AutoRef').value;

  if(ev) { //($("#StagePAtext").is(":checked")) {
    $("#StagePAtextF").show();
    $(".StagePAFileF").hide();
    $("#StagePA").text("");
    $.post("formfill.php", {'D':typeval, 'F':'StagePA', 'V':'', 'Y':yearval, 'I':refval});
  } else {
    $("#StagePAtextF").hide();
    $(".StagePAFileF").show();
    $("#StagePA").text("@@FILE@@");
    $.post("formfill.php", {'D':typeval, 'F':'StagePA', 'V':'@@FILE@@', 'Y':yearval, 'I':refval});
  }
}

function PASpecChanged(ev) {
  debugger;

}

function AddBandRow(BPerR) {
//  debugger;
  var row=0;
  while (document.getElementById("BandRow" + row)) row++;
  var newrow = "<tr id=BandRow" + row + "><td>";
  for (var i=0;i<BPerR;i++) { 
    var id = "BandMember" + (BPerR*row+i) + ":0";
    newrow += "<td><input name=" + id + " id=" + id + " type=text size=16 onchange=BandChange(event) oninput=AutoInput('" + id + "') >";
  };
  newrow += "</tr>";
  $("#AddHere").before(newrow);
//  document.getElementById("BandMemRow1").rowSpan(row+1);
  return false;
}


function BandChange(ev) { 
}

function SetTradeType(p,c,i,r,d,dc,ps) {
//  debugger;
  if (p) { $('.PublicHealth').show() } else { $('.PublicHealth').hide() };
  if (c) { $('.Charity').show() } else { $('.Charity').hide() };
  $('#TTDescription').text(d);
  $('#TTDescription').css('background',dc);
  $('.DefaultPitch').text(ps);
  $('#PitchSize0').val(ps);
}
      
function PowerChange(t,i) {
//  debugger;
  if (t!=2) { 
    $('#Power' + i).val('') 
  } else { 
    $('#PowerTypeRequest' + i).attr('checked',true) 
  }
}

// Old code for deleteion
function OlapCatChange(e,l,v) {
//  debugger;
  var lmtch = l.match(/(\d*$)/);
  var olapn = lmtch[1];

  $('#OlapSide' + olapn).hide();
  $('#OlapAct' + olapn).hide();
  $('#OlapOther' + olapn).hide();
  if (v == 0) $('#OlapSide' + olapn).show();
  if (v == 1) $('#OlapAct' + olapn).show();
  if (v == 2) $('#OlapOther' + olapn).show();
}

// Now used for Events and Overlaps
function EventPerfSel(e,l,v) {
//  debugger;
  var lmtch = l.match(/.*(\d+)/);
  var i = lmtch[1];
  
  for (var p=0;p<5;p++) { // 5 needs to be number of perftypes
    if (p == v) { 
      $('#Perf' + p + '_Side' + i).show();
    } else {
      $('#Perf' + p + '_Side' + i).hide();    
    }
  }
}

function ForceOneProcession(e) {
  debugger;
  var fld = e.target.id;
  var day = (fld.match(/Procession(\w*)/))[1];
    
  if ( $("input[name='" + fld + "']:checked").val() ) {
    if ((fld != 'ProcessionSat') && ($('#ProcessionSat').length)) {
      $('#ProcessionSat').prop('checked', false);
      $('#SatDance').val(4);
      AutoCheckBoxInput('ProcessionSat');
      AutoInput('SatDance');
    }
    if ((fld != 'ProcessionSun') && ($('#ProcessionSun').length)) {
      $('#ProcessionSun').prop('checked', false);
      $('#SunDance').val(4);
      AutoCheckBoxInput('ProcessionSun');
      AutoInput('SunDance');
    }
    if ((fld != 'ProcessionMon') && ($('#ProcessionMon').length)) {
      $('#ProcessionMon').prop('checked', false);
      $('#MonDance').val(4);
      AutoCheckBoxInput('ProcessionMon');      
      AutoInput('MonDance');
    }
    $('#' + day + 'Dance').val(3);
    AutoInput(day + 'Dance');
  }
  CheckDiscount();
}

function Trader_Insurance_Upload() {
  $('#Insurance').val(1);
  document.getElementById('InsuranceButton').click();
}

