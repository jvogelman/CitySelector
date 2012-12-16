<?php

function getGoogleKeyInfo(&$googleKey, &$customSearchEngineIdentifier) {
	$googleKey = 'AIzaSyAhIqD8IE7ad2O1W_elcwc9fGrpY3-cTRw';
	$customSearchEngineIdentifier = '002564124849599434674:zamvpdxusfu';
}

function queryForImages($cityName, $startIndex = 1) {
	$googleKey;
	$customSearchEngineIdentifier;
	getGoogleKeyInfo($googleKey, $customSearchEngineIdentifier);
	$url = 'https://www.googleapis.com/customsearch/v1?key=' . $googleKey . '&cx=' . $customSearchEngineIdentifier . '&q=' .
			$cityName . '&searchType=image&count=10&imgType=photo&start=' . $startIndex;

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	$json_result = curl_exec($ch);
	curl_close($ch);

	$json_images = json_decode($json_result, true);
	return $json_images;
}