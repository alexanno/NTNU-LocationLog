<?php
  //if(!defined('KONAJA')) die('File cannot be invoked directly');

  /*
   * Created on 03.jul.2007
   * Author: Haakon Jensen
   */
class GeoPosXML
{
  // From constructor
  public $config;
  public $type;
  public $identifier;

  // Geopos
  public $response;
  public $xmlParsed;
  public $errors;

  // Location
  public $geoLon;
  public $geoLat;

  public $XYdump = 0;
  public $localX = 0;
  public $localY = 0;
  public $utmLat = 0;
  public $utmLng = 0;
  public $confidence = 0;
  public $floorLevel;

  /**
   * Constructor
   *
   * @param config array $config
   * @param mac/ip $type
   * @param macadr/ip $identifier
   */
  function __construct($config, $type, $identifier) {
    //Enable debug mode if wanted.
    if($config['debugmode']) {
      error_reporting(E_ALL);
      ini_set('error_reporting', E_ALL);
      ini_set('display_errors', 1);
    }

    $this->errors = array();
    $this->geoLon = 0;
    $this->geoLat = 0;
	$this->floorLevel = null;
    $this->config = $config;
    $this->type = $type;
    $this->identifier = $this->checkidentifier($identifier, $type);
    $this->send();
    $this->parseResponse($this->response);
    		//print_r($config);
    if($this->config['debugmode'])
      $this->varDump($this->errors);
  }

  /*
   * Send query to webservice
   */
  public function send() {
    //	print_r($this);
    try {
      // Connect to geopos webservice
      $c = new SoapClient($this->config['wsdl'], array(
                                                       'location' => $this->config['host'],
                                                       'local_cert' => $this->config['cert'],
                                                       'passphrase' => $this->config['certpass']
                                                       ));

      if($this->config['debugmode'] == TRUE)
        echo '<p>Geopos inputs to getLocationFor: Type: '.
          $this->type.', identifier: '.$this->identifier.'</p>';

      // @todo implement support for Eduroam
      // Get location, params user, password, targettype (MAC/IPV4), targetID (mac w/::, IP)
      $this->response = $c->getLocationFor(
                                           $this->config['user'],
                                           $this->config['password'],
                                           $this->type,
                                           $this->identifier
                                           );
    } catch (SoapFault $exception) {
      $this->response = $exception;
	  
	  //$this->errors["send exception"] = $exception;
    }
  }

  /*
   *  Parse the response from Geopos
   */
  private function parseResponse($response) {
    // If response is a soapfault object we dont want to continue
    
    if(is_object($this->response) && get_class($this->response) == 'SoapFault') {
      $this->errors['soap_fail'] = $this->response;

      return;
    } else {
      try
        {
          $this->xmlParsed = new SimpleXMLElement($response);
        }
      catch (Exception $e)
        {
          // Server malfunction?
          $this->errors['soap_fail'] = $e->getMessage();
          return;
        }
      if($this->config['debugmode'])
      	$this->varDump($this->xmlParsed);

      // Check if error element exists, if so add to error array
      if(count($this->xmlParsed->ResponseBody->ErrorList) > 0) {
        $this->errors['geopos_error'] = $this->xmlParsed->ResponseBody->ErrorList;
        // Get our so longed for location coordinates.
      } elseif(count($this->xmlParsed->ResponseHeader->ErrorList) > 0) {
        $this->errors['geopos_error'] = $this->xmlParsed->ResponseHeader->ErrorList;
      } else {
      	$this->XYdump = $this->xmlParsed->ResponseBody->XYPosition;
		$this->localX = $this->xmlParsed->ResponseBody->XYPosition->X;
		$this->localY = $this->xmlParsed->ResponseBody->XYPosition->Y;
		$this->utmLat = $this->xmlParsed->ResponseBody->XYPosition->Latitude;
		$this->utmLng = $this->xmlParsed->ResponseBody->XYPosition->Longitude;
		$this->confidence = $this->xmlParsed->ResponseBody->XYPosition->ConfidenceFactor;
        $this->geoLon = $this->xmlParsed->ResponseBody->XYPosition->geoLongitude;
        $this->geoLat = $this->xmlParsed->ResponseBody->XYPosition->geoLatitude;
		$this->floorLevel = str_replace(" ", "_", $this->xmlParsed->ResponseBody->XYPosition->Elem[2]);
		
      }
    }
  }

  /**
   * Checks if the given mac-addr is on the valid format
   *
   * @param string $mac (mac adr without ::)
   * @return string (mac addr with ::)
   */
  private function checkidentifier($identifier, $type) {
    // @todo if IP adress not is in accepted eduroam range, create specific errormsg

    // Validate MAC on TrT-format: 000G6A55CD76
    if($type == 'MAC') {

      // Return and create error if input identifier doesnt match trt-format
      if(strlen($identifier) != 12) {
        $this->errors['mac_error'] = 'Input MAC not valid TRT: '.$identifier;
        return $identifier;
      }

      // Create valid format for Geopos
      $mac = $identifier;
      for($i=2; $i<15; $i=$i+3)
        $mac=substr_replace($mac, ':', $i, 0);

      // Check if mac is valid, if not return identifier and add error
      // add to lowercase because geopos only accepts lowercase macs.
      if(preg_match("/([A-F0-9]{2}\:?){6}/",$mac))
        return strtolower($mac);
      else {
        $this->errors['mac_error'] = 'Input MAC not valid: '.$identifier;
        return $identifier;
      }

      // Validate valid IPv4-adr
    } elseif ($type == 'IPv4') {
      $ipv4 = $identifier;

      // Check that format of the ip address is matched
      if(preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/",$ipv4)) {
        // Check parts in range
        $parts=explode(".",$ipv4);
        foreach($parts as $ip_parts)
          if(intval($ip_parts)>255 || intval($ip_parts)<0) {
            $this->errors['ipv4_error'] = 'Input IPv4 not valid: '.$identifier;
            return $identifier;
          }

        // Return IP
        return $ipv4;

        // If input ip doesnt match format, return error
      } else {
        $this->errors['ipv4_error'] = 'Input IPv4 not valid: '.$identifier;
        return $identifier;
      }
    }
  }

  /**
   * Access functions
   *
   */
  public function getGeoLon() {	return $this->geoLon; }
  public function getGeoLat()  {return $this->geoLat;  }
  public function getFloor()  {	return $this->floorLevel;  }
  public function getErrors() { return $this->errors; }

  /**
   * Debug
   */
  private function varDump($var) { echo "<pre>\n\n"; var_dump($var); echo "</pre>\n\n"; }
}

?>