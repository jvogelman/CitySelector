<?php 

require_once 'Image.php';
require_once 'Util.php';

class City {
	
	//static public $_Cities;
	
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
		$city = new City($mysqli, false, $cityName, $row['LastImageDisplayed'], $row['LastImageRetrieved']);
		return $city;
	}
	
	function __construct($mysqli, $addToDB, $cityName, $lastImageDisplayed, $lastImageRetrieved) { 
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
			// #### put anything here?
		}
		
		/*if (self::$_Cities == null) {
			self::$_Cities = array();
		}
		
		self::$_Cities[$this->_cityName] = $this;*/
		
	} 
	
	function addImage($index, $link, $thumbnailWidth, $thumbnailHeight, $visible) {
		$_images[$index] = new Image($this->_mysqli, $this->_id, $index, $link, $thumbnailWidth, $thumbnailHeight, $visible);
	}
	
	function setImageVisibility($index, $visible) {
		
	}
	
	function getVisibleImages() {

		static $stmt = null;
		if ($stmt == null) {
			// construct a prepared statement to prevent SQL Injection and to look for this city
			$preparedStmt = "SELECT Image.Link, Image.ThumbnailWidth, Image.ThumbnailHeight FROM City, Image WHERE " .
					"City.Name = ? AND City.ID = Image.CityID AND Image.Visible = true";
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
}

