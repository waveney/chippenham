<?php
  include_once("int/fest.php");
  include_once("int/VolLib.php");
  include_once("int/DispLib.php");
  global $PLANYEAR,$YEARDATA;
  
  dohead("Get Involved",["/js/Articles.js"],1);

  echo "The festival is entirely reliant on a dedicated army of brilliant volunteers all who give up their time and provide their " .
       "skills and expertise to the event free of charge. $PLANYEAR's event runs from " . FestDate($YEARDATA['FirstDay'],'Y') . " to " . 
       FestDate($YEARDATA['LastDay'],'Y') . ", there are many different ways in which you can get involved...<p>" .
       "When you get to the form you can select more than one team.<p>";
       
  echo "Volunteers get free tickets and camping.<p>";
  
  echo "<b><a href=int/ShowFile?f=233>Volunteers Handbook 2024</a></b><p>";


  $Vol_Cats = Gen_Get_All('VolCats','ORDER BY Importance DESC');

  $Arts = [];
  foreach ($Vol_Cats as $Cat) {
    if (($Cat['Props'] & VOL_USE)==0) continue;
    if (($Cat['Props'] & VOL_NoList) !=0 ) continue;
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

    $Arts[] = ['SN' => $Cat['Name'],'Type'=>0, 'Link'=>'int/Volunteers?A=New', 'HideTitle'=>0, 'RedTitle'=>0,
               'Image'=>$Cat['Image'], 'ImageHeight'=>$Cat['ImageHeight'] , 'ImageWidth'=>$Cat['ImageWidth'],'Format'=>0,
               'Text'=> ($Cat['Description'] . "<p>" . $Cat['LongDesc'] . 
                 ((($Cat['Props'] & VOL_TeamFull) > 0)?'<P>This team has all the volunteers it needs this year, please select another team':
                 "<a href=int/Volunteers?A=New><div class=VolButtonWrap><div class=VolButton>Please Volunteer for " . $Cat['Name'] . "</a></div></div>")) 
              ];
  }
   
  Show_Articles_For($Arts,0,'400,700,20,3');
  
  dotail();
