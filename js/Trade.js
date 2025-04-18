// Tools for Trade maps
var Dragged ;

  function RemoveGrid(src) {
  
  }

  function SetGrid(src,dst) {
    var dstmtch = dst.id.match(/Posn(\d*)/);
    var Txt = src.innerHTML; // TODO change this... not innerHTML needs some ref to pick up trader name

    var s = src.id.match(/TradeN(\d*)/);
    if (s) {
      var TradeNum = s[1];
    } else if (src.id.match(/Posn(\d*)/)) {
      var TradeNum = src.getAttribute("data-d");
      RemoveTrade(src);
    } 
    if (dstmtch) UpdateTrade(dst,TradeNum,Txt);
    $("#InformationPane").load("trupdate.php", "D=" + dst.id + "&S=" + src.id + "&I=" + TradeNum + "&A=" + $("#DayId").text() );
  }

  function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
    Dragged = ev.target;
  }


  function drop(ev) {
    debugger;
    ev.preventDefault();
    SetGrid(Dragged,ev.target);    
  }

// probably duff
  function allow(ev) {
    var dat = ev.target.getAttribute("data-d");
    if (!dat) ev.preventDefault();
  }    


function wraptorect(textnode, boxObject, padding, linePadding) {

    var x_pos = parseInt(boxObject.getAttribute('x')),
    y_pos = parseInt(boxObject.getAttribute('y')),
    boxwidth = parseInt(boxObject.getAttribute('width')),
    fz = parseInt(window.getComputedStyle(textnode)['font-size']);  // We use this to calculate dy for each TSPAN.

    var line_height = fz + linePadding;

// Clone the original text node to store and display the final wrapping text.

   var wrapping = textnode.cloneNode(false);        // False means any TSPANs in the textnode will be discarded
   wrapping.setAttributeNS(null, 'x', x_pos + padding);
   wrapping.setAttributeNS(null, 'y', y_pos + padding);

// Make a copy of this node and hide it to progressively draw, measure and calculate line breaks.

   var testing = wrapping.cloneNode(false);
   testing.setAttributeNS(null, 'visibility', 'hidden');  // Comment this out to debug

   var testingTSPAN = document.createElementNS(null, 'tspan');
   var testingTEXTNODE = document.createTextNode(textnode.textContent);
   testingTSPAN.appendChild(testingTEXTNODE);

   testing.appendChild(testingTSPAN);
   var tester = document.getElementsByTagName('svg')[0].appendChild(testing);

   var words = textnode.textContent.split(" ");
   var line = line2 = "";
   var linecounter = 0;
   var testwidth;

   for (var n = 0; n < words.length; n++) {

      line2 = line + words[n] + " ";
      testing.textContent = line2;
      testwidth = testing.getBBox().width;

      if ((testwidth + 2*padding) > boxwidth) {

        testingTSPAN = document.createElementNS('http://www.w3.org/2000/svg', 'tspan');
        testingTSPAN.setAttributeNS(null, 'x', x_pos + padding);
        testingTSPAN.setAttributeNS(null, 'dy', line_height);

        testingTEXTNODE = document.createTextNode(line);
        testingTSPAN.appendChild(testingTEXTNODE);
        wrapping.appendChild(testingTSPAN);

        line = words[n] + " ";
        linecounter++;
      }
      else {
        line = line2;
      }
    }

    var testingTSPAN = document.createElementNS('http://www.w3.org/2000/svg', 'tspan');
    testingTSPAN.setAttributeNS(null, 'x', x_pos + padding);
    testingTSPAN.setAttributeNS(null, 'dy', line_height);

    var testingTEXTNODE = document.createTextNode(line);
    testingTSPAN.appendChild(testingTEXTNODE);

    wrapping.appendChild(testingTSPAN);

    testing.parentNode.removeChild(testing);
    textnode.parentNode.replaceChild(wrapping,textnode);

    return linecounter;
}

/*
document.getElementById('original').onmouseover = function () {

    var container = document.getElementById('destination');
    var numberoflines = wraptorect(this,container,20,1);
    console.log(numberoflines);  // In case you need it

};
*/

function SetTradeType(p,c,i,r,d,dc,ps) {
//  debugger;
  if (p) { $('.PublicHealth').show(); } else { $('.PublicHealth').hide(); };
  if (c) { $('.Charity').show(); } else { $('.Charity').hide(); };
  $('#TTDescription').text(d);
  $('#TTDescription').css('background',dc);
  $('.DefaultPitch').text(ps);
  $('#PitchSize0').val(ps);
}
      
function PowerChange(t,i) {
//  debugger;
  if (t!=2) { 
    $('#Power' + i).val('') ;
  } else { 
    $('#PowerTypeRequest' + i).attr('checked',true) ;
  }
}

function Trader_Insurance_Upload() {
  $('#Insurance').val(1);
  document.getElementById('InsuranceButton').click();
}

function UpdatePower(pno, pitchfee) {
  debugger;

  /*
  var TLoc = document.getElementById('TradeLocData').value;
  var TLocCont = JSON.parse(TLoc);
  var LocSel = document.getElementById('PitchLoc' + pno).value;
  var Props = TLocCont[LocSel]['Props'];
  
  if (Props &1) {
    $('#Table0' + pno).show();
  } else {
    $('#Table0' + pno).hide();
  }
    */
//  if (pitchfee == 0) {
//    $('.Powerelems').hide();
//    return; 
//  }

  if (pitchfee < 0) pitchfee = 0;
  var powercost = tablecost = 0;
  var xtraPwr;
  
  if ((xtraPwr = document.getElementById('ExtraPowerCost'))) {
    var cst = xtraPwr.value.match(/(\d*)/);
    powercost += Number(cst[1]);
  }

  for (var stall=0;stall<3;stall++) {
    var Pselect = document.querySelector('input[name="Power' + stall + '"]:checked');
    if (Pselect) {
      var psel = Pselect.value;
      var label = document.querySelector('label[for="Power' + stall + psel + '"]').innerHTML;
      var pwr = label.match(/£(\d*)/);
      if (pwr) powercost += Number(pwr[1]);
    
      var tablebox = document.getElementById('Tables' + stall);
      if (tablebox) {
        var tables = tablebox.value;
        var label = document.querySelector('label[for="Tables' + stall + '"]').innerHTML;
        if (label) var cst = label.match(/£(\d*)/);
        if (cst) tablecost += tables * Number(cst[1]);
      }
    }
  }
  if (powercost || tablecost) {
    var cost = Number(pitchfee) + Number(powercost) + Number(tablecost);
    $('#PowerFee').text('£' + cost );
    $('.Powerelems').show();
  } else {
    $('.Powerelems').hide();
  }
  
}

function PitchNumChange(oldval) {
  if (oldval) {
    $('#PitchChangeButton').show();
  } else {
    $('#PitchAssignButton').show();  
  }
}

function FeeChange(x=0,y=0) {
  var buts = ['Quote','ArtInvite','Invite','InviteBetter'];
  var bb;
  buts.forEach((but) => { 
    if ((bb = $('#' + but + 'Button'))) {
      bb.show();
    }
  });  
  UpdatePower(x,y);
}

function CheckReQuote(tid) {
  $('#BookState').load('tradeauto.php', 'I=' + tid + '&A=RQ');
//  return;
  setTimeout(function(){
    window.location.reload();
  }, 100);
}

function MoreStalls(stall) {
  $('#Stall' + (stall+1)).show(); 
}

function EnableXtraPower() {
  $('.XtraPower').toggle();
}

function UpdateTraderInfo(t) {
  $('#TraderContent').load('tradeinfo.php', 'I=' + t); 
}

var Dragged;

function SetPitch(what,target) {
  
  
}

function CampingTradeSet() {
  var CampVal = $("input[name='CampNeed']:checked").val();
  if (!CampVal || CampVal < 10) { $('#CampPUB').hide(); $('#CampREST').hide(); }
  else if (CampVal < 20) { $('#CampPUB').show(); $('#CampREST').hide(); }
  else if (CampVal < 30) { $('#CampPUB').hide(); $('#CampREST').show(); }; 
}



function drag(ev) {
  ev.dataTransfer.setData("text", ev.target.id);
  Dragged = ev.target;
}

// Prob ok - changed
function drop(ev) {
  ev.preventDefault();
  SetPitch(Dragged,ev.target);    
}

// Need to make work for non shared use
// Grey, Big = not ok,  data-d? (not = ok) - you have a hook to allow some large event adds using drop 
function allow(ev) {
  /*var dat = ev.target.getAttribute("data-d");
  if (!dat) */
  
  ev.preventDefault();
}    

$(document).ready(function() {
  if ($('#CampNeed')) CampingTradeSet();
});

/* copied code

var nexthl = 0;
var hlights = [];
var sidlight = [];
var InfoPaneDefault = '';

// Old format Le:t:l:s
// New format Lv:t:l (L = G,S) - data-d: L:e:s:d:w L=(N,S)
// 1st line data-e:e:d, every line data data-s:s ...?

// Big Change Needed
  function RemoveGrid(loc) {
// Drops usage count, clears content, changes id to no side    
    var Side=loc.getAttribute('data-d');
    var cur = $("#SideH"+Side).text();
    if (cur) { cur--; $("#SideH"+Side).text(cur); };
    loc.innerHTML='';
    loc.classList.remove('Side' + Side);
    if (hlights[Side]) loc.classList.remove(hlights[Side]);
    loc.removeAttribute('data-d');
// if rowspan need to unhide cells below
    if (loc.getAttribute('rowspan')) {
      var dstmtch = loc.id.match(/G:(\d*):(\d*):(\d*)/);
      var gp = "G:" + dstmtch[1] + ":" + dstmtch[2] + ":";
      var t = dstmtch[2];
      var rwst = $("#RowTime" + t);
      var vens = [];
      var elem = rwst[0];
      while (elem = elem.nextSibling) vens.push((elem.id.match(/G:(\d*):(\d*):(\d*)/))[1]);

      for (var v in vens) {
        var id = "G:" + vens[v] + ":" + t + ":0";
        var nloc = document.getElementById(id);
        if (!nloc.hasAttribute("hidden") && !nloc.hasAttribute("rowspan")) {
        // check visibility of the 4 rows and work out the one to unhide
          for ( var unhide=1;unhide<4;unhide++) {
            if (document.getElementById("G:" + vens[v] + ":" + t + ":" + unhide).hasAttribute("hidden")) break;
          }
          break;
        }
      }
      for (var i=1;i<unhide;i++) document.getElementById(gp + i).removeAttribute("hidden");
      loc.removeAttribute('rowspan');
    }
  }

// Big Change needed
  function UpdateGrid(dst,Side,text) {
// Increases usage count, enters content, change id to side
    var cur = $("#SideH"+Side).text();
    if (!cur) cur = 0;
    cur++;
    $("#SideH"+Side).text(cur); 
    dst.innerHTML=text;
    dst.classList.add('Side' + Side);
    if (hlights[Side]) dst.classList.add(hlights[Side]);
    dst.setAttribute('data-d',Side);

    var datw = $("#SideN"+Side).attr("data-w");
    if (datw) {
      var dstmtch = dst.id.match(/G:(\d*):(\d*):(\d*)/);
      if (dstmtch[3] == 0) {
        dst.setAttribute('rowspan',4);
        var gp = "G:" + dstmtch[1] + ":" + dstmtch[2] + ":";
        for (var i=1;i<4;i++) document.getElementById(gp + i).setAttribute("hidden",true);
      }
    }
  }

  function CopyErrorCount() {
    var src = $("#DanceErrsSrc");
    var dst = $("#DanceErrsDest")
    
    dst.html(src.html());
  }


// Big Change needed
  function SetGrid(src,dst,sand) {
    var dstmtch = dst.id.match(/G:(\d*):(\d*):(\d*)/);
    var Txt = src.innerHTML;

    var s = src.id.match(/SideN(\d*)/);
    if (s) {
      var SideNum = s[1]; 
    } else if (src.id.match(/G:(\d*):(\d*):(\d*)/)) {
      var SideNum = src.getAttribute("data-d");
      RemoveGrid(src);
    } 
    if (dstmtch) UpdateGrid(dst,SideNum,Txt);
    if (!sand) $("#InformationPane").load("dpupdate.php", "D=" + dst.id + "&S=" + src.id + "&I=" + SideNum + "&A=" + $("#DayId").text() + "&E=" + 
                        $("input[type='radio'][name='EInfo']:checked").val(), CopyErrorCount       );
  }
  
// Prob working new
  function UpdateInfo(cond) {
    $("#InformationPane").load("dpupdate.php", "E=" + $("input[type='radio'][name='EInfo']:checked").val(),CopyErrorCount );
  }

  function SaveAndUpdateInfo() {
    $("#InformationPane").load("dpupdate.php", "P=S&E=" + $("input[type='radio'][name='EInfo']:checked").val(),CopyErrorCount );
  }

// Working on New
  $(document).ready(function() {
    $("#Grid").tableHeadFixer({'left':1});
    UpdateInfo();
  } );

// Prob ok - changed ?? .id
  function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
    Dragged = ev.target
  }

// Prob ok - changed
  function drop(ev,sand) {
    ev.preventDefault();
    SetGrid(Dragged,ev.target,sand);    
  }

// Need to make work for non shared use
// Grey, Big = not ok,  data-d? (not = ok) - you have a hook to allow some large event adds using drop 
  function allow(ev) {
    var dat = ev.target.getAttribute("data-d");
    if (!dat) ev.preventDefault();
  }    

// Should work on New as unchanged
  function dispinfo(t,s) {
    if (InfoPaneDefault == '') InfoPaneDefault = $("#InfoPane").html();
    $("#InfoPane").load("dpinfo.php", "S=" + s + "&T=" + t);
  }

// Works on New
  function highlight(id) {
    var oc=hlights[id];
    if (oc) {
      $('.'+oc).removeClass(oc);
      hlights[id]='';
    } else {
      $('.BGColour'+nexthl).removeClass('BGColour'+nexthl);
      if (sidlight[nexthl]) $('#SideHL' + sidlight[nexthl]).prop("checked",false);
      $('.Side'+id).addClass('BGColour'+nexthl);
      hlights[id] = 'BGColour' + nexthl;
      sidlight[nexthl] = id;
      nexthl++;
      if (nexthl>7) nexthl=0;
    }
  }

// New Code
  function UnhideARow(t) {
// search venues to find non hidden td, not wrapped, search lines to find hidden line
//  each venue, if line above not hidden unhide
    var rwst = $("#RowTime" + t);
    var vens = [];
    var elem = rwst[0];
    while (elem = elem.nextSibling) vens.push((elem.id.match(/G:(\d*):(\d*):(\d*)/))[1]);

    for (var v in vens) {
      var id = "G:" + vens[v] + ":" + t + ":0";
      var loc = document.getElementById(id);
      if (!loc.hasAttribute("hidden") && !loc.hasAttribute("rowspan")) {
        // check visibility of the 4 rows and work out the one to unhide
        for ( var unhide=1;unhide<4;unhide++) {
          if (document.getElementById("G:" + vens[v] + ":" + t + ":" + unhide).hasAttribute("hidden")) break;
        }
        break;
      }
    }
    for (var v in vens) {
      var id = "G:" + vens[v] + ":" + t + ":";
        if (!document.getElementById(id + (unhide-1)).hasAttribute("hidden")) document.getElementById(id + unhide).removeAttribute("hidden");
//      if (!document.getElementById(id + 0).hasAttribute("rowspan")) document.getElementById(id + unhide).removeAttribute("hidden");  // Duff
    };
    if (unhide == 3) {
      $('#AddRow' + t).hide();
    }
  }

function infoclose(e) {
  $("#InfoPane").html(InfoPaneDefault);
}

function ClearHL() {
  for (var hl=0; hl<8;hl++) {
    if (sidlight[hl]) {
      var oc=hlights[sidlight[hl]];
      $('.'+oc).removeClass(oc);
      hlights[id]='';
    }
  }
  sidlight = [];
  nexthl = 0;
} */
