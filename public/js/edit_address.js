var map,infoWindow ;
var myLatLng;
var latval,lngval;

$(document).ready(function() {
  createMap();
  function createMap() {
      latval= $("#langitude_txt").val();
      lngval= $("#longitude_txt").val();      
       myLatLng = new google.maps.LatLng(latval, lngval);
       map = new google.maps.Map(document.getElementById('map'), {
               center: myLatLng,
               zoom: 18
             });
       var marker = new google.maps.Marker({
               position: myLatLng,
               map: map,
               draggable:true,
               label:"Posisi outlet"
           });
       var input = document.getElementById('pac-input');
       var searchBox = new google.maps.places.SearchBox(input);
       map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

       // Bias the SearchBox results towards current map's viewport.
       map.addListener('bounds_changed', function() {
         searchBox.setBounds(map.getBounds());
       });
       var markers = [];
       // Listen for the event fired when the user selects a prediction and retrieve
       // more details for that place.
       searchBox.addListener('places_changed', function() {
         var places = searchBox.getPlaces();

         if (places.length == 0) {
           return;
         }

         // Clear out the old markers.
         markers.forEach(function(marker) {
           marker.setMap(null);
         });
         markers = [];

         // For each place, get the icon, name and location.
         var bounds = new google.maps.LatLngBounds();
         var i, place;

         for (i=0;place=places[i];i++)
         {
           bounds.extend(place.geometry.location);
           marker.setPosition(place.geometry.location);
           document.getElementById("langitude_txt").value=place.geometry.location.lat();
           document.getElementById("longitude_txt").value=place.geometry.location.lng();
         }

         map.fitBounds(bounds);
         map.setZoom(18);
       });

       //infoWindow = new google.maps.InfoWindow;
       google.maps.event.addListener(marker, 'dragend', function (event) {
     	  document.getElementById("langitude_txt").value=this.getPosition().lat();
            document.getElementById("longitude_txt").value=this.getPosition().lng();
            //document.getElementById("pac-input").value=this.formatted_address;
       });
   }

});
