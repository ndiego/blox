<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Posttype class.
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Posttype {

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

        // Build the labels for the post type.
		$labels = apply_filters( 'blox_post_type_labels',
			array(
				'name'               => __( 'Global Content Blocks', 'blox' ),
				'singular_name'      => __( 'Global Block', 'blox' ),
				'add_new'            => __( 'Add New', 'blox' ),
				'add_new_item'       => __( 'Add New Global Block', 'blox' ),
				'edit_item'          => __( 'Edit Global Block', 'blox' ),
				'new_item'           => __( 'New Global Block', 'blox' ),
				'view_item'          => __( 'View Global Block', 'blox' ),
				'search_items'       => __( 'Search Global Blocks', 'blox' ),
				'not_found'          => __( 'No global blocks found.', 'blox' ),
				'not_found_in_trash' => __( 'No global blocks found in trash.', 'blox' ),
				'parent_item_colon'  => '',
				'all_items'          => __( 'All Global Blocks', 'blox' ),
				'menu_name'          => $this->base->plugin_name
			)
		);

		// Build out the post type arguments.
		$args = apply_filters( 'blox_post_type_args',
			array(
				'labels'              => $labels,
				'public'              => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_admin_bar'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'menu_position'       => apply_filters( 'blox_post_type_menu_position', 248 ),
				'supports'            => array( 'title' )
			)
		);

		// Register the post type with WordPress.
		register_post_type( 'blox', $args );
		
		// Check if the curent user has permission the manage global blocks, and remove the blocks from the admin if they don't
		add_action( 'admin_head', array( $this, 'global_permissions' ) );
				
		// Load global admin css.
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
    }
	
	
	/**
	 * Global Admin Styles
	 *
	 * Loads the CSS for the Blox admin styles including the Blox admin icon.
	 *
	 * @since 1.0.0
	 */
	public function admin_styles() {
	
	    // Load necessary admin styles
        wp_register_style( 'blox-admin-styles', plugins_url( 'assets/css/admin.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( 'blox-admin-styles' );
        
        // Fire a hook to load styles to the admin
        do_action( 'blox_admin_styles' );
	}
	
    
    /**
     * Removes the global block options if the user does not have the required permissions
     *
     * @since 1.0.0
     */
    public function global_permissions() {
    
		// Get the global block permissions
		$global_permissions = blox_get_option( 'global_permissions', 'manage_options' );

		$global_permissions = ! empty( $global_permissions ) ? $global_permissions : 'manage_options';
		
		if ( ! current_user_can( $global_permissions ) ) {
			remove_menu_page( 'edit.php?post_type=blox' );
		}
    }


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Posttype ) ) {
            self::$instance = new Blox_Posttype();
        }

        return self::$instance;
    }
}

// Load the posttype class.
$blox_posttype = Blox_Posttype::get_instance();
