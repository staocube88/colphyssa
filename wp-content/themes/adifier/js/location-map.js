jQuery(document).ready(function($){
	"use strict";

    var $location = $('.location-map');
    var zoom = $location.data('zoom');
    zoom = zoom != '' ? zoom : 15;

    function startMapStuff(){

        var markerUrl = $location.data('icon') ? $location.data('icon') : adifier_data.marker_icon;
        var iconwidth = '';
        var iconheight = '';
        if( $location.data('icon') && $location.data('iconwidth') && $location.data('iconheight') ){
            iconwidth = $location.data('iconwidth');
            iconheight = $location.data('iconheight');
        }
        else if( adifier_data.marker_icon && adifier_data.marker_icon_width && adifier_data.marker_icon_height ){
            iconwidth = adifier_data.marker_icon_width;
            iconheight = adifier_data.marker_icon_height;
        }

        if( adifier_map_data.map_source == 'google' ){
            var location = new google.maps.LatLng( $location.data('lat'), $location.data('long') );
            var map = new google.maps.Map($location[0], {
                zoom: zoom,
                center: location,
                styles: adifier_data.map_style ? JSON.parse( adifier_data.map_style ) : ''
            });

            var icon = markerUrl;
            if( icon !== '' ){
                icon = {
                    url: markerUrl,
                    size: iconwidth ? new google.maps.Size( iconwidth / 2, iconheight / 2 ) : '',
                    scaledSize: iconwidth ? new google.maps.Size( iconwidth / 2, iconheight / 2 ) : ''
                };
            }

            var marker = new google.maps.Marker({
                position: location,
                map: map,
                icon: icon,
                title: ''
            });
        }
        if( adifier_map_data.map_source == 'mapbox' ){

            if( markerUrl == '' ){
                markerUrl = adifier_mapbox_data.default_marker;
                iconwidth = 110;
                iconheight = 110;
            }

            mapboxgl.accessToken = adifier_mapbox_data.api;
            var location = new mapboxgl.LngLat( $location.data('long'), $location.data('lat') );
            var map = new mapboxgl.Map({
                container: $location[0],
                zoom: zoom,
                center: location,
                style: adifier_data.map_style ? adifier_data.map_style : 'mapbox://styles/mapbox/light-v9'
            });     

			map.on('styledata', function(){
				map.getStyle().layers.forEach(function(thisLayer){
					if(thisLayer.id.indexOf('-label')>0){
						console.log('change '+thisLayer.id);
						map.setLayoutProperty(thisLayer.id, 'text-field', ['get', adifier_data.mapbox_map_lang]);
					}
				});
			});

            var el = '';
            if( markerUrl !== '' ){
                el = document.createElement('div');
                el.className = 'mapboxgl-marker';
                el.style.backgroundSize = 'contain';
                el.style.backgroundImage = 'url('+markerUrl+')';
                el.style.width =  ( iconwidth / 2 ) + 'px';
                el.style.height = ( iconheight / 2 ) + 'px';
            }

            var marker = new mapboxgl.Marker(el).setLngLat(location).addTo(map);
        }
        else if( adifier_map_data.map_source == 'osm' ){
            var latLong = [$location.data('lat'), $location.data('long')];
            $location.attr('id', 'osmMap');
			
            map = L.map('osmMap', {dragging: !($(window).width() < 1025), tap: !($(window).width() < 1025)}).setView(latLong, zoom);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            var icon;
            if( markerUrl !== '' ){
                icon = L.icon({
                    iconUrl: markerUrl,
                    iconSize: [iconwidth / 2, iconheight / 2],
                });            
            }

            L.marker(latLong, icon).addTo(map);
        }        
    }

    if( $location.length > 0 ){
        $(document).on( 'adifierMapStart', function(){
            startMapStuff();
        });
        $location.adifierMapConsent();
    }
})