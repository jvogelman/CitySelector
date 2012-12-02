function WikipediaView(element, cityNameElement) {
	
	var _this = this;
	this._tabbedContentView = new TabbedContentView(element);
	this._cityNameElement = cityNameElement;        		
	this._pageName = '';
	
	this.tryPage = function(options, i, successFunction, errorFunction) {
	        var pageName = options[i];
	        _this._pageName = '';
	        

			// clear wikipedia tabs and content
			_this._tabbedContentView.clearItems();
	        
	        $.getJSON("http://en.wikipedia.org/w/api.php?action=parse&format=json&callback=?", 
			{page:pageName, prop:"text", redirects:"true"}, // #### redirects is a parameter that takes no value ?
			function(parsed_json) {
			  	var parse = parsed_json.parse;
			  	
			  	if (parse) {
				  	var text = parse.text;
				  	if (text) {
				  		
				  		var textVal = parse.text['*'];
		  	  		  	
						if (_this.entryFound(textVal)) {			  	  		  	
			  	  			// inspect here if page was found or not
							_this.displayPage(pageName);
							$(_this._cityNameElement).html(pageName.replace(/_/g, ' '));
							_this._pageName = pageName;
							
							if (successFunction != null) {
								successFunction();
							}
			  	  			return;
						}
				  	}
			  	}
					
				// not found
				i++;
				if (i in options) {
					_this.tryPage(options, i, successFunction, errorFunction);
				} else {
					$(_this._tabbedContentView.el).html('I got nothin\'');
					if (errorFunction != null) {
						errorFunction();
					}
				}
			  	
	  	  	});
	
		
	}
	
	this._exclusionList = new Array('References', 'References and notes', 'See also', 'External links', 'Further reading');
	
	// return true if tabName is on the _exclusionList
	this.exclude = function(tabName) {
		for (var i = 0; i < _this._exclusionList.length; i++) {
			if (_this._exclusionList[i] == tabName) {
				return true;
			}
		}
		
		return false;
	}
	
	this.displayPage = function(pageName, textVal) {
		// add description at the top followed by tabbed nav bar of contents
	
		// get the section names/numbers in the document
		$.getJSON("http://en.wikipedia.org/w/api.php?action=parse&format=json&callback=?", 
				{page:pageName, prop:"sections", redirects:"true"},
				function(parsed_json) {
					var sections = parsed_json.parse.sections;
	
					
	    			for (var i = 0; i < sections.length; i++) {
						var name = sections[i].line;
						var index = sections[i].index;
						var level = sections[i].level;
						var number = sections[i].number;
						
						if (_this.exclude(name)) {
							continue;
						}
	
						// if it's level 2, let's devote a tab to it
						if (level == 2) {
							_this.addWikipediaTab(pageName, name, index);
							
						}
	    			}
		  	});
	}
	
	this.addWikipediaTab = function(pageName, name, number) {
		
		$.getJSON("http://en.wikipedia.org/w/api.php?action=parse&format=json&callback=?", 
				{page:pageName, prop:"text", redirects:"true", section:number},
				function(parsed_json) {
					var str = parsed_json.parse.text['*'];
					
					str = _this.removeStr(str, '[edit]');
					
					var item;
					if (number == 0) {
						item = _this._tabbedContentView.addItem(name, str, true); 
					} else {
						item = _this._tabbedContentView.addItem(name, str, false); 
					}
					//wikipediaView.appendItem(item);

					$('strong:contains("Cite error")').remove();
				}
		);
	}
	
	this.addTab = function(name, html, active) {
		_this._tabbedContentView.addItem(name, html, active);		
	}
	
	this.numTabs = function() {
		return this._tabbedContentView.numItems();
	}
	
	
	this.entryFound = function(text) {
	    // if the entire result is commented out, return false
	    	
	    text = text.trim();
	    var len = text.length;
		if ((text.substr(0, 4) == '<!--') && (text.substr(len - 3, 3) == '-->')) {
			// look for any end comments in the middle of the text
			var middle = text.substring(4, len - 4);
			if (middle.indexOf('-->') == -1) {
					return false; 
			}
		}
	
		return true;
	}
	
	// remove the passed string 
	this.removeStr = function(str, searchStr) {
		var returnStr = '';
		var searchIndex = 0;
		var foundStr = '';
		
		// go through the string character by character: ignore html tags and locate the characters in searchStr
		// build up returnStr to include everything but any instances of searchStr
		for (var index = 0; index < str.length; index++) {
			var char = str[index];
			
			while (char == '<' || char == searchStr[searchIndex]) {
				if (char == '<') {
					// add the entire tag to returnStr
					while (char != '>') {
						returnStr += char;
						index++;
						char = str[index];
					} 
					returnStr += '>';
					index++;
					char = str[index];
				} else {
					// looks like we might be finding the searchStr
					foundStr += char;
					if (foundStr == searchStr) {
						// we found it!
						foundStr = '';
						searchIndex = 0;
						index++;
						char = str[index];
						continue;
					}
					index++;
					char = str[index];
					searchIndex++;
				}
			}
			
			if (foundStr != '') {
				// we thought we were finding searchStr, but it turned out it wasn't it
				returnStr += foundStr;
				foundStr = '';
				searchIndex = 0;
			}
			
			returnStr += char;
		}
		
		return returnStr;
	}
}



