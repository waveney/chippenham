<?php
  include_once("int/fest.php");

  dohead("Trade Stands",[]);

  global $PLANYEAR;
  echo "<div class=biodiv>
<img src=/images/ArticleImgs/Traders.jpeg class=bioimg>
<p>Some of our traders.</p>
</div>
<h2>Trade in " . substr($PLANYEAR,0,4) . " </h2>
Applications for trading at the festival, which will be held over the weekend of " . FestDate(0,'L') . " to " . FestDate(2,'L') . " are <a href=/int/Trader>OPEN</a>.<p>

Prices for $PLANYEAR trade stands are listed below.  See also the <a href=/int/TradeFAQ><strong>Trade FAQ</strong></a>.  
Any queries should be sent to <a href='mailto:trade@chippfolk.co.uk'><strong>trade@chippfolk.co.uk</strong></a>.<p>

Trade Stands are not allocated on a first come first served basis, each application is assessed on the choice, quality, 
origin and pricing structure to ensure a fair and varied amount of traders are offered a place.<p>

After your application is submitted and accepted. You will, in most cases, need to promptly pay a deposit to secure your place.<p>

All applicants are required to provide their insurance and a completed Risk Assessment form. <p>

All food and drink Traders are required to 
indicate which local authority they are registered with.<p>

";
  echo "<div class=trader-app-link><a href=/int/Trader>Traders application Form</a></div>\n";

  include_once "int/TradePublic.php";
  Trade_Type_Table('Sattab');

  echo "<div class=trader-app-link><a href=/int/Trader>Traders application Form</a></div>\n";

  dotail();
?>
