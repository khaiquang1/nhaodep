var map = "";//new GMap2(document.getElementById("map_canvas"));
var geocoder = "";//new GClientGeocoder();;
function initMap() {
  var myLatLng = {lat:$lat, lng: $lng};
  // Create a map object and specify the DOM element for display.
  var map = new google.maps.Map(document.getElementById('map_canvas'), {
    center: myLatLng,
    scrollwheel: true,
    zoom: 16,
  });
  var marker = new google.maps.Marker({
    map: map,
    position: myLatLng,
    title: 'Your address',
	draggable:true
  });
	google.maps.event.addListener(marker,'drag',function(event) {
		document.getElementById('latitude').value = event.latLng.lat();
		document.getElementById('longitude').value = event.latLng.lng();
			/*
		var infowindow = new google.maps.InfoWindow({
			content: 'latitude: ' + event.latLng.lat() + '<br>Longitude: ' + event.latLng.lng()
		  });
		infowindow.open(map,marker); */
	});

	google.maps.event.addListener(marker,'dragend',function(event) 
			{
		document.getElementById('latitude').value =event.latLng.lat();
		document.getElementById('longitude').value =event.latLng.lng();
		/*
		var infowindow = new google.maps.InfoWindow({
			content: 'latitude: ' + event.latLng.lat() + '<br>Longitude:'+event.latLng.lng()
		  });
		infowindow.open(map,marker); */
	});
}
function initialize(address) {
	var map = new google.maps.Map(document.getElementById('map_canvas'), {
	  zoom: 16,
	  center: {lat: 10.786602, lng: 106.691505}
	});
	var geocoder = new google.maps.Geocoder();
	//document.getElementById('submit').addEventListener('click', function() {
	  geocodeAddress(geocoder, map, address);
	//});
} 
function initialize2() {
	if (GBrowserIsCompatible()) {
	map = new GMap2(document.getElementById("map_canvas"));
	map.setCenter(new GLatLng(10.786602, 106.691505), 1);
	map.setUIToDefault();
	geocoder = new GClientGeocoder();
  }
}

function geocodeAddress(geocoder, resultsMap, address) {
        var address = document.getElementById('address').value;
        geocoder.geocode({'address': address}, function(results, status) {
          if (status === google.maps.GeocoderStatus.OK) {
			var latLng=results[0].geometry.location;
            resultsMap.setCenter(results[0].geometry.location);
            var marker = new google.maps.Marker({
              map: resultsMap,
              position: latLng,
			  draggable:true
            });
			document.getElementById('latitude').value = latLng.lat();
			document.getElementById('longitude').value = latLng.lng();
			google.maps.event.addListener(marker,'drag',function(event) {
				document.getElementById('latitude').value = event.latLng.lat();
				document.getElementById('longitude').value = event.latLng.lng();
					/*
				var infowindow = new google.maps.InfoWindow({
					content: 'latitude: ' + event.latLng.lat() + '<br>Longitude: ' + event.latLng.lng()
				  });
				infowindow.open(map,marker); */
			});

			google.maps.event.addListener(marker,'dragend',function(event) 
					{
				document.getElementById('latitude').value =event.latLng.lat();
				document.getElementById('longitude').value =event.latLng.lng();
				/*
				var infowindow = new google.maps.InfoWindow({
					content: 'latitude: ' + event.latLng.lat() + '<br>Longitude:'+event.latLng.lng()
				  });
				infowindow.open(map,marker); */
			});
          } else {
				alert('Geocode was not successful for the following reason: ' + status);
          }
        });
      }
	  
function showAddress(address){
	$address=$("#address").val();
	$cityname=$("#cityname").val();
	$districtname=$("#districtname").val();
	address=$address+", "+$districtname+", "+$cityname+", Viet Nam";
    initialize(address); 
}
function dataparse(point2,position)
{
	var wrkar = point2.split(",",2) ;			// Break each point into x and y
	return parseFloat(wrkar[position]); 
}
function cuttext2(point2,position)
{
	var wrkar = point2.split(":",2);
	return wrkar[position]; 
}
function callShowmap(){
	address=$('#address').val();
	address1 = address + ", " + $("#districts option:selected").text() + ", " + $("#cities option:selected").text()+" city, Viet Nam";
	return showAddress(address1);
}