<?php
  header('Content-Type: application/json');
  $server = '54.244.190.208';
  $url = 'http://' . $server . ':8080/geoserver/algal_blooms/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=algal_blooms:us_states&outputFormat=application%2Fjson';
  $content = file_get_contents($url);
  $type = getType($content);
  echo (file_get_contents($url));

?>
