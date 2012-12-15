<?php 

require_once 'City.php';

header('content-type: application/json; charset=utf-8');
header("access-control-allow-origin: *");



function connectToDatabase(){
	$mysqli = new mysqli("localhost", "root", "rromE1(", "City");
	if ($mysqli->connect_errno) {
		return null;
	}
	return $mysqli;
}


try
{
	if (isset($_GET['City'])) {
		
		$mysqli = connectToDatabase();
		if ($mysqli == null) {
			echo 'failed to connect to database';
			return;
		}
		
		$cityName = $_GET['City'];
		
		
		$city = City::GetCity($mysqli, $cityName);
			
		if ($city == null) {
			
			// if not, make a JSON request to Google Images
			$googleKey = 'AIzaSyAhIqD8IE7ad2O1W_elcwc9fGrpY3-cTRw';
			$customSearchEngineIdentifier = '002564124849599434674:zamvpdxusfu';
			$url = 'https://www.googleapis.com/customsearch/v1?key=' . $googleKey . '&cx=' . $customSearchEngineIdentifier . '&q=' .
					$cityName . '&searchType=image&count=10&imgType=photo';
			
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
			$city = new City($mysqli, true, $cityName, 10, 10);
			
			
			// create new Image entries
			for ($imageIndex = 0; $imageIndex < 10; $imageIndex++) {
				
				$item = $items[$imageIndex];
				$link = $item['link'];
				$thumbnailWidth = $item['image']['thumbnailWidth'];
				$thumbnailHeight = $item['image']['thumbnailHeight'];
								
				$city->addImage($imageIndex + 1, $link, $thumbnailWidth, $thumbnailHeight, true);
			}
		}
		
		$images = $city->getVisibleImages();
		
		echo $_GET['callback'] . '('.json_encode($images).')';
		return;
	}

} catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
	
