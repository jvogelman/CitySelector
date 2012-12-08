
function imageError(imagesPage, img) {
	imagesPage.removeImage(img);	
}

ImagesPage.prototype._ImagesPage = 0;

function ImagesPage(key, customSearchEngineIdentifier, elementWidth) {

	var _this = this;
	
	this._html = '';
	
	elementWidth -= 20;	// #### I couldn't figure out why it was necessary to add this...
	
	this._images = Array();
	this._errorTimeout = 0;
	
	
		
	this.search = function(searchStr, displayFunction) {

		ImagesPage.prototype._ImagesPage = this;

		
		_this._errorTimeout = 0;	// clear this
		
		var numImages = 20;
		
		//var url = 'https://www.googleapis.com/customsearch/v1?key=' + key + '&cx=' + customSearchEngineIdentifier + '&q=' + 
		//	searchStr + '&searchType=image&count=' + numImages + '&imgType=photo&format=json&callback=?';
		var url = 'http://localhost/CitySelector/getImages.php?City=' + searchStr;
		//var url = 'http://localhost/CitySelector/getImages.php?City=' + searchStr + '&callback=?';

		$.getJSON(url, {},
			function(result) {
				if (result == null) {
					return;
				}
				
				_this._images = result;
				
				_this._html = _this.getFormattedImages();
				
				if (displayFunction != null) {
					displayFunction(_this._html);
				}
			}
		);	
		
	}
	
	
	this.getFormattedImages = function(){
		
		var imageHeight = 150;
		var minMargin = 5;
		var returnStr = '';
		
		numImg = _this._images.length;
		
		// for each row, get the total width occupied by images
		// (this will be useful later when we determine how much margin to add between images)
		var currentRow = 0;
		var rowImageWidth = new Array();	// the current number of pixels occupied by image in this row
		rowImageWidth[currentRow] = 0;
		var numImages = new Array();		// the number of images in each row
		numImages[currentRow] = 0;
		var rowCurrWidth = minMargin;	// the current number of pixels (occupied by image or by margin) in this row
		
		for (var i = 0; i < numImg; i++) {
			
			if (_this._images[i].Image == '') {
				continue;
			}
			
			var origWidth = _this._images[i].ThumbnailWidth;
			var origHeight = _this._images[i].ThumbnailHeight;			
			var newWidth = imageHeight * origWidth/origHeight;
			
			if (rowCurrWidth + newWidth > elementWidth) {
				// no more room in this row
				currentRow++;
				rowImageWidth[currentRow] = 0;
				numImages[currentRow] = 0;
				rowCurrWidth = minMargin;
			} 
				
			rowImageWidth[currentRow] += newWidth;
			rowCurrWidth += newWidth + minMargin;
			numImages[currentRow]++;
		}
		
		currentRow = 0;
		var imagesThisRow = 0;
		
		for (var i = 0; i < numImg; i++) {
			
			if (_this._images[i].Image == '') {
				continue;
			}
			
			var origWidth = _this._images[i].ThumbnailWidth;
			var origHeight = _this._images[i].ThumbnailHeight;			
			var newWidth = imageHeight * origWidth/origHeight;
			
			// what is the width of the margin in this row?
			var marginWidth = (elementWidth - rowImageWidth[currentRow]) / (numImages[currentRow] + 1);
			
				
			returnStr += '<img class="googleImage" src="' + _this._images[i].Image + '" onerror="imageError(ImagesPage.prototype._ImagesPage, this)"' +
			'style="height:' + imageHeight + 'px;width:' + newWidth + 'px;margin-left:' + marginWidth + 'px;margin-right:0px;margin-top:0px;margin-bottom:8px;padding:0px"/>';
			
		
			imagesThisRow++;
			if (imagesThisRow == numImages[currentRow]) {
				currentRow++;
				imagesThisRow = 0;
				returnStr += '<br>';
			}
			
		}
		
		return returnStr;
	}
	
}



ImagesPage.prototype.removeImage = function(img) {
	
	for (var i = 0; i < this._images.length; i++) {
		if (this._images[i].link == img.src) {
			this._images[i].link = '';		
	
			var _this = this;
			
			// set a timer to go off to refresh the page once we can be pretty sure that
			// all image errors have been accounted for
			if (_this._errorTimeout == 0) {
				_this._errorTimeout = setTimeout(
					function()
					{
						clearTimeout(_this._errorTimeout);
						_this._html = _this.getFormattedImages();
						var parent = $(img).parent('.tab-pane');
						$(parent).delay(500).html(_this._html);
					}, 500);
			}
		}
	}
}



