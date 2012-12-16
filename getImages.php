<?php 

require_once 'Util.php';
require_once 'City.php';
require_once 'queryGoogleImages.php';

header('content-type: application/json; charset=utf-8');
header("access-control-allow-origin: *");


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
			$json_images = queryForImages($cityName);
			$items = $json_images['items'];
			
			// create new City entry
			$city = new City($mysqli, true, $cityName, count($items), count($items));			
			
			// create new Image entries
			for ($imageIndex = 0; $imageIndex < count($items); $imageIndex++) {
				
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
	
