// Sponsors on home page

  // Read sponsors into array
  // Work out width of winow and hence number of icons
  // randomly choose icons
  // setup periodic callback
  // choose random icon and random position to update
  // need record of icons in use
$(document).ready(function() {
  var SponUse = [];
  var SponPos = [];
  var Spons;
  var maxi;
  var recent = -1;
  var ChangeTime = +$('#ChangeTime').val();
  var SponIndexes = [];
  var Spindexes = 0;
  
  
  function Get_ASpon() {
    return SponIndexes[Math.floor(Math.random() * SponIndexes.length)];
  }

  function SetupSpons() {
    Spons = $('.SponsorsIds');
    if (!Spons) return 0;
    SponUse = [];
    SponPos = [];
    SponIndexes = [];
    Spindexes = 0;
    
    // Build indexes
    Spons.each(function(spid){
      var imptnc = $(this).attr("data-i");
      for(var i=0; i<imptnc; i++) SponIndexes[Spindexes++] = spid;
    });
    
    var wid = $('#SponDisplay').width();
    for(i=0;(i+1)*170<wid;i++) {
      var elem = Get_ASpon();
      var tries=1;
      while (SponUse[elem] && tries++ <10) elem = Get_ASpon();
      SponUse[elem]=i+1;
      $('#SponsorRow').append( "<td id=#SponPos" + i + " class=HomePageSponsors>" + Spons[elem].innerHTML );
      SponPos[i] = elem;
      maxi=i;
    }
    return 1;
  }
  
  function UpdateSpon() {
    var pos = Math.floor(Math.random() * (maxi+1));
    if (pos == recent) pos = Math.floor(Math.random() * maxi);
    var elem = Get_ASpon();
    var tries=1;
    while (SponUse[elem] && tries++ <5) elem = Get_ASpon();
    SponUse[SponPos[pos]] = 0;
    SponPos[pos] = elem;
    SponUse[elem] = pos+1; 
    var pn = '#SponPos' + pos;
    document.getElementById(pn).innerHTML = Spons[elem].innerHTML;
    setTimeout(UpdateSpon,ChangeTime);  
    recent = pos;
  }

  function Resize() {
    $('#SponsorRow').empty();
    SetupSpons();
  };

  if (SetupSpons()) {
    window.addEventListener('resize',Resize);
    setTimeout(UpdateSpon,ChangeTime);  
  }
});

// 
