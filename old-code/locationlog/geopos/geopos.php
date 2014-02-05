<?php
error_reporting(E_ERROR);
require_once 'GeoPosXML.class.php';

class geopos {
	function getConfig() {
	/**
	 * For http host, cert and certpass is not used.
	 */
		$config = array(
			"debugmode" => false, 
			"wsdl" => "geopos/geopos.wsdl",/*Watch out for path */
			//"host" => "https://geoposen.item.ntnu.no:8443/geofinder/ws/geofinder/ws/",
			//"host" => "http://geoposen.item.ntnu.no:8080/ws4ee/services/GposRequest/", 
			//"host" => "http://195.18.202.5:8443/geofinder/ws/geofinder/ws/",
			 
			"host" => "http://195.18.202.5:8080/ws4ee/services/GposRequest/", # Fungerer, men 8080 ønskes lukket
			//"host" => "https://195.18.202.5:8443/geofinder/ws/geofinder/ws/", #CampusGuide bruker denne
			
			"cert" => "gposSpinOff_chain.pem",
			//"cert" => "gposkey.pem",
			"certpass" => "spinspin", 
			"user" => "geomatikk:Campus", 
			"password" => "Aesais0yzie6Eg3a"
		);
		return $config;
	}
	//$type = "MAC";
	//$identifyer = "1093E9445CEF";//ipad
	//$identifyer = "00216A5B409E"; //hedvig
	//$identifyer = "10::93::E9::44::5C::EF";
	//$type = "IPv4";
	//$identifyer = "78.91.45.56";
	
	function getLocation($identifier,$type) {

		$geopos = new GeoPosXML($this->getConfig(), $type, $identifier);
		
		return $geopos;	
	}
}
?>
