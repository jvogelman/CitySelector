<?php 

require_once 'Image.php';
require_once 'Util.php';

class City {
	
	
	public $_images;
	public $_id;
	public $_cityName;
	public $_lastImageDisplayed;
	public $_lastImageRetrieved;
	public $_mysqli;
	
	static function GetCity($mysqli, $cityName) {
		
		static $stmt = null;
		if ($stmt == null) {
			// construct a prepared statement to prevent SQL Injection and to add this city
			$preparedStmt = "SELECT id, LastImageDisplayed, LastImageRetrieved from City where Name = ?";
			if (!($stmt = $mysqli->prepare($preparedStmt))) {
				$str = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				throw new Exception($str);
			}
		}		
		
		$stmt->bind_param( "s", $cityName );
		$stmt->execute();
		$arr = extractArrayFromSelect($stmt);
		if (count($arr) == 0) {
			return null;
		}
		
		$row = $arr[0];
		$city = new City($mysqli, false, $cityName, $row['LastImageDisplayed'], $row['LastImageRetrieved'], $row['id']);
		return $city;
	}
	
	function __construct($mysqli, $addToDB, $cityName, $lastImageDisplayed, $lastImageRetrieved, $cityId = -1) { 
		$this->_mysqli = $mysqli;
		$this->_cityName = $cityName;
		$this->_lastImageDisplayed = $lastImageDisplayed;
		$this->_lastImageRetrieved = $lastImageRetrieved;
		
		if ($addToDB) {
			$this->_images = array();
			
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
			$this->_id = $stmt->insert_id;
		} else {

			$this->_id = $cityId;
			
			// get the Images for this city from the database and fill up our array
			$images = $this->getAllImages();
			for ($index = 0; $index < count($images); $index++) {
				$image = $images[$index];
				$this->addImage($image['Index'], $image['Link'], $image['ThumbnailWidth'], $image['ThumbnailHeight'], $image['Visible'], false);
			}
		}
		
	} 
	
	function addImage($index, $link, $thumbnailWidth, $thumbnailHeight, $visible, $addToDB = true) {
		$this->_images[$index] = new Image($this->_mysqli, $addToDB, $this->_id, $index, $link, $thumbnailWidth, $thumbnailHeight, $visible);
	}
	
	function setImageVisibility($index, $visible) {
		$this->_images[$index]->setVisibility($visible);
	}
	

	// ### this could be expanded to take as a parameter an array of fieldNames instead
	function getField($fieldName) {
		static $getStmt = array();
		if (!array_key_exists($fieldName, $getStmt)) {
			// construct a prepared statement to prevent SQL Injection and to look for this city
			$preparedStmt = "SELECT " . $fieldName . " FROM City WHERE id = ?";
			if (!($getStmt[$fieldName] = $this->_mysqli->prepare($preparedStmt))) {
				$str = "Prepare failed: (" . $this->_mysqli->errno . ") " . $this->_mysqli->error;
				throw new Exception($str);
			}
		}
		
		$getStmt[$fieldName]->bind_param( 'i', $this->_id);
		$getStmt[$fieldName]->execute();
		$arr = extractArrayFromSelect($getStmt[$fieldName]);
		if (count($arr) == 0) {
			return null;
		}
		
		return $arr[0][$fieldName];
	}
	
	function setField($fieldName, $typeIndicator, $value) {
		static $setStmt = array();
		if (!array_key_exists($fieldName, $setStmt)) {
			// construct a prepared statement to prevent SQL Injection and to look for this city
			$preparedStmt = "UPDATE City SET " . $fieldName . "= ? WHERE id = ?";
			if (!($setStmt[$fieldName] = $this->_mysqli->prepare($preparedStmt))) {
				$str = "Prepare failed: (" . $this->_mysqli->errno . ") " . $this->_mysqli->error;
				throw new Exception($str);
			}
		}
		
		$setStmt[$fieldName]->bind_param( $typeIndicator . 'i', $value, $this->_id);
		$setStmt[$fieldName]->execute();		
	}
	
	function getLastImageDisplayed() {
		/*
	
		static $stmt = null;
		if ($stmt == null) {
			// construct a prepared statement to prevent SQL Injection and to look for this city
			$preparedStmt = "SELECT LastImageDisplayed FROM City WHERE id = ?";
			if (!($stmt = $this->_mysqli->prepare($preparedStmt))) {
				$str = "Prepare failed: (" . $this->_mysqli->errno . ") " . $this->_mysqli->error;
				throw new Exception($str);
			}
		}
		
		$stmt->bind_param( "i", $this->_id);
		$stmt->execute();
		$arr = extractArrayFromSelect($stmt);
		if (count($arr) == 0) {
			return null;
		}
		
		return $arr[0]['LastImageDisplayed'];*/
		return $this->getField('LastImageDisplayed', 'i');
		
	}
	
	function getLastImageRetrieved() {
		/*
	
		static $stmt = null;
		if ($stmt == null) {
			// construct a prepared statement to prevent SQL Injection and to look for this city
			$preparedStmt = "SELECT LastImageRetrieved FROM City WHERE id = ?";
			if (!($stmt = $this->_mysqli->prepare($preparedStmt))) {
				$str = "Prepare failed: (" . $this->_mysqli->errno . ") " . $this->_mysqli->error;
				throw new Exception($str);
			}
		}
		
		$stmt->bind_param( "i", $this->_id);
		$stmt->execute();
		$arr = extractArrayFromSelect($stmt);
		if (count($arr) == 0) {
			return null;
		}
		
		return $arr[0]['LastImageRetrieved'];*/
		return $this->getField('LastImageRetrieved', 'i');
		
	}

	function setLastImageDisplayed($value) {
		$this->setField('LastImageDisplayed', 'i', $value);
	}

	function setLastImageRetrieved($value) {
		$this->setField('LastImageRetrieved', 'i', $value);
	}
	
	function getAllImages() {

		static $stmt = null;
		if ($stmt == null) {
			// construct a prepared statement to prevent SQL Injection and to look for this city
			$preparedStmt = "SELECT * FROM City, Image WHERE " .
					"City.Name = ? AND City.ID = Image.CityID";
			if (!($stmt = $this->_mysqli->prepare($preparedStmt))) {
				$str = "Prepare failed: (" . $this->_mysqli->errno . ") " . $this->_mysqli->error;
				throw new Exception($str);
			}
		}
		
		$stmt->bind_param( "s", $this->_cityName);
		$stmt->execute();
		
		$images = extractArrayFromSelect($stmt);
		if (count($images) == 0) {
			return null;
		} else {
			return $images;
		}
	}
	
	function getVisibleImages() {

		static $stmt = null;
		if ($stmt == null) {
			// construct a prepared statement to prevent SQL Injection and to look for this city
			$preparedStmt = "SELECT Image.Link, Image.ThumbnailWidth, Image.ThumbnailHeight, Image.Index FROM Image WHERE " .
					"Image.CityID = ? AND Image.Visible = true";
			if (!($stmt = $this->_mysqli->prepare($preparedStmt))) {
				$str = "Prepare failed: (" . $this->_mysqli->errno . ") " . $this->_mysqli->error;
				throw new Exception($str);
			}
		}
		
		$stmt->bind_param( "i", $this->_id);
		$stmt->execute();
		
		$images = extractArrayFromSelect($stmt);
		if (count($images) == 0) {
			return null;
		} else {
			return $images;
		}
	}
	
	function getLastDisplayedImage() {

		static $stmt = null;
		if ($stmt == null) {
			// construct a prepared statement to prevent SQL Injection and to look for this city
			$preparedStmt = "SELECT Image.Link, Image.ThumbnailWidth, Image.ThumbnailHeight, Image.Index FROM Image " . 
				"INNER JOIN City ON Image.Index = City.LastImageDisplayed WHERE Image.CityId = ? AND City.id = ?";
			if (!($stmt = $this->_mysqli->prepare($preparedStmt))) {
				$str = "Prepare failed: (" . $this->_mysqli->errno . ") " . $this->_mysqli->error;
				throw new Exception($str);
			}
		}
		
		$stmt->bind_param( "ii", $this->_id, $this->_id);
		$stmt->execute();
		
		$images = extractArrayFromSelect($stmt);
		if (count($images) == 0) {
			return null;
		} else {
			return $images[0];
		}
		
	}
}

