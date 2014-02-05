<?php
error_reporting(E_ERROR);
require_once 'geopos/geopos.php';

//assume keys exist and make global
$in['logID'] = $_POST['logid'];
$in['logdesc'] = $_POST['logdesc'];
$in['identifier'] = $_POST['identifier'];
$in['type'] = $_POST['type'];

$in['geolocLat'] = $_POST['geolocLat'];
$in['geolocLng'] = $_POST['geolocLng'];

//$macChange = $_POST['macchange'];

//debugging
//$in['identifier'] = "78.91.46.230";$in['type'] = "IPv4";

init($in);

function getConnection() {
	return new PDO("sqlite:data/locations.sqlite");
}

function init($in) {
	//make input variables variables in the function scope
	foreach ($in as $key => $value) {
		$$key = $value;
	}	
	
	$geopos = new geopos();
	
	$geopos = $geopos->getLocation($identifier,$type);
	
	$identifier = $geopos->identifier;
	$type = $geopos->type;
	$localX = $geopos->localX;
	$localY = $geopos->localY;
	$utmLat = $geopos->utmLat;
	$utmLng = $geopos->utmLng;
	$geolon = $geopos->geoLon; 
	$geolat = $geopos->geoLat;
	$floor = $geopos->floorLevel;
	$confidence = $geopos->confidence;
	$XYdump = $geopos->XYdump;
	$errors = $geopos->getErrors();
	
	savelocation($geopos,$in);
	

	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');

	$out = '{
		"identifier": "' . $identifier .'",
		"type": "' . $type .'",
		"localX": "' . $localX .'",
		"localY": "' . $localY .'",
		"utmLat": "' . $utmLat .'",
		"utmLng": "' . $utmLng .'",
		"geolng": "' . $geolon .'",
		"geolat": "' . $geolat .'",
		"geolocLat": "' . $geolocLat .'",
		"geolocLng": "' . $geolocLng .'",
		"floor": "' . $floor .'",
		"confidence": "' . $confidence .'",
		"timestamp": "'.strftime('%c').'",
		"XYdump": ' . json_encode($XYdump) .',
		"errors": ' . json_encode($errors) .'
	}';
	echo $out;
}
function saveLocation($geoPosObj,$in) {
	try {
		//make input variables variables in the function scope
		foreach ($in as $key => $value) {
			$$key = $value;
		}

		$conn = getConnection();
		$query = "INSERT INTO locations(logid,logdesc,timestamp,local_x,local_y,local_floor,geo_lat,geo_lng,geo_floor,geoloc_lat,geoloc_lng,utmlat,utmlng,confidence,logbin) 
			VALUES (
				'" . $logID ."',
				'" . $logdesc ."',
				'".strftime('%s')."',
				'".$geoPosObj->localX."',
				'".$geoPosObj->localY."',
				'".$geoPosObj->floorLevel."',
				'".$geoPosObj->geoLat."',
				'".$geoPosObj->geoLon."',
				'".$geoPosObj->floorLevel."',
				'".$geolocLat."',
				'".$geolocLng."',
				'".$geoPosObj->utmLat."',
				'".$geoPosObj->utmLng."',
				'".$geoPosObj->confidence."',
				'".json_encode($geoPosObj->XYdump)." || ".json_encode($geoPosObj->getErrors())."')"; 

		$q = $conn->prepare($query);
		
		$q->execute();
		
		return true;
	} catch(PDOException $e) {
		print 'Exception : ' . $e -> getMessage();
		return false;
	}	
}

?>
