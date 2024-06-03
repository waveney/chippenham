<?php
// Admin tasks that are likely to be very rarely used, just to avoid cluttering the main page

  include_once("fest.php");
  A_Check('SysAdmin');
  global $FESTSYS,$VERSION;
  dostaffhead("Rare Admin");
  echo "<form method=post action=RareAdmin>";
  
function  Check_FavIcons() {
  $ImgLoc256 = $_REQUEST['OrigImage256'] ?? '';
  $Crops = ['None','Crop','Pad'];
//  if (!$ImgLoc256 || !file_exists($ImgLoc256)) {
    if ($ImgLoc256 && !file_exists($ImgLoc256)) echo "<h2 class=Err>$ImgLoc256 not found</h2>";
    
    echo "<h1>Set up Favicon and related images</h1>";
    echo "Put an image location (at least 256x256 and square if possible) in the largest file below.<p>";
    echo "Then Click <b>Check Icons</b>.<p>";
    echo "If you are happy with all images, then click <b>Generate Icons</b><p>";
    echo "Otherwise change the image(s) to new ones.  A suppled image, will be used for all smaller icos, if they are not specified.<p>";
//    echo "If NOT square click either the crop or pad options for the image<p>";
    
    echo "<table border><tr><td>Size<td>View<td colspan=2>File<td>Notes\n";
    
    $LastImg = '';
    $CropType = 0;
    $Swidth = $Sheight = 0;
    foreach([256,64,48,32,16] as $Size) {
      if (!empty($_REQUEST["OrigImage$Size"])) {
        $LastImg = $_REQUEST["OrigImage$Size"];
        [$Swidth,$Sheight] = getimagesize($LastImg);
        $CropType = (($Swidth == $Sheight)?0:($_REQUEST["CropImage$Size"] ?? 0));
      }
      $Style = " style='height:$Size;width:$Size;object-fit:contain'";      
/*      switch ($CropType) {
        case 1: // Crop
          $Style = " style='" . (($Swidth <  $Sheight)? "height:$Size;width:$Size;object-fit:contain'" : "height:$Size;width:$Size;object-fit:contain'") ;
          break;
            
        case 2: // Pad
          $Style = (($Swidth <  $Sheight)? "height=$Size" : "width=$Size");
          break;
          
        case 0: 
        default: // No actions needed
          $Style = " width=$Size height=$Size";
          break;
          
      }   */
        
      echo "<tr><td>$Size x $Size<td><img src=$LastImg $Style>";
      echo fm_text1('',$_REQUEST,"OrigImage$Size",2);
//      echo fm_radio('<br>Process',$Crops,$_REQUEST,"CropImage$Size",'',-1);
      echo "<td>"; //Notes go here if/when written
    }
    echo "</table><br><input type=submit name=ACTION value='Check Icons'>";
    
    if ($ImgLoc256) echo "<input type=submit name=ACTION value='Generate Icons'>";
    
    dotail();  
//  }
}

function  Make_FavIcons() {  
  include_once('vendor/chrisjean/php-ico/class-php-ico.php');
  
  $ico_lib = new PHP_ICO();
  
  $imgs = [];
  
  $Sizes = [];
  $LastImg = '';
  $CropType = 0;
  $Swidth = $Sheight = 0;
  foreach([256,64,48,32,16] as $Size) {
    if (!empty($_REQUEST["OrigImage$Size"])) {
      if ($Sizes) $imgs []= [$LastImg,array_reverse($Sizes)];
      $LastImg = $_REQUEST["OrigImage$Size"];
      $Sizes = [[$Size,$Size]];
    } else {
      $Sizes []= [$Size,$Size];
    }
  }
  if ($Sizes) $imgs []= [$LastImg,array_reverse($Sizes)];
  
  foreach (array_reverse($imgs) as $ent) {
    $ico_lib->add_image($ent[0],$ent[1]);
  }
  
  $ico_lib->save_ico( "../favicon.ico" );

  echo "FavIcon Written<p>";
    
}

function Start_RealFavicons() {
  echo "Please supply a starting image - best at least 256 x 256, on the server here is best<p>";
  echo fm_text1('',$_REQUEST,"OrigImage256",2) , "<input type=submit name=ACTION value='Call_Favicons'>";
  dotail();
}


function Call_RealFavicons() {
  global $FESTSYS;
  $ImgLoc256 = $_REQUEST['OrigImage256'] ?? '';
  if (!$ImgLoc256) Start_RealFavicons();
  
  $json = ["favicon_generation"=>[
    "api-key"=>Feature('RealFaviconGeneratorKey'),
    "master_picture"=> [
      "type"=> "url",
			"url"=> "https://" . Feature('HostURL') . "/" . $ImgLoc256,
			"demo"=> "false"
		  ],
		"files_location"=> [
			"type"=> "root",
		  ],
		"callback" => [
			"type"=> "url",
			"url"=> "https://" . Feature('HostURL') . "/int/RareAdmin?ACTION=Callback_RealFavicons",
			"short_url"=> "false",
			"path_only"=> "false",
			"custom_parameter"=> hash('md5',json_encode($FESTSYS)),
		  ]
	  ]
  ];
  
  $jsn = json_encode($json);
  echo "<p>";
  var_dump($json); echo "<p>";
  var_dump($jsn);

}


function Callback_RealFavicons() {


}




  // START HERE

// var_dump($_REQUEST); 
  if ( isset($_REQUEST['ACTION'])) {
    switch ($_REQUEST['ACTION']) {
      case 'Check Icons':
        Check_FavIcons();
        break;
        
      case 'Generate Icons':
        Make_FavIcons();      
        break;
        
      case 'Favicons':
        Start_RealFavicons();      
        break;

      case 'Call_Favicons':
        Call_RealFavicons();      
        break;

      case 'CallBack_Favicons':
        Callback_RealFavicons();      
        break;
        
        
      
      default:
    }
  } 
  
  echo "<ul>";
  echo "<li><a href=LinkManage>Manage Other Fest Links</a>\n";
  echo "<li><a href=RareAdmin?ACTION=Check%20Icons>Set up Favicon</a>\n";
  echo "<li><a href=RareAdmin?ACTION=Favicons>Set up Favicon (new method - not working yet)</a>\n";
  echo "<li><a href=EditMainMenu>Edit Main Menu</a>\n";
  echo "</ul>";
  
  dotail();

?>
