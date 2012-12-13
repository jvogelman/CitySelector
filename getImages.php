<?php 
header('content-type: application/json; charset=utf-8');
header("access-control-allow-origin: *");


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

function connectToDatabase(){
	$mysqli = new mysqli("localhost", "root", "rromE1(", "City");
	if ($mysqli->connect_errno) {
		return null;
	}
	return $mysqli;
}

function addCityToDatabase($mysqli, $cityName, $lastImageDisplayed, $lastImageRetrieved) {
	static $stmt = null;
	if ($stmt == null) {
		// construct a prepared statement to prevent SQL Injection and to add this city
		$preparedStmt = "INSERT into City (Name, LastImageDisplayed, LastImageRetrieved) VALUES(?, ?, ?)";
		if (!($stmt = $mysqli->prepare($preparedStmt))) {
			$str = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			throw new Exception($str);
		}
	}

	$stmt->bind_param( "sii", $cityName, $lastImageDisplayed, $lastImageRetrieved );
	$stmt->execute();
}

function addCityImageToDatabase($mysqli, $cityId, $index, $link, $thumbnailWidth, $thumbnailHeight, $visible) {
	static $stmt = null;
	if ($stmt == null) {
		// construct a prepared statement to prevent SQL Injection and to add this image
		$preparedStmt = "INSERT into Image VALUES(?, ?, ?, ?, ?, ?)";
		if (!($stmt = $mysqli->prepare($preparedStmt))) {
			$str = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			throw new Exception($str);
		}
	}
	
	$stmt->bind_param( "iisiii", $cityId, $index, $link, $thumbnailWidth, $thumbnailHeight, $visible );
	$stmt->execute();
}

function getImagesForCity($mysqli, $cityName) {
	static $stmt = null;
	if ($stmt == null) {
		// construct a prepared statement to prevent SQL Injection and to look for this city
		$preparedStmt = "SELECT Image.Link, Image.ThumbnailWidth, Image.ThumbnailHeight FROM City, Image WHERE ' + 
				'City.Name = ? AND City.ID = Image.CityID AND Image.Visible = true";
		if (!($stmt = $mysqli->prepare($preparedStmt))) {
			$str = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			throw new Exception($str);
		}
	}
	
	$stmt->bind_param( "s", $cityName);
	$stmt->execute();
	
	$images = extractArrayFromSelect($stmt);
	if (count($images) == 0) {
		return null;
	} else {
		return $images;
	}
}

try
{
	if (isset($_GET['City'])) {
		
		$mysqli = connectToDatabase();
		if ($mysqli == null) {
			echo 'failed to connect to database';
			return;
		}
		
		$city = $_GET['City'];
		$images = getImagesForCity($mysqli, $city);
		if ($images == null) {
			
			// if not, make a JSON request to Google Images
			$googleKey = 'AIzaSyAhIqD8IE7ad2O1W_elcwc9fGrpY3-cTRw';
			$customSearchEngineIdentifier = '002564124849599434674:zamvpdxusfu';
			$url = 'https://www.googleapis.com/customsearch/v1?key=' . $googleKey . '&cx=' . $customSearchEngineIdentifier . '&q=' .
					$city . '&searchType=image&count=10&imgType=photo';
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
			$json_result = curl_exec($ch);
			curl_close($ch);
			
			$json_images = json_decode($json_result, true);
			
			$items = $json_images['items'];
			
			// create new City entry
			$cityId = addCityToDatabase($mysqli, $city, 10, 10);
			
			// create new Image entries
			for ($imageIndex = 0; $imageIndex < 10; $imageIndex++) {
				
				$item = $items[$imageIndex];
				$link = $item['link'];
				$thumbnailWidth = $item['image']['thumbnailWidth'];
				$thumbnailHeight = $item['image']['thumbnailHeight'];
				
				addCityImageToDatabase($mysqli, $cityId, $imageIndex, $link, $thumbnailWidth, $thumbnailHeight, true);
			}
				
			$images = getImagesForCity($mysqli, $city);
		}
		
		echo $_GET['callback'] . '('.json_encode($images).')';
		//echo json_encode($images);
		return;
	}

} catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
	
