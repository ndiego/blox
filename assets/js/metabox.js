jQuery(document).ready(function($){

	/* General Metabox scripts
	-------------------------------------------------------------- */

	// Show the selected content type
	function show_selected_content() {
		$('.blox-content-type').each( function() {
			var content_type = $(this).val();

			// All content sections start as hidden, so show the one selected
			$( this ).parents( '.blox-content-type-container' ).siblings( '.blox-content-' + content_type ).removeClass( 'blox-hidden' );
		});
	};

	// Run on page load so selected content is visible
	show_selected_content();

	// Shows and hides each content type on selection
	$( document ).on( 'change', '.blox-content-type', function(){
		var content_type = $(this).val();

		$( this ).parents( '.blox-content-type-container' ).siblings().addClass( 'blox-hidden' );
		$( this ).parents( '.blox-content-type-container' ).siblings( '.blox-content-' + content_type ).removeClass( 'blox-hidden' );
	});



	/* Modal scripts
	-------------------------------------------------------------- */

	// Close the modal if you click on the overlay
	$(document).on( 'click', '#blox_overlay', function() {
		$( '#blox_overlay' ).fadeOut(200);
		$( '.blox-modal' ).css({ 'display' : 'none' });

		// Reset the resize attribute on all textareas, it may have been
		// set to "none" for a bug fix.
		$( '.blox-tab-content textarea' ).css( 'resize', 'vertical' );
	});

	// Close the modal if you click on close button
	$(document).on( 'click', '.blox-modal-close', function() {
		$( '#blox_overlay' ).fadeOut(200);
		$( '.blox-modal' ).css({ 'display' : 'none' });

		// Reset the resize attribute on all textareas, it may have been
		// set to "none" for a bug fix.
		$( '.blox-tab-content textarea' ).css( 'resize', 'vertical' );
	});



	/* Content - Image scripts
	-------------------------------------------------------------- */

	// Show the custom image type sections if custom or featured-custom
	$('.blox-image-type').each( function() {
		var image_type = $( this ).val();

		if ( image_type == 'custom' || image_type == 'featured-custom' ) {
			// All sections start as hidden, so show the custom image uploader if selected
			$( this ).parents( '.blox-content-image' ).find( '.blox-content-image-custom' ).show();
		} else {
			$( this ).parents( '.blox-content-image' ).find( '.blox-content-image-custom' ).hide();
		}

		if ( image_type == 'custom' ) {
			$( this ).siblings( '.blox-featured-singular-only' ).hide();
		} else {
			$( this ).siblings( '.blox-featured-singular-only' ).show();
		}
	});

	// Shows and hides custom image uploader on selection
	$( document ).on( 'change', '.blox-image-type', function() {
		var image_type = $(this).val();

		if ( image_type == 'custom' || image_type == 'featured-custom' ) {
			// Show the custom image uploader if selected, otherwise hide it
			$( this ).parents( '.blox-content-image' ).find( '.blox-content-image-custom' ).show();
			$( this ).siblings().find( '.blox-featured-singular-only' ).hide();
		} else {
			$( this ).parents( '.blox-content-image' ).find( '.blox-content-image-custom' ).hide();
		}

		if ( image_type == 'custom' ) {
			$( this ).siblings( '.blox-featured-singular-only' ).hide();
		} else {
			$( this ).siblings( '.blox-featured-singular-only' ).show();
		}

	});

	// Image Uploader function
	blox_staticImageUpload = {

		/* Call this from the upload button to initiate the upload frame.
		 *
		 * @param int id The content block id so we can target the correct block
		 */
		uploader : function( id ) {
			var block_id = id;

			var frame = wp.media({
				title : blox_localize_metabox_scripts.image_media_title,
				multiple : false,
				library : { type : 'image' }, //only can upload images
				button : { text : blox_localize_metabox_scripts.image_media_button }
			});

			// Handle results from media manager
			frame.on( 'select', function() {
				var attachments = frame.state().get( 'selection' ).toJSON();
				blox_staticImageUpload.render( attachments[0], id );
			});

			frame.open();
			return false;
		},

		/* Output Image preview and populate widget form
		 *
		 * @param object attachment All of the images that were selected
		 * @param int id            The content block id so we can target the correct block
		 */
		render : function( attachment, id) {

			$( '#' + id + ' .blox-image-preview' ).attr( 'src', attachment.url );
			$( '#' + id + ' .blox-custom-image-id' ).val( attachment.id );
			$( '#' + id + ' .blox-custom-image-url' ).val( attachment.url );
			$( '#' + id + ' .blox-custom-image-alt' ).val( attachment.alt );
			$( '#' + id + ' .blox-custom-image-title' ).val( attachment.title );
			$( '#' + id + ' .blox-image-default' ).addClass( 'hidden' );
			$( '#' + id + ' .blox-image-preview' ).removeClass( 'hidden' );

			// Show the image atts input fields
			$( '#' + id + ' .blox-image-atts' ).show();
		},
	};

    // Remove the image
    $( document ).on( 'click', '.blox-remove-image', function() {
    	var empty = '';

    	// Need to use .find() because we are transversing two levels of the DOM
    	$( this ).siblings( '.blox-image-preview-wrapper' ).find( '.blox-image-preview' ).attr( 'src', empty ).addClass( 'hidden' );
  		$( this ).siblings( '.blox-image-preview-wrapper' ).find( '.blox-image-default' ).removeClass( 'hidden' );
    	$( this ).siblings( '.blox-custom-image-id' ).val( empty );
    	$( this ).siblings( '.blox-custom-image-url' ).val( empty );

    	// Hide the image atts input fields and empty them
    	$( this ).siblings( '.blox-image-atts' ).hide();
 	  	$( this ).siblings( '.blox-image-atts' ).find( '.blox-custom-image-alt' ).val( empty );
 	  	$( this ).siblings( '.blox-image-atts' ).find( '.blox-custom-image-title' ).val( empty );
    });

	// Toggle image custom sizing options
	/* NOT CURRENTLY BEING USED
	$(document).on( 'change', '.genesis-image-size-selector', function(){
		if ( $(this).val() == 'custom' ) {
			$(this).siblings( '.blox-image-size-custom' ).removeClass( 'blox-hidden' );
		} else {
			$(this).siblings( '.blox-image-size-custom' ).addClass( 'blox-hidden' );
		}
	});
	*/

	// Show the image link settings if enabled on page load
	$( '.blox-image-link-enable input' ).each( function() {
		if ( $(this).is( ':checked' ) ) {
		  $(this).parents( '.blox-image-link-enable' ).siblings( '.blox-image-link' ).show();
		}
	});

	// Show the image link settings if checked
	$(document).on( 'change', '.blox-image-link-enable input', function(){
		if ( $(this).is( ':checked' ) ) {
		  $(this).parents( '.blox-image-link-enable' ).siblings( '.blox-image-link' ).show();
		} else {
		  $(this).parents( '.blox-image-link-enable' ).siblings( '.blox-image-link' ).hide();
	  	}
	});



	/* Content - Slideshow scripts
	-------------------------------------------------------------- */

	$('.blox-slideshow-type').each( function() {
		var slideshow_type = $(this).val();

		$(this).parents( '.blox-slideshow-type-container' ).siblings( '.blox-slideshow-option' ).addClass( 'blox-hidden' );

		// All content sections start as hidden, so show the one selected
		$( this ).parents( '.blox-content-slideshow' ).find( '.blox-content-slideshow-' + slideshow_type ).removeClass( 'blox-hidden' );
	});

	// Shows and hides each slideshow type on selection
	$(document).on( 'change', '.blox-slideshow-type', function(){
		var slideshow_type = $(this).val();

		$(this).parents( '.blox-slideshow-type-container' ).siblings( '.blox-slideshow-option' ).addClass( 'blox-hidden' );
		$(this).parents( '.blox-slideshow-type-container' ).siblings( '.blox-content-slideshow-' + slideshow_type ).removeClass( 'blox-hidden' );
	});

	// Slideshow Uploader function
	blox_builtinSlideshowUpload = {

		// Call this from the upload button to initiate the upload frame.
		uploader : function( name_prefix ) {
			var frame = wp.media({
				id : name_prefix, // We set the id to be the name_prefix so that we can save our slides
				title : blox_localize_metabox_scripts.slideshow_media_title,
				multiple : true,
				library : { type : 'image' }, //only can upload images
				button : { text : blox_localize_metabox_scripts.slideshow_media_button }
			});

			// Handle results from media manager
			frame.on( 'select', function() {

				// Extract the block id from the frame.id (i.e the name prefix)
				var block_id = frame.id.substring( 25, 29 );

				// If we are on a global block, the retrieved block id will be gibberish and not be a number. But if we are on a global block we don't need to worry about targeting...
				if ( ! isNaN( block_id ) ) {
					// We are on local so we need to target using the block id
					var select_target = '#' + block_id + ' .blox-slides-container';
				} else {
					// We are on global so we don't worry about targeting
					var select_target ='.blox-slides-container';
				}

				var selection = frame.state().get( 'selection' );

				// Need this to handle multiple images selected
				selection.map( function( attachment ) {
					attachment = attachment.toJSON();
					$( select_target ).append( function() {

						// Generate a unique id for each slide: From http://stackoverflow.com/questions/6248666/how-to-generate-short-uid-like-ax4j9z-in-js
						var randSlideId = 'slide_' + ("0000" + (Math.random()*Math.pow(36,4) << 0).toString(36)).slice(-4);
						var output = '';

						output += '<li id="' + randSlideId + '" class="blox-slideshow-item" >';
						output += '<div class="blox-slide-container"><image class="slide-image-thumbnail" src="' + attachment.sizes.thumbnail.url + '" alt="' + attachment.alt + '" /></div>';
						output += '<input type="text" class="slide-type blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][slide_type]" value="image" />';
						output += '<input type="checkbox" class="slide-visibility-disable blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][visibility][disable]" value="1" />';

						output += '<input type="text" class="slide-image-id blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][id]" value="' + attachment.id + '" />';
						output += '<input type="text" class="slide-image-url blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][url]" value="' + attachment.url + '" />';
						output += '<input type="text" class="slide-image-title blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][title]" value="' + attachment.title + '" />';
						output += '<input type="text" class="slide-image-alt blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][alt]" value="' + attachment.alt + '" />';
						output += '<input type="text" class="slide-image-size blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][size]" value="full" />';

						output += '<input type="checkbox" class="slide-image-link-enable blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][link][enable]" value="1" />';
						output += '<input type="text" class="slide-image-link-url blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][link][url]" value="http://" />';
						output += '<input type="text" class="slide-image-link-title blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][link][title]" value="" />';
						output += '<input type="checkbox" class="slide-image-link-target blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][link][target]" value="1" />';

						//output += '<input type="text" class="slide-image-caption blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][caption]" value="' + attachment.caption + '" />';

						output += '<textarea class="slide-image-caption blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][caption]" >' + attachment.caption + '</textarea>';

						output += '<input type="text" class="slide-image-classes blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][classes]" value="" />';
						output += blox_slide_tools( frame.id );
						output += '</li>';

						return output;
					});
				});

				// If our filler slide is present, remove it!
				if ( $('.blox-filler').length > 0 ) {
					$('.blox-filler').remove();
				}

			});

			frame.open();
			return false;
		},

	};

	// Copy Slideshow Items
	// Need to '.on' because we are working with dynamically generated content
	$(document).on( 'click', '.blox-slides-container .blox-slide-copy', function(e) {

		e.preventDefault();

		var block_id    = $(this).parents( '.blox-content-block' ).attr( 'id' ),
			name_prefix = $(this).data( 'name-prefix' );

		// If we are on a global block, the retrieved block id will be gibberish and not be a number. But if we are on a global block we don't need to worry about targeting...
		block_id = ! isNaN( block_id ) ? ( '#' + block_id ) : '';

		// Grab our existing slide details
		var slide_id 			= $( this ).parents( 'li' ).attr( 'id' ),
			slide_type			= $( '#' + slide_id + ' .slide-type' ).attr( 'value' ),
			visibility_disable 	= $( '#' + slide_id + ' .slide-visibility-disable' ).is( ':checked' ),
			image_id 			= $( '#' + slide_id + ' .slide-image-id' ).attr( 'value' ),
			image_url 			= $( '#' + slide_id + ' .slide-image-url' ).attr( 'value' ),
			image_thumbnail 	= $( '#' + slide_id + ' .slide-image-thumbnail' ).attr( 'src' ),
			image_title 		= $( '#' + slide_id + ' .slide-image-title' ).attr( 'value' ),
			image_alt 			= $( '#' + slide_id + ' .slide-image-alt' ).attr( 'value' ),
			image_size 			= $( '#' + slide_id + ' .slide-image-size' ).attr( 'value' ),
			link_enable 		= $( '#' + slide_id + ' .slide-image-link-enable' ).is( ':checked' ),
			link_url 			= $( '#' + slide_id + ' .slide-image-link-url' ).attr( 'value' ),
			link_title 			= $( '#' + slide_id + ' .slide-image-link-title' ).attr( 'value' ),
			link_target 		= $( '#' + slide_id + ' .slide-image-link-target' ).is( ':checked' ),
			caption 			= $( '#' + slide_id + ' .slide-image-caption' ).attr( 'value' ),
			classes 			= $( '#' + slide_id + ' .slide-image-classes' ).attr( 'value' );

		visibility_flag	   = visibility_disable ? 'disabled' : '';
		visibility_disable = visibility_disable ? 'checked' : '';
		link_enable 	   = link_enable ? 'checked' : '';
		link_target 	   = link_target ? 'checked' : '';

			// Generate a new slide id
			new_slide_id    = 'slide_' + ("0000" + (Math.random()*Math.pow(36,4) << 0).toString(36)).slice(-4),
			output       	= '';

		$( block_id + ' .blox-slides-container' ).append( function() {

			// Put together the copied slide
			output += '<li id="' + new_slide_id + '" class="blox-slideshow-item ' + visibility_flag + '" >';
			output += '<div class="blox-slide-container"><image class="slide-image-thumbnail" src="' + image_thumbnail + '" alt="' + image_alt + '" /></div>';
			output += '<input type="text" class="slide-type blox-force-hidden" name="' + name_prefix + '[slideshow][builtin][slides]['+ new_slide_id +'][slide_type]" value="' + slide_type + '" />';
			output += '<input type="checkbox" class="slide-visibility-disable blox-force-hidden" name="' + name_prefix + '[slideshow][builtin][slides]['+ new_slide_id +'][visibility][disable]" value="1" ' + visibility_disable + '/>';

			output += '<input type="text" class="slide-image-id blox-force-hidden" name="' + name_prefix + '[slideshow][builtin][slides]['+ new_slide_id +'][image][id]" value="' + image_id + '" />';
			output += '<input type="text" class="slide-image-url blox-force-hidden" name="' + name_prefix + '[slideshow][builtin][slides]['+ new_slide_id +'][image][url]" value="' + image_url + '" />';
			output += '<input type="text" class="slide-image-title blox-force-hidden" name="' + name_prefix + '[slideshow][builtin][slides]['+ new_slide_id +'][image][title]" value="' + image_title + '" />';
			output += '<input type="text" class="slide-image-alt blox-force-hidden" name="' + name_prefix + '[slideshow][builtin][slides]['+ new_slide_id +'][image][alt]" value="' + image_alt + '" />';
			output += '<input type="text" class="slide-image-size blox-force-hidden" name="' + name_prefix + '[slideshow][builtin][slides]['+ new_slide_id +'][image][size]" value="' + image_size + '" />';

			output += '<input type="checkbox" class="slide-image-link-enable blox-force-hidden" name="' + name_prefix + '[slideshow][builtin][slides]['+ new_slide_id +'][image][link][enable]" value="1" ' + link_enable + '/>';
			output += '<input type="text" class="slide-image-link-url blox-force-hidden" name="' + name_prefix + '[slideshow][builtin][slides]['+ new_slide_id +'][image][link][url]" value="' + link_url + '" />';
			output += '<input type="text" class="slide-image-link-title blox-force-hidden" name="' + name_prefix + '[slideshow][builtin][slides]['+ new_slide_id +'][image][link][title]" value="' + link_title + '" />';
			output += '<input type="checkbox" class="slide-image-link-target blox-force-hidden" name="' + name_prefix + '[slideshow][builtin][slides]['+ new_slide_id +'][image][link][target]" value="1" ' + link_target + '/>';

			output += '<input type="text" class="slide-image-caption blox-force-hidden" name="' + name_prefix + '[slideshow][builtin][slides]['+ new_slide_id +'][image][caption]" value="' + caption + '" />';
			output += '<input type="text" class="slide-image-classes blox-force-hidden" name="' + name_prefix + '[slideshow][builtin][slides]['+ new_slide_id +'][image][classes]" value="' + classes + '" />';
			output += blox_slide_tools( name_prefix );
			output += '</li>';

			return output;
		});

	});

	// Remove Slideshow Items
	// Need to '.on' because we are working with dynamically generated content
	$(document).on( 'click', '.blox-slides-container .blox-slide-delete', function() {

		var message = confirm( blox_localize_metabox_scripts.slideshow_confirm_remove );

		if ( message == true ) {

			var block_id = $(this).parents( '.blox-content-block' ).attr( 'id' );

			// If we are on a global block, the retrieved block id will be gibberish and not be a number. But if we are on a global block we don't need to worry about targeting...
			if ( ! isNaN( block_id ) ) {
				// We are on local so we need to target using the block id
				var block_id = '#' + block_id;
			} else {
				// We are on global so we don't worry about targeting
				var block_id = '';
			}

			// Now that we have retrieved the block id, remove the slide
			$(this).parents( '.blox-slideshow-item' ).remove();

			// If we remove the slide and there are no more, show our filler slide
			if ( $( block_id + ' .blox-filler').length == 0 && $( block_id + ' .blox-slideshow-item' ).length == 0 ) {
				$( block_id + ' .blox-slides-container' ).append( blox_filler_slide() );
			}
			return false;
		} else {
			// Makes the browser not shoot to the top of the page on "cancel"
			return false;
		}
	});

	// Toggle slide visibility on icon check
	$(document).on( 'click', '.blox-slides-container .blox-slide-visibility', function(e) {

		e.preventDefault();

		var slide_id  			= $( this ).parents( 'li' ).attr( 'id' ),
			visibility_disable 	= $( '#' + slide_id + ' .slide-visibility-disable' ).is( ':checked' );

		if ( visibility_disable ) {
			$( '#' + slide_id ).removeClass( 'disabled' );
			$( '#' + slide_id + ' .slide-visibility-disable' ).prop( 'checked', false );
		} else {
			$( '#' + slide_id ).addClass( 'disabled' );
			$( '#' + slide_id + ' .slide-visibility-disable' ).prop( 'checked', true );
		}

	});

	// Display the slide details modal (need .on because new slides are dynamically added to the page)
	// Code is a heavily modified version of http://leanmodal.finelysliced.com.au
	$(document).on( 'click', '.blox-slide-edit', function(e) {

		e.preventDefault();

		// Set the modal id and set the selected slide id
		var modal_id = '#blox_slide_details',
			slide_id = $( this ).parents( 'li' ).attr( 'id' );

		// Determine whether the prev/next buttons should be enabled or not
		blox_set_prev_next_buttons( modal_id, slide_id );

		// Hide the apply settings message if visible
		blox_hide_applied_message();

		// Import all slide settings
		blox_import_slide_details( slide_id );

		// Open the modal last
		blox_open_modal( modal_id );

		// Close the modal if you click on the overlay
		$(document).on( 'click', '#blox_overlay', function() {
			blox_close_modal( modal_id );
		});

		// Close the modal if you click on close button
		$(document).on( 'click', '.blox-modal-close', function() {
			blox_close_modal( modal_id );
		});

	});

	// Apply slide modal settings to the slide on click
	$(document).on( 'click', '#blox-slide-apply-settings', function() {

		var slide_id = $( '.modal-slide-id' ).val();

		blox_enable_disable_slide_visibility( slide_id );

		$( '#' + slide_id + ' .slide-type' ).val( $( '.modal-slide-type' ).val() );
		$( '#' + slide_id + ' .slide-visibility-disable' ).prop( 'checked', $( '.modal-slide-visibility-disable' ).is( ':checked' ) );
		$( '#' + slide_id + ' .slide-image-thumbnail' ).attr( 'src', $( '.modal-slide-image-thumbnail' ).val() );
		$( '#' + slide_id + ' .slide-image-id' ).val( $( '.modal-slide-image-id' ).val() );
		$( '#' + slide_id + ' .slide-image-url' ).val( $( '.modal-slide-image-url' ).val() );
		$( '#' + slide_id + ' .slide-image-title' ).val( $( '.modal-slide-image-title' ).val() );
		$( '#' + slide_id + ' .slide-image-alt' ).val( $( '.modal-slide-image-alt' ).val() );
		$( '#' + slide_id + ' .slide-image-size' ).val( $( '.modal-slide-image-size' ).val() );
		$( '#' + slide_id + ' .slide-image-link-enable' ).prop( 'checked', $( '.modal-slide-image-link-enable' ).is( ':checked' ) );
		$( '#' + slide_id + ' .slide-image-link-url' ).val( $( '.modal-slide-image-link-url' ).val() );
		$( '#' + slide_id + ' .slide-image-link-title' ).val( $( '.modal-slide-image-link-title' ).val() );
		$( '#' + slide_id + ' .slide-image-link-target' ).prop( 'checked', $( '.modal-slide-image-link-target' ).is( ':checked' ) );
		$( '#' + slide_id + ' .slide-image-caption' ).val( $( '.modal-slide-image-caption' ).val() );
		$( '#' + slide_id + ' .slide-image-classes' ).val( $( '.modal-slide-image-classes' ).val() );

		blox_show_applied_message( 'success', 1000 );
	});

	// Enable/Disable side based on modal visibility setting for given slide
	function blox_enable_disable_slide_visibility( slide_id ) {

		// Add the disable flag to the slide container
		if ( $( '.modal-slide-visibility-disable' ).is( ':checked' ) ) {
			$( '#' + slide_id ).addClass( 'disabled' );
		} else {
			$( '#' + slide_id ).removeClass( 'disabled' );
		}
	}

	// Show the applied message with the spinner
	function blox_show_applied_message( status, time ) {

		// If the message is visible, hide it
		$( '#blox-slide-apply-settings-message' ).css( 'display', 'none' );

		// Run the spinner
		blox_run_spinner( time );

		// Delay the message show until spinner is done
		setTimeout( function() {

			// Display the message
			if ( status === 'success' ) {
				$( '#blox-slide-apply-settings-message' ).addClass( 'success' ).css( 'display', 'inline-block' );
			}

		}, time );
	}

	// Utility function to hide message
	function blox_hide_applied_message() {
		// If the message is visible, hide it
		$( '#blox-slide-apply-settings-message' ).css( 'display', 'none' );
	}

	// Show and hide the spinner on Apply Settings click
	function blox_run_spinner( time ) {
		$( '.blox-modal-spinner' ).css( 'visibility', 'visible' );

		// Need setTimeout because .css does not work natively with a delay
		setTimeout( function() { $( '.blox-modal-spinner' ).css( 'visibility', 'hidden' ); }, time );
	}

	// Slideshow Uploader function
	blox_slideshow_change_image = {

		// Call this from the upload button to initiate the upload frame.
		uploader : function() {
			var frame = wp.media({
				title : blox_localize_metabox_scripts.image_media_title,
				multiple : false,
				library : { type : 'image' }, // Only can upload images
				button : { text : blox_localize_metabox_scripts.image_media_button }
			});

			// Handle results from media manager
			frame.on( 'select', function() {
				var attachments = frame.state().get( 'selection' ).toJSON();

				$( '.modal-slide-image-preview' ).attr( 'src', attachments[0].url );
				$( '.modal-slide-image-id' ).val( attachments[0].id );
				$( '.modal-slide-image-url' ).val( attachments[0].url );
				$( '.modal-slide-image-thumbnail' ).val( attachments[0].sizes['thumbnail'].url );
			});

			frame.open();
			return false;
		},
	};

	// Switch to the next slide in the slideshow
	$(document).on( 'click', '.blox-modal-next', function(e) {

		e.preventDefault();

		// Bail early if the button is disabled
		if ( $( this ).hasClass( 'disabled' ) ) {
			return;
		}

		// Hide the apply settings message if visible
		blox_hide_applied_message();

		// Get the required ids and indices
		var current_slide_id    = $( '.modal-slide-id' ).val(),
			next_slide_id       = '',
			current_slide_index = $( '#' + current_slide_id ).index() + 1,
			num_slides          = $( '#' + current_slide_id ).parent().children().length;

		// Only run if this is not the last slide
		if ( current_slide_index < num_slides ) {
			next_slide_id = $( '#' + current_slide_id ).next( 'li').attr( 'id' );

			blox_import_slide_details( next_slide_id );

			// Remove the disabled flag from the "prev" button if it exists
			$( this ).siblings( 'button' ).removeClass( 'disabled' );

			// Disable the button if the current slide is next to last
			if ( current_slide_index == ( num_slides - 1 ) ) {
				$( this ).addClass( 'disabled' );
			} else {
				$( this ).removeClass( 'disabled' );
			}
		}
	});

	// Switch to the previous slide in the slideshow
	$(document).on( 'click', '.blox-modal-prev', function(e) {

		e.preventDefault();

		// Bail early if the button is disabled
		if ( $( this ).hasClass( 'disabled' ) ) {
			return;
		}

		// Hide the apply settings message if visible
		blox_hide_applied_message();

		// Get the required ids and indices
		var current_slide_id    = $( '.modal-slide-id' ).val(),
			prev_slide_id       = '',
			current_slide_index = $( '#' + current_slide_id ).index() + 1,
			num_slides          = $( '#' + current_slide_id ).parent().children().length;

		// Only run if this is not the first slide
		if ( current_slide_index > 1 ) {
			prev_slide_id = $( '#' + current_slide_id ).prev( 'li').attr( 'id' );

			blox_import_slide_details( prev_slide_id );

			// Remove the disabled flag from the "next" button if it exists
			$( this ).siblings( 'button' ).removeClass( 'disabled' );

			// Disable the button if the current slide is 2nd in the list
			if ( current_slide_index == 2 ) {
				$( this ).addClass( 'disabled' );
			} else {
				$( this ).removeClass( 'disabled' );
			}
		}
	});

	// Import slide details into the Edit Slide modal
	// The slide_id is the unique id string for the given slide
	function blox_import_slide_details( slide_id ) {

		// Start by emptying all the settings in the modal
		blox_reset_settings( '#blox_slide_details' );

		// Grab our existing slide details
		var slide_id 			= slide_id,
			slide_type			= $( '#' + slide_id + ' .slide-type' ).attr( 'value' ),
			visibility_disable 	= $( '#' + slide_id + ' .slide-visibility-disable' ).is( ':checked' ),
			image_id 			= $( '#' + slide_id + ' .slide-image-id' ).attr( 'value' ),
			image_url 			= $( '#' + slide_id + ' .slide-image-url' ).attr( 'value' ),
			image_thumbnail 	= $( '#' + slide_id + ' .slide-image-thumbnail' ).attr( 'src' ),
			image_title 		= $( '#' + slide_id + ' .slide-image-title' ).attr( 'value' ),
			image_alt 			= $( '#' + slide_id + ' .slide-image-alt' ).attr( 'value' ),
			image_size 			= $( '#' + slide_id + ' .slide-image-size' ).attr( 'value' ),
			link_enable 		= $( '#' + slide_id + ' .slide-image-link-enable' ).is( ':checked' ),
			link_url 			= $( '#' + slide_id + ' .slide-image-link-url' ).attr( 'value' ),
			link_title 			= $( '#' + slide_id + ' .slide-image-link-title' ).attr( 'value' ),
			link_target 		= $( '#' + slide_id + ' .slide-image-link-target' ).is( ':checked' ),
			caption 			= $( '#' + slide_id + ' .slide-image-caption' ).attr( 'value' ),
			classes 			= $( '#' + slide_id + ' .slide-image-classes' ).attr( 'value' );

		// In the case that no image size is set, default to full
		image_size = image_size != '' ? image_size : 'full';

		// Populate the modal with existing details on open
		$( '.modal-slide-id' ).attr( 'value' , slide_id );
		$( '.modal-slide-type' ).attr( 'value' , slide_type );
		$( '.modal-slide-visibility-disable' ).prop( 'checked', visibility_disable );
		$( '.modal-slide-image-preview' ).attr( 'src' , image_url );
		$( '.modal-slide-image-id' ).attr( 'value' , image_id );
		$( '.modal-slide-image-url' ).attr( 'value' , image_url );
		$( '.modal-slide-image-thumbnail' ).attr( 'value' , image_thumbnail );
		$( '.modal-slide-image-title' ).attr( 'value' , image_title );
		$( '.modal-slide-image-alt' ).attr( 'value' , image_alt );
		$( '.modal-slide-image-size' ).attr( 'value' , image_size );
		$( '.modal-slide-image-link-enable' ).prop( 'checked', link_enable );
		$( '.modal-slide-image-link-url' ).attr( 'value' , link_url );
		$( '.modal-slide-image-link-title' ).attr( 'value' , link_title );
		$( '.modal-slide-image-link-target' ).prop( 'checked', link_target );
		$( '.modal-slide-image-caption' ).attr( 'value' , caption );
		$( '.modal-slide-image-classes' ).attr( 'value' , classes );

		var link_enable_checkbox    = $( '.modal-slide-image-link-enable' ),
		    link_settings_container = link_enable_checkbox.parent().siblings( '.blox-modal-subsettings.image-link' );

		// If the image link is enabled, show the additional options
		link_enable_checkbox.is( ':checked' ) ? link_settings_container.show() : link_settings_container.hide();
	}

	// Reset all settings in a form
	// Reference: http://stackoverflow.com/questions/680241/resetting-a-multi-stage-form-with-jquery
	function blox_reset_settings( settings ) {
    	$( settings ).find( 'input:text, select, textarea' ).val( '' );
    	$( settings ).find( 'input:radio, input:checkbox' ).removeAttr('checked').removeAttr('selected');
	}

	// Print the standard tools output
	// The name_prefix is needed for the copy function
	function blox_slide_tools( name_prefix ) {
		var edit_title       = blox_localize_metabox_scripts.slideshow_edit,
			visibility_title = blox_localize_metabox_scripts.slideshow_visibility,
			delete_title     = blox_localize_metabox_scripts.slideshow_delete,
			copy_title       = blox_localize_metabox_scripts.slideshow_copy

		return (
			'<div class="blox-slide-tools-container">' +
				'<a class="blox-slide-edit dashicons" href="#blox_slide_details" title="' + edit_title + '"></a>' +
				'<a class="blox-slide-visibility dashicons" href="#" title="' + visibility_title + '"></a>' +
				'<a class="blox-slide-delete dashicons right" href="#" title="' + delete_title + '"></a>' +
				'<a class="blox-slide-copy dashicons right" href="#" title="' + copy_title + '" data-name-prefix="' + name_prefix + '"></a>' +
			'</div>'
		);
	}

	// Print empty "filler" slide
	function blox_filler_slide() {
		return (
			'<li class="blox-filler">' +
				'<div class="blox-filler-container"></div>' +
				'<div class="blox-filler-tools">' +
					'<span class="edit dashicons"></span>' +
					'<span class="visibility dashicons"></span>' +
					'<span class="delete dashicons right"></span>' +
					'<span class="copy dashicons right"></span>' +
				'</div>' +
			'</li>'
		);
	}

	// Open the modal
	function blox_open_modal( modal_id ) {
		// Add the overlay to the page and style on click
		var overlay = '<div id="blox_overlay"></div>';

		$( 'body' ).append( overlay );
		$( '#blox_overlay' ).show();

		// Add modal open flag so we can disable body scrolling
  		$( 'body' ).addClass( 'blox-modal-open' );

		// Add the modal to the page and style on click
		var modal_height = $( modal_id ).outerHeight(),
			modal_width  = $( modal_id ).outerWidth();

		$( modal_id ).css({
			'display' : 'block',
			'position' : 'fixed',
			'z-index': 110000,
			'top' : 30 + 'px',
			'bottom' : 30 + 'px',
			'left' : 30 + 'px',
			'right' : 30 + 'px'
		});

		$( modal_id ).show();
	}

	// Close the modal
	function blox_close_modal( modal_id ) {
		$( "#blox_overlay" ).hide();
		$( modal_id ).hide();

		// Remove modal open flag to return body scrolling back to normal
		$( 'body' ).removeClass( 'blox-modal-open' );
	}

	// Set prev/next buttons
	function blox_set_prev_next_buttons( modal_id, slide_id ) {
		var slide_index = $( '#' + slide_id ).index() + 1,
			num_slides  = $( '#' + slide_id ).parent().children().length;

		// Reset prev/next buttons
		$( modal_id + ' .blox-modal-prev' ).removeClass( 'disabled' );
		$( modal_id + ' .blox-modal-next' ).removeClass( 'disabled' );

		if ( slide_index == num_slides && num_slides == 1 ) {
			$( modal_id + ' .blox-modal-prev' ).addClass( 'disabled' );
			$( modal_id + ' .blox-modal-next' ).addClass( 'disabled' );
		} else if ( slide_index == num_slides ) {
			$( modal_id + ' .blox-modal-next' ).addClass( 'disabled' );
		} else if ( slide_index == 1 ) {
			$( modal_id + ' .blox-modal-prev' ).addClass( 'disabled' );
		}
	}

	// Make Slideshow Items sortable
	$( '.blox-slides-container' ).sortable({
		items: '.blox-slideshow-item',
		cursor: 'move',
		forcePlaceholderSize: true,
		placeholder: 'placeholder'
	});

	// Show and hide image link based on click
	$(document).on( 'click', '.modal-slide-image-link-enable', function(e) {

		var checkbox  = $( this ),
			container = checkbox.parent().siblings( '.blox-modal-subsettings.image-link' );

		// If the image link is enabled, show the additional options
		checkbox.is( ':checked' ) ? container.show() : container.hide();
	});


	/* Content - Editor scripts
	-------------------------------------------------------------- */

    // Show/Hide the source textarea, defaults to hidden
    $(document).on( 'click', '.blox-editor-show-source', function(e) {

		e.preventDefault();

		// Get the block id
		var block_id = $(this).parents( '.blox-content-block' ).attr( 'id' );

		// Toggle the source textarea
		$( '#' + block_id + ' .blox-editor-output-wrapper' ).toggle();

		// Toggle the name of the button
		$(this).html( $(this).text() == blox_localize_metabox_scripts.editor_show_html ? blox_localize_metabox_scripts.editor_hide_html : blox_localize_metabox_scripts.editor_show_html );

    });


	// Display the editor modal
	// Code is a heavily modified version of http://leanmodal.finelysliced.com.au
	$(document).on( 'click', '.blox-editor-add', function(e) {

		e.preventDefault();

		// Get the block id
		var block_id = $(this).parents( '.blox-content-block' ).attr( 'id' );

		// Set the block id in the modal for future use
		$( '#blox_editor_master_id' ).val( block_id );

		// Set the source content in the editor depending on which frame of the editor is active
		if ( $( '#wp-blox_editor_master-wrap' ).hasClass( 'tmce-active' ) ){
			tinyMCE.get('blox_editor_master').setContent( $( '#' + block_id + ' .blox-editor-output' ).val() );
		} else if ( $( '#wp-blox_editor_master-wrap' ).hasClass( 'html-active' ) ){
		 	$( '#blox_editor_master' ).val( $( '#' + block_id + ' .blox-editor-output' ).val() );
		}

		// Add the overlay to the page and style on click
		var overlay = $( '<div id="blox_overlay"></div>' );
		$( 'body' ).append( overlay );
		$( '#blox_overlay' ).css({ 'display' : 'block', 'opacity' : 0 });
		$( '#blox_overlay' ).fadeTo( 200, 0.7 );

		// Add the modal to the page and style on click
		$( '#blox_editor' ).css({
			'display' : 'block',
			'position' : 'fixed',
			'opacity' : 0,
			'z-index': 100000,
			'top' : 30 + 'px',
			'bottom' : 30 + 'px',
			'left' : 30 + 'px',
			'right' : 30 + 'px'

		});
		$( '#blox_editor' ).fadeTo( 200, 1 );

		// Get the block id and the block title
		var id = $( this ).parents( '.blox-content-block' ).attr( 'id' );
		var title = $( '#' + id + ' .blox-content-block-title' ).html();

		// Add block title to the editor modal title
		$( '#editor-title' ).html( title );

	});

	// After content has been added to the editor, insert it into the source textarea on click
	$(document).on( 'click', '#blox_editor_insert', function(e) {

		e.preventDefault();

		// Get the block id
		var block_id = $( '#blox_editor_master_id' ).val();

		var editor_content = '';

		// Get the editor content depending on which frame is active
		if ( $( '#wp-blox_editor_master-wrap' ).hasClass( 'tmce-active' ) ){

			// Before we insert the content, save the content in the editor or tinymce will get confused
			tinyMCE.get('blox_editor_master').save( { no_events: true } );

			// Set our content variable to the editor content
			editor_content = tinyMCE.get( 'blox_editor_master' ).getContent();

			// After we have the content, empty the editor and save
			tinymce.get( 'blox_editor_master' ).setContent( '' );
			tinyMCE.get( 'blox_editor_master' ).save( { no_events: true } );

		} else if ( $( '#wp-blox_editor_master-wrap' ).hasClass( 'html-active' ) ) {

			// Since we are on the HTML frame, things are much easier
			// Set our content variable to the editor content
			editor_content = $( '#blox_editor_master' ).val();

			// After we have the content, empty the editor
			$( '#blox_editor_master' ).val( '' );
		}

		// Insert the editor content into the source textarea
		// If the editor is empty is will still return '<p> </p>' occasionally so account for that...
		if ( editor_content === '<p> </p>' || editor_content == '' ){
			$( '#' + block_id + ' .blox-editor-output' ).val( '' );
			$( '#' + block_id + ' .blox-editor-add' ).text( blox_localize_metabox_scripts.editor_add );
		} else {
			$( '#' + block_id + ' .blox-editor-output' ).val( editor_content );
			$( '#' + block_id + ' .blox-editor-add' ).text( blox_localize_metabox_scripts.editor_edit );

			// If we have actually added some content, show the source textarea and change the name of the button
			$( '#' + block_id + ' .blox-editor-output-wrapper' ).show();
			$( '#' + block_id + ' .blox-editor-show-source' ).html( blox_localize_metabox_scripts.editor_hide_html );
		}

		// Close modal and remove the overlay
		$( '#blox_overlay' ).fadeOut(200);
		$( '#blox_editor' ).css( { 'display' : 'none' } );

    });



	/* Content - Raw scripts
	-------------------------------------------------------------- */

	// Display the editor modal
	// Code is a heavily modified version of http://leanmodal.finelysliced.com.au
	$(document).on( 'click', '.blox-raw-expand', function(e) {

		e.preventDefault();

		var block_type       = '';
		var block_id         = '';
		var existing_content = '';

		// Get the block id
		if ( $(this).parents( '.blox-settings-tabs' ).hasClass( 'local' ) ) {
			block_type       = 'local';
			block_id         = $(this).parents( '.blox-content-block' ).attr( 'id' );
			existing_content = $( '#' + block_id + ' .blox-raw-output' ).val();
		} else if ( $(this).parents( '.blox-settings-tabs' ).hasClass( 'global' ) ) {
			block_type       = 'global';
			block_id         = $( '#post_ID' ).val();
			existing_content = $( '.blox-raw-output' ).val();
		}

		// Set the block id and type in the modal for future use
		$( '#blox_raw_block_type' ).val( block_type );
		$( '#blox_raw_block_id' ).val( block_id );
		$( '#blox_raw_content' ).val( existing_content );

		// Add the overlay to the page and style on click
		var overlay = $( '<div id="blox_overlay"></div>' );
		$( 'body' ).append( overlay );
		$( '#blox_overlay' ).css({ 'display' : 'block', 'opacity' : 0 });
		$( '#blox_overlay' ).fadeTo( 200, 0.7 );

		// Add the modal to the page and style on click
		$( '#blox_raw' ).css({
			'display' : 'block',
			'position' : 'fixed',
			'opacity' : 0,
			'z-index': 100000,
			'top' : 30 + 'px',
			'bottom' : 30 + 'px',
			'left' : 30 + 'px',
			'right' : 30 + 'px'

		});
		$( '#blox_raw' ).fadeTo( 200, 1 );

		// If syntax highlighting is enabled, do stuff
		if( blox_localize_metabox_scripts.raw_syntax_highlighting_disable == false ) {
			// Need to call after the modal is displayed or else codemirror won't work properly
			blox_raw_fullscreen_editor.setValue(existing_content);
			blox_raw_fullscreen_editor.refresh(); // Always refresh codemirror editor after changes are made
		}

		// Fix bug with resize icon showing through to the modal
		$( '.blox-raw-output' ).css( 'resize', 'none' );
	});


	// After content has been added to the raw modal, insert it into the source textarea on click
	$(document).on( 'click', '#blox_raw_insert', function(e) {

		e.preventDefault();

		// Get the block id and type
		var block_type = $( '#blox_raw_block_type' ).val();
		var block_id   = $( '#blox_raw_block_id' ).val();

		var raw_content = '';

		// If syntax highlighting is enabled, do stuff
		if( blox_localize_metabox_scripts.raw_syntax_highlighting_disable == false ) {
			// Fill the default text area with the codemirror editor's content
			$( '#blox_raw_content' ).val( blox_raw_fullscreen_editor.getValue() );
		}

		// Set our content variable
		raw_content = $( '#blox_raw_content' ).val();

		// After we have the content, empty the raw content editor for future use
		$( '#blox_raw_content' ).val( '' );

		// Insert the raw content into the source textarea
		if ( block_type == 'local' ){
			$( '#' + block_id + ' .blox-raw-output' ).val( raw_content );
		} else {
			$( '.blox-raw-output' ).val( raw_content );
		}

		// Close modal and remove the overlay
		$( '#blox_overlay' ).fadeOut(200);
		$( '#blox_raw' ).css( { 'display' : 'none' } );

		// Reset the resize attribute, see previous function for details
		$( '.blox-raw-output' ).css( 'resize', 'vertical' );
    });



	/* Position scripts
	-------------------------------------------------------------- */

	// Shows and hides each content type on selection
	$(document).on( 'change', '.blox-position-type select', function(){

		var selected         = $(this).val(),
			default_position = $(this).siblings( '.blox-position-default' ),
			custom_position  = $(this).parents( '.blox-position-type' ).siblings( '.blox-position-custom' ),
			hidden           = 'blox-hidden';

		if ( selected === 'default' ) {
			default_position.removeClass( hidden );
			custom_position.addClass( hidden );
		} else if ( selected === 'custom' ) {
			default_position.addClass( hidden );
			custom_position.removeClass( hidden );
		} else {
			default_position.removeClass( 'blox-hidden' );
			custom_position.addClass( hidden );
		}
	});



	/* Visibility scripts
	-------------------------------------------------------------- */

	// Shows and hides visibility restrictions
	$(document).on( 'change', '.blox-visibility-role_type select', function(){

		var selected      = $(this).val(),
			role_restrict = $(this).parents( '.blox-visibility-role_type' ).siblings( '.blox-visibility-role-restrictions' ),
			hidden        = 'blox-hidden';

		if ( selected === 'restrict' ) {
			role_restrict.removeClass( hidden );
		} else {
			role_restrict.addClass( hidden );
		}
	});



	/* Location scripts
	-------------------------------------------------------------- */

	// Shows and hides each content type on selection
	$(document).on( 'change', '#blox_location_type', function(){
		if ( $(this).val() == 'hide_selected' ) {
			$( '.blox-location-container').removeClass( 'blox-hidden' );
			$( '.blox-test-description' ).html( blox_localize_metabox_scripts.location_test_hide );
			$( 'tr#blox_location_manual_hide' ).addClass( 'blox-hidden' );
			$( 'tr#blox_location_manual_show' ).removeClass( 'blox-hidden' );
		} else if ( $(this).val() == 'show_selected' ) {
			$( '.blox-location-container').removeClass( 'blox-hidden' );
			$( '.blox-test-description' ).html( blox_localize_metabox_scripts.location_test_show );
			$( 'tr#blox_location_manual_hide' ).removeClass( 'blox-hidden' );
			$( 'tr#blox_location_manual_show' ).addClass( 'blox-hidden' );
		} else {
			$( '.blox-location-container').addClass( 'blox-hidden' );
		}
	});

	//
	$(document).on( 'change', '.blox-location-selection input', function(){

		// Get the input id and change underscores to dashes to match class styles
		var selection = $(this).val();

		// Show and hide location option advanced settings based on check
	  	if ( $(this).is( ':checked' ) ) {
			$( 'tr#blox_location_' + selection ).removeClass( 'blox-hidden' );
	  	} else {
			$( 'tr#blox_location_' + selection ).addClass( 'blox-hidden' );
		}
	});

	// Show/Hide singles selection
	$(document).on( 'change', '.blox-location-singles-selection input', function(){
		var inputClass = 'blox-location-singles-' + $(this).val();

	  	if ( $(this).is( ':checked' ) ) {
			$( '.' + inputClass ).removeClass( 'blox-hidden' );
	  	} else {
			$( '.' + inputClass ).addClass( 'blox-hidden' );
		}
	});

	// Show/Hide archive selection
	$(document).on( 'change', '.blox-location-archive-selection input', function(){
		var inputClass = 'blox-location-archive-' + $(this).val();

	  	if ( $(this).is( ':checked' ) ) {
			$( '.' + inputClass ).removeClass( 'blox-hidden' );
	  	} else {
			$( '.' + inputClass ).addClass( 'blox-hidden' );
		}
	});

	//
	$(document).on( 'change', '.blox-location-select_type', function(){
		if ( $(this).val() == 'selected' ) {
			$(this).siblings( '.blox-location-selected-container' ).removeClass( 'blox-hidden' );
		} else {
			$(this).siblings( '.blox-location-selected-container' ).addClass( 'blox-hidden' );
		}
	});

	//
	$(document).on( 'change', '.blox-singles-select_type', function(){
		if ( $(this).val() == 'selected_posts' ) {
			$(this).siblings( '.blox-singles-container-inner' ).show();
			$(this).siblings( '.blox-singles-container-inner' ).children( '.blox-singles-post-container' ).show();
			$(this).siblings( '.blox-singles-container-inner' ).children( '.blox-singles-taxonomy-container-wrapper' ).hide();
			$(this).siblings( '.blox-singles-container-inner' ).children( '.blox-singles-authors-container-wrapper' ).hide();
		} else if ( $(this).val() == 'selected_taxonomies' ) {
			$(this).siblings( '.blox-singles-container-inner' ).show();
			$(this).siblings( '.blox-singles-container-inner' ).children( '.blox-singles-post-container' ).hide();
			$(this).siblings( '.blox-singles-container-inner' ).children( '.blox-singles-taxonomy-container-wrapper' ).show();
			$(this).siblings( '.blox-singles-container-inner' ).children( '.blox-singles-authors-container-wrapper' ).hide();
		} else if ( $(this).val() == 'selected_authors' ) {
			$(this).siblings( '.blox-singles-container-inner' ).show();
			$(this).siblings( '.blox-singles-container-inner' ).children( '.blox-singles-post-container' ).hide();
			$(this).siblings( '.blox-singles-container-inner' ).children( '.blox-singles-taxonomy-container-wrapper' ).hide();
			$(this).siblings( '.blox-singles-container-inner' ).children( '.blox-singles-authors-container-wrapper' ).show();
		} else {
			$(this).siblings( '.blox-singles-container-inner' ).hide();
		}
	});

	//
	$(document).on( 'change', '.blox-taxonomy-select_type', function(){
		if ( $(this).val() == 'selected_taxonomies' ) {
			$(this).siblings( '.blox-singles-taxonomy-container-inner' ).show();
		} else {
			$(this).siblings( '.blox-singles-taxonomy-container-inner' ).hide();
		}
	});



	/* Multi Checkbox Select All/None
	-------------------------------------------------------------- */

	// Select all options
	$( '.blox-checkbox-select-all' ).click( function(e) {
		e.preventDefault();

		$(this).parent().siblings( '.blox-checkbox-container' ).find( 'input' ).prop('checked', true).trigger("change");
	});

	// Deselect all options
	$( '.blox-checkbox-select-none' ).click( function(e) {
		e.preventDefault();

		$(this).parent().siblings( '.blox-checkbox-container' ).find( 'input' ).prop('checked', false).trigger("change");
	});



	/* Helper Text scripts
	-------------------------------------------------------------- */

	// Show/Hide help text when (?) is clicked
	var helpIcon = window.helpIcon = {

		toggleHelp : function( el ) {
			$( el ).parent().siblings( '.blox-help-text' ).slideToggle( 'fast' );
			return false;
		}
	}



	/* Local Blocks metabox scripts
	-------------------------------------------------------------- */

	// Remove Content Blocks (Need to '.on' because we are working with dynamically generated content)
	$(document).on( 'click', '#blox_content_blocks_container .blox-remove-block', function() {

		var message = confirm( blox_localize_metabox_scripts.confirm_remove );

		if ( message == true ) {
			$(this).parents( '.blox-content-block' ).remove();
			return false;
		} else {
			// Makes the browser not shoot to the top of the page on "cancel"
			return false;
		}
	});

	// Edit Content Blocks (Need to '.on' because we are working with dynamically generated content)
	$(document).on( 'click', '.blox-content-block-header', function() {
		//$( this ).siblings( '.blox-settings-tabs' ).toggle( 0 );
		$( this ).parents( '.blox-content-block' ).toggleClass( 'editing' );

		var editing = $( this ).siblings( '.blox-content-block-editing' );
		editing.prop( 'checked', !editing.prop( 'checked' ) );

		return false;

	});

	// Prevent content container from closing when you click on the title input
	$(document).on( 'click', '.blox-content-block-title-input', function(e) {
		e.stopPropagation();
	});

	// Make local blocks sortable
	$( '#blox_content_blocks_container' ).sortable({
		items: '.blox-content-block',
		cursor: 'move',
		handle: '.blox-content-block-header',
		forcePlaceholderSize: true,
		placeholder: 'placeholder'
	});

	// Updates our content block title field in real time
	$(document).on( 'keyup', '.blox-content-block-title-input input', function(e) {
		titleText = e.target.value;

		if ( titleText != '' ) {
			// If a new title has been added, update the title div
			$(this).parents( '.blox-content-block-title-input' ).siblings( '.blox-content-block-title' ).text( titleText );
		} else {
			// If the title has been removed, add our "No Title" text
			$(this).parents( '.blox-content-block-title-input' ).siblings( '.blox-content-block-title' ).html( '<span class="no-title">No Title</span>' );
		}
	});

	// On global blocks, preserve current tab on save an on page refresh
	if ( $( 'body' ).hasClass( 'post-type-blox' ) ) {
		var blox_tabs_hash 	    = window.location.hash,
			blox_tabs_hash_sani = window.location.hash.replace('!', '');

		// If we have a hash and it begins with "soliloquy-tab", set the proper tab to be opened.
		if ( blox_tabs_hash && blox_tabs_hash.indexOf( 'blox_tab_' ) >= 0 ) {
			$( '.blox-tab-navigation li' ).removeClass( 'current' );
			$( '.blox-tab-navigation' ).find( 'li a[href="' + blox_tabs_hash_sani + '"]' ).parent().addClass( 'current' );
			$( '.blox-tabs-container' ).children().hide();
			$( '.blox-tabs-container' ).children( blox_tabs_hash_sani ).show();

			// Update the post action to contain our hash so the proper tab can be loaded on save.
			var post_action = $( '#post' ).attr( 'action' );
			if ( post_action ) {
				post_action = post_action.split( '#' )[0];
				$( '#post' ).attr( 'action', post_action + blox_tabs_hash );
			}
		}
	}

	// Show desired tab on click
  	$(document).on( 'click', '.blox-tab-navigation a', function(e) {
		e.preventDefault();

		if ( $( this ).parent().hasClass( 'current' ) ) {
			return;
		} else {
			// Adds current class to active tab heading
			$( this ).parent().addClass( 'current' );
			$( this ).parent().siblings().removeClass( 'current' );

			var tab = $( this ).attr( 'href' );

			if ( $( this ).parents( '.blox-settings-tabs' ).hasClass( 'global' ) ) {

				// We add the ! so the addition of the hash does not cause the page to jump
				window.location.hash = $( this ).attr( 'href' ).split( '#' ).join( '#!' );

				// Update the post action to contain our hash so the proper tab can be loaded on save.
                var post_action = $( '#post' ).attr( 'action' );
                if ( post_action ) {
                    post_action = post_action.split('#')[0];
                    $( '#post' ).attr( 'action', post_action + window.location.hash );
                }

			}

			// Show the correct tab
			$(this).parents( '.blox-tab-navigation' ).siblings( '.blox-tabs-container' ).children( '.blox-tab-content' ).not( tab ).hide();
			$(this).parents( '.blox-tab-navigation' ).siblings( '.blox-tabs-container' ).children( tab ).show();
		}

    });

    // Script to add empty block when button is clicked
	$(document).on( 'click', '#blox_add_block', function(e) {

		e.preventDefault();

		// Get the block id for targeting purposes
		var block_id = null;

		// Get the post id for saving purposes
		var post_id = $( '#post_ID' ).attr( 'value' );

		// Store callback method name and nonce field value in an array.
		var data = {
			action: 'blox_add_block', // AJAX callback
			post_id: post_id,
			block_id: block_id,
			type: 'new',
			blox_add_block_nonce: blox_localize_metabox_scripts.blox_add_block_nonce
		};

		// AJAX call.
		$.post( blox_localize_metabox_scripts.ajax_url, data, function( response ) {
			$('#blox_content_blocks_container').prepend(response);
			$('#blox_content_blocks_container .blox-content-block').first().addClass( 'editing new' );
			$('#blox_content_blocks_container .blox-content-block').first().children( '.blox-content-block-editing' ).prop( 'checked', true );

			// Run when new block is added so default content is visible
			show_selected_content();

			// Hide the Add Block button description if it is there.
			$( '#blox_add_block_description' ).hide();
		});

	});

	// Script to duplicate an existing block when button is clicked
	$(document).on( 'click', '.blox-duplicate-block', function(e) {

		e.preventDefault();

		// Get the block id for targeting purposes
		var block_id = $( this ).parents( '.blox-content-block' ).attr( 'id' );

		// Get the post id for saving purposes
		var post_id = $( '#post_ID' ).attr( 'value' );

		//alert ( block_id );

		// Store callback method name and nonce field value in an array.
		var data = {
			action: 'blox_add_block', // AJAX callback
			post_id: post_id,
			block_id: block_id,
			type: 'copy',
			blox_add_block_nonce: blox_localize_metabox_scripts.blox_add_block_nonce
		};

		// AJAX call.
		$.post( blox_localize_metabox_scripts.ajax_url, data, function( response ) {
			$('#blox_content_blocks_container').prepend(response);
			$('#blox_content_blocks_container .blox-content-block').first().addClass( 'editing new' );
			$('#blox_content_blocks_container .blox-content-block').first().children( '.blox-content-block-editing' ).prop( 'checked', true );

			// Run when new block is added so default content is visible
			show_selected_content();
		});

		// Stop any additional js from firing after replication
		e.stopPropagation();

	});

});
