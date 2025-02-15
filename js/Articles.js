// Article Display
$(document).ready(function() {
  
  var MinWidth = 300;
  var MaxWidth = 600;
  var PadWidth = 20;
  var MaxColns = 99;
  
  var OrigArt = $('#OrigArt');
  if (!OrigArt) return; 
  
  function SetupArts() {
    // Works out columns
    var Show = $('#ShowArt');
    var WorkWidth = Show.width();
    var ColCount = 1;
    var Cols =[];
    
    if (WorkWidth > (2*MinWidth+PadWidth)) ColCount = Math.min(Math.floor(WorkWidth/(MinWidth+PadWidth)),MaxColns);
    var ActOutColWidth = Math.min(Math.floor((WorkWidth - ColCount*PadWidth)/ColCount) - 5,MaxWidth);
    var ActColWidth = ActOutColWidth-PadWidth*2;

    // Create columns
    Show.append('<div class=ArtBanner id=ArtBanners></div><div id=ArtCols></div>');
    for(var i=1; i<=ColCount; i++) {
      $('#ArtCols').append('<div id=ArtCol' + i +' class=ArtColClass></div>');
      Cols[i] = $("#ArtCol" +i);
      };
    $('.ArtColClass').width(ActOutColWidth);

    // go through each Art, create in shortest col - fudge image stats
    var ArtNum = 0;
    var Art;
    while ((Art = document.getElementById('Art' + ArtNum))) {
      var Col = Cols[1];
      for (i=2; i<=ColCount; i++) if (Cols[i].height() < Col.height()) Col = Cols[i];
      var clone = Art.outerHTML;
      clone = clone.replace(/id="Art/g,'id="SArt');
      switch ($(Art).data('format')) {
      case 0: // Large - image display width is actcolwidth, image display height = acthieght*actcolwith/origheight
        var cloneimg = $('#ArtImg' + ArtNum);
        var imgwd = cloneimg.data('width');
        var imght = cloneimg.data('height');
        var newwidth = ActColWidth;
        var newheight = Math.floor(imght*newwidth/imgwd);
        clone = clone.replace(/class="ArtImageL"/,'class="ArtImageL" width=' + newwidth + ' height=' + newheight);
        Col.append(clone); 
        break;
        
      case 1: // Small- image display width is actcolwidth*.45, image display height = acthieght*actcolwith*.45/(origheight) - img =45%, pad =5% txt =50%
        var cloneimg = $('#ArtImg' + ArtNum);
        var imgwd = cloneimg.data('width');
        var imght = cloneimg.data('height');
        var newwidth = Math.floor(ActColWidth*.45);
        var newheight = Math.floor(imght*newwidth/imgwd);
        clone = clone.replace(/class="ArtImageS"/,'class="ArtImageS" width=' + newwidth + ' height=' + newheight);
        Col.append(clone); 
//        $('#SArt'+ArtNum).height(newheight+PadWidth/2);
        break;
        
      case 2: // text - no actions needed
        Col.append(clone); 
        break;
        
      case 3: // Banner Image - no image manipulation needed
        Col = $('#ArtBanners');
        Col.append(clone);
        break;

      case 4: // Banner Text
        Col = $('#ArtBanners');
        Col.append(clone);
        break;

      case 5: // Fixed 550:500 - Get image shape, if landscape(ish) work out height of text, shrink image to leave enough space between title and text not to overflow
      // if Portrait (enough) if text box beside full height picture fits - fine, if not shrink picture by 5% until it fits.
      // So need to do title, text then picture
        var cloneimg = $('#ArtImg' + ArtNum);
        var imgwd = cloneimg.data('width');
        var imght = cloneimg.data('height');
        var targetht = ActColWidth*500/550;
        if (imgwd/imght < 0.8) { // Portrait 
          // if title, Swap title and image elements over - class becomes FP - scrolls text if needed
          clone = clone.replace(/(<div class="ArtTitleF" (.*?)>)(<img class="ArtImageF" (.*?)>)/,
                                "<img class=\"ArtImageFP\" $4 width=0 height=0><div class=\"ArtTitleFP\" $2>");
          clone = clone.replace(/class="ArtTextF"/,"class=ArtTextFP");
          clone = clone.replace(/clear="all"/,"");
          Col.append(clone); 
          $('#SArt'+ArtNum).height(targetht);       
          $('#SArtImg'+ArtNum).width(imgwd*targetht/imght);
          $('#SArtImg'+ArtNum).height(targetht);                
        } else { // Landscape
          clone = clone.replace(/class="ArtImageF"/,'class="ArtImageF" width=0 height=0 ');
          Col.append(clone); //No image yet
          $('#SArt' +ArtNum).height(targetht);
          // Find actual height left
          var used = $('#SArtTitle' +ArtNum).height() + $('#SArtText' +ArtNum).height();
          var imgspace = targetht - used - PadWidth -10;
          var newwidth = ActColWidth;
          var newheight = Math.floor(imght*newwidth/imgwd);
          if (newheight > imgspace) {
            newwidth = Math.floor(newwidth*imgspace/newheight);
            newheight = Math.floor(newheight*imgspace/newheight);
          } else { // Spare space
            var spare = imgspace-newheight;
            spare = Math.min(spare/2,40);
            $('#SArtText'+ArtNum).css({"padding-top":spare});
          }
          $('#SArtImg'+ArtNum).width(newwidth);
          $('#SArtImg'+ArtNum).height(newheight);         
        }
        break;
        
      case 6: // Left/Right Pairs fullwidth
        // TODO
      
      case 7: // 2/3rds Banner Image
        Col = $('#ArtBanners');
        Col.append(clone);
        break;
        // TODO
      
      case 8: // middle image below text
        var cloneimg = $('#ArtImg' + ArtNum);
        var imgwd = cloneimg.data('width');
        var imght = cloneimg.data('height');
        var newwidth = Math.floor(ActColWidth*.45);
        var newheight = Math.floor(imght*newwidth/imgwd);
        clone = clone.replace(/class="ArtImageL"/,'class="ArtImageS" width=' + newwidth + ' height=' + newheight);
        Col.append(clone); 
        break;
      
      case 9: // V Small image to right of heading
        var cloneimg = $('#ArtImg' + ArtNum);
        var imgwd = cloneimg.data('width');
        var imght = cloneimg.data('height');
        var newwidth = Math.floor(ActColWidth*.45);
        var newheight = Math.floor(imght*newwidth/imgwd);
        clone = clone.replace(/class="ArtImageVS"/,'class="ArtImageVS" width=' + newwidth + ' height=' + newheight);
        Col.append(clone); 
//        $('#SArt'+ArtNum).height(newheight+PadWidth/2);
        break;
        
      case 10: // Large - image display Title overlay width is actcolwidth, image display height = acthieght*actcolwith/origheight
        var cloneimg = $('#ArtImg' + ArtNum);
        var imgwd = cloneimg.data('width');
        var imght = cloneimg.data('height');
        var newwidth = ActColWidth;
        var newheight = Math.floor(imght*newwidth/imgwd);
        clone = clone.replace(/class="ArtImageL"/,'class="ArtImageL" width=' + newwidth + ' height=' + newheight);
        Col.append(clone); 
        break;
          
    
      }
      ArtNum++;
    }
  }
  
  function ArtResize() {
    $('#ShowArt').empty();
    SetupArts();    
  }

  [MinWidth, MaxWidth, PadWidth, MaxColns] = $('#ShowArt').data('settings').split(',').map(Number); // Settings
  SetupArts();
  window.addEventListener('resize',ArtResize);
});

// 
