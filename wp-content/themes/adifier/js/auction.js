jQuery(document).ready(function($){
	"use strict";
	/* BIDDING RESPONSE */
	$(document).on('bidding-response', function(e, response){
		if( response.price ){
			var $price = $('.single-price-wrap .price');
			$price.after( response.price );
			$price.remove();

			$('input[name="bid"]').attr( 'placeholder', response.min_bid_text );

			$('input[name="bid"]').attr( 'min', response.min_bid ).val('');
		}
	});

	$(document).on('bidding-history-response', function(e, response){
		$('.bidding-history-results').append( response.message );
		if( response.next_page ){
			$('.bidding-history').text( response.btn_text );
			$('input[name="bidpage"]').val( response.next_page );
		}
		else{
			$('.bidding-history').remove();
		}
	});

	/* CONTACT BUYER AFTER AUCITON ENDS */
	$(document).on('click', '.contact-buyer', function(e){
		if( $(this).attr('href').indexOf('http') == -1 ){
	    	e.preventDefault();
	    	$('#contact-buyer input[name="buyer_id"]').val( $(this).data('buyer_id') );
	    	$('#contact-buyer').modal('show');
		}
	});
});