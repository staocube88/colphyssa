jQuery(document).ready(function($){
	"use strict";

	paypal.Buttons({
		createOrder: function(data, actions) {
	    	$('.purchase-loader').show();

			return fetch(adifier_data.ajaxurl, {
				method: 'post',
				credentials: 'same-origin',
				headers: {
					'Accept': 'application/json, text/plain, */*',
					'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
				},
				body: 'action=paypal_create_payment&adifier_nonce='+adifier_data.adifier_nonce+'&order='+$('#purchase textarea').val()
			}).then(function(res) {				
				return res.json();
			}).then(function(data) {
				$('.purchase-loader').hide();
				return data.orderID;			
			});
		},
		onApprove: function(data) {
	    	$('.purchase-loader').show();
			return fetch(adifier_data.ajaxurl, {
				method: 'post',
				headers: {
					'Accept': 'application/json, text/plain, */*',
					'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
				},
				body: 'action=paypal_execute_payment&adifier_nonce='+adifier_data.adifier_nonce+'&orderID='+data.orderID
			})
			.then(function(res){
				return res.json();
			})
			.then(function(data){
				$('.purchase-loader').hide();
				$(document).trigger( 'adifier_payment_completed', [data] );
			});
		}
	}).render('#paypal-button');

});