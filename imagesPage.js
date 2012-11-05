function ImagesPage(key, customSearchEngineIdentifier, elementWidth) {

	var _this = this;
	
	this._html = '';
	
	this.search = function(searchStr, successFunction) {
		
		var numImages = 20;
		var imageHeight = 150;
		var imagePadding = 8;
		
		var url = 'https://www.googleapis.com/customsearch/v1?key=' + key + '&cx=' + customSearchEngineIdentifier + '&q=' + 
			searchStr + '&searchType=image&count=' + numImages;
		$.getJSON(url, {},
			function(result) {
				var returnStr = '<table><tr>';
				numImages = result.items.length;
				var currWidth = imagePadding;
				
				for (var i = 0; i < numImages; i++) {
					var origWidth = result.items[i].image.thumbnailWidth;
					var origHeight = result.items[i].image.thumbnailHeight;			
					var newWidth = imageHeight * origWidth/origHeight;
					
					if (currWidth + newWidth > elementWidth) {
						returnStr += '</tr><tr>';
						currWidth = imagePadding;
					} else {
						currWidth += newWidth + imagePadding;
					}
						
					returnStr += '<td style="padding:' + imagePadding + 'px"><img src="' + result.items[i].link + '" height="' + imageHeight + '"/></td>';
					
				}
				
				returnStr += '</tr></table>';
				this._html = returnStr;
				
				if (successFunction != null) {
					successFunction(this._html);
				}
			}
		);
	}
	
}