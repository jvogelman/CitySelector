<?php

function extractArrayFromSelect($stmt) {
	$parameters = array();
	$results = array();
	$meta = $stmt->result_metadata();
	while ( $field = $meta->fetch_field() ) {
		$parameters[] = &$row[$field->name];
	}
	call_user_func_array(array($stmt, 'bind_result'), $parameters);
	while ( $stmt->fetch() ) {
		$x = array();
		foreach( $row as $key => $val ) {
			$x[$key] = $val;
		}
		$results[] = $x;
	}
	return $results;
}

try
{
	if (isset($_GET['City'])) {
		// do we already have this city in our database?
		$mysqli = new mysqli("localhost", "root", "rromE1(", "City");
		if ($mysqli->connect_errno) {
			echo "Failed to connect to MySQL: " . $mysqli->connect_error;
			return;
		} 
		
		// construct a prepared statement to prevent SQL Injection and to look for this city
		if (!($stmt = $mysqli->prepare("SELECT Image FROM CityImages WHERE CityName = ?"))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		$city = $_GET['City'];
		$stmt->bind_param( "s", $city); 
		$stmt->execute();
		
		$images = extractArrayFromSelect($stmt);
		echo "found " . count($images) . " images";
		
		// if not, make a JSON request to Google Images
	}

} catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
	
