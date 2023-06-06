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
                 "<a href=int/Volunteers?A=New><div class=VolButtonWrap><div class=VolButton>Please Volunteer for " . $Cat['Name'] . "</a></div></div>") ];
  }
  
//  var_dump($Arts);
   
  Show_Articles_For($Arts,0,'400,700,20,3');
  
/*  
  foreach ($Vol_Cats as $Cat) {
    $Id = $Cat['id'];
    echo "<div class=LineupFit2Wrapper>";

    echo "<div class='LineupFit2 LineUpBase' onmouseover=AddLineUpHighlight($Id) onmouseout=RemoveLineUpHighlight($Id) id=LineUp$Id>";
    echo "<a href=int/Volunteers?A=New>";

    echo "<div class=LineUpFitTitle style='font-size:24px'>" . $Cat['Name'] . "</div>";
    if ($Cat['Image']) echo "<img class=Rounded src=" . $Cat['Image'] . ">";
    if ($Cat['Description']) echo "<div class=LineUptxt>" . $Cat['Description'] . "</div>";
    if ($Cat['LongDesc']) echo "<div class=LineUpSmalltxt>" . $Cat['LongDesc'] . "</div>";       

    echo "<div class=LUCenter>Please Volunteer for " . $Cat['Name'] . "</div>";
    
    echo "</a></div>";
  }
  echo "</div><br clear=all></div>";

  
/*  
  $Shown = 0;
  echo "<table width=95%>";
  foreach ($Vol_Cats as $Cat) {
    if (($Cat['Props'] & VOL_USE)==0) continue;
    echo "<tr>";

    if ($Shown & 1) {
      echo "<td width=45%>";
      echo "<center><img src=" . $Cat['Image'] ." width=400 class=rounded></center>";   
      echo "<td width=45% valign=top>";
      echo "<h1>" . $Cat['Name'] . "</h1>";
      echo $Cat['LongDesc'];
      echo "<div class=trader-app-link><a href=int/Volunteers?A=New>Please Volunteer for " . $Cat['Name'] . "</a></div>";
    } else {
      echo "<td width=45% valign=top>";     
      echo "<h1>" . $Cat['Name'] . "</h1>";
      echo $Cat['LongDesc'];
      echo "<div class=trader-app-link><a href=int/Volunteers?A=New>Please Volunteer for " . $Cat['Name'] . "</a></div>";
      echo "<td width=45%>";     
      echo "<center><img src=" . $Cat['Image'] ." width=400 class=rounded></center>";   
    }
    $Shown++;
  }
  echo "</table>";
*/   
  dotail();  
/*
Please volunteer at Chippenham Folk Festival<p>

We are looking for vibrant people to be part of the festival team who are prepared to work in shifts.<p>

You must be over 18, reliable, have good people skills and be able to remain pleasant and cheerful whatever the circumstances!<p>

In exchange for your time, we will give you a pass for festival events plus a free souvenir programme, free parking and free camping!<p>

<ul>
<li>You will generally be working in teams.
<li>Training will be given if necessary.
<!--<li>Certain jobs may require a DBS (Disclosure & Barring Service) check, which we can arrange.-->
<li>You will need to sort out your own refreshments.
<li>A free day/weekend pass will be given to stewards which covers all festival events
<li>You will receive a free souvenir programme.
<li>Free parking will be available for stewards within the festival area.
</ul>

Stewarding & Volunteer roles available:<p>
<div class=BorderBox><h2>Stewarding</h2>
<ul>

<li>Information desk at The Square
<li>Event stewards at Concerts, Ceildihs, Sessions and Workshops.
<li>Procession stewards
<li>Road Closures points
<li>Collection tin stewards and programme sales
<li>Providing information and guidance to Festival visitors
</ul>

<p><strong>Operational Hours</strong>:
<ul>
<li>Thursday 1900-2300 (Evening concert, very limited requirement)
<li>Friday 0900-1200 hrs (Limited Requirement) , 1200-2300 hrs
<li>Saturday 0900-2300 hrs
<li>Sunday 0900-2300 hrs
</ul>
Schedules will be designed to suit your requirements as much as possible.<p>

<div class=trader-app-link><a href=int/Volunteers?A=New>Please Volunteer as a Steward</a></div>
</div><div class=BorderBox>

<h2>Setup/Cleardown Crew</h2>
<ul>
<li>Setup and cleardown of Banners, Marquees, Bunting, Furniture, Venues, Posters, Generators, 
<li>Most of these tasks are before and after the festival, leaving you free to enjoy the festival
<li>Many jobs involve heavy lifting.  Hi vis jackets and hard hats provided as appropriate.
</ul>

<p><strong>Operational Days/Hours</strong>:
<ul>
<li>Starting in April: Banners, Posters (A couple of hours every few weeks or so for 2-3 crew)
<li>The Week Before: Some decorations, Bunting, Road Notices etc - A few hours many days for a small crew.
<li>Thursday: Stage Setup, Some Marquees - All day for a small crew
<li>Friday: Fencing, Marquees, Stages, Generators, Furniture, Posters - All day for a large crew
<li>Saturday: A few minor jobs 
<li>Sunday: Starting at 5pm - Main cleardown of Marquees and Clearing up many venues - A large crew is needed
<li>Monday: Finish all Marquees and tidying of town, removal of Posters, removal of stages - Some crew, maybe all day
<li>Tuesday: Rest!  (Hopefully)
</ul>


<div class=trader-app-link><a href=int/Volunteers?A=New>Please Volunteer for the Setup Crew</a></div>
</div><div class=BorderBox>
<h2>Artistic Team</h2>
<ul>
<li>Setup / Packdown of festival art displays at the Allendale
<li>Assisting with decorating the town
<li>Assisting creative workshops prior to festival
<li>Stewarding the Art displays and workshops.
</ul>

<div class=trader-app-link><a href=int/Volunteers?A=New>Please Volunteer for the Art Team</a></div>
</div><div class=BorderBox>
<h2>Media Team</h2>
<ul><li>Photographers
<li>Videographers
</ul>


<div class=trader-app-link><a href=int/Volunteers?A=New>Please Volunteer for Media</a></div>
</div>

<?php
  dotail();
*/
?>
