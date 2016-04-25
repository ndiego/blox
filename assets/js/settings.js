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
		$(this).siblings( '.no-edit' ).prop( 'readonly', false);
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
	
	
	/* Multi Checkbox Select All/None
	-------------------------------------------------------------- */
	
	// Select all options
	$( '.blox-checkbox-select-all' ).click( function(e) {
		e.preventDefault();
		
		$(this).parent().siblings( '.blox-checkbox-container' ).find( 'input[type=checkbox]' ).prop('checked', true).trigger("change");
	});
	
	// Deselect all options
	$( '.blox-checkbox-select-none' ).click( function(e) {
		e.preventDefault();
		
		$(this).parent().siblings( '.blox-checkbox-container' ).find( 'input[type=checkbox]' ).prop('checked', false).trigger("change");
	});
	
	/* Default Hooks scripts
	-------------------------------------------------------------- */
	
	// Add a custom hook
	$( '.add-custom-button a' ).click( function(e) {
		e.preventDefault();
		
		hook_slug = $( '.custom-hook-entry' ).val();
		
		// Strip out anything that should not be in a custom hook
		hook_slug = hook_slug.replace(/[^\w\-]/g, '');
		
		if ( hook_slug != '' ) {
		
			hook_name	  = 'blox_settings[default_custom_hooks][available_hooks][custom][hooks][' + hook_slug + ']';
			enable_name  = 'blox_settings[default_custom_hooks][available_hooks][custom][hooks][' + hook_slug + '][enable]';
			name_name    = 'blox_settings[default_custom_hooks][available_hooks][custom][hooks][' + hook_slug + '][name]';
			title_name   = 'blox_settings[default_custom_hooks][available_hooks][custom][hooks][' + hook_slug + '][title]';

		
			hook_fields = '<li><span>';
		
			hook_fields += '<input class="blox-force-hidden" disabled type="text" name="' + hook_name + '" value="' + hook_slug + '" />';
			hook_fields += '<input type="checkbox" name="' + enable_name + '" value="1" />';
			hook_fields += '<input class="hook-name" type="text" name="' + name_name + '" placeholder="' + hook_slug + '" value="' + hook_slug + '" />';
			hook_fields += '<input class="blox-force-hidden" type="text" name="' + title_name + '" value="" />';
			hook_fields += '<a class="delete-custom-hook">' + blox_localize_settings_scripts.delete_hook + '</a>';

		
			hook_fields += '</span></li>';
			
			$( '#default_custom_hook_settings' ).removeClass( 'blox-hidden' );
			$( '.custom-hooks' ).append( hook_fields );
			
			$( '.no-hooks' ).remove();
			$( '.custom-hook-entry' ).val( '' );
		}
	});
	
	
	$(document).on( 'click', '.delete-custom-hook', function(){
		
		// Need to have the "return" or won't work
	   	var message = confirm( blox_localize_settings_scripts.confirm_delete_hook );
		
		if ( message == true ) {
			$(this).parents( 'li' ).remove();
			// If we remove the slide and there are no more, show our filler slide
			if ( $( '.custom-hooks li').length == 0 ) {
				$( '.custom-hooks' ).append( '<li class="no-hooks">' + blox_localize_settings_scripts.no_hooks + '</li>' );
			}
			
			return false;
		} else {
			// Makes the browser not shoot to the top of the page on "cancel"
			return false;
		}
	});

});