jQuery(document).ready(function($){

    // Hide unneeded fields from Blox quickedit menu
    $( '.inline-edit-blox td fieldset:first-child .inline-edit-col' ).children().not(':first').css( "display" , "none");

    // we create a copy of the WP inline edit post function
	var $wp_inline_edit = inlineEditPost.edit;

	// and then we overwrite the function with our own code
	inlineEditPost.edit = function( id ) {

		// "call" the original WP edit function
		// we don't want to leave WordPress hanging
		$wp_inline_edit.apply( this, arguments );

		// now we take care of our business

		// get the post ID
		var $post_id = 0;
		if ( typeof( id ) == 'object' ) {
			$post_id = parseInt( this.getId( id ) );
		}

		if ( $post_id > 0 ) {
			// define the edit row
			var $edit_row = $( '#edit-' + $post_id ),
                $post_row = $( '#post-' + $post_id );

			// Get visibility data
			var $global_disable = $( '.column-visibility input[name="global_disable"]', $post_row ).val();
            $global_disable = ( $global_disable == 1 ) ? true : false;

            // Populate visibility data
            $( ':input[name="global_disable"]', $edit_row ).prop('checked', $global_disable );


            // Get position data
            var $position_type   = $( '.column-position input[name="position_type"]', $post_row ).val(),
                $custom_position = $( '.column-position input[name="custom_position"]', $post_row ).val(),
                $custom_priority = $( '.column-position input[name="custom_priority"]', $post_row ).val();

            // Populate position data
            $( 'select[name="position_type"]', $edit_row ).val( $position_type );
            $( 'select[name="custom_position"]', $edit_row ).val( $custom_position );
            $( ':input[name="custom_priority"]', $edit_row ).val( $custom_priority );

            // Show/hide the position hook containers based on hook type
            if ( $position_type == 'default' ){
                $( '.quickedit-position-hook-default', $edit_row ).css( 'display', 'block' );
            } else if ( $position_type == 'custom' ) {
                $( '.quickedit-position-hook-custom', $edit_row ).css( 'display', 'block' );
            }

            // Shows and hides each hook type on selection
        	$(document).on( 'change', 'select[name="position_type"]', function(){
        		if ( $(this).val() == 'custom' ) {
        			$( '.quickedit-position-hook-default', $edit_row ).css( 'display', 'none' );
        			$( '.quickedit-position-hook-custom', $edit_row ).css( 'display', 'block' );
        		} else {
                    $( '.quickedit-position-hook-default', $edit_row ).css( 'display', 'block' );
                    $( '.quickedit-position-hook-custom', $edit_row ).css( 'display', 'none' ); 
        		}
        	});


		}
	};
});
