jQuery(document).ready(function($){
	"use strict";

	$('#stripecard').on('hidden.bs.modal', function () {
		$('#purchase').modal('show');
		$('#card-element').html('');
		$('.stripe-pay').off('click'); 
		$('.purchase-loader').hide();
	});

	$('#stripecard').on('show.bs.modal', function (e) {
		$('#purchase').modal('hide');
		$('.purchase-loader').show();
	});

	var ajaxing = false;

	$(document).on( 'click', '#stripe-button', function(e){
		e.preventDefault();
		$('.purchase-loader').show();
		$.ajax({
			url: adifier_data.ajaxurl,
			data:{
				action: 'stripe_create_payment',
				adifier_nonce: adifier_data.adifier_nonce,
				order: $('#purchase textarea').val(),
				redirectUrl: window.location.href.split("#")[0],
			},
			method: 'POST',
			dataType: "JSON",
			success: function(response){
				if( !response.error ){
					var stripe = Stripe( response.key );
					stripe.redirectToCheckout({ sessionId: response.sessionId })
				}
				else{
					alert( response.error );
				}
			},
			complete: function(){
				$('.purchase-loader').hide();
			}
		});
	});	

	if( window.location.hash && window.location.hash == '#stripe-return' ){
		var res = {
			message: $('#stripe-button').data('returnmessage')
		};
		$(document).trigger('adifier_payment_return', [res]);
	}

});