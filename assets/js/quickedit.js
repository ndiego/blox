jQuery(document).ready(function($){

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

			// get the data
			var $global_disable = $( '.column-visibility input[name="global_disable"]', $post_row ).val();
            $global_disable = ( $global_disable == 1 ) ? true : false;


			// populate the data
			$( ':input[name="global_disable"]', $edit_row ).prop('checked', $global_disable );
		}
	};
});
