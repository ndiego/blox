jQuery(document).ready(function($) {

	/* Settings scripts
	-------------------------------------------------------------- */
	
	// Provides a confirmation popup when clicking "Reset".
	$( '#reset' ).on( 'click', function(){
		// Need to have the "return" or won't work
	   	return confirm( blox_localize_settings_scripts.reset );
	});
	
	// Make the license field readonly after the license key has been saved prevent people from inadvertently altering the key.
	$( '.no-edit' ).prop( 'readonly', true);
	
	// Enable editing of key, but hide activation button until key is re-saved
	$( 'a.edit-license' ).on( 'click', function(){
		$( '.no-edit' ).prop( 'readonly', false);
		$(this).hide();
		$(this).siblings( 'input.button-primary' ).hide();
		$(this).siblings( '.description' ).hide();
		$(this).siblings( 'p.edit-license.description' ).show();
	});
	
	// Move .updated and .error alert boxes. Don't move boxes designed to be inline.
	$( 'div.wrap h2:first' ).nextAll( 'div.updated, div.error' ).addClass( 'below-h2' );
	$( 'div.updated, div.error' ).not( '.below-h2, .inline' ).insertAfter( $( 'div.wrap h2:first' ) );
	
	// Allows you to *tab* in textareas. (http://stackoverflow.com/questions/6637341/use-tab-to-indent-in-textarea)
	$(document).delegate( '.blox-textarea-code', 'keydown', function(e) {
		var keyCode = e.keyCode || e.which;

		if (keyCode == 9) {
			e.preventDefault();
			var start = $(this).get(0).selectionStart;
			var end = $(this).get(0).selectionEnd;

			// set textarea value to: text before caret + tab + text after caret
			$(this).val($(this).val().substring(0, start)
				+ "\t"
				+ $(this).val().substring(end));

			// put caret at right position again
			$(this).get(0).selectionStart =
			$(this).get(0).selectionEnd = start + 1;
	  	}
	});
});