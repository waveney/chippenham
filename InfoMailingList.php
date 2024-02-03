<?php
  include_once("int/fest.php");

  dohead("Mailing List");

  echo "<h2>Subscribe to our Talking Folk Mailing List</h2>";
  
  echo TnC('MailingListIntro');
  
  echo "<div class=trader-app-link><a href=int/MailListMgr>Click Here to Sign up</a></div>";
  echo "The mailing list is managed by our festival team using Mail Octopus. " .
       "This is a front end allowing us to check that you are a real person.<p>";
  echo "The check is manual so may take a few days, please be patient.<p>";



  dotail();