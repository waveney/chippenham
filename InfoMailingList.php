<?php
  include_once("int/fest.php");

  dohead("Mailing List");

  echo "<h2>Subscribe to our Talking Folk Mailing List</h2>";
  
  echo TnC('MailingListIntro');
  
  echo "<h2><a href=int/MailListMgr>Sign up</a></h2>";
  echo "The mailing list is run by Mail Octopus, this is just a front end allowing us to check you are real.<p>";
  echo "The check is manual so may take a few days, please be patient.<p>";



  dotail();
?>
