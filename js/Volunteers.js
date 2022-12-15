function isNumber(n) { return /^-?[\d.]+$/.test(n); }

var Teams = [];
var PlanYear;

function TeamsSelected() {
  Teams.forEach((team,idx)=> { team['Selected'] = $('#Props:' +team['id'] + ':' +PlanYear).is(':checked')});
  var a=1;
}

function ShowAvails() {
//  debugger;
//  TeamsSelected();
  return;
  var avs = ['Before','Week','_4','_3','_2','_1',0,1,2,3,4,5,6,7,8,9,10,11];
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

}

function Update_VolCats(cls,catid,year) {
  $('.'+cls).toggle();
  ShowAvails();
}

$(document).ready(function() {
//  debugger;
  var cats1 = $('#VolCatsRaw').val();
  var cats2 = atob(cats1);
  Teams = JSON.parse(cats2);
  
  ShowAvails();
} );



function VolScanTeams() {
//  $("#Props:*").each


}

function CampingVolSet(name) {
  if (name != 'CampNeed::2023') {
    debugger;
  }
//  var CampVal = $("input[name='CampNeed::2023']:checked").val();
  var CampVal = $("input[name='" + name + "']:checked").val();
console.info(CampVal);
  if (!CampVal || CampVal < 10) { $('#CampPUB').hide(); $('#CampREST').hide(); }
  else if (CampVal < 20) { $('#CampPUB').show(); $('#CampREST').hide(); }
  else if (CampVal < 30) { $('#CampPUB').hide(); $('#CampREST').show(); }; 
}

function VolEnables(volid,year) {
  PlanYear = year;

//  debugger;  
// Read all the Teams

// Show Avils for relevant team

// Sort out camping display

}
