/*jshint devel:true */
/*global google */

function AdifierMapInit( fieldEl ) {

	var searchInput = jQuery('.map-search', fieldEl ).get(0);
	var mapCanvas   = jQuery('.map-holder', fieldEl ).get(0);
	var latitude    = jQuery('input[name="lat"]', fieldEl );
	var longitude   = jQuery('input[name="long"]', fieldEl );
	var country   	= jQuery('input[name="country"]', fieldEl );
	var state   	= jQuery('input[name="state"]', fieldEl );
	var city   		= jQuery('input[name="city"]', fieldEl );
	var street   	= jQuery('input[name="street"]', fieldEl );


	if( latitude.length == 0 ){
		return false;
	}

	if( adifier_map_data.map_source == 'google' ){

		var componentForm = {
			route: 'long_name',
			street_number: 'long_name',
			locality: 'long_name',
			administrative_area_level_1: 'long_name',
			country: 'long_name',
		};

		var mapOptions = {
			center:    new google.maps.LatLng( 0,0 ),
			zoom:      3,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		if( typeof adifier_data !== 'undefined' ){
			mapOptions.styles = adifier_data.map_style ? JSON.parse( adifier_data.map_style ) : '';
		}

		var map      = new google.maps.Map( mapCanvas, mapOptions );
		var marker = new google.maps.Marker({
			map: map,
			draggable: true
		});

		function setPosition( latLng ) {
			marker.setPosition( latLng );
			map.setCenter( latLng );
		}

		function updateCoords( latLng ){
			latitude.val( latLng.lat() );
			longitude.val( latLng.lng() );
		}

		// Set stored Coordinates
		if ( latitude.val() && longitude.val() ) {
			latLng = new google.maps.LatLng( latitude.val(), longitude.val() );
			setPosition( latLng );
			map.setZoom( 17 );
		}

		google.maps.event.addListener( marker, 'dragend', function() {
			updateCoords( marker.getPosition() );
		});

		// Search
		var autocomplete = new google.maps.places.Autocomplete(searchInput);
		if( typeof adifier_data !== 'undefined' ){
			if( adifier_data.country_restriction ){
				autocomplete.setComponentRestrictions({'country': adifier_data.country_restriction.split(',')});
			}
		}	
		autocomplete.bindTo('bounds', map);

		google.maps.event.addListener(autocomplete, 'place_changed', function() {
			var place = autocomplete.getPlace();
			if (place.geometry.viewport) {
				map.fitBounds(place.geometry.viewport);
			}

			setPosition( place.geometry.location );
			updateCoords( place.geometry.location );

			var street_val = '';
			street.val('');
			for (var i = 0; i < place.address_components.length; i++) {
				var addressType = place.address_components[i].types[0];
				if (componentForm[addressType]) {
					var val = place.address_components[i][componentForm[addressType]];
					if( addressType == 'route' ){
						street_val = street.val();
						if( street_val ){
							val = adifier_data.address_order == 'front' ? val+' '+street_val : street_val+' '+val;
						}
						street.val( val );
					}
					if( addressType == 'street_number' ){
						street_val = street.val();
						if( street_val ){
							val = adifier_data.address_order == 'front' ? street_val+' '+val : val+' '+street_val;
						}
						street.val( val );
					}					
					else if( addressType == 'locality' ){
						city.val( val );
					}
					else if( addressType == 'administrative_area_level_1' ){
						state.val( val );	
					}
					else if( addressType == 'country' ){
						country.val( val );	
					}
				}
			}		

		});

		jQuery(searchInput).keypress(function(e) {
			if (e.keyCode === 13) {
				e.preventDefault();
			}
		});
	}
	else if( adifier_map_data.map_source == 'mapbox' ){
		mapboxgl.accessToken = adifier_mapbox_data.api;
		var lngLat = {
			lng: longitude.val(),
			lat: latitude.val()
		};		
		var map = new mapboxgl.Map({
			container: mapCanvas,
			style: typeof adifier_data !== 'undefined' && adifier_data.map_style ? adifier_data.map_style : 'mapbox://styles/mapbox/light-v9'
		});
		var countries = '';

		map.on('styledata', function(){
			map.getStyle().layers.forEach(function(thisLayer){
				if(thisLayer.id.indexOf('-label')>0){
					console.log('change '+thisLayer.id);
					map.setLayoutProperty(thisLayer.id, 'text-field', ['get', adifier_data.mapbox_map_lang]);
				}
			});					
		});
		
		if( typeof adifier_data !== 'undefined' ){
			if( adifier_data.country_restriction ){
				countries = adifier_data.country_restriction;
			}
		}	

		var geocoder = new MapboxGeocoder({
			accessToken: mapboxgl.accessToken,
			countries: countries,
			mapboxgl: mapboxgl,
			placeholder: adifier_mapbox_data.placeholder,
			language: adifier_data.mapbox_geocoder_lang,
			marker: false
		});

		var marker = new mapboxgl.Marker({
			draggable: true
		});

		function setPosition( lngLat ) {
			map.setCenter( lngLat )
			marker.setLngLat( lngLat );

			if( !onMap ){
				marker.addTo(map);
				onMap = true;
			}
		}

		function updateCoords(lngLat){
			latitude.val( lngLat.lat );
			longitude.val( lngLat.lng );
		}

		if( lngLat.lat != '' ){
			setPosition( lngLat );
			map.setZoom( 17 );
		}		


		geocoder.on('result', function( response ){
			if( response.result.place_type ){
				var types = [ 'address', 'place', 'region', 'country' ];
				var data = {
					address: '',
					place: '',
					region: '',
					country: ''
				};
				var start = types.indexOf( response.result.place_type[0] );
				if( start == -1 && response.result.place_type[0] == 'poi' ){
					start = 0;
					data[types[start]] = response.result.properties.address;
				}
				else{
					data[types[start]] = response.result.text;
				}
				for (var i = start+1; i < types.length; i++) {
					for ( var j=0; j<response.result.context.length; j++ ){
						if( response.result.context[j].id.indexOf( types[i] ) > -1 ){
							data[types[i]] = response.result.context[j].text;
						}
					}
				}

				street.val( data.address );
				city.val( data.place );
				state.val( data.region );
				country.val( data.country );

				setPosition( {lng: response.result.geometry.coordinates[0], lat: response.result.geometry.coordinates[1] } );
				updateCoords( {lng: response.result.geometry.coordinates[0], lat: response.result.geometry.coordinates[1] } );
			}
		});


		marker.on('dragend', function(){
			setPosition( marker.getLngLat() );
			updateCoords( marker.getLngLat() );
		});
		 
		document.getElementById('map-search').appendChild(geocoder.onAdd(map));
	}
	else if( adifier_map_data.map_source == 'osm' ){
		jQuery(mapCanvas).attr('id', 'osmMap');
		var latLng = [0,0];
		var map;
		var onMap = false;
		window.results = [];
		var country_restriction = '';

		if( typeof adifier_data !== 'undefined' ){
			country_restriction = adifier_data.country_restriction;
		}

		if ( latitude.val() && longitude.val() ) {
			latLng = [ latitude.val(), longitude.val() ];
		}
		
		map = L.map('osmMap', {dragging: !(jQuery(window).width() < 1025), tap: !(jQuery(window).width() < 1025)}).setView(latLng, 17);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map);		

		var marker = L.marker([0,0], {draggable:'true'});

		function setPosition( latLng ) {
			marker.setLatLng( latLng );
			
			if( !onMap ){
				marker.addTo(map);
				onMap = true;
			}

			map.setView( latLng );
		}

		function updateCoords(latLng){
			latitude.val( latLng.lat );
			longitude.val( latLng.lng );
		}

		if( latLng[0] != '0' ){
			setPosition( latLng );
			map.setZoom( 17 );
		}		

		marker.on('dragend', function(){
			setPosition( marker.getLatLng() );
			updateCoords( marker.getLatLng() );
		});

		jQuery('.map-search').devbridgeAutocomplete({
			minChars: 3,
			noCache: true,
			transformResult: function(response) {
				var suggestions = [];
				if( response.length > 0 ){
					jQuery.each(response, function(key, item){
						window.results[item.place_id] = {
							lat: item.lat,
							lng: item.lon,
							address: item.address
						};
						suggestions.push({
							value: item.display_name,
							data: {
								place_id: item.place_id
							}
						});
					});
				}

				return {
					suggestions: suggestions
				};
			},
			onSelect: function (suggestion) {
				var place = window.results[suggestion.data.place_id];

				if( typeof place.address.street !== 'undefined' ){
					street.val( place.address.street );
				}
				if( typeof place.address.village !== 'undefined' || typeof place.address.city !== 'undefined' ){
					city.val( place.address.village ? place.address.village : place.address.city );
				}				
				if( typeof place.address.state !== 'undefined' ){
					state.val( place.address.state );
				}				
				if( typeof place.address.country !== 'undefined' ){
					country.val( place.address.country );
				}								

				setPosition( L.latLng(place.lat, place.lng) );
				updateCoords( L.latLng(place.lat, place.lng) );

			},			
			serviceUrl: 'https://nominatim.openstreetmap.org/search',
			paramName: 'q',
			dataType: 'json',
			params: {
				limit: 5,
				format: 'json',
				addressdetails: 1,
				countrycodes: country_restriction,
				"accept-language": adifier_data.osm_map_lang
			},
			deferRequestBy: 1000
		});
	}

}

(function(jQuery) {

	var jQueryadifierMap = jQuery('.adifier-map');
	if( jQueryadifierMap.length > 0 && jQuery('.reveal-after').length == 0 ){
		jQuery(document).ready(function(){
			AdifierMapInit( jQueryadifierMap );
		});
	}			

}(jQuery));