<?php

class Image {
	
	public $_cityId;
	public $_index;
	public $_link;
	public $_thumbnailWidth;
	public $_thumbnailHeight;
	public $_visible;
	public $_mysqli;
	
	function __construct($mysqli, $addToDB, $cityId, $index, $link, $thumbnailWidth, $thumbnailHeight, $visible) {
		$this->_cityId = $cityId;
		$this->_index = $index;
		$this->_link = $link;
		$this->_thumbnailWidth = $thumbnailWidth;
		$this->_thumbnailHeight = $thumbnailHeight;
		$this->_visible = $visible;
		$this->_mysqli = $mysqli;
		
		
		if ($addToDB) {
			static $stmt = null;
			if ($stmt == null) {
				// construct a prepared statement to prevent SQL Injection and to add this image
				$preparedStmt = "INSERT into Image VALUES(?, ?, ?, ?, ?, ?)";
				if (!($stmt = $this->_mysqli->prepare($preparedStmt))) {
					$str = "Prepare failed: (" . $this->_mysqli->errno . ") " . $this->_mysqli->error;
					throw new Exception($str);
				}
			}
			
			$stmt->bind_param( "iisiii", $cityId, $index, $link, $thumbnailWidth, $thumbnailHeight, $visible );
			$stmt->execute();
		}
	}
	
	function setVisibility($visible) {
		static $stmt = null;
		if ($stmt == null) {
			// construct a prepared statement to prevent SQL Injection and to add this image
			$preparedStmt = "Update Image set Visible = ? where Image.cityId = ? and Image.Index = ?";
			if (!($stmt = $this->_mysqli->prepare($preparedStmt))) {
				$str = "Prepare failed: (" . $this->_mysqli->errno . ") " . $this->_mysqli->error;
				throw new Exception($str);
			}
		}
		
		
		$stmt->bind_param( "iii", $visible, $this->_cityId, $this->_index );
		$stmt->execute();

		$this->_visible = $visible;
	}
}