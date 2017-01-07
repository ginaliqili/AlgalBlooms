<?php
  header('Content-Type: application/json');
  $server = '54.244.190.208';
  $current_state_abbr = $_REQUEST['current_state_abbr'];
  //$current_state_abbr = 'MD';
  //$url = 'http://' . $server . ':8080/geoserver/algal_blooms/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=algal_blooms:lakesandstates_reprojected&PROPERTY_NAME=geom&CQL_FILTER=state_abbr=' . $current_state_abbr . '&outputFormat=application%2Fjson';
  $url = "http://" . $server . ":8080/geoserver/algal_blooms/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=algal_blooms:lakesandstates_reprojected&PROPERTY_NAME=geom&CQL_FILTER=state_abbr=%27". $current_state_abbr . "%27&outputFormat=application%2Fjson";
  $content = file_get_contents($url);
  $type = getType($content);
  //echo($type);
  echo (file_get_contents($url));

?>
