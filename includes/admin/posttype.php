<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Posttype admin class.
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Posttype_Admin {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;


    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;


    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $base;


    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Blox_Main::get_instance();

        // Remove quick editing from the global blocks post type row actions.
        add_filter( 'post_row_actions', array( $this, 'row_actions' ), 10, 2 );

        // Enable replication for Global blocks
        add_filter( 'post_row_actions', array( $this, 'duplicate_row_link' ), 10, 2 );
		add_action( 'post_submitbox_start', array( $this, 'duplicate_submitbox_link' ) );
        add_action( 'admin_action_blox_duplicate_block', array( $this, 'duplicate_block' ) );

        // Enable quick edit for Global blocks
        add_filter( 'post_row_actions', array( $this, 'quickedit_row_link' ), 10, 2 );
        add_action( 'quick_edit_custom_box', array( $this, 'display_custom_quickedit' ), 10, 2 );
        add_action( 'save_post', array( $this, 'save_quickedit_meta' ) );

        add_action( 'save_post', array( $this, 'local_blocks_columns_quickedit' ));

        // Enable bulk edit for Global blocks
        add_action( 'bulk_edit_custom_box', array( $this, 'display_custom_bulkedit' ), 10, 2 );
        add_action( 'wp_ajax_blox_save_bulkedit_meta', array( $this, 'save_bulkedit_meta' ) );

        // Manage post type columns.
        add_filter( 'manage_edit-blox_columns', array( $this, 'admin_column_titles' ) );
        add_filter( 'manage_blox_posts_custom_column', array( $this, 'admin_column_data' ), 10, 2 );

        // Update post type messages.
        add_filter( 'post_updated_messages', array( $this, 'messages' ) );

        // Conditionally add the Local Blocks column to admin pages
        // Note: Need to fire immediately after admin_init, hence current_screen
        add_action( 'current_screen', array( $this, 'local_blocks_columns' ) );
	}


	/**
	 * Add the duplicate link to action list for post_row_actions
	 *
	 * @since 1.1.0
	 *
	 * @param array $actions Existing array of action links
	 * @param obj $post The original block object (one to be duplicated)
	 */
	public function duplicate_row_link( $actions, $post ) {

		if ( $post->post_type == 'blox' ){

            //echo print_r( $post );

			$link  = admin_url( 'admin.php?action=blox_duplicate_block&amp;post=' . $post->ID );
			$title = __( 'Duplicate', 'blox' );

			$actions['duplicate'] = '<a href="' . $link . '" title="' . $title . '">' . $title . '</a>';
		}

		return $actions;
	}


	/**
	 * Add the duplicate link to submitbox on all Global blocks
	 *
	 * @since 1.1.0
	 */
	public function duplicate_submitbox_link() {

		$post  = isset( $_GET['post'] ) ? get_post( $_GET['post'] ) : false;

		if ( isset( $post ) && $post != null && $post->post_type == 'blox' ) {

			$link  = admin_url( 'admin.php?action=blox_duplicate_block&amp;post=' . $post->ID );
			$title = __( 'Duplicate Block', 'blox' );

			$output =  '<div id="blox-duplicate-action">';
			$output .= '<a href="' . $link . '" title="' . $title . '" style="text-decoration:none">' . $title . '</a>';
			$output .= '</div>';

			echo $output;
		}
	}


	/**
	 * Duplicate the given Global block
	 *
	 * @since 1.1.0
	 */
	public function duplicate_block() {

		if ( ! ( isset( $_GET['post'] ) || isset( $_POST['post'] )  || ( isset( $_REQUEST['action'] ) && 'blox_duplicate_block' == $_REQUEST['action'] ) ) ) {
			wp_die( __( 'You didn\'t choose a block to duplicate...try again.', 'blox' ) );
		}

		// Get the original post
		$id   = ( isset($_GET['post'] ) ? $_GET['post'] : $_POST['post']);
		$post = get_post( $id );

		// Duplicate the block
		if ( isset( $post ) && $post != null ) {

			// New block args
			$args = array(
				'post_status' => 'draft',
				'post_title' => $post->post_title . ' ' . __( 'Copy', 'blox' ),
				'post_type' => $post->post_type,
			);

			// Create new block
			$new_block_id = wp_insert_post( $args );

			// Get the metadata from the old block
			$block_meta   = get_post_meta( $post->ID, '_blox_content_blocks_data', true );

			// Play is safe and remove any existing meta data and then add old block's data to new block
			delete_post_meta( $new_block_id, '_blox_content_blocks_data' );
			update_post_meta( $new_block_id, '_blox_content_blocks_data', $block_meta );

			// Redirect to the edit screen for the new draft post
			wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_block_id ) );

			exit;

		} else {
			wp_die( esc_attr( __( 'Duplication has failed, the original block could not be located.', 'blox' ) ) );
		}
	}


    /**
     * Add the Quick Edit to Global Blocks
     *
     * @since 1.3.0
     *
     * @param array $actions Existing array of action links
     * @param obj $post The original block object
     */
    public function quickedit_row_link( $actions, $post ) {

        if ( $post->post_type == 'blox' ){

            $link = sprintf( '<a href="#" class="editinline" aria-label="%s">%s</a>', esc_attr( sprintf( __( 'Quick edit &#8220;%s&#8221; inline' ), $post->post_title ) ), __( 'Quick&nbsp;Edit' ) );

            //echo print_r( $post );
            $actions = array_slice( $actions, 0, 1, true) + array( 'inline hide-if-no-js' => $link ) + array_slice( $actions, 1, count( $actions ) - 1, true ) ;
        }

        return $actions;
    }


    /**
     * Print all of the bulk edit settings
     * Note: this function is called for each custom admin column
     *
     * @since 1.3.0
     *
     * @param string $column_name The current column type
     * @param string $post_type   The current post type
     */
    function display_custom_bulkedit( $column_name, $post_type ) {

        // If we are not quick editing a global block, bail
        if ( $post_type != 'blox' ) {
            return;
        }

        // Since this function is called once for each custom column, this
        // ensures the nonce field is only printed once, note each time this
        // function is called.
        static $print_nonce = TRUE;
        if ( $print_nonce ) {
            wp_nonce_field( plugin_basename( __FILE__ ), 'blox_bulkedit_nonce' );

            // We already printed the nonce, so don't do it again
            $print_nonce = FALSE;
        }

        do_action( 'blox_bulkedit_settings_' . $column_name, $post_type, 'bulk' );
    }


    /**
     * Save bulk edit settings via AJAX
     *
     * @since 1.3.0
     */
    public function save_bulkedit_meta() {

        // NEED TO ADD NONCE

        // Get all the selected posts (blocks)
        $post_ids = ( isset( $_POST['post_ids'] ) && ! empty( $_POST['post_ids'] ) ) ? $_POST['post_ids'] : array();

        if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
    		foreach ( $post_ids as $post_id ) {

                // Get existing block settings
                $settings = get_post_meta( $post_id, '_blox_content_blocks_data', true );

                // Update all our setting via filter
                $settings = apply_filters( 'blox_bulkedit_save_settings', $settings, $_POST, 'bulk' );

                // Push updates to post meta
                update_post_meta( $post_id, '_blox_content_blocks_data', $settings );
    		}
    	}

        // Since this function is called via ajax we need to call wp_die()
    	wp_die();
    }

    /**
     * Print all of the quickedit settings
     * Note: this function is called for each custom admin column
     *
     * @since 1.3.0
     *
     * @param string $column_name The current column type
     * @param string $post_type   The current post type
     */
    function display_custom_quickedit( $column_name, $post_type ) {

        // If we are not quick editing a global block, bail
        if ( $post_type != 'blox' ) {
            return;
        }

        // Since this function is called once for each custom column, this
        // ensures the nonce field is only printed once, note each time this
        // function is called.
        static $print_nonce = TRUE;
        if ( $print_nonce ) {
            wp_nonce_field( plugin_basename( __FILE__ ), 'blox_quickedit_nonce' );

            // We already printed the nonce, so don't do it again
            $print_nonce = FALSE;
        }

        do_action( 'blox_quickedit_settings_' . $column_name, $post_type, 'quick' );
    }


    /**
     * Save quick edit settings
     *
     * @since 1.3.0
     *
     * @param string $post_id The id of the block we are quick editing
     */
    function save_quickedit_meta( $post_id ) {

        $_POST += array( 'blox_quickedit_nonce' => '' );

        if ( !wp_verify_nonce( $_POST['blox_quickedit_nonce'], plugin_basename( __FILE__ ) ) ) {
            return;
        }

        if ( 'blox' !== $_POST['post_type'] ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Get existing block settings
        $settings = get_post_meta( $post_id, '_blox_content_blocks_data', true );

        // Update all our setting via filter
        $settings = apply_filters( 'blox_quickedit_save_settings', $settings, $_REQUEST, 'quick' );

        // Push updates to post meta
        update_post_meta( $post_id, '_blox_content_blocks_data', $settings );
    }


    /**
     * Customize the post columns for the blox post type.
     *
     * @since 1.0.0
     *
     * @param array $columns  The default columns.
     * @return array $columns Amended columns.
     */
    public function admin_column_titles( $columns ) {

        $columns = array(
            'cb'     => '<input type="checkbox" />',
            'title'  => __( 'Title', 'blox' )
        );

        $columns = apply_filters( 'blox_admin_column_titles', $columns );

        $columns['modified'] = __( 'Last Modified', 'blox' );
        $columns['date'] = __( 'Date', 'blox' );

        return $columns;
    }


    /**
     * Render the content for the admin columns for global blocks
     *
     * @since 1.0.0
     *
     * @global object $post  The current post object.
     * @param string $column The name of the custom column.
     * @param int $post_id   The current post ID.
     */
    public function admin_column_data( $column, $post_id ) {

        global $post;
        $post_id = absint( $post_id );

        $block_data = get_post_meta( $post_id, '_blox_content_blocks_data', true );

        // Print all additional column data
        do_action( 'blox_admin_column_data_' . $column, $post_id, $block_data );

        // Print the date last modified
        if ( $column == 'modified' ) {
        	the_modified_date();
        }

        // Hook in additional generic column settings
        do_action( 'blox_admin_column_data', $column, $post_id, $block_data );
    }


    /**
     * Filter out unnecessary row actions from the global blocks post table.
     *
     * @since 1.0.0
     *
     * @param array $actions  Default row actions.
     * @param object $post    The current post object.
     * @return array $actions Amended row actions.
     */
    public function row_actions( $actions, $post ) {

        if ( isset( get_current_screen()->post_type ) && 'blox' == get_current_screen()->post_type ) {
            unset( $actions['inline hide-if-no-js'] );
        }

        return apply_filters( 'blox_row_actions', $actions, $post );
    }


    /**
     * Contextualizes the post updated messages.
     *
     * @since 1.0.0
     *
     * @global object $post    The current post object.
     * @param array $messages  Array of default post updated messages.
     * @return array $messages Amended array of post updated messages.
     */
    public function messages( $messages ) {

        global $post;

        // Contextualize the messages.
        $messages['blox'] = apply_filters( 'blox_block_messages',
            array(
                0  => '',
                1  => __( 'Global block updated.', 'blox' ),
                2  => __( 'Global block custom field updated.', 'blox' ),
                3  => __( 'Global block custom field deleted.', 'blox' ),
                4  => __( 'Global block updated.', 'blox' ),
                5  => isset( $_GET['revision'] ) ? sprintf( __( 'Global block restored to revision from %s.', 'blox' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
                6  => __( 'Global block published.', 'blox' ),
                7  => __( 'Global block saved.', 'blox' ),
                8  => __( 'Global block submitted.', 'blox' ),
                9  => sprintf( __( 'Global block scheduled for: <strong>%1$s</strong>.', 'blox' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
                10 => __( 'Global block draft updated.', 'blox' )
            )
        );

        return apply_filters( 'blox_post_messages', $messages, $post );
    }


	/**
     * Conditionally add the Local Blocks column to admin pages
     *
     * @since 1.0.0
     *
     * @global string $typenow The current post type.
     */
	public function local_blocks_columns() {

        global $typenow;

		$local_enable  = blox_get_option( 'local_enable', false );

		if ( $local_enable ) {

			$enabled_pages = blox_get_option( 'local_enabled_pages', '' );

			// Note this does not work on some custom post types in other plugins, need to explore reason...
			if ( ! empty( $enabled_pages ) && in_array( $typenow, $enabled_pages ) ) {
				add_filter( 'manage_' . $typenow . '_posts_columns', array( $this, 'local_blocks_column_title' ), 5 );
				add_action( 'manage_' . $typenow . '_posts_custom_column', array( $this, 'local_blocks_column_data' ), 10, 2);

				// Tell Wordpress that the Local Blocks column is sortable
				add_filter( 'manage_edit-' . $typenow . '_sortable_columns', array( $this, 'local_blocks_columns_sortable' ), 5 );
			}
        }

        // Tell Wordpress how to sort Local Blocks
        add_filter( 'request', array( $this, 'local_blocks_columns_orderby' ) );
	}


    /**
     * Fixed bug where custom columns are not added back on quick edit (A bit redundant though)
     *
     * @since 1.4.1
     */
	public function local_blocks_columns_quickedit() {

        // If there is no post_type, bail...
        if ( ! isset( $_POST['post_type'] ) ) {
            return;
        }

        // Since this function is run when WP is using ajax, we need to use the $_POST to get the current post type
        $typenow = $_POST['post_type'];

		$local_enable  = blox_get_option( 'local_enable', false );

		if ( $local_enable ) {

			$enabled_pages = blox_get_option( 'local_enabled_pages', '' );

			// Note this does not work on some custom post types in other plugins, need to explore reason...
			if ( ! empty( $enabled_pages ) && in_array( $typenow, $enabled_pages ) ) {
				add_filter( 'manage_' . $typenow . '_posts_columns', array( $this, 'local_blocks_column_title' ), 5 );
				add_action( 'manage_' . $typenow . '_posts_custom_column', array( $this, 'local_blocks_column_data' ), 10, 2);

				// Tell Wordpress that the Local Blocks column is sortable
				add_filter( 'manage_edit-' . $typenow . '_sortable_columns', array( $this, 'local_blocks_columns_sortable' ), 5 );
			}
        }

        // Tell Wordpress how to sort Local Blocks
        add_filter( 'request', array( $this, 'local_blocks_columns_orderby' ) );
	}


	/**
     * Add Local Blocks column title
     *
     * @since 1.0.0
     *
     * @param array $columns  An array of all admin columns for give post type
     * @return array $columns The array of admin columns with our new one appended
     */
	public function local_blocks_column_title( $columns ) {
	  	$new_columns = array();

	  	// Specify where we want to put our column
  		foreach( $columns as $key => $title ) {
    		$new_columns[$key] = $title;
    		if ( $key == 'title' ) { // Put the Local Blocks column after the Title column
      			$new_columns['local_blocks'] = __( 'Local Blocks', 'blox' );
      		}
  		}
  		return $new_columns;
	}


	/**
     * Add content to the Local Blocks column
     *
     * @since 1.0.0
     *
     * @param string $column_name  The name of the column to be added
     * @param string $post_ID      The current post's ID
     */
	public function local_blocks_column_data( $column_name, $post_ID ) {
		if ( $column_name == 'local_blocks' ) {

			// Get the number of local blocks on the given post
			$count = get_post_meta( $post_ID, '_blox_content_blocks_count', true );

			if ( ! empty( $count ) ) {
				echo $count;
				// Possibly add more than just the number of block in the future...
			} else {
			    echo '<span aria-hidden="true">—</span>';
			}
		}
	}


	/**
     * Tell Wordpress that the Local Blocks column is sortable
     *
     * @since 1.0.0
     *
     * @param array $vars  Array of query variables
     */
	public function local_blocks_columns_sortable( $sortable_columns ) {

		$sortable_columns[ 'local_blocks' ] = 'local_blocks';
		return $sortable_columns;
	}

	/**
     * Tell Wordpress how to sort Local Blocks
     *
     * @since 1.0.0
     *
     * @param array $vars  Array of query variables
     */
	public function local_blocks_columns_orderby( $vars ) {

		if ( isset( $vars['orderby'] ) && 'local_blocks' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
				'meta_key' => '_blox_content_blocks_count',
				'orderby' => 'meta_value_num'
			) );
		}

		return $vars;
	}


	/**
     * Helper function for retrieving the available content types.
     *
     * @since 1.0.0
     *
     * @return array Array of image size data.
     */
    public function get_content_types() {

        $instance = Blox_Common::get_instance();
        return $instance->get_content_types();
    }


    /**
     * Helper method for retrieving all Genesis hooks.
     *
     * @since 1.3.0
     *
     * @return array Array of all Genesis hooks.
     */
    public function get_genesis_hooks() {

        $instance = Blox_Common::get_instance();
        return $instance->get_genesis_hooks();

    }


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Posttype_Admin ) ) {
            self::$instance = new Blox_Posttype_Admin();
        }

        return self::$instance;
    }
}

// Load the posttype admin class.
$blox_posttype_admin = Blox_Posttype_Admin::get_instance();
