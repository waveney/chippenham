function isNumber(n) { return /^-?[\d.]+$/.test(n); }

function ShowAvails() {
  debugger;
  return;
  var avs = ['Before','Week','_4','_3','_2','_1',0,1,2,3,4,5,6,7,8,9,10,11];
  avs.forEach((X,n)=>{
    $('#TRAvail'+X).show();
    var teams = $('#Teams'+X); // Testing
    var ht = teams.children(":visible").text()
    if (!ht) $('#TRAvail'+X).hide();
  });

}

function Update_VolCats(cls,catid,year) {
  $('.'+cls).toggle();
  ShowAvails();
}

$(document).ready(function() {
  ShowAvails();
} );


