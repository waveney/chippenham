<?php
include_once("fest.php");

$OldVols = Gen_Get_All('RepairVoluteers');

foreach ($OldVols as $OV) {
  Gen_Put('Volunteers',$OV);
}

echo "Done";
