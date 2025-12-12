(function($) {
	$(window).load( function() {
		$( '.blockchain-onboarding-notice' ).parents( '.is-dismissible' ).on( 'click', 'button', function( e ) {
			$.ajax( {
				type: 'post',
				url: ajaxurl,
				data: {
					action: 'blockchain_dismiss_onboarding',
					nonce: blockchain_Onboarding.dismiss_nonce,
					dismissed: true
				},
				dataType: 'text',
				success: function( response ) {
					// console.log( response );
				}
			} );
		});
	});
})(jQuery);
