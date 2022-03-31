jQuery(document).ready(function($){
	/* PAY WITH FONDY */
	$(document).on( 'click', '#fondy-button', function(e){
		e.preventDefault();
		$('.purchase-loader').show();
		$.ajax({
			url: adifier_data.ajaxurl,
			method: 'POST',
			data: {
				action: 'fondy_create_payment',
				adifier_nonce: adifier_data.adifier_nonce,
				redirectUrl: window.location.href.split("#")[0],
				order: $('#purchase textarea').val()
			},
			dataType: 'JSON',
			success: function(response){
				if( typeof response.form !== 'undefined' ){
					$('#fondy-button').after(response.form);
					$('.fondy-form').submit();
				}
				else{
					alert( response.error );
				}
			}
		})
	});

	if( window.location.hash && window.location.hash == '#fondy-return' ){
		var res = {
			message: $('#fondy-button').data('returnmessage')
		};

		$(document).trigger('adifier_payment_return', [res]);
	}
});