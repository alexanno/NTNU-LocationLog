var logging = false;
$(document).ready(function() {
	$.get("getIP.php", function(data) {
		$("#identifier").val(data.ip);
	
	});
	
	var d=new Date();
	$("#logid").val("P1-x_"+d.getDate()+"/"+(d.getMonth()+1)+"/"+d.getFullYear()+"_1");
	
	$("#notifications").append("timestamp\t\t\tlocalX\tlocalY\tutmLat\tutmLng\tgeoLat\tgeoLng\tgeolocLat\tgeolocLng\tfloor\tconfidence\terrors\n");
	$("#notifications").append("---------------------------------------------------------------------------------------------------------------------------------------------- \n");

	$("#start").click(function() {
		if(logging != false) {
			clearInterval(logging);
			logging = false;
			$("#start").val("Start");
		} else {
			pollLocation();
			logging = setInterval(pollLocation,$("#interval").val());
			$("#start").val("Stop");	
		}
	});
	 
	//do this every X seconds
	//
});



var markers = "";
var mapCenter = null;

//https://developer.mozilla.org/en/Using_geolocation
function pollLocation() {
	if($("#usegeoloc:checked").val() == "on") {
		if(navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(storeLocation, handle_errors,{enableHighAccuracy:true, maximumAge:100, timeout:60000});
		} else {
	      	$("#notifications").append("ERROR: NOT CONNECTED TO BROWSERS GEOLOCATION, using null");
		}
	} else {
	    storeLocation(null);
	    //getCampusGuideLocation();
	}
}

function getCampusGuideLocation() {
	$.getJSON('http://app.campusguiden.no/position?callback=?', function(data) {
			console.log(data);
	});
}



function storeLocation(geolocData) {
	if(geolocData == null) {
		geolocData = {coords:{latitude:0,longitude:0}};
	}
	
	$.post("getStoreLocation.php",
	      {
	      	logid: $("#logid").val(),
	      	logdesc: $("#logdesc").val(),
	      	identifier: $("#identifier").val(),
	      	type: $("#type:checked").val(),
	      	geolocLat: geolocData.coords.latitude,
	      	geolocLng: geolocData.coords.longitude
	      },
	      function(obj)
	          {
	//          	timestamp	localX	localY	utmLat	utmLng	geoLat	geoLng	geolocLat	geolocLng	floor	confidence	errors
	
	          	var out = 
	          		obj.timestamp+"\t"
	          		+obj.localX+"\t"
	          		+obj.localY+"\t"
	          		+obj.utmLat+"\t"
	          		+obj.utmLng+"\t"
	          		+obj.geolat+"\t"
	          		+obj.geolng+"\t"
	          		+obj.geolocLat+"\t"
	          		+obj.geolocLng+"\t"
	          		+obj.floor+"\t"
	          		+obj.confidence+"\t"
	          		+JSON.stringify(obj.errors)+"\n";
	          	$("#notifications").append(out);
	          	

	          	/*
	          	mapCenter = returned_data.lat+','+returned_data.lng;
	          	markers += returned_data.lat+','+returned_data.lng + '|';
	          	if(markers.length > 1500) {
	          		markers = "";
	          	}
	          	mapURL = 'http://maps.google.com/maps/api/staticmap?center='+mapCenter+'&zoom=20&markers='+markers+'&size=500x300&sensor=false';
	          	$("#map").attr('src',mapURL);
	          	*/

	          }).error(
	          	function(event, request, settings) {
	          		console.log(event);
	          		$("#notifications").append("ERROR");
	          	}
	          );
}

function handle_errors(err) {
	$("#notifications").append(err);
}
