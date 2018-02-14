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

	// Allows you to *tab* in textareas. (http://stackoverflow.com/questions/6637341/use-tab-to-indent-in-textarea)
	$(document).delegate( '.blox-enable-tab', 'keydown', function(e) {
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

			hook_name	  = 'blox_settings[default_custom_hooks][custom][hooks][' + hook_slug + ']';
			disable_name  = 'blox_settings[default_custom_hooks][custom][hooks][' + hook_slug + '][disable]';
			name_name    = 'blox_settings[default_custom_hooks][custom][hooks][' + hook_slug + '][name]';
			title_name   = 'blox_settings[default_custom_hooks][custom][hooks][' + hook_slug + '][title]';


			hook_fields = '<div class="row hook-row">';

			hook_fields += '<div class="hook-disable"><input type="checkbox" name="' + disable_name + '" value="1" /></div>';
			hook_fields += '<div class="hook-slug"><span>' + hook_slug + '</span></div>';
			hook_fields += '<div class="hook-name"><input class="hook-name" type="text" name="' + name_name + '" placeholder="' + hook_slug + '" value="' + hook_slug + '" /></div>';
			hook_fields += '<div class="hook-desc"><textarea class="hook-title" rows="1" name="' + title_name + '"></textarea></div>';
			hook_fields += '<div class="hook-delete"><a class="blox-custom-hook-delete dashicons right" href="#" title="' + blox_localize_settings_scripts.delete_hook + '"></a></div>';


			hook_fields += '</div>';

			$( '.blox-hook-table.custom' ).append( hook_fields );

			$( '.blox-no-custom-hooks' ).remove();
			$( '.custom-hook-entry' ).val( '' );
		}
	});

	// Edit hook section title
	// Updates our content block title field in real time
	$(document).on( 'focusout', '.blox-hook-section-title .section-title input', function(e) {
		titleText = e.target.value;

		if ( titleText != '' ) {
			// If a new title has been added, update the title div
			$(this).siblings( '.current-section-title' ).text( titleText );
		} else {
			default_title = $(this).attr( 'data-default-name' );
			// If the title has been removed, add show the default title
			$(this).siblings( '.current-section-title' ).text( default_title );

			// And add the default title to the input field
			$(this).val( default_title );
		}
	});

	// Display the section title editor on click
	$(document).on( 'click', '.blox-hook-section-title .toggle-section-title-editor', function(){
		$(this).toggleClass( 'editor-active' );
		$(this).siblings( '.current-section-title' ).toggle();
		$(this).siblings( '.section-title-editor' ).toggle();
	});

	$(document).on( 'change', '.section-disable-checkbox', function(){
		if ( $(this).is( ':checked' ) ){
			$(this).parents( '.blox-hook-section-title' ).addClass( 'section-disabled' );
			$(this).parents( '.blox-hook-section-title' ).next( '.blox-hook-table-container' ).hide();
		} else {
			$(this).parents( '.blox-hook-section-title' ).removeClass( 'section-disabled' );
			$(this).parents( '.blox-hook-section-title' ).next( '.blox-hook-table-container' ).show();
		}
	});

	// Delete a custom hook on click
	$(document).on( 'click', '.blox-custom-hook-delete', function(){

		// Need to have the "return" or won't work
	   	var message = confirm( blox_localize_settings_scripts.confirm_delete_hook );

		if ( message == true ) {
			$(this).parents( 'div.hook-row' ).remove();
			// If we remove the slide and there are no more, show our filler slide
			if ( $( '.blox-hook-table div.hook-row').length == 0 ) {
				$( '.blox-hook-table' ).append( '<div class="blox-no-custom-hooks">' + blox_localize_settings_scripts.no_hooks + '</div>' );
			}

			return false;
		} else {
			// Makes the browser not shoot to the top of the page on "cancel"
			return false;
		}
	});

	$( '.blox-hook-table.custom' ).sortable({
		items: '.hook-row',
		cursor: 'move',
		forcePlaceholderSize: true,
		placeholder: 'placeholder',
	});

	// Disable all hooks
	$( '.blox-hook-disable-all' ).click( function(e) {
		e.preventDefault();

		$(this).parent().prev( '.blox-hook-table' ).find( 'input[type=checkbox]' ).prop('checked', true).trigger("change");
	});

	// Enable all hooks
	$( '.blox-hook-enable-all' ).click( function(e) {
		e.preventDefault();

		$(this).parent().prev( '.blox-hook-table' ).find( 'input[type=checkbox]' ).prop('checked', false).trigger("change");
	});

	// Disable all custom hooks (only available for custom hooks)
	$( '.blox-hook-delete-all' ).click( function(e) {
		e.preventDefault();

		// If there are no custom hooks, do nothing
		if ( $( '.blox-hook-table div.hook-row').length == 0 ) {
			return;
		}

		// Need to have the "return" or won't work
		var message = confirm( blox_localize_settings_scripts.confirm_delete_all_hooks );

		if ( message == true ) {
			$(this).parent().siblings( '.blox-hook-table' ).find( 'div.hook-row' ).remove();
			// If we remove the slide and there are no more, show our filler slide
			if ( $( '.blox-hook-table div.hook-row').length == 0 ) {
				$( '.blox-hook-table' ).append( '<div class="blox-no-custom-hooks">' + blox_localize_settings_scripts.no_hooks + '</div>' );
			}

			return false;
		} else {

			// Makes the browser not shoot to the top of the page on "cancel"
			return false;
		}
	});


});
