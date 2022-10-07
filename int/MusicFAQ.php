<?php
  include_once("fest.php");
  include_once("Email.php");
  dohead("Music FAQ");

    $faq = TnC('MusicFAQ');
    $faq = Parse_Proforma($faq);
  
  echo $faq;
  dotail();
?>

