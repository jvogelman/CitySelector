<!DOCTYPE HTML>
<!--<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 5.0 Transitional//EN">
   <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">-->
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    	
    <link rel="stylesheet" href="/js/bootstrap/css/bootstrap.min.css">
    
    <style type="text/css">
      	html { height: 100% }
      	body { height: 100%; margin: 0; padding: 0 }
      	#map_canvas { 
      		height: 100% ;
      		border-width: 3px;
      		border-style: solid;
      	}
      	#wikipedia {
			height: 100%;
			overflow: scroll;
			padding: 5px;
	  	}
	  	#cityName {
	  		padding: 10px;
	  		font-family: Georgia;
	  	}
	  	#location {
			border: 1px solid #666;
			height: 24px;
		}
		#goButton {
			height: 32px;
		}
		
		
	  		  	
    </style>
    
    <script type="text/javascript">
		var googleKey = 'AIzaSyAhIqD8IE7ad2O1W_elcwc9fGrpY3-cTRw';
		var customSearchEngineIdentifier = '002564124849599434674:zamvpdxusfu';
    </script>
    
    <script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
    <script type="text/javascript"
      src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAhIqD8IE7ad2O1W_elcwc9fGrpY3-cTRw&libraries=places&sensor=false">
    </script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>		<!-- Google Loader -->
	<script type="text/javascript" src="/js/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/underscore-min.js"></script>
    <script type="text/javascript" src="/js/backbone-min.js"></script>
    <script type="text/javascript" src="/CitySelector/tabbedContentView.js"></script>
    <script type="text/javascript" src="/CitySelector/wikipediaView.js"></script>
    <script type="text/javascript" src="/CitySelector/imagesPage.js"></script>
	<script type="text/javascript" src="/js/jquery.cookie.js"></script>
    <script type="text/javascript">

    var wikipediaView;
    var map;
    var stateAcronymsByStateName;
    var stateAcronymsByAcronym;

	// trim spaces before and after a string
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/g, "");
    };

    function MapMarker(wikiPageName, latLng) {
    	this._marker = new google.maps.Marker({
	        map:map,
	        draggable:false,
	        animation: google.maps.Animation.DROP,
	        position: latLng
	      });   
		this._wikiPageName = wikiPageName;
		var _this = this;

		$.cookie('latitude', latLng.lat());
		$.cookie('longitude', latLng.lng());

		google.maps.event.addListener(this._marker, 'click', 
			function() {
				wikipediaView.tryPage([_this._wikiPageName], 0, 
					function() { 
						var width = $('#wikipedia').width();
						var imagesPage = new ImagesPage(googleKey, customSearchEngineIdentifier, width);  
		        	 	imagesPage.search(wikipediaView._pageName,
		                	function(html) {
		        	 			wikipediaView.addTab('Images', html, true);
		        	 		}
	        	 		);
					}
		    	);
			}
		);
    }

    // assuming wikipediaPageName represents a U.S. city, extract the city name
    function getCity(wikipediaPageName) {
        var comma;
		if ((comma = wikipediaPageName.indexOf(',_')) != '') {
			return wikipediaPageName.substr(0, comma);
		} else {
			return '';
		}
    }

    // assuming wikipediaPageName represents a U.S. city, extract the state name
    function getState(wikipediaPageName) {
        var comma;
		if ((comma = wikipediaPageName.indexOf(',_')) != '') {
			return wikipediaPageName.substr(comma + 2);
		} else {
			return '';
		}
    }

 	       
	// build up a named array of locations based on the results of the geocoder
	function getAddressComponents(geocoderResults) {
		var addrComp = new Array();
		for (var i = 0; i < geocoderResults.length; i++) {
			for (var component = 0; component < geocoderResults[i].address_components.length; component++) {
				var longName = geocoderResults[i].address_components[component].long_name;
				var types = geocoderResults[i].address_components[component].types;
			  	for (var type = 0; type < types.length; type++) {
				  	var t = types[type];
				  	if (!(t in addrComp) && t != 'political') {					  	
					  	addrComp[t] = longName;
				  	}
			  	}
			}
    	}  	         				  	
		return addrComp;	  	
    }
    	

	// Try showing a wikipedia page based on the results of the geocoder: try different possibilities
    function showWikipedia(latLng, geocoderResults) {

		// get all of the different areas indicated by the geocoder results (i.e. 'country', 'locality', etc)
    	var addrComp = getAddressComponents(geocoderResults);

    	// build an array of possible pages to search for in wikipedia
        if ('country' in addrComp && addrComp['country'] == 'United States') {
        	var options = new Array();
            var num=0;
            if ('administrative_area_level_1' in addrComp && 'locality' in addrComp) {
            	options[num] = addrComp['locality'] + ',_' + addrComp['administrative_area_level_1'];
            	num++;
            }
            if ('administrative_area_level_1' in addrComp && 'administrative_area_level_2' in addrComp) {
            	options[num] = addrComp['administrative_area_level_2'] + ',_' + addrComp['administrative_area_level_1'];
            	num++;
            }
            if ('administrative_area_level_1' in addrComp && 'administrative_area_level_3' in addrComp) {
            	options[num] = addrComp['administrative_area_level_3'] + ',_' + addrComp['administrative_area_level_1'];
            	num++;
            }
            

        } else {
        	var options = new Array();
            var num=0;
            if ('locality' in addrComp && 'country' in addrComp) {
            	options[num] = addrComp['locality'] + ',_' + addrComp['country'];
            	num++;
            }
            if ('administrative_area_level_2' in addrComp && 'country' in addrComp) {
            	options[num] = addrComp['administrative_area_level_2'] + ',_' + addrComp['country'];
            	num++;
            }
            if ('administrative_area_level_1' in addrComp && 'country' in addrComp) {
            	options[num] = addrComp['administrative_area_level_1'] + ',_' + addrComp['country'];
            	num++;
            }
            if ('administrative_area_level_1' in addrComp) {
            	options[num] = addrComp['administrative_area_level_1'];
            	num++;
            }
            if ('country' in addrComp) {
            	options[num] = addrComp['country'];
            	num++;
            }
        	
    	}

		// using the array we built, start by searching for the first possible page and continue until we find one
		// or until we run out of page names
        wikipediaView.tryPage(options, 0, 
        	// success function
        	function (){
        	 	var marker = new MapMarker(wikipediaView._pageName, latLng);  
        	 	var width = $('#wikipedia').width();
				var imagesPage = new ImagesPage(googleKey, customSearchEngineIdentifier, width); 
        	 	imagesPage.search(wikipediaView._pageName,
                	function(html) {
        	 			wikipediaView.addTab('Images', html, true);
        	 		}
    	 		);
        	}
        );
    }

    $(window).load(function(){    	
    	// fade in and out popovers to explain how to use the page
    	$('#map_canvas').popover({placement: 'top', content: 'Double click on a city on the map...',
			animation: 'true', trigger: 'manual' });
		$('#location').popover({placement: 'left', content: '...or enter a city over here', animation: 'true', trigger: 'manual' });
        $('#map_canvas').popover('show');
        window.setTimeout(function() {
        	$('#map_canvas').popover('hide');

    		$('#location').popover({content: '...or enter a city over here'}); // this is a redundant, but trying to figure out why it's not working...    		
        	$('#location').popover('show');
        	 window.setTimeout(function() {
        		 $('#location').popover('hide');
        	}, 2500);
        }, 2500);
    });
	    
    $(document).ready(function(){

        // get state acronyms from server
        $.ajax({
            type: 'GET',
            dataType: 'json',
            data: {},
            url: 'stateAcronyms.php',
            success: function(data) {
            	stateAcronymsByAcronym = data['MappedByAcronym'];
            	stateAcronymsByStateName = data['MappedByStateName'];
            },
            error: function(data) {
                alert('Darn: couldn\'t get list of state acronyms');
            }
        });

        var lat, lng;
        if ($.cookie('latitude') != null && $.cookie('longitude') != null) {
            lat = $.cookie('latitude');
            lng = $.cookie('longitude');
        } else {
            lat = 34.4;
            lng = -119.7;
        }

        // construct map
		var mapOptions = {
	    	center: new google.maps.LatLng(lat, lng),
	        zoom: 7,
	        mapTypeId: google.maps.MapTypeId.ROADMAP,
	        disableDoubleClickZoom: true
	    };
	    map = new google.maps.Map(document.getElementById("map_canvas"),
	    	mapOptions);

		// input search box will allow auto-completion for city names
	    var user_input = document.getElementById('location');

	    var options = {
	    	    bounds: new google.maps.LatLngBounds(),
	    	    types: ['(cities)']
	    };
	    var userAutocomplete = new google.maps.places.Autocomplete(user_input, options);

	    // when we double click on a city, show the corresponding wikipedia page
		google.maps.event.addListener(map, 'dblclick', function(e) {
    	    var center = map.getCenter();
    	    var lat = center.lat();
    	    var lng = center.lng();
    	    //var str = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' + lat + ',' + lng + '&sensor=false';

    	    var geocoder = new google.maps.Geocoder(); // for some reason, this needs to be constructed every time; otherwise, sometimes
    	    											// the map will not come up
    	    //var latlng = new google.maps.LatLng(lat, lng);
    	    geocoder.geocode({'latLng': e.latLng}, function(results, status) {
    	    	// get all possible city,country or city,state combinations and try each at wikipedia until we get a match
    	    	//displayResults(results);

    	    	showWikipedia(e.latLng, results);
    	    });
	
		    	    
		});

    	// callbacks for wikipedia links: set external links to open in a new window
		$('a').live('click', function(e) {
			var link = $(this).attr('href');

			// locally bookmarked items: load these into our wikipedia window
			if (link[0] == '#') {
				return true;
			}

			// links that start with '/' should actually be links into the wikipedia site
			if (link[0] == '/') {
				link = 'http://en.wikipedia.org' + link;
			}

			// open external links in a new window
			window.open(link, '', 'width=' + screen.width * .75 + ', height=' + screen.height * .75 + 
					', left=' + screen.width * .125 + ', top=' + screen.height * .125);
			return false;
		});

		function fixCase(cityName) {
			// in case user didn't enter city name in correct case, fix it
			// each first letter should be upper case, and each successive letter should be lower case

			var finalName = cityName.substr(0, 1).toUpperCase();
			cityName = cityName.substr(1);
			
			// locate the spaces
			var space;
			while ((space = cityName.indexOf(' ')) != -1) {
				// all but the first letter of the current word
				finalName += cityName.substr(0, space + 1).toLowerCase();
				cityName = cityName.substr(space + 1);

				// first letter of next word
				finalName += cityName.substr(0, 1).toUpperCase();
				cityName = cityName.substr(1);
			}

			// add the rest of the string
			if (cityName != '') {
				finalName += cityName.toLowerCase();
			}
			
			return finalName;
		}

		// when the "Go" button is clicked, look for the wikipedia page and if found, move the map to the new location
		$('#goButton').live('click', function(e) {
			var location = $('#location').val();
			var page = location.trim();

			// if user specifies state name by acronym, convert to full state name
			var comma;
			if ((comma = page.indexOf(',')) != -1) {
				var stateOrCountry = page.substr(comma + 1).trim().toUpperCase();
				if (stateOrCountry.length == 2 && stateAcronymsByAcronym[stateOrCountry] != undefined) {
					page = page.substr(0, comma + 1) + ' ' + stateAcronymsByAcronym[stateOrCountry];
				}	
			}

			// each first letter should be upper case, and each successive letter should be lower case (wikipedia won't find page
			// otherwise ))
			page = fixCase(page);
			// replace spaces with commas
			var space;
			while ((space = page.indexOf(' ')) != -1) {
				page = page.replace(' ', '_');
			}			
			
			wikipediaView.tryPage([page], 0,
				// success function
				function(){
					// page found! reposition the map and get the wikipedia page
					
					// get lat/lng for location so we can move the map			
					var geocoder = new google.maps.Geocoder(); // for some reason, this needs to be constructed every time; otherwise, sometimes
		    	    											// the map will not come up
		    	    geocoder.geocode( { 'address': page}, function(results, status) {
						if (status == google.maps.GeocoderStatus.OK) {
							map.setCenter(results[0].geometry.location);
					        
				    	    var marker = new MapMarker(wikipediaView._pageName, results[0].geometry.location);      
				    	    var width = $('#wikipedia').width();
							var imagesPage = new ImagesPage(googleKey, customSearchEngineIdentifier, width);     
			        	 	imagesPage.search(wikipediaView._pageName,
			                	function(html) {
			        	 			wikipediaView.addTab('Images', html, true);
			        	 		}
			    	 		);
				    	      
					    } else {
					        alert("Geocode was not successful for the following reason: " + status);
					    }
					});
				},
				// error function
				function() {
					alert('I\'m sorry. Looks like wikipedia doesn\'t have an entry for ' + page);
				}
			);
		});

		wikipediaView = new WikipediaView($('#wikipedia'), $('#cityName'));
    
		$('#location').keypress( function(e) {
	        if(e.which == 13) {	// enter button
		        e.preventDefault();
		        $('#goButton').click();
	        }
		});

		$('#location').tooltip({trigger:'hover', placement: 'bottom', title: 'Format: "City, State" or "City, Country"',
			animation: 'true', delay: { show: 200, hide: 100 }});
    });
    </script>
    <title>City Viewer</title>
  </head>
  <body>
  	<div class='row-fluid' style='height:10%'>
  	<h1><div class='span6 offset1'><span id='cityName'></span></div></h1>
  	<div class='span4 offset1 input-append'><h4 style="display:inline"><span style='vertical-align:middle'>City: </span></h4><input type='text' id='location' placeholder='Please enter a location' ><button id='goButton'>Go!</button></div>
  	</div>

	<div class='row-fluid' style='height:90%'>
		<div class='span6' id="map_canvas"></div>
		<div class='span6' id="wikipedia"></div>
	</div>
  </body>
</html>