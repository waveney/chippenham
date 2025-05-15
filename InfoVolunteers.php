<?php
  include_once("int/fest.php");
  include_once("int/VolLib.php");
  include_once("int/DispLib.php");
  include_once("int/Email.php");
  global $PLANYEAR,$YEARDATA;

  dohead("Volunteer",["/js/Articles.js"],1);

  $msg = TnC('Volunteer_Header');
  Parse_Proforma($msg);
  echo $msg;

  $Vol_Cats = Gen_Get_All('VolCats','ORDER BY Importance DESC');

  $mtch = $Arts = [];
  foreach ($Vol_Cats as $Cat) {
    if (($Cat['Props'] & VOL_USE)==0) continue;
    if (($Cat['Props'] & VOL_NoList) !=0 ) continue;
    if (($Cat['Props'] & VOL_GROUPQS) !=0 ) continue;

    if (isset($Cat['Image']) && $Cat['Image']) {
      if (isset($Cat['ImageWidth']) && $Cat['ImageWidth'] != 0) {
        // No Action
      } else {
        $img = $Cat['Image'];
        if (preg_match('/^https?:\/\//i',$img)) {
          $stuff = getimagesize($img);
        } else if (preg_match('/^\/(.*)/',$img,$mtch)) {
          if (file_exists($mtch[1])) {
            $stuff = getimagesize($mtch[1]);
          } else {
            $stuff = [0,0];
          }
        } else {
          $stuff = getimagesize($img);
        }
        if ($stuff) {
          $Cat['ImageWidth'] = $stuff[0];
          $Cat['ImageHeight'] = $stuff[1];
        }
        Gen_Put('VolCats',$Cat);
      }
    } else {
        $Cat['ImageWidth'] = 0;
        $Cat['ImageHeight'] = 0;
    }

    $Force = (Access('SysAdmin')?'&FORCE':''); // For testing
    $Arts[] = ['SN' => $Cat['Name'],'Type'=>0, 'Link'=>'int/Volunteers?A=New', 'HideTitle'=>0, 'RedTitle'=>0,
               'Image'=>$Cat['Image'], 'ImageHeight'=>$Cat['ImageHeight'] , 'ImageWidth'=>$Cat['ImageWidth'],'Format'=>0,
               'Text'=> ($Cat['Description'] . "<p>" . $Cat['LongDesc'] .
                 (($Cat['Props2'] & VOL_CAT_FULL)?'<P>This team has all the volunteers it needs this year, please select another team':(
                 "<a href=int/Volunteers?A=New$Force&C=" . $Cat['id'] . "><div class=VolButtonWrap><div class=VolButton>Please Volunteer for " .
                   $Cat['Name'] . "</a></div></div>")))
              ];
  }

  Show_Articles_For($Arts,0,'400,700,20,3');

  dotail();
