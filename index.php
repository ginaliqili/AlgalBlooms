<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8' />
    <title>Algal Blooms</title>
    <meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />
    <script src='https://api.tiles.mapbox.com/mapbox-gl-js/v0.29.0/mapbox-gl.js'></script>
    <script src="https://unpkg.com/leaflet@1.0.2/dist/leaflet.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
    <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v0.29.0/mapbox-gl.css' rel='stylesheet' />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.2/dist/leaflet.css" />

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-89990446-4', 'auto');
  ga('send', 'pageview');

</script>




    <style>
        body { margin:0; padding:0; }
        #map { position:absolute; top:0; bottom:0; width:100%; }

        a {
          border: none !important;
        }

        .info, .infoLake {
          padding: 6px 8px;
          font: 14px/16px Arial, Helvetica, sans-serif;
          background: white;
          background: rgba(255,255,255,0.8);
          box-shadow: 0 0 15px rgba(0,0,0,0.2);
          border-radius: 5px;
        }

        .loadersmall {
          border: 5px solid #f3f3f3;
          -webkit-animation: spin 1s linear infinite;
          animation: spin 1s linear infinite;
          border-top: 5px solid #8aafff;
          border-radius: 50%;
          width: 20px;
          height: 20px;
      }

      @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
      }

      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }

      .popupHeader {
        font-weight: bold;
      }

    </style>


</head>
<body>

<div id='map'>

  <div id="load" style="z-index: 9999; position: fixed; left: 50%; top: 40%; display: none;">
    <div class="loadersmall" style="text-align: center; height: 20px; width: 20px; margin: 0 auto;">
      <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
      <br>

    </div>
    <span id="load_text" style="font-size: 12px; font-weight: bold">Loading Map</span>
  </div>

<div id="lakePopup" style="display: none; z-index: 9999; position: fixed; left: 1%; bottom: 5%; background-color: #ffffff; padding: 5px; width: 250px; height: 250px; border-radius: 8px;">
  <span class="popupHeader">Name: </span>
  <span id="lakeName"></span>
  <br /><br />

  <span class="popupHeader">Type: </span>
  <span id="type"></span>
  <br /><br />

  <span class="popupHeader">Area: </span>
  <span id="area"></span><span> km<sup>2</sup></span>
  <br /><br />

  <span class="popupHeader">Cyanobacteria Concentration: </span>
  <span id="cyano">NaN cells/mL</span>
  <br /><br />

  <span class="popupHeader">Chlorophyll-A Concentration: </span>
  <span id="chlorophyllA">NaN mg/L</span>
  <br /><br />

  <span class="popupHeader">Turbidity: </span>
  <span id="turbidity">NaN NTU</span>
  <br /><br />
</div>


</div>

<script>

var map;
var geoJson_states_layer;

function createMap() {
  $('#load').show();

  map = L.map('map').setView([40, -100], 3);
  L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
      attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
      maxZoom: 18,
      id: 'ginali.2f35mag7',
      accessToken: 'pk.eyJ1IjoiZ2luYWxpIiwiYSI6IklzeVlHUDgifQ.2jXdyrCI3HvTUGC2EIM8Qg'
  }).addTo(map);
  map.doubleClickZoom.disable();

  var lakeandstates = L.tileLayer.wms('http://54.244.190.208:8080/geoserver/algal_blooms/wms?', {
      layers: 'algal_blooms:lakesandstates_reprojected',
      transparent: true,
      format: 'image/png'
  }).addTo(map);

}

function retrieveStates() {
    var json_url = "load_states.php";

  $.ajax({
          'async': true,
          'global': false,
          'url': json_url,
          'dataType': "json",
          'success': function (data) {
            $('#load').hide();
            stateData = data;
            statesOverlay();
            addRemoveStates();

    }
  });
}

var clicked_state_data;
var states_geoJson;

var info = L.control();

function statesOverlay() {
  states_geoJson = L.geoJson(stateData, {style: style, onEachFeature: onEachFeature});
  states_geoJson.addTo(map);

  var geojson;
  var current_state_abbr;

  //var info = L.control();
  var div;

  info.onAdd = function (map) {
      this._div = L.DomUtil.create('div', 'info'); // create a div with a class "info"
      this.update();
      return this._div;
  };

  // method that we will use to update the control based on feature properties passed
  info.update = function (props) {
    //console.log(props);
    //current_state_abbr = props.stusps;
    if (props != null) {
      current_state_abbr = props.stusps;
    }


      this._div.innerHTML = '<b>State Name</b><br /><br />' +  (props ?
          props.name + '<br /><i>' + props.lake_count + ' bodies of water</i>'
          : 'Select state to begin');
  };

  info.addTo(map);

function style(feature) {
    return {
        fillColor: '#ccffcc',
        weight: 2,
        opacity: 1,
        color: 'white',
        dashArray: '3',
        fillOpacity: 0.4
    };
}

function highlightFeature(e) {
    var layer = e.target;

    layer.setStyle({
        weight: 5,
        color: '#666',
        dashArray: '',
        fillOpacity: 0.7
    });

    if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
        layer.bringToFront();
    }

    info.update(layer.feature.properties);
}

function resetHighlight(e) {
    states_geoJson.resetStyle(e.target);
    info.update();
}

function style_state_lakes(feature) {
    return {
        fillColor: 'blue',
        weight: 2,
        opacity: 1,
        color: 'blue',
        dashArray: '3',
        fillOpacity: 0.7
    };
}


function zoomToFeature(e) {
  // Load lake features
    $('#load_text').text("Loading Lakes");
    $('#load').show();

    click_lat = e.latlng.lat;
    click_long = e.latlng.lng;

    var click_state_url = "click_state.php?current_state_abbr=" + current_state_abbr;

      $.ajax({
          'async': true,
          'global': false,
          'url': click_state_url,
          'dataType': "json",
          'success': function (data) {
              clicked_state_data = data;
                $('#load').hide();
              lakesOverlay();
          }
      });




    map.fitBounds(e.target.getBounds());

    if (states_geoJson != null) {
        map.removeLayer(states_geoJson);
    }


}

function onEachFeature(feature, layer) {
    layer.on({
        mouseover: highlightFeature,
        mouseout: resetHighlight,
        click: zoomToFeature
    });
}




}

var infoLake = L.control();
var clicked_lake_geoJson;
var clicked_lake;
function lakesOverlay() {

infoLake.onAdd = function (map) {

    this._div = L.DomUtil.create('div', 'infoLake'); // create a div with a class "info"
    this.update();
    return this._div;
};

// method that we will use to update the control based on feature properties passed
infoLake.update = function (props) {
    this._div.innerHTML = '<b>Water Body</b><br /><br />' +  (props ?
        props.name + '<br />'
        : 'Hover over a lake');
};

infoLake.addTo(map);

console.log("lakesOverlay");

function style2(feature) {
    return {
        weight: 0.5,
        color: '#ffffff',
        fillColor: '#0000ff',
        dashArray: '1',
        fillOpacity: 0.7
    };
}

function highlightFeature2(e) {
  var layer = e.target;

  layer.setStyle({
      weight: 1,
      color: '#ffffff',
      fillColor: '#8aafff',
      dashArray: '',
      fillOpacity: 0.7
  });

  if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
      layer.bringToFront();
  }

  infoLake.update(layer.feature.properties);
}

function resetHighlight2(e) {
    clicked_state_data_geoJson.resetStyle(e.target);
    infoLake.update();
}

var clicked_style;



function zoomToFeature2(e) {
    click_lat = e.latlng.lat;
    click_long = e.latlng.lng;

    map.fitBounds(e.target.getBounds());

    var click_lake_url = "click_lake.php?click_lat=" + click_lat + "&click_long=" + click_long;
    console.log(click_lat + "   " + click_long);

    var click_lake_style= {"color": "#006400", "fillColor": "	#00FF7F", "fillOpacity": 1};
    $.ajax({
        'async': true,
        'global': false,
        'url': click_lake_url,
        'dataType': "json",
        'success': function (data) {
            clicked_lake = data;
            if (map.hasLayer(clicked_lake_geoJson)) {
              map.removeLayer(clicked_lake_geoJson);
            }
            clicked_lake_geoJson = L.geoJson(clicked_lake, click_lake_style);
            console.log(clicked_lake);
            clicked_lake_geoJson.addTo(map);
            lakePopup(clicked_lake);
        }
    });

}



function onEachFeature2(feature, layer) {
    layer.on({
        mouseover: highlightFeature2,
        mouseout: resetHighlight2,
        click: zoomToFeature2
    });
}



clicked_state_data_geoJson = L.geoJson(clicked_state_data, {style: style2, onEachFeature: onEachFeature2});
clicked_state_data_geoJson.addTo(map);


}

function lakePopup(clicked_lake) {
  $('#lakePopup').show();
  var properties = clicked_lake.features[0].properties;
  $('#lakeName').text(properties.name);
  $('#area').text(properties.sq_km);
  $('#type').text(properties.ftype);
}

function addRemoveStates() {
  var removed = false;
    map.on('zoomend', function () {
      if (map.getZoom() >= 7) {
        if (removed == false) {
            map.eachLayer(function (layer) {
              map.removeLayer(states_geoJson);
              removed = true;
          });
        }

      }
      else {
        if (removed == true) {
          states_geoJson.addTo(map);
          removed = false;
        }
      }
    });
}

$(document).ready(function() {
  console.log("0.5");
	createMap();
  retrieveStates();



});

</script>

</body>
</html>
