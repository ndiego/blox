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
	});

	// Close the modal if you click on close button
	$(document).on( 'click', '.blox-modal-close', function() {
		$( '#blox_overlay' ).fadeOut(200);
		$( '.blox-modal' ).css({ 'display' : 'none' });
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
					var select_target = '#' + block_id + ' .blox-slider-container';
				} else {
					// We are on global so we don't worry about targeting
					var select_target ='.blox-slider-container';
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
						output += '<div class="blox-slide-container"><image  src="' + attachment.sizes.thumbnail.url + '" alt="' + attachment.alt + '" /></div>';
						output += '<input type="text" class="slide-image-id blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][slide_type]" value="image" />';
						output += '<input type="text" class="slide-image-id blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][id]" value="' + attachment.id + '" />';
						output += '<input type="text" class="slide-image-url blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][url]" value="' + attachment.url + '" />';
						output += '<input type="text" class="slide-image-title blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][title]" value="' + attachment.title + '" />';
						output += '<input type="text" class="slide-image-alt blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][alt]" value="' + attachment.alt + '" />';
						output += '<input type="checkbox" class="slide-image-link-enable blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][link][enable]" value="1" />';
						output += '<input type="text" class="slide-image-link-url blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][link][url]" value="http://" />';
						output += '<input type="text" class="slide-image-link-title blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][link][title]" value="" />';
						output += '<input type="checkbox" class="slide-image-link-target blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][link][target]" value="1" />';
						output += '<input type="text" class="slide-image-caption blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][caption]" value="' + attachment.caption + '" />';
						output += '<input type="text" class="slide-image-classes blox-force-hidden" name="' + frame.id + '[slideshow][builtin][slides]['+ randSlideId +'][image][classes]" value="" />';
						output += '<div class="blox-slide-details-container"><a class="blox-slide-details" href="#blox_slide_details">' + blox_localize_metabox_scripts.slideshow_details + '</a><a class="blox-slide-remove" href="#">' + blox_localize_metabox_scripts.slideshow_remove + '</a></div>';
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

	// Remove Slideshow Items
	// Need to '.on' because we are working with dynamically generated content
	$(document).on( 'click', '.blox-slider-container .blox-slide-remove', function() {

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
				$( block_id + ' .blox-slider-container' ).append( '<li class="blox-filler" ><div class="blox-filler-container"></div><div class="blox-filler-text"><span>' + blox_localize_metabox_scripts.slideshow_details + '</span><span class="right">' + blox_localize_metabox_scripts.slideshow_remove + '</span></div></li>' );
			}
			return false;
		} else {
			// Makes the browser not shoot to the top of the page on "cancel"
			return false;
		}
	});

	// Make Slideshow Items sortable
	$( '.blox-slider-container' ).sortable({
		items: '.blox-slideshow-item',
		cursor: 'move',
		forcePlaceholderSize: true,
		placeholder: 'placeholder'
	});

	// Common function for both Galleries and Slideshows
	// Display the slide details modal (need .on because new slides are dynamically added to the page)
	// Code is a heavily modified version of http://leanmodal.finelysliced.com.au
	$(document).on( 'click', '.blox-slide-details', function(e) {

		e.preventDefault();

		var $is_gallery = $( this ).parents( 'li' ).hasClass( 'blox-gallery-item' );

		if ( $is_gallery ) {
			//alert('this is a gallery');
		}

		// Add the overlay to the page and style on click
		var overlay = $( '<div id="blox_overlay"></div>' );
		$( 'body' ).append(overlay);
		$( '#blox_overlay' ).css( { 'display' : 'block', 'opacity' : 0 } );
		$( '#blox_overlay' ).fadeTo( 200, 0.7 );

		// Add the modal to the page and style on click
		var modal_id = "#blox_slide_details";
		var modal_height = $( modal_id ).outerHeight();
		var modal_width = $( modal_id ).outerWidth();
		$( modal_id ).css({
			'display' : 'block',
			'position' : 'fixed',
			'opacity' : 0,
			'z-index': 110000,
			'top' : 30 + 'px',
			'bottom' : 30 + 'px',
			'left' : 30 + 'px',
			'right' : 30 + 'px'

			// Old Styling
			//'left' : 50 + '%',
			//'margin-left' : -(modal_width/2) + "px",
			//'top' : 40 + "%",
			//'margin-top' : -(modal_height/2) + "px"
		});
		$( modal_id ).fadeTo( 200, 1 );

		// Grab our existing slide details
		var id 			= $( this ).parents( 'li' ).attr( 'id' );
		var title 		= $( '#' + id + ' .slide-image-title' ).attr( 'value' );
		var alt 		= $( '#' + id + ' .slide-image-alt' ).attr( 'value' );
		var caption 	= $( '#' + id + ' .slide-image-caption' ).attr( 'value' );
		var link_enable = $( '#' + id + ' .slide-image-link-enable' ).is( ':checked' );
		var link_url 	= $( '#' + id + ' .slide-image-link-url' ).attr( 'value' );
		var link_title 	= $( '#' + id + ' .slide-image-link-title' ).attr( 'value' );
		var link_target = $( '#' + id + ' .slide-image-link-target' ).is( ':checked' );
		var classes 	= $( '#' + id + ' .slide-image-classes' ).attr( 'value' );

		// Populate the modal with existing details on open
		$( '.modal-slide-id' ).attr( 'value' , id );
		$( '.modal-slide-image-title' ).attr( 'value' , title );
		$( '.modal-slide-image-alt' ).attr( 'value' , alt );
		$( '.modal-slide-image-caption' ).attr( 'value' , caption );
		$( '.modal-slide-image-link-enable' ).prop( 'checked', link_enable );
		$( '.modal-slide-image-link-url' ).attr( 'value' , link_url );
		$( '.modal-slide-image-link-title' ).attr( 'value' , link_title );
		$( '.modal-slide-image-link-target' ).prop( 'checked', link_target );
		$( '.modal-slide-image-classes' ).attr( 'value' , classes );

		// If the image link is enabled, show the additional options
		if ( $( '.modal-slide-image-link-enable' ).is( ':checked' ) ) {
		  	$( '.modal-slide-image-link-enable' ).parents( '.blox-image-link-enable' ).siblings( '.blox-image-link' ).show();
		}

		// Gallery specific settings
		if ( $is_gallery ) {
			var width = $( '#' + id + ' .slide-width' ).attr( 'value' );
			var height = $( '#' + id + ' .slide-height' ).attr( 'value' );

			$( '.modal-slide-width' ).attr( 'value' , width );
			$( '.modal-slide-height' ).attr( 'value' , height );
		}


		// Add our new details to the slide on button click
		// Need to use .data() otherwise won't work due to dynamic targeting issue
		$(document).data( 'slide-metadata', { ids: id }).on( 'click', '#blox-apply-details', function() {
			$( '#' + $( document ).data( "slide-metadata" ).ids + ' .slide-image-title' ).val( $( '.modal-slide-image-title' ).val() );
			$( '#' + $( document ).data( "slide-metadata" ).ids + ' .slide-image-alt' ).val( $( '.modal-slide-image-alt' ).val() );
			$( '#' + $( document ).data( "slide-metadata" ).ids + ' .slide-image-caption' ).val( $( '.modal-slide-image-caption' ).val() );

			$( '#' + $( document ).data( "slide-metadata" ).ids + ' .slide-image-link-enable' ).prop( 'checked', $( '.modal-slide-image-link-enable' ).is( ':checked' ) );
			$( '#' + $( document ).data( "slide-metadata" ).ids + ' .slide-image-link-url' ).val( $( '.modal-slide-image-link-url' ).val() );
			$( '#' + $( document ).data( "slide-metadata" ).ids + ' .slide-image-link-title' ).val( $( '.modal-slide-image-link-title' ).val() );
			$( '#' + $( document ).data( "slide-metadata" ).ids + ' .slide-image-link-target' ).prop( 'checked', $( '.modal-slide-image-link-target' ).is( ':checked' ) );
			$( '#' + $( document ).data( "slide-metadata" ).ids + ' .slide-image-classes' ).val( $( '.modal-slide-image-classes' ).val() );

			// Gallery specific settings
			if ( $is_gallery ) {
				$( '#' + $( document ).data( "slide-metadata" ).ids + ' .slide-width' ).val( $( '.modal-slide-width' ).val() );
				$( '#' + $( document ).data( "slide-metadata" ).ids + ' .slide-height' ).val( $( '.modal-slide-height' ).val() );
			}

			$( "#blox_overlay" ).fadeOut(200);
			$( modal_id ).css( { 'display' : 'none' } );
		});

		// Close the modal if you click on the overlay
		$(document).on( 'click', '#blox_overlay', function() {
			$( "#blox_overlay" ).fadeOut(200);
			$( modal_id ).css( { 'display' : 'none' } );
		});

		// Close the modal if you click on close button
		$(document).on( 'click', '.blox-modal-close', function() {
			$( "#blox_overlay" ).fadeOut(200);
			$( modal_id ).css( { 'display' : 'none' } );
		});

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
		if ( $(this).val() == 'default' ) {
			$(this).siblings( '.blox-position-default' ).removeClass( 'blox-hidden' );
			$(this).parents( '.blox-position-type' ).siblings( '.blox-position-custom' ).addClass( 'blox-hidden' );
		} else if ( $(this).val() == 'custom' ) {
			$(this).siblings( '.blox-position-default' ).addClass( 'blox-hidden' );
			$(this).parents( '.blox-position-type' ).siblings( '.blox-position-custom').removeClass( 'blox-hidden' );
		} else {
			$(this).siblings( '.blox-position-default' ).removeClass( 'blox-hidden' );
			$(this).parents( '.blox-position-type' ).siblings( '.blox-position-custom').addClass( 'blox-hidden' );
		}
	});



	/* Visibility scripts
	-------------------------------------------------------------- */

	// Shows and hides visibility restrictions
	$(document).on( 'change', '.blox-visibility-role_type select', function(){
		if ( $(this).val() == 'restrict' ) {
			$(this).parents( '.blox-visibility-role_type' ).siblings( '.blox-visibility-role-restrictions' ).removeClass( 'blox-hidden' );
		} else {
			$(this).parents( '.blox-visibility-role_type' ).siblings( '.blox-visibility-role-restrictions' ).addClass( 'blox-hidden' );
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
