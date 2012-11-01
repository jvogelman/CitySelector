function WikipediaView(element, cityNameElement) {
	
	var _this = this;
	this._tabbedContentView = new TabbedContentView(element);
	this._cityNameElement = cityNameElement;
	
	this.tryPage = function(options, i) {
	        var pageName = options[i];
	        $.getJSON("http://en.wikipedia.org/w/api.php?action=parse&format=json&callback=?", 
			{page:pageName, prop:"text", redirects:"true"}, // #### redirects is a parameter that takes no value ?
			function(parsed_json) {
			  	var parse = parsed_json.parse;
			  	var text = parse.text;
	  	  		var textVal = parse.text['*'];
	  	  		  	
				if (_this.entryFound(textVal)) {			  	  		  	
	  	  			// inspect here if page was found or not
					_this.displayPage(pageName);
					$(_this._cityNameElement).html(pageName.replace('_', ' '));
	  	  			return true;
				} else {
					i++;
					if (i in options) {
						_this.tryPage(options, i);
					} else {
						$(_this.el).html('I got nothin\'');
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
	
					// clear wikipedia tabs and content
					_this._tabbedContentView.clearItems();
					
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
							_this.addTab(pageName, name, index);
							
						}
	    			}
		  	});
	}
	
	this.addTab = function(pageName, name, number) {
		
		$.getJSON("http://en.wikipedia.org/w/api.php?action=parse&format=json&callback=?", 
				{page:pageName, prop:"text", redirects:"true", section:number},
				function(parsed_json) {
					var str = parsed_json.parse.text['*'];
					//str = _this.removeReferences(str);
					
					var item;
					if (number == 0) {
						item = _this._tabbedContentView.addItem(number, name, str, true); 
					} else {
						item = _this._tabbedContentView.addItem(number, name, str, false); 
					}
					//wikipediaView.appendItem(item);

					$('strong:contains("Cite error")').remove();
				}
		);
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
	

}
