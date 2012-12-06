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
		if (!($stmt = $mysqli->prepare("SELECT Image, ThumbnailWidth, ThumbnailHeight FROM CityImages WHERE CityName = ?"))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		$city = $_GET['City'];
		$stmt->bind_param( "s", $city); 
		$stmt->execute();
		
		$images = extractArrayFromSelect($stmt);
		
		if (count($images) > 0) {
			// we have this city in our table
		
			echo json_encode($images);
			return;
		} else {
			
			// if not, make a JSON request to Google Images
			$googleKey = 'AIzaSyAhIqD8IE7ad2O1W_elcwc9fGrpY3-cTRw';
			$customSearchEngineIdentifier = '002564124849599434674:zamvpdxusfu';
			//$url = 'https://www.googleapis.com/customsearch/v1?key=' . $googleKey . '&cx=' + $customSearchEngineIdentifier . '&q=' .
			//	$city . '&searchType=image&count=10&imgType=photo&format=json&callback=?';
			$url = 'https://www.googleapis.com/customsearch/v1?key=' . $googleKey . '&cx=' . $customSearchEngineIdentifier . '&q=' .
					$city . '&searchType=image&count=10&imgType=photo';
			//echo 'url = ' . $url;	
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
			$json_result = curl_exec($ch);
			curl_close($ch);
			
			$json_images = json_decode($json_result, true);
			//echo 'json_images: ' . var_dump($json_images);
			if (isset($json_images['items'])) {
				$items = $json_images['items'];
				
				foreach ($items as $item) {
					$link = $item['link'];
					$thumbnailWidth = $item['image']['thumbnailWidth'];
					$thumbnailHeight = $item['image']['thumbnailHeight'];
					
				}
				
			} else {
				echo 'items not in array';
			}
		}
	}

} catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
	
