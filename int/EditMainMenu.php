<?php

include_once("fest.php");
dostaffhead("Edit Main Menu");

$Menu = Gen_Get('MainMenu',1);
Register_AutoUpdate('MainMenu',1);
echo "text=>link or text=>[submenu] (recuresive)<br>
1st char of text * - not selectable, ! Icon, ? Only Dance, # Not Dance, = Get Tickets, - minor, &lt; Minor 2, % Donate, @ Special, > move to end,
                ~ Event Changes, _ Perf Changes, : Trade <br>
1st char of link ! - external, ~ Only after Program freeze<br>
Specials 0 Sherlocks, 1 Gallery Index";

echo fm_textarea1('Main Menu',$Menu,'Menu');

dotail();

