<?php

require_once 'Util.php';
require_once 'City.php';
require_once 'queryGoogleImages.php';

header('content-type: application/json; charset=utf-8');
header("access-control-allow-origin: *");


try
{
	if (isset($_GET['City']) && isset($_GET['ImageIndex'])) {

		$mysqli = connectToDatabase();
		if ($mysqli == null) {
			echo 'failed to connect to database';
			return;
		}

		$cityName = $_GET['City'];
		$imageIndex = $_GET['ImageIndex'];

		$city = City::GetCity($mysqli, $cityName);
			
		if ($city == null) {
			throw new Exception('Cannot find City in database');
		}
		
		$city->setImageVisibility($imageIndex, 0);
		
		$lastImageDisplayed = $city->getLastImageDisplayed();
		$lastImageRetrieved = $city->getLastImageRetrieved();
		
		// we get 10 images at a time from Google to save on the number of queries we make to their servers
		
		// do we have at least one image in our database for this city that we haven't used yet, or do we have to make
		// a new query?
		if ($lastImageDisplayed == $lastImageRetrieved) {
			// need more images, so make a JSON request to Google Images
			$json_images = queryForImages($cityName, $lastImageRetrieved + 1);			
			$items = $json_images['items'];		
			
			// create new Image entries
			for ($index = 0; $index < count($items); $index++) {
				
				$item = $items[$index];
				$link = $item['link'];
				$thumbnailWidth = $item['image']['thumbnailWidth'];
				$thumbnailHeight = $item['image']['thumbnailHeight'];
				$lastImageRetrieved++;
								
				if ($lastImageRetrieved == $lastImageDisplayed + 1) {
					// first image gets set to visible since this is what we are returning to the client to display
					$city->addImage($lastImageRetrieved, $link, $thumbnailWidth, $thumbnailHeight, true);
				} else {
					$city->addImage($lastImageRetrieved, $link, $thumbnailWidth, $thumbnailHeight, false);
				}
			}
			
			$lastImageDisplayed++;
			
			$city->setLastImageDisplayed($lastImageDisplayed);
			$city->setLastImageRetrieved($lastImageRetrieved);
		} else {
			// we already have an image to extract from our database, so get it

			$lastImageDisplayed++;				
			$city->setLastImageDisplayed($lastImageDisplayed);
			$city->setImageVisibility($lastImageDisplayed, 1);
		}


		$item = $city->getLastDisplayedImage();
			
		// return the first item to the user
		echo $_GET['callback'] . '(' . json_encode($item) . ')';
	}

} catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}