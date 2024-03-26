<?php
  include_once("int/fest.php");

  
  if (isset($_REQUEST['No'])) {
    dohead("That's a Shame",[],1);

    echo "<center><h3>We are sorry you have changed your mind, hope to see you at the festival </h3>";
  } else {
    dohead("Thank You",[],1);
    echo "<center><h1>Thank you for supporting " . Feature('FestName') . 
      ".</h1><h3>This festival would not be possible without the generosity of our supporters</h3>";
  }

  dotail();