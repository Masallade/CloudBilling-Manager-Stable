<head>
	<style type="">
		#map {
			height: 650px;
		}
	</style>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.5.1/leaflet.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.5.1/leaflet.css">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
  <script src="https://rawgit.com/hayeswise/Leaflet.PointInPolygon/master/wise-leaflet-pip.js"></script>
   <script src="https://cdn.datatables.net/1.11.1/js/jquery.dataTables.min.js"></script>

</head>

<div class="content-body">
    <div class="card">
        <div class="card-content">
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message"></div>
            </div>
            <div class="card-body">
            <form method="post"action="<?php echo site_url('invoices/export_invoice_geofence')?>" target="_blank" id="export_invoice_geofence">
                <input type="hidden" name="fence_data" id="hidden_export">
                <input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
            </form>
                <div id="map"></div>
                <br>
                <div class="btn btn-primary" onclick="generate_pdf()" id="pdf_export_button" style="display:none;float:right" >Generate pdf</div>
                <br><br>
                <div>
                <br>
                    <table id="geofence_table" class="table table-striped">
                    <thead>
                        <tr>
                           <th>Customer Name</th>
                           <th>Code</th>
                           <th>Zone</th> 
                        </tr>
                    </thead>
                    <tbody>
                    <tr style="display:none"><td></td><td></td><td></td></tr>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDyr-8RixGvvicFiUFtgj9KZzxhB7jtPYY&libraries=&v=weekly&sensor=false&libraries=geometry"></script>
<script>

   


 var osmUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            osmAttrib = '&copy; <a href="http://openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            osm = L.tileLayer(osmUrl, { maxZoom: 18, attribution: osmAttrib }),
            map = new L.Map('map', { center: new L.LatLng(51.505, -0.09), zoom: 5 }),
            drawnItems = L.featureGroup().addTo(map);
    // L.control.layers({
    //     'osm': osm.addTo(map),
    //     "google": L.tileLayer('http://www.google.cn/maps/vt?lyrs=s@189&gl=cn&x={x}&y={y}&z={z}', {
    //         attribution: 'google'
    //     })
    // }, { 'drawlayer': drawnItems }, { position: 'topright', collapsed: true }).addTo(map);
   L.control.layers({
        'osm': osm.addTo(map)
        });
   
    // map.addControl(new L.Control.Draw({
    // 	position: 'topright',
    //     edit: {
    //         featureGroup: drawnItems,
    //         poly: {
    //             allowIntersection: false
    //         }
    //     },
    //     draw: {
    //         polygon: {
    //             allowIntersection: false,
    //             showArea: true
    //         }
    //     }
    // }));
    // map.on(L.Draw.Event.CREATED, function (event) {
    //     var layer = event.layer;

    //     drawnItems.addLayer(layer);
    // });
var latlngs = [[18.739046, 80.505755], [15.892787, 77.236081]];
var rectOptions = {color: 'Red', weight: 1};
var rectangle = L.rectangle(latlngs, rectOptions);
rectangle.addTo(map);

function onMapClick(e) {
  var contained = polygon.contains(e.latlng);
  var message = contained ? "This is inside the polygon!" : "This is not inside the polygon.";
  popup
    .setLatLng(e.latlng)
    .setContent(message)
    .openOn(map);
}
function onMarkerClick(e) {


  var contained = polygon.contains(e.latlng);
  var message = contained ? "This marker is inside the polygon!" : "This marker is not inside the polygon.";
  popup
    .setLatLng(e.latlng)
    .setContent(message)
    .openOn(map);
}
// Setup
// var mymap = L.map('mapid').setView([51.505, -0.09], 13);
// L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
//   attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
//   maxZoom: 18,
//   id: 'mapid',
//   accessToken: 'pk.eyJ1IjoiY29uZG9ydGhlZ3JlYXQiLCJhIjoiY2l6MXYwaDQyMDRneDMzcWZ4djRibWdiYiJ9.rbvqKXa9H0axkE3EAPSzgQ'
// }).addTo(mymap);



// var polygon = L.polygon([
//   [51.51, -0.08],
//   [51.503, -0.06],
//   [51.51, -0.047]
// ]).addTo(map);
var polygon = L.polygon([
  [49.970477, -5.792674],
  [51.107948, -5.792674],
  [51.107948, 1.511337],
  [49.970477, 1.511337]
]).addTo(map);
polygon.bindTooltip("1",
   {permanent: true, direction:"top"}
  ).openTooltip();



var polygon2 = L.polygon([
  [51.098736, -5.792674],
  [52.056014, -5.792674],
  [52.056014, 1.496699],
  [51.098736, 1.496699]
]).addTo(map);
polygon2.bindTooltip("2",
   {permanent: true, direction:"top"}
  ).openTooltip();

var polygon3 = L.polygon([
  [52.057012, -4.915072],
  [52.905793, -4.915072],
  [52.905793, 1.774173],
  [52.057012, 1.774173]
]).addTo(map);
polygon3.bindTooltip("3",
   {permanent: true, direction:"top"}
  ).openTooltip();



var polygon4 = L.polygon([
  [52.925239, -4.775765],
  [53.452662, -4.775765],
  [53.452662, 1.488998],
  [52.925239, 1.488998]
]).addTo(map);
polygon4.bindTooltip("4",
   {permanent: true, direction:"top"}
  ).openTooltip();


var polygon5 = L.polygon([
  [53.443623, -3.65566],
  [54.521916, -3.65566],
  [54.521916, 0.369597],
  [53.443623, 0.369597]
]).addTo(map);
polygon5.bindTooltip("5",
   {permanent: true, direction:"top"}
  ).openTooltip();


var polygon6 = L.polygon([
  [54.523897, -5.39897],
  [55.274677, -5.39897],
  [55.274677, -0.612574],
  [54.523897, -0.612574]
]).addTo(map);
polygon6.bindTooltip("6",
   {permanent: true, direction:"top"}
  ).openTooltip();



var polygon7 = L.polygon([
  [55.264143, -7.122684],
  [56.651925, -7.122684],
  [56.651925, -0.594451],
  [55.264143, -0.594451]
]).addTo(map);
polygon7.bindTooltip("7",
   {permanent: true, direction:"top"}
  ).openTooltip();


var polygon8 = L.polygon([
  [56.664383, -8.427098],
  [58.718159, -8.427098],
  [58.718159, -0.522958],
  [56.664383, -0.522958]
]).addTo(map);
polygon8.bindTooltip("8",
   {permanent: true, direction:"top"}
  ).openTooltip();




var popup = L.popup();
// map.on('click', onMapClick);
// var m1 = L.marker([51.515, -0.07]).addTo(map).on('click', onMarkerClick);
// var m2 = L.marker([51.506, -0.06]).addTo(map).on('click', onMarkerClick);
// var m3 = L.marker([51.505, -0.074]).addTo(map).on('click', onMarkerClick);
// var m4 = L.marker([51.51, -0.067]).addTo(map).on('click', onMarkerClick);
// console.log(polygon.contains(m1.getLatLng()));
// // ==> false
// console.log(polygon.contains(m2.getLatLng()));
// // ==> true
// console.log(polygon.contains(m3.getLatLng()));
// // ==> false
// console.log(polygon.contains(m4.getLatLng()));
// // ==> true




map.on('draw:created', function (e) {
    var type = e.layerType,
        layer = e.layer;
 alert(layer.getLatLngs());    
    // if (type === 'rectangle') {
        // layer.on('mouseover', function() {
        //     alert(layer.getLatLngs());    
        // });
    // }
    drawnItems.addLayer(layer);
});

var data_array=[];
function generate_pdf(){
    $('#hidden_export').val(JSON.stringify(data_array)); //store array
     $("#export_invoice_geofence").submit();

// var value = $('#input_hidden_field').val(); //retrieve array
// value = JSON.parse(value);
}
$(document).ready(function(){


 $.ajax({
       url : "<?php echo site_url('invoices/invoice_customers_ajax')?>",
       method: "get",
       success:function(customers){
        //    console.log(data);
        // var zipcode = 695564;
        var zone_count=0;
        // var i=0;
        // var total_rec=JSON.parse(customers).length;
                $.each(JSON.parse(customers), function(key,val) {
                    //  i++;
                            
                     if(val.postbox){


                $.ajax({
                    url : "https://maps.googleapis.com/maps/api/geocode/json?address="+val.postbox+"&key=AIzaSyDyr-8RixGvvicFiUFtgj9KZzxhB7jtPYY",
                    method: "get",
                    success:function(data){
                        latitude = data.results[0].geometry.location.lat;
                        longitude= data.results[0].geometry.location.lng;
                        // console.log("Lat = "+latitude+"- Long = "+longitude);
                        var m1 = L.marker([latitude,longitude]).addTo(map);
                        // var m1 = L.marker([latitude,longitude]).addTo(map).on('click', onMarkerClick);
                        if(polygon.contains(m1.getLatLng())==true){
                            $('#geofence_table tr:last').after('<tr><td>'+val.name_s +'</td><td>'+val.name +'</td><td>Zone-1</td></tr>');
                            data_array.push({fence: 'Zone 1', code: val.name,name: val.name_s});
                        }
                        if(polygon2.contains(m1.getLatLng())==true){
                            $('#geofence_table tr:last').after('<tr><td>'+val.name_s +'</td><td>'+val.name +'</td><td>Zone-2</td></tr>');
                            data_array.push({fence: 'Zone 2', code: val.name,name: val.name_s});
                        }
                        if(polygon3.contains(m1.getLatLng())==true){
                            $('#geofence_table tr:last').after('<tr><td>'+val.name_s +'</td><td>'+val.name +'</td><td>Zone-3</td></tr>');
                            data_array.push({fence: 'Zone 3', code: val.name,name: val.name_s});
                        }
                        if(polygon4.contains(m1.getLatLng())==true){
                            $('#geofence_table tr:last').after('<tr><td>'+val.name_s +'</td><td>'+val.name +'</td><td>Zone-4</td></tr>');
                            data_array.push({fence: 'Zone 4', code: val.name,name: val.name_s});
                        }
                         if(polygon5.contains(m1.getLatLng())==true){
                            $('#geofence_table tr:last').after('<tr><td>'+val.name_s +'</td><td>'+val.name +'</td><td>Zone-5</td></tr>');
                            data_array.push({fence: 'Zone 5', code: val.name,name: val.name_s});
                        }
                         if(polygon6.contains(m1.getLatLng())==true){
                            $('#geofence_table tr:last').after('<tr><td>'+val.name_s +'</td><td>'+val.name +'</td><td>Zone-6</td></tr>');
                            data_array.push({fence: 'Zone 6', code: val.name,name: val.name_s});
                        }
                         if(polygon7.contains(m1.getLatLng())==true){
                            $('#geofence_table tr:last').after('<tr><td>'+val.name_s +'</td><td>'+val.name +'</td><td>Zone-7</td></tr>');
                            data_array.push({fence: 'Zone 7', code: val.name,name: val.name_s});
                        }
                        if(polygon8.contains(m1.getLatLng())==true){
                            $('#geofence_table tr:last').after('<tr><td>'+val.name_s +'</td><td>'+val.name +'</td><td>Zone-8</td></tr>');
                            data_array.push({fence: 'Zone 8', code: val.name, name: val.name_s});
                        }
                     
                        zone_count+=1;
                        
                    }
                });

                     }
                    //     console.log(i);
                    //    if(i==total_rec){
                    //         
                    //    }       
                    
                }); 
            
       }
        
    });
   
 
    });
setTimeout(function(){  $('#geofence_table').DataTable({ "order": [],"pageLength": 50}); $('#pdf_export_button').show();   }, 12000);
</script>


