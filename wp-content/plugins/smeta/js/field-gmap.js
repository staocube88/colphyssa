/*jshint devel:true */
/*global google */

(function($) {

	var CMBGmapsInit = function( fieldEl ) {

		var searchInput = $('.map-search', fieldEl ).get(0);
		var mapCanvas   = $('.map', fieldEl ).get(0);
		var latitude    = $('.latitude', fieldEl );
		var longitude   = $('.longitude', fieldEl );
		var elevation   = $('.elevation', fieldEl );
		var country    	= $('.country', fieldEl );
		var state   	= $('.state', fieldEl );
		var city   		= $('.city', fieldEl );
		var street   	= $('.street', fieldEl );


		if( CMBGmaps.map_source == 'google' ){

			var elevator    = new google.maps.ElevationService();

			var componentForm = {
				route: 'long_name',
				street_number: 'long_name',
				locality: 'long_name',
				administrative_area_level_1: 'long_name',
				country: 'long_name',
			};		

			var mapOptions = {
				center:    new google.maps.LatLng( CMBGmaps.defaults.latitude, CMBGmaps.defaults.longitude ),
				zoom:      parseInt( CMBGmaps.defaults.zoom ),
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};

			var map      = new google.maps.Map( mapCanvas, mapOptions );

			// Marker
			var markerOptions = {
				map: map,
				draggable: true,
				title: CMBGmaps.strings.markerTitle
			};

			var marker = new google.maps.Marker( markerOptions );
			marker.setPosition( mapOptions.center );

			function setPosition( latLng, zoom ) {

				marker.setPosition( latLng );
				map.setCenter( latLng );

				if ( zoom ) {
					map.setZoom( zoom );
				}

				latitude.val( latLng.lat() );
				longitude.val( latLng.lng() );

				elevator.getElevationForLocations( { locations: [ marker.getPosition() ] }, function (results, status) {
					if (status == google.maps.ElevationStatus.OK && results[0] ) {
						elevation.val( results[0].elevation );
					}
				});

			}

			// Set stored Coordinates
			if ( latitude.val() && longitude.val() ) {
				latLng = new google.maps.LatLng( latitude.val(), longitude.val() );
				setPosition( latLng, 17 )
			}

			google.maps.event.addListener( marker, 'dragend', function() {
				setPosition( marker.getPosition() );
			});

			// Search
			var autocomplete = new google.maps.places.Autocomplete(searchInput);
			autocomplete.bindTo('bounds', map);

			google.maps.event.addListener(autocomplete, 'place_changed', function() {
				var place = autocomplete.getPlace();
				if (place.geometry.viewport) {
					map.fitBounds(place.geometry.viewport);
				}

				setPosition( place.geometry.location, 17 );

				if( country.length > 0 ){
					var street_val = '';
					street.val('');
					for (var i = 0; i < place.address_components.length; i++) {
						var addressType = place.address_components[i].types[0];
						if (componentForm[addressType]) {
							var val = place.address_components[i][componentForm[addressType]];
							if( addressType == 'route' ){
								street_val = street.val();
								if( street_val ){
									val = val+' '+street_val;
								}
								street.val( val );
							}
							if( addressType == 'street_number' ){
								street_val = street.val();
								if( street_val ){
									val = street_val+' '+val;
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
				}

			});

			$(searchInput).keypress(function(e) {
				if (e.keyCode === 13) {
					e.preventDefault();
				}
			});
		}
		else if( CMBGmaps.map_source == 'mapbox' ){
			mapboxgl.accessToken = CMBGmaps.api_key;
			var map = new mapboxgl.Map({
				container: mapCanvas,
				style: 'mapbox://styles/mapbox/light-v9'
			});

			map.on('style.load', function(){
				 map.resize();
			});

			var geocoder = new MapboxGeocoder({
				accessToken: mapboxgl.accessToken,
				mapboxgl: mapboxgl,
				marker: false
			});

			var marker = new mapboxgl.Marker({
				draggable: true
			}).setLngLat([0, 0]).addTo(map);		

			function setPosition( lngLat, zoom ) {
				map.setCenter( lngLat )
				marker.setLngLat( lngLat );

				if ( zoom ) {
					map.setZoom( zoom );
				}			

				latitude.val( lngLat.lat );
				longitude.val( lngLat.lng );
			}


			// Set stored Coordinates
			if ( latitude.val() && longitude.val() ) {
				var latLng = {
					lat: latitude.val(),
					lng: longitude.val()
				};
				setPosition( latLng, 17 );
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
				}
			});

			marker.on('dragend', function(){
				setPosition( marker.getLngLat() );
			});
			 
			document.getElementById('map-search').appendChild(geocoder.onAdd(map));
		}
		else if( CMBGmaps.map_source == 'osm' ){


			setTimeout(function(){
				$(mapCanvas).attr('id', 'osmMap');
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
				
				map = L.map('osmMap', {dragging: !($(window).width() < 1025), tap: !($(window).width() < 1025)}).setView(latLng, 17);
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
		
				$('.map-search').devbridgeAutocomplete({
					minChars: 3,
					noCache: true,
					transformResult: function(response) {
						var suggestions = [];
						if( response.length > 0 ){
							$.each(response, function(key, item){
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
						countrycodes: country_restriction
					},
					deferRequestBy: 1000
				});					
			}, 100);
		}

	}

	CMB.addCallbackForInit( function() {
		$('.CMB_Gmap_Field .field-item').each(function() {
			CMBGmapsInit( $(this) );
		});
	} );

	CMB.addCallbackForClonedField( ['CMB_Gmap_Field'], CMBGmapsInit );

}(jQuery));