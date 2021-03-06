jQuery(document).ready(function($){
	/* PAY WITH IDEAL */
	$(document).on( 'click', '#quickpay-button', function(e){
		e.preventDefault();
		$('.purchase-loader').show();
		$.ajax({
			url: adifier_data.ajaxurl,
			method: 'POST',
			data: {
				action: 'quickpay_create_payment',
				adifier_nonce: adifier_data.adifier_nonce,
				redirectUrl: window.location.href.split("#")[0],
				order: $('#purchase textarea').val()
			},
			dataType: 'JSON',
			success: function(response){
				if( typeof response.form !== 'undefined' ){
					$('#quickpay-button').after(response.form);
					$('.quickpay-form').submit();
				}
				else{
					alert( response.error );
				}
			}
		})
	});

	if( window.location.hash && window.location.hash == '#quickpay-return' ){
		var res = {
			message: $('#quickpay-button').data('returnmessage')
		};

		$(document).trigger('adifier_payment_return', [res]);
	}
});