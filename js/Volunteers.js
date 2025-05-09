function isNumber(n) { return /^-?[\d.]+$/.test(n); }

var Teams = [];
var PlanYear;

function TeamsSelected() {
  Teams.forEach((team)=> { team['Selected'] = $('#Props:' +team['id'] + ':' +PlanYear).is(':checked');});
//  var a=1;
}

function ShowAvails() {
//  debugger;
//  TeamsSelected();
  return;
/*  var avs = ['Before','Week','_4','_3','_2','_1',0,1,2,3,4,5,6,7,8,9,10,11];
  avs.forEach((X,n)=>{
    $('#TRAvail'+X).hide();
    var teams = $('#Teams'+X).children(":visible"); // Testing
    if (teams) { // not an array...
      teams.forEach((txt)=> {
        if (txt.text()) { 
          $('#TRAvail'+X).show();
        }
      });
    }   
  });
*/
}

function Update_VolCats(e,cls,catid,year) {
  var Mgr = document.getElementById('VolManager').value;
  
  var Val = (Mgr? + e.target.value:e.target.checked);
  if (Val) {
    $('.'+cls).show();
    var cat = Teams[catid];
    if (cat['FormGroup']) {
      $('.CatGroup' + cat['FormGroup']).show();
    }
    if (cat['Props'] & 0x100000) {
      $('.NeedAvail').show();
    } else {
      $('.NeedDept').show();
    }
    $('.NoTeams').hide();
  } else {  
    $('.'+cls).hide();
    var cat = Teams[catid];
    if (cat['FormGroup']) {
      var Gs =0 , NeedAv =0, NeedDp = 0;
      for (var i in Teams) { 
        var statuse = document.getElementById('Status:'+Teams[i].id+':'+year);
        var select = (Mgr? (statuse && (statuse.value > 0)): (statuse && statuse.checked));

        if ((Teams[i].Props & 1) && select ) {
          if (((Teams[i].Props & 0x200000) ==0) && (Teams[i].FormGroup == cat['FormGroup'])) Gs = 1;
          if (Teams[i].Props & 0x100000) {
             NeedAv = 1;
          } else {
             NeedDp = 1;
          } 
        }
      }
      
      if (!Gs) $('.CatGroup' + cat['FormGroup']).hide();
      if (NeedAv) { $('.NeedAvail').show()} else {$('.NeedAvail').hide()};
      if (NeedDp) { $('.NeedDept').show() } else {$('.NeedDept').hide()};
      if (NeedAv || NeedDp ) {
        $('.NoTeams').hide();
      } else {
        $('.NoTeams').show();
      }
    } 
  }
}

/*
function Update_VolMgrCats(e,cls,catid,year) {
debugger;
  var Val = e.target.value;
  if (Val) {
    $('.'+cls).show();
    var cat = Teams[catid];
    if (cat['FormGroup']) {
      $('.CatGroup' + cat['FormGroup']).show();
    }
  } else {  
    $('.'+cls).hide();
    var cat = Teams[catid];
    if (cat['FormGroup']) {
      $Gs = 0;
      Teams.forEach(function(C){ 
        if (C['FormGroup'] == cat['FormGroup']) {
          var vis = document.getElementById('Status:'+C['id']+':'+year).value;
          if (vis) $Gs = 1;
        }
      })
      
      if (!$Gs) $('.CatGroup' + cat['FormGroup']).hide();
    } 
  }
}*/

$(document).ready(function() {
//  debugger;
  var cats1 = $('#VolCatsRaw').val();
  var cats2 = atob(cats1);

  Teams = JSON.parse(cats2);
});

  
//  ShowAvails();


function VolScanTeams() {
//  $("#Props:*").each


}

function CampingVolSet(nam) {
//  debugger;
  var CampVal = $("input[name='" + nam + "']:checked").val();
  if (!CampVal || CampVal < 10) { $('#CampPUB').hide(); $('#CampREST').hide(); }
  else if (CampVal < 20) { $('#CampPUB').show(); $('#CampREST').hide(); }
  else if (CampVal < 30) { $('#CampPUB').hide(); $('#CampREST').show(); }; 
}

/*
function VolEnables(volid,year) {
  PlanYear = year;

//  debugger;  
// Read all the Teams

// Show Avils for relevant team

// Sort out camping display

}*/

function VolListFilter() {
// debugger;
  var Show = $("input[name=ThingShow]:checked").val();
//  var dbg = document.getElementById('Debug');
  $(".Volunteer").each(function() {
    if (Show == 0) $(this).show();
    if (Show > 0) {
      if ($(this).hasClass("VolCat" + Show)) { $(this).show(); } else { $(this).hide(); };
    }
  });
  
  for (var i in Teams) { 
    var catid = Teams[i].id;
    if (Show == 0 || Show==catid) {
      $('.Cat' + i).show();
    } else { 
      $('.Cat' + i).hide();
    }
  }
}

var Clickids = [];

function AcceptTeam(id,catid) {
  var now = Date.now();
  var clid = id + '/' + catid;
  if (Clickids[clid] > (now -10000) ) return; // 10 Second window
  Clickids[clid]= now;  
  
  // Call Volunteer with appropriate paras  
  $("#YearStatus" + id).load("volaction.php", "A=Accept1&id=" + id + "&Catid=" + catid);
  // Hide all A buttons $("[id^=jander]")
  
  $(".Accept" + id).hide();
  $("#Wanted" + id + 'CAT' + catid).text('Y');
}

var SelectedAvail = 0;

function AvailDisp(Code) {
  $('#Avail' + SelectedAvail).removeClass('AvSelect');
  SelectedAvail = Code;
  $('#Avail' + SelectedAvail).addClass('AvSelect');

  switch (Code) {
    case 0: 
      $('.AvailD1').hide();
      $('.AvailD2').hide();
      $('.AvailD3').hide();
      break;

    case 1: 
      $('.AvailD1').show();
      $('.AvailD2').hide();
      $('.AvailD3').show();
      break;

    case 2: 
      $('.AvailD1').hide();
      $('.AvailD2').show();
      $('.AvailD3').show();
      break;

    case 3: 
      $('.AvailD1').show();
      $('.AvailD2').show();
      $('.AvailD3').show();
      break;

  }
}
