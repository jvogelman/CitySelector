<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    	
    <link rel="stylesheet" href="../js/bootstrap/css/bootstrap.min.css">
    <style type="text/css">
      	html { height: 100% }
      	body { height: 100%; margin: 0; padding: 0 }
      	#map_canvas { height: 100% }
      	#wikipedia {
			height: 100%;
			overflow: scroll;
			padding: 5px;
	  	}
	  	#cityName {
	  		padding: 10px;
	  	}
	  		  	
    </style>
    <script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
    <script type="text/javascript"
      src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAhIqD8IE7ad2O1W_elcwc9fGrpY3-cTRw&libraries=places&sensor=false">
    </script>
	<script type="text/javascript" src="../js/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../js/underscore.js"></script>
    <script type="text/javascript" src="../js/backbone.js"></script>
    <script type="text/javascript" src="./util.js"></script>
    <script type="text/javascript" src="./tabbedContentView.js"></script>
    <script type="text/javascript" src="./wikipediaView.js"></script>
    <script type="text/javascript">

    var wikipediaView;
    var map;

	// trim spaces before and after a string
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/g, "");
    };

 	
        

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
    	

	// Try the following:
	// in U.S.: 'administrative area level 2, administrative area level 1'
	//			or 'administrative area level 3, administrative area level 1'
	//			or 'locality, administrative area level 1'
	// foreign:
	// could be: 'locality, country'
	// could be: 'administrative area level 1, country'
	// could be: 'administrative area level 2, country'
	// sometimes just 'administrative area level 1'
	// sometimes only country is available
    function showWikipedia(geocoderResults) {

    	var addrComp = getAddressComponents(geocoderResults);
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

            wikipediaView.tryPage(options, 0);
            

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
            
            wikipediaView.tryPage(options, 0);
        	
    	}
			
    }

	    
    function initialize() {
		var mapOptions = {
	    	center: new google.maps.LatLng(37, -121),
	        zoom: 8,
	        mapTypeId: google.maps.MapTypeId.ROADMAP,
	        disableDoubleClickZoom: true
	    };
	    map = new google.maps.Map(document.getElementById("map_canvas"),
	    	mapOptions);


	    var user_input = document.getElementById('location');

	    var options = {
	    	    bounds: new google.maps.LatLngBounds(),
	    	    types: ['geocode']
	    };
	    var userAutocomplete = new google.maps.places.Autocomplete(user_input, options);
    	
    	//var geocoder = new google.maps.Geocoder();
	      
    	//google.maps.event.addListener(map, 'mouseup', function(e) 	    
		google.maps.event.addListener(map, 'dblclick', function(e) {
    	    var center = map.getCenter();
    	    var lat = center.Xa;
    	    var lng = center.Ya;
    	    var str = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' + lat + ',' + lng + '&sensor=false';

    	    var geocoder = new google.maps.Geocoder(); // for some reason, this needs to be constructed every time; otherwise, sometimes
    	    											// the map will not come up
    	    //var latlng = new google.maps.LatLng(lat, lng);
    	    geocoder.geocode({'latLng': e.latLng}, function(results, status) {
    	    	// get all possible city,country or city,state combinations and try each at wikipedia until we get a match
    	    	//displayResults(results);

    	    	showWikipedia(results);
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


		$('#goButton').live('click', function(e) {
			var location = $('#location').val();
			var page = location.trim();
			wikipediaView.tryPage([page], 0,
				function(){
					// page found! reposition the map and get the wikipedia page
					
					// get lat/lng for location so we can move the map			
					var geocoder = new google.maps.Geocoder(); // for some reason, this needs to be constructed every time; otherwise, sometimes
		    	    											// the map will not come up
		    	    geocoder.geocode( { 'address': page}, function(results, status) {
						if (status == google.maps.GeocoderStatus.OK) {
							map.setCenter(results[0].geometry.location);
					        
					    } else {
					        alert("Geocode was not successful for the following reason: " + status);
					    }
					});
				}
			);
		});

		wikipediaView = new WikipediaView($('#wikipedia'), $('#cityName'));
	}
    </script>
  </head>
  <body onload="initialize()">
  	<!--  
  	<div class='row-fluid' style='border-width:1px;border-style:solid;height:100%;background-color:red'>
  	<span class='span6'><div id='map_canvas'>abc</div></span>
  	<span class='span6'><div id='wikipedia'>def</div></span>
  	</div>-->
  	
  	<div class='row-fluid' style='height:10%'>
  	<h1><span class='span4 offset1'><em><span id='cityName'></span></em></span></h1>
  	<span class='span4 offset3'>City or Zip Code: <input type='text' id='location' placeholder='Please enter a location'/><button id='goButton'>Go!</button></span>
  	</div>
    <table style='width:100%;height:100%'>
    <!--  
    <tr style='height:10%'>
    	<td><h1><em><span id='cityName'></span></em></h1></td>
    </tr>
    -->
    <tr style='height:90%'>
    <td style='border-width:1px;border-style:solid;width:50%;' valign=top><div id="map_canvas" style=" height:100%"></div></td>

	<td style='border-width:1px;border-style:solid;' valign=top><div id="wikipedia" style="height:100%"></div></td>
    	
    </tr></table>
    <div id="status"></div>
  	<div id="results"><table></table></div>
  </body>
</html>