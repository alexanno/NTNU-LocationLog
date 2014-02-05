var positioningURL = 'https://ntnu.pos.campusguiden.no/position?callback=?';

var logging = false;

$(document).ready(function() {
	$.get("getIP.php", function(data) {
		$("#notifications").append("<b>IP-address: " + data.ip + "</b><br>");
		
	});
	
	$("#start").click(function() {
		if(logging != false) {
			clearInterval(logging);
			logging = false;
			$("#start").val("Start");
		} else {
			getCampusGuideLocation();
			logging = setInterval(getCampusGuideLocation,$("#interval").val());
			$("#start").val("Stop");
		}
	});
});

var writtenHeader = false;
function writeHeaders(object) {
	$("#notifications").append("timestamp\t");
	for(var key in object) {
		$("#notifications").append(key + "\t");
	}
	$("#notifications").append("\n---------------------------------------------------------------------------------------------------------------------------------------------- \n");
	writtenHeader = true;
}


function getCampusGuideLocation() {
	$.getJSON(positioningURL, function(data) {
			if(writtenHeader == false) {
				writeHeaders(data);
			}
			var d = new Date();
			$("#notifications").append(d.getTime() + "\t");
			for(var key in data) {
				$("#notifications").append(data[key] + "\t");
			}
			$("#notifications").append("\n");
			//console.log(data);
	});
}
