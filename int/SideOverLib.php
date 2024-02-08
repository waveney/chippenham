<?php

// Library for Side overlays

function Get_Overlay(&$Side,$Isa) {
  static $Overlays = [];
  
  if (isset($Overlays[$Side['SideId']][$Isa])) return $Overlays[$Side['SideId']][$Isa];
  
  $Olay = Gen_Get_Cond1('SideOverlays',"SideId=" . $Side['SideId'] . " AND IsType='$Isa'");
  return $Overlays[$Side['SideId']][$Isa] = $Olay;
}

function Put_Overlay(&$Olay) {
  Gen_Put('SideOverlays',$Olay);
}

function OvPhoto(&$Side,$Isa='') {
  if ($Side['HasOverlays'] && $Isa) {
    $Olay = Get_Overlay($Side,$Isa);
    if ($Olay) {
      return $Olay['Photo'] ?? $Side['Photo'];
    }
  }
  
  return $Side['Photo'];
}

function OvName(&$Side,$Isa='') {
  if ($Side['HasOverlays'] && $Isa) {
    $Olay = Get_Overlay($Side,$Isa);
    if ($Olay) {
      return $Olay['SN'] ?? $Side['SN'];
    }
  }
  
  return $Side['SN'];
}

function OvDesc(&$Side,$Isa='') {
  if ($Side['HasOverlays'] && $Isa) {
    $Olay = Get_Overlay($Side,$Isa);
    if ($Olay) {
      return $Olay['Description'] ?? $Side['Description'];
    }
  }
  
  return $Side['Description'];
}

function OvBlurb(&$Side,$Isa='') {
  if ($Side['HasOverlays'] && $Isa) {
    $Olay = Get_Overlay($Side,$Isa);
    if ($Olay) {
      return $Olay['Blurb'] ?? $Side['Blurb'];
    }
  }
  
  return $Side['Blurb'];
}

function OvTwitter(&$Side,$Isa='') {
  if ($Side['HasOverlays'] && $Isa) {
    $Olay = Get_Overlay($Side,$Isa);
    if ($Olay) {
      return $Olay['Twitter'] ?? $Side['Twitter'];
    }
  }
  
  return $Side['Twitter'];
}

function OvFacebook(&$Side,$Isa='') {
  if ($Side['HasOverlays'] && $Isa) {
    $Olay = Get_Overlay($Side,$Isa);
    if ($Olay) {
      return $Olay['Facebook'] ?? $Side['Facebook'];
    }
  }
  
  return $Side['Facebook'];
}

function OvInstagram(&$Side,$Isa='') {
  if ($Side['HasOverlays'] && $Isa) {
    $Olay = Get_Overlay($Side,$Isa);
    if ($Olay) {
      return $Olay['Instagram'] ?? $Side['Instagram'];
    }
  }
  
  return $Side['Instagram'];
}

function OvWebsite(&$Side,$Isa='') {
  if ($Side['HasOverlays'] && $Isa) {
    $Olay = Get_Overlay($Side,$Isa);
    if ($Olay) {
      return $Olay['Website'] ?? $Side['Website'];
    }
  }
  
  return $Side['Website'];
}



