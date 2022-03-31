jQuery(document).ready(function($){
	"use strict";
	function sendBeacon(){
		$.ajax({
			url: adifier_data.ajaxurl,
			data: {
				adifier_nonce: adifier_data.adifier_nonce,
				action: 'adifier_online_beacon',
			}
		});		
	}

	sendBeacon();

	setInterval(function(){
		sendBeacon();
	}, 900000);
});