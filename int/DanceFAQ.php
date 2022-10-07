<?php
  include_once("fest.php");
  include_once("Email.php");
  dohead("Dance FAQ");

    $faq = TnC('DanceFAQ');
    $faq = Parse_Proforma($faq);
  
  echo $faq;
  dotail();
?>

