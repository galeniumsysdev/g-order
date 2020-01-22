var map,infoWindow ;
var myLatLng;

$(document).ready(function() {
var apiGeolocationSuccess = function(position) {
    success(position);
    //alert("API geolocation success!\n\nlat = " + position.coords.latitude + "\nlng = " + position.coords.longitude);
};

var tryAPIGeolocation = function() {
    jQuery.post( "https://www.googleapis.com/geolocation/v1/geolocate?key=AIzaSyDh9yEKw9W4sFrlTFFw_cZjvnAYSeMSa2w", function(success) {
        apiGeolocationSuccess({coords: {latitude: success.location.lat, longitude: success.location.lng}});
  })
  .fail(function(err) {
    //document.getElementById("btnlogin").disabled =true;
    alert("API Geolocation error! \n\n"+err);
  });
};

var browserGeolocationSuccess = function(position) {
    alert("Browser geolocation success!\n\nlat = " + position.coords.latitude + "\nlng = " + position.coords.longitude);
    success(position);
};

var browserGeolocationFail = function(error) {
  switch (error.code) {
    case error.TIMEOUT:
      alert("Browser geolocation error !\n\nTimeout.");
      defaultmap();
      break;
    case error.PERMISSION_DENIED:
      if(error.message.indexOf("Only secure origins are allowed") == 0 || error.message.indexOf("User denied geolocation") == 0) {
        tryAPIGeolocation();
      }
      break;
    case error.POSITION_UNAVAILABLE:
      alert("Browser geolocation error !\n\nPosition unavailable.");
      defaultmap();
      break;
    default:
        alert("failed! "+error.message);
  }
};

var tryGeolocation = function() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        browserGeolocationSuccess,
      browserGeolocationFail,
      {maximumAge: 50000, timeout: 20000, enableHighAccuracy: true});
  }
};

tryGeolocation();
  /*geoLocationInit();
  function geoLocationInit()
  {
    if (navigator.geolocation){
      navigator.geolocation.getCurrentPosition(success,fail);
    }else{
      alert("Browser not supported");
      //handleLocationError(false, infoWindow, map.getCenter());
    }
  }
  function handleLocationError(browserHasGeolocation, infoWindow, pos) {
    infoWindow.setPosition(pos);
    infoWindow.setContent(browserHasGeolocation ?
                          'Error: The Geolocation service failed.' :
                          'Error: Your browser doesn\'t support geolocation.');
    infoWindow.open(map);
  }*/


  function success(position) {
       console.log(position);
       document.getElementById("btnlogin").disabled =false;
       var latval = position.coords.latitude;
       var lngval = position.coords.longitude;
       document.getElementById("langitude_txt").value=latval;
       document.getElementById("longitude_txt").value=lngval;
       myLatLng = new google.maps.LatLng(latval, lngval);
       createMap(myLatLng);
      //  nearbySearch(myLatLng, "school");
       //searchGirls(latval,lngval);
   }

   function defaultmap()
   {
     var latval = -6.242292;
     var lngval = 106.804220;
     document.getElementById("langitude_txt").value=latval;
     document.getElementById("longitude_txt").value=lngval;
     myLatLng = new google.maps.LatLng(latval, lngval);
     createMap(myLatLng);
   }

   function fail() {
        alert("You must share location before login!");
  	 document.getElementById("btnlogin").disabled =true;
	 tryGeolocation();
    }

  //var myLatLng = new google.maps.LatLng(-33.8665433,151.1956316);

  function createMap(myLatLng){
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
      /*places.forEach(function(place) {
        if (!place.geometry) {
          console.log("Returned place contains no geometry");
          return;
        }
        var icon = {
          url: place.icon,
          size: new google.maps.Size(71, 71),
          origin: new google.maps.Point(0, 0),
          anchor: new google.maps.Point(17, 34),
          scaledSize: new google.maps.Size(25, 25)
        };

        // Create a marker for each place.
        markers.push(new google.maps.Marker({
          map: map,
          icon: icon,
          title: place.name,
          position: place.geometry.location,
          draggable:true,
        }));

        if(place.geometry.location)
        {
          document.getElementById("langitude_txt").value=place.geometry.location.lat();
             document.getElementById("longitude_txt").value=place.geometry.location.lng();
        }

        if (place.geometry.viewport) {
          // Only geocodes have viewport.
          bounds.union(place.geometry.viewport);
        } else {
          bounds.extend(place.geometry.location);
        }
      });*/
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

//this is marker
  function createMarker(latlng,icn,name){
    var markers = new google.maps.Marker({
              position: latlng,
              map: map,
              icon:icn,
              label: name,
              draggable:true,
            });
  }

  function nearbySearch(myLatLng, myType){
    var request = {
            location: myLatLng,
            radius: '500',
            type: [myType]
          };

    service = new google.maps.places.PlacesService(map);
    service.nearbySearch(request, callback);

    function callback(results, status) {
      console.log(results);
      if (status == google.maps.places.PlacesServiceStatus.OK) {
        for (var i = 0; i < results.length; i++) {
          var place = results[i];
          latlng =place.geometry.location;
          icn = "https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png";
          name = place.name
          createMarker(latlng, icn,name);
        }
      }
    }
  }


});
