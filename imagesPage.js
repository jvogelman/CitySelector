
function imageError(img, displayFunction) {
	$(img).remove();
	
	//if (displayFunction != null) {
	//	displayFunction("just a test");
	//}
}

function ImagesPage(key, customSearchEngineIdentifier, elementWidth) {

	var _this = this;
	
	this._html = '';
	
	elementWidth -= 20;	// #### I couldn't figure out why it was necessary to add this...
	
	this.displayImages = function(items) {
		
		
	}
	
	this.search = function(searchStr, displayFunction) {
		
		var numImages = 20;
		var imageHeight = 150;
		var minMargin = 5;
		
		var url = 'https://www.googleapis.com/customsearch/v1?key=' + key + '&cx=' + customSearchEngineIdentifier + '&q=' + 
			searchStr + '&searchType=image&count=' + numImages + '&imgType=photo';
		$.getJSON(url, {},
			function(result) {
				var returnStr = '';
				//_this.displayImages(result.items);
				numImg = result.items.length;
				
				// for each row, get the total width occupied by images
				// (this will be useful later when we determine how much margin to add between images)
				var currentRow = 0;
				var rowImageWidth = new Array();	// the current number of pixels occupied by image in this row
				rowImageWidth[currentRow] = 0;
				var numImages = new Array();		// the number of images in each row
				numImages[currentRow] = 0;
				var rowCurrWidth = minMargin;	// the current number of pixels (occupied by image or by margin) in this row
				
				for (var i = 0; i < numImg; i++, numImages[currentRow]++) {
					var origWidth = result.items[i].image.thumbnailWidth;
					var origHeight = result.items[i].image.thumbnailHeight;			
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
					
				}
				
				currentRow = 0;
				var imagesThisRow = 0;
				
				for (var i = 0; i < numImg; i++) {
					var origWidth = result.items[i].image.thumbnailWidth;
					var origHeight = result.items[i].image.thumbnailHeight;			
					var newWidth = imageHeight * origWidth/origHeight;
					
					// what is the width of the margin in this row?
					var marginWidth = (elementWidth - rowImageWidth[currentRow]) / (numImages[currentRow] + 1);
						
					returnStr += '<img class="googleImage" onerror="imageError(this, ' + displayFunction + ')" src="' + result.items[i].link + '" height="' + imageHeight + 
					' " width="' + newWidth + '" style="margin-left:' + marginWidth + 'px;margin-right:0px;margin-top:0px;margin-bottom:8px;padding:0px"/>';
				
					imagesThisRow++;
					if (imagesThisRow == numImages[currentRow]) {
						currentRow++;
						imagesThisRow = 0;
						returnStr += '<br>';
					}
					
				}
				
				this._html = returnStr;
				
				if (displayFunction != null) {
					displayFunction(this._html);
				}
			}
		);
	}
	
}