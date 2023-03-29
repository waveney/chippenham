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
      setTimeout(function(){$("#" + UpdateId).load("setfields.php", "I=" + snum + "&O=R"); // , function() {
//       $("#" + UpdateId).scrollTop(1E6+ProformasSent*100)}
      },500)
    }
  }

//  ProformasSent++;
}

function MList_ProformaSend(name,snum,label,link,AlwaysBespoke=0,AltEmail='',UpdateId='') {
  var year = $("#Year").val();
  if (UpdateId == '') UpdateId = "Vited" + snum;
  
  
  if ($('#BespokeMess').is(':visible') || AlwaysBespoke>0) {
    var newwin = window.open((link + "?I=" + snum + "&N=" + name + "&L=" + label + "&E=" + AltEmail),"Bespoke Message " + snum);
    newwin.onbeforeunload = function(){ // the callback should change YearState
      setTimeout(function(){
        $("#" + UpdateId).load("setfields.php", "I=" + snum + "&O=R&F=Invited"); // Read the messages - have been updated by window call
//        $.get("setfields.php", "I=" + snum + "&O=Z&Y=" + year, function(data) { $("#BookState" + snum).replaceWith(data);});

        $("#BookState" + snum).load("setfields.php", "I=" + snum + "&O=Z&Y=" + year);  // needs to change what it loads into
      },500)};
  } else {
  
    $("#DebugPane").load("sendMproforma.php", "I=" + snum + "&N=" + name +"&E=" +AltEmail); // the callback should change YearState
    $("#" + UpdateId).load("setfields.php", "I=" + snum + "&O=R&F=Invited"); // Read the messages - have been updated by sendMproforma
//    $.get("setfields.php", "I=" + snum + "&O=Z&Y=" + year, function(data) { $("#BookState" + snum).replaceWith(data);});
    $("#BookState" + snum).load("setfields.php", "I=" + snum + "&O=Z&Y=" + year); // needs to change what it loads into
  }

//  ProformasSent++;
}

function MProformaSend(name,snum,label,link,AlwaysBespoke=0,AltEmail='',UpdateId='') {
  var year = $("#Year").val();
  if (UpdateId == '') UpdateId = "Vited" + snum;
  
  if ((AlwaysBespoke == 1) || ($('#BespokeMess').is(':visible') && (AlwaysBespoke == 0))) {
    $("#DebugPane").load("SendPerfEmail.php", "I=" + snum + "&N=" + name +"&E=" +AltEmail);
    $("#" + UpdateId).load("setfields.php", "I=" + snum + "&O=K&Y=" + year + "&L=" + label); //, function() {$("#Vited" + snum).scrollTop(1E6+ProformasSent*100)});
//    $('input[name=YearState][value=5]').attr('checked', 'checked');
    $("#BookState" + snum).replaceWith("setfields.php", "I=" + snum + "&O=Z&Y=" + year + "&F=YearState&V=5");
    
  } else {
    var newwin = window.open((link + "?I=" + snum + "&N=" + name + "&L=" + label + "&E=" + AltEmail),"Bespoke Message " + snum);
    newwin.onbeforeunload = function(){
      setTimeout(function(){$("#" + UpdateId).load("setfields.php", "I=" + snum + "&O=R"); // , function() {
//       $("#" + UpdateId).scrollTop(1E6+ProformasSent*100)}
      },500)
    }
  }

//  ProformasSent++;
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

