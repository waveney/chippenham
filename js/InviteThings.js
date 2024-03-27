// Things for various pages


// InviteDance

function ChangeInvite(ev) {

//  if (DoingTableSort) return;
  var id=ev.target.id;
  var snumm = id.match(/Invite(\d*)/);
  var snum = snumm[1];
  var iv = ev.target.value;
  var year = $("#Year").val();
  $("#InformationPane").load("setfields.php", "I=" + snum + "&O=Y&F=Invite&V=" + iv + "&Y=" + year);
  if (iv != 3 && iv != 0) {
    $("#Invie" + snum).show();
  } else $("#Invie" + snum).hide();
}

function ReportTed(ev) {

//  if (DoingTableSort) return;
  var id=ev.target.id;
  var snumm = id.match(/(\d+)/);
  var snum = snumm[1];
  var year = $("#Year").val();
  $("#Vited" + snum).load("setfields.php", "I=" + snum + "&O=I&Y=" + year);
}

// var ProformasSent = 1;

function ProformaSend(name,snum,label,link,AlwaysBespoke=0,AltEmail='',UpdateId='') {
  var year = $("#Year").val();
  if (UpdateId == '') UpdateId = "Vited" + snum;
  
  if ($('#BespokeM').is(':visible') && (AlwaysBespoke == 0)) {
    $("#DebugPane").load("sendproforma.php", "I=" + snum + "&N=" + name +"&E=" +AltEmail);
    $("#" + UpdateId).load("setfields.php", "I=" + snum + "&O=I&Y=" + year + "&L=" + label); //, function() {$("#Vited" + snum).scrollTop(1E6+ProformasSent*100)});
  } else {
    var newwin = window.open((link + "?id=" + snum + "&N=" + name + "&L=" + label + "&E=" + AltEmail),"Bespoke Message " + snum);
    newwin.onbeforeunload = function(){
      setTimeout(function(){$("#" + UpdateId).load("setfields.php", "I=" + snum + "&O=R&F=Invited"); // , function() {
//       $("#" + UpdateId).scrollTop(1E6+ProformasSent*100)}
      },500);
    };
  }

//  ProformasSent++;
}

function MList_ProformaSend(name,snum,label,link,AlwaysBespoke=0,AltEmail='',UpdateId='') { // Actions on List Pages
  var year = $("#Year").val();
  if (UpdateId == '') UpdateId = "Vited" + snum;
  
  if ($('#BespokeMess').is(':visible') || AlwaysBespoke>0) {
    var newwin = window.open((link + "?I=" + snum + "&N=" + name + "&L=" + label + "&E=" + AltEmail),"Bespoke Message " + snum);
    newwin.onbeforeunload = function(){ // the callback should change YearState
      setTimeout(function(){
        $("#" + UpdateId).load("setfields.php", "I=" + snum + "&O=R&F=Invited"); // Read the messages - have been updated by window call
//        $.get("setfields.php", "I=" + snum + "&O=Z&Y=" + year, function(data) { $("#BookState" + snum).replaceWith(data);});

        $("#BookState" + snum).load("setfields.php", "I=" + snum + "&O=Z&Y=" + year);  // needs to change what it loads into
      },500);};
  } else {
  
    $("#DebugPane").load("sendMproforma.php", "I=" + snum + "&N=" + name +"&E=" +AltEmail); // the callback should change YearState
    setTimeout(function(){
      $("#" + UpdateId).load("setfields.php", "I=" + snum + "&O=R&F=Invited"); // Read the messages - have been updated by sendMproforma
//    $.get("setfields.php", "I=" + snum + "&O=Z&Y=" + year, function(data) { $("#BookState" + snum).replaceWith(data);});
      $("#BookState" + snum).load("setfields.php", "I=" + snum + "&O=Z&Y=" + year); // needs to change what it loads into
    },500);
  }
}

function MProformaSend(name,snum,label,link,AlwaysBespoke=0,E='') { // Actions on Performer page
  var year = $("#Year").val();
  
  if (AlwaysBespoke == 2) {
    var newwin = window.open((link + "?I=" + snum + "&N=" + name + "&L=" + label + (E?"&E=" + E + 'Email':'')),"Bespoke Message " + snum);
    newwin.onbeforeunload = function(){ // the callback should change YearState
      setTimeout(function(){
        $("#Invited").load("setfields.php", "I=" + snum + "&O=R&F=Invited"); // Read the messages - have been updated by window call
        $('input[name="YearState"]').filter("[value='5']").prop('checked', true); 
      },500);};
  } else {  
    $("#DebugPane").load("sendMproforma.php", "I=" + snum + "&N=" + name + (E?"&E=" + E + 'Email':'')); // the callback should change YearState
    $("#Invited").load("setfields.php", "I=" + snum + "&O=R&F=Invited"); // Read the messages - have been updated by sendMproforma
    $('input[name="YearState"]').filter("[value='5']").prop('checked', true); 
  } 
}

function ProformaVolSend(name,id,code) {
  $("#Debug").load("sendVproforma.php", "I=" + id + "&N=" + name + "&C=" + code); 
  $('#VolSendEmail' + code + id).hide();
  $('#MessMap' +id).load("setvfields.php", "I=" + id + "&O=VYM" + "&C=" + code);
}

function Add_Bespoke() {
  $('.ProfButton').addClass('BespokeBorder');
  $('.ProfSmallButton').addClass('BespokeBorder');
  $('.Bespoke').toggle();
}

function Remove_Bespoke() {
  $('.BespokeBorder').removeClass('BespokeBorder');
  $('.Bespoke').toggle();
}

$(document).ready(function() {
  Add_Bespoke();
} );

function TicketsCollected(id,c=1) { // c is for later changes
  if (c) {
    $("#Collect" + id).load("setfields.php", "I=" + id + "&O=PC");
    setTimeout(function(){
      $("#Oops" + id).fadeOut(3000);
    },10000);
  } else {
    $("#Collect" + id).load("setfields.php", "I=" + id + "&O=NC");  
  }
}

function VTicketsCollected(id,c=1) { // c is for later changes
  if (c) {
    $("#Collect" + id).load("setvfields.php", "I=" + id + "&O=VC");
    setTimeout(function(){
      $("#Oops" + id).fadeOut(3000);
    },10000);
  } else {
    $("#Collect" + id).load("setvfields.php", "I=" + id + "&O=VNC");  
  }
}

