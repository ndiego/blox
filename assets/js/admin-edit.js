jQuery(document).ready(function($){


    /* Admin quick edit and bulk edit scripts
    -------------------------------------------------------------- */

    // Hide unneeded fields from Blox quickedit menu
    $( '.quick-edit-row.inline-edit-blox td fieldset:first-child .inline-edit-col' ).children().not(':first').css( "display" , "none");

    // we create a copy of the WP inline edit post function
	var wp_inline_edit = inlineEditPost.edit;

	// and then we overwrite the function with our own code
	inlineEditPost.edit = function( id ) {

		// "call" the original WP edit function
		// we don't want to leave WordPress hanging
		wp_inline_edit.apply( this, arguments );

		// now we take care of our business

		// get the post ID
		var post_id = 0;
		if ( typeof( id ) == 'object' ) {
			post_id = parseInt( this.getId( id ) );
		}

		if ( post_id > 0 ) {
			// define the edit row
			var edit_row = $( '#edit-' + post_id ),
                post_row = $( '#post-' + post_id );

			// Get visibility data
			var global_disable = $( '.column-visibility input[name="global_disable"]', post_row ).val();
            global_disable = ( global_disable == 1 ) ? 1 : 0;

            // Populate visibility data
            $( ':input[name="global_disable"]', edit_row ).prop('checked', global_disable );
		}
	};


    // Bulk edit save
    $( document.body ).on( 'click', '#bulk_edit', function() {

        // Define the bulk edit row
        var bulk_row = $( '#bulk-edit' );

        // Get the selected post ids that are being edited
        var post_ids = new Array();
        bulk_row.find( '#bulk-titles' ).children().each( function() {
            post_ids.push( $( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
        });

        // Get visibility data
        var global_disable = bulk_row.find( 'input[name="global_disable"]' ).is(':checked') ? 1 : 0;

        var data = {
            action: 'blox_save_bulkedit_meta',
            post_ids: post_ids,
            global_disable: global_disable,
            // NEED TO ADD NONCE
        }

        // Save the data
        $.post( ajaxurl, data );
    });



    /* Admin column scripts
    -------------------------------------------------------------- */

    // Add a "condensed" class so we can conditionally style column data
    function conditionally_condense_data(){
        if ( $( window ).width() > 782 ) {
            if ( $( '.column-visibility' ).width() < 165 || $( '.column-position' ).width() < 165 ) {
                $( '.wp-list-table' ).addClass( 'blox-condensed-data' );
            } else {
                $( '.wp-list-table' ).removeClass( 'blox-condensed-data' );
            }
        } else {
           $( '.wp-list-table' ).removeClass( 'blox-condensed-data' );
       }
    }


    // Load condenser function on page load
    conditionally_condense_data();


    // Load condenser function on window resize
    $( window ).on( 'resize', function(){
        conditionally_condense_data();
    } );


    // Toggle column data
    $( document.body ).on( 'click', '.blox-data-control-toggle', function(e) {

        var type = $(this).attr( 'data-details-type' );
        var block_id = '#' + $(this).parents( 'tr' ).attr( 'id' );

        if ( $(this).parents( '.blox-data-control' ).hasClass( 'selected' ) ) {

            // If the control is already selected, deselect
            $(this).parents( '.blox-data-control' ).removeClass( 'selected' );
            $( block_id + ' .blox-data-details' ).removeClass( 'selected' );
        } else {

            // If not, deselect all controls and then select the one clicked
            $( block_id + ' .blox-data-control' ).removeClass( 'selected' );
            $( block_id + ' .blox-data-details' ).removeClass( 'selected' );

            $(this).parents( '.blox-data-control' ).addClass( 'selected' );
            $( block_id + ' .blox-data-details.' + type ).addClass( 'selected' );
        }
    });
});
