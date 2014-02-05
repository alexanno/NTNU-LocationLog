<?php
error_reporting(E_ERROR);

//assume keys exist and make global
if(key_exists("logid", $_GET)) {
	if(key_exists("download", $_GET)) {
		getCSV($_GET['logid'],TRUE);
	} if(key_exists("getXYs", $_GET)) {
		getXYs($_GET['logid']);
	} else {
		getCSV($_GET['logid'],false);	
	}
	
}

if(key_exists("logids", $_GET)) {
	getLogIDs();
}


function getConnection() {
	return new PDO("sqlite:data/locations.sqlite");
}

function getLogIDs() {
	try {
		$conn = getConnection();
		$query = "SELECT DISTINCT logid FROM locations"; 

		$q = $conn->query($query);

		//$q->execute();
		$first_row = true;
		while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
			$out = "";
			foreach ($row as $key => $value) {
				$out .= '<option value='.$value.'>'.$value.'</option>';
			}
			
			echo $out . "\n";
		}
	} catch(PDOException $e) {
		print 'Exception : ' . $e -> getMessage();
		return false;
	}	
}

function getXYs($logID) {
	try {
		$conn = getConnection();
		$query = "SELECT geo_lat,geo_lng FROM locations WHERE logid='".$logID."'"; 

		$q = $conn->query($query);

		//$q->execute();
		$first_row = true;
		$out = "[";
		while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
			$out .= json_encode($row) . ",";
		}
		$out = substr($out, 0,strlen($out)-1);
		$out .= "]";
		echo $out;
	} catch(PDOException $e) {
		print 'Exception : ' . $e -> getMessage();
		return false;
	}	
}

function getCSV($logID,$download=false) {
	try {
		$conn = getConnection();
		$query = "SELECT * FROM locations WHERE logid = '".$logID."'"; 

		$q = $conn->query($query);
		if($download) {
			header("Content-type: application/csv");
			header("Content-Disposition: attachment; filename=".str_replace(" ", "_",$logID).".csv");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		//$q->execute();
		$first_row = true;
		while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
			
			if ($first_row) {
			    $first_row = false;
			    foreach ($row as $key => $value) {
					$out .= $key . ';';
				}
				
				$out = substr($out, 0,strlen($out)-1);
				echo $out . "\n";
				
			}
			$out = "";
			foreach ($row as $key => $value) {
				$out .= $value . ';';
			}
			$out = substr($out, 0,strlen($out)-1);
			$out = str_replace(".", ",", $out);
			echo $out . "\n";
		}
	} catch(PDOException $e) {
		print 'Exception : ' . $e -> getMessage();
		return false;
	}	
}

?>
