<?php
  header('Content-Type: application/json');
  $server = '54.244.190.208';
  $click_lat = $_REQUEST['click_lat'];
  //$click_lat = 38.03078569382294;
  //$click_long = -77.76123046875001;
  $click_long = $_REQUEST['click_long'];

  $url = "http://" . $server . ":8080/geoserver/algal_blooms/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=algal_blooms:lakesandstates_reprojected&PROPERTY_NAME=geom&CQL_FILTER=CONTAINS(geom,Point(" . $click_long . "%20" . $click_lat . '))&outputFormat=application%2Fjson';

  echo (file_get_contents($url));

?>
