<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adds the blox shortcode and prints the content
 *
 * @since 	2.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Shortcode_Positioning {

    /**
     * Holds the class object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public static $instance;


    /**
     * Path to the file.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $file = __FILE__;


    /**
     * Holds the base class object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public $base;


	/**
	 * Primary class constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

        // Load the base class object.
        $this->base = Blox_Main::get_instance();

        // Add the blox shortcode
        add_shortcode( 'blox', array( $this, 'display_shortcode' ) );
    }


    /**
	 * Display the shortcode content if tests are passed
	 *
	 * @since 2.0.0
     *
     * @param array $atts All of the accepted shortcode atts
     *
     * @return string     The block output
	 */
    public function display_shortcode( $atts ) {

        // The accepted shortcode atts
        $atts = shortcode_atts( array(
            'id'    => '',
            'title' => '', // The Title does not currently do anything, just helps the user remember what the shortcode is
        ), $atts );

        // Check if there is an id specified
        if ( ! empty( $atts['id'] ) ) {
            $id = esc_attr( $atts['id'] );
        } else {
            return;
        }

        // Define the scope. If no scope, not a valid id, so return
        if ( strpos( $id, 'global' ) !== false ) {
            $scope = 'global';
        } else if ( strpos( $id, 'local' ) !== false ) {
            $scope = 'local';
        } else {
            return;
        }

        // Trim the id to remove the scope
        $id = substr( $id, strlen( $scope ) + 1 );

        // If $scope blocks have been globally disabled, return
        if ( ! blox_get_option( $scope . '_enable', false ) ) {
            return;
        }

        // If shortcodes have been disabled for $scope blocks, return
        if ( blox_get_option( $scope . '_disable_shortcode_positioning', false ) ) {
            return;
        }

        // Get the block data
        if ( $scope == 'global' ) {

            $block  = get_post_meta( $id, '_blox_content_blocks_data', true );
            $global = true;

            // If there is no block associated with the id given, return
            if ( empty( $block ) ) {
                return;
            }

        } else if ( $scope == 'local' ) {

            // Local blocks only run on singular pages, so make sure it is a singular page before proceding and also that local blocks are enabled
            if ( ! is_singular() ) {
                return;
            }

            // Get the post type of the current page, and our array of enabled post types
            $post_type     = get_post_type( get_the_ID() );
            $enabled_pages = blox_get_option( 'local_enabled_pages', '' );
            $global 	   = false;

            // Make sure local blocks are allowed on this post type
            if ( empty( $enabled_pages ) || ! in_array( $post_type, $enabled_pages ) ) {
                return;
            }

            // Get all of the Local Content Blocks
            $local_blocks = get_post_meta( get_the_ID(), '_blox_content_blocks_data', true );

            // Get the block data, and if there is no local block with that id, return
            if ( ! empty( $local_blocks[$id] ) ) {
                $block = $local_blocks[$id];
            } else {
                return;
            }

        } else {
            return;
        }

        // If the disable shortcode setting is set, return
        if ( isset( $block['position']['shortcode']['disable'] ) && $block['position']['shortcode']['disable'] ) {
            return;
        }

        // The display test begins as true
        $display_test = true;

        // Let all available tests filter the test parameter
        $display_test = apply_filters( 'blox_display_test', $display_test, $id, $block, $global, 'shortcode' );

        // If the test parameter is still true, proceed with block positioning
        if ( $display_test == true ) {

            // We need to use output buffering here to ensure the slider content is contained in the wrapper div
            ob_start();

            // @TODO remove
            //echo print_r($block);

            blox_frontend_content( null, array( $id, $block, $global ) );
            $output = ob_get_clean();

            return $output;
        }
    }


	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 2.0.0
	 *
	 * @return object The class object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Shortcode_Positioning ) ) {
			self::$instance = new Blox_Shortcode_Positioning();
		}

		return self::$instance;
	}
}

// Load the main class.
$blox_shortcode = Blox_Shortcode_Positioning::get_instance();
