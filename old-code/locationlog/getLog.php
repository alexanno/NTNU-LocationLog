<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>index</title>
	<meta name="author" content="Alexander" />
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<style></style>
	<script>
var map;
$(document).ready(function() {	
	$.get("getCSVLog.php?logids=true", function(data) {
		$("#selectbox").append(data);
	});

	$("select#selectbox").change(function(){
		//map.destroy();
	    getCSVLog($(this).children(":selected").html().replace(" ","%20"));
	});

	$('a#lastned').click(function(){
		
  		window.open('getCSVLog.php?download&logid='+$("select#selectbox").children(":selected").html().replace(" ","%20"), 'window name', 'window settings');
  		return false;
	});

	
});

function getCSVLog(logid) {
	console.log(logid);
	$.get("getCSVLog.php?logid="+logid, function(data) {
		$("#log").html(data);
	});
	
	addMap(logid);
}

</script>
	

	</script>
	
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
	<style>
	#map_canvas {
    	height: 500px;
    	width: 500px;
  	}
	</style>
	  
    <script type="text/javascript">
		function addMap(logid) {
			
		    var myLatlng = new google.maps.LatLng(-25.363882,131.044922);
		    var myOptions = {
		      zoom: 4,
		      center: myLatlng,
		      mapTypeId: google.maps.MapTypeId.ROADMAP
		    }
		    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		    
		    
		     
			$.get("getCSVLog.php?getXYs&logid="+logid, function(data) {
				data = eval(data);
				var latlngbounds = new google.maps.LatLngBounds();
				for (i=0; i<data.length; i++) {
					var latlng = new google.maps.LatLng(data[i].geo_lat, data[i].geo_lng);
					latlngbounds.extend(latlng);
					var marker = new google.maps.Marker({
				        position: latlng, 
				        map: map,
				        title: data[i].geo_lat+","+data[i].geo_lng
				    });
				}
				
				map.setCenter(latlngbounds.getCenter());
				map.fitBounds(latlngbounds); 
			});			
		
		}
		
	</script>

  </head>
	
</head>
<body>
<label>Log ID</label>
<select id="selectbox"></select> 
<a id="lastned" href="">last ned</a>
<div style="float: right;" id="map_canvas"></div>

<pre id="log"></pre>

</body>
</html>

