jQuery(document).ready(function($) {

	if ( $.isFunction( $.fn.datepicker ) ) {
		var ciDatePicker = $( '.postbox .datepicker' );

		ciDatePicker.each( function() {
			$( this ).datepicker( {
				dateFormat: 'yy-mm-dd'
			} );
		} );
	}

});
