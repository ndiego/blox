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

        // If shortcodes have been disabled for $scope blocks, return
        if ( blox_get_option( $scope . '_disable_shortcode_positioning', false ) ) return;

        // Get the display test results from the frontend file
        $display_test = Blox_Frontend::get_instance()->display_test;

        // Make sure our block is represented in the test
        if ( ! array_key_exists( $id, $display_test ) || empty( $display_test[$id] ) ) return;

        // Trim the id to remove the scope
        $true_id = substr( $id, strlen( $scope ) + 1 );

        // Get the block data
        if ( $scope == 'global' ) {

            $block  = get_post_meta( $true_id, '_blox_content_blocks_data', true );
            $global = true;

            // If there is no block associated with the id given, return
            if ( empty( $block ) ) return;

        } else if ( $scope == 'local' ) {

            // Local blocks only run on singular pages, so make sure it is a singular page before proceding and also that local blocks are enabled
            if ( ! is_singular() ) return;

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
            if ( ! empty( $local_blocks[$true_id] ) ) {
                $block = $local_blocks[$true_id];
            } else {
                return;
            }

        } else {
            return;
        }

        // If there is no block data associated with the id given, return
        if ( empty( $block ) ) return;

        // If the disable shortcode setting is set, return
        if ( isset( $block['position']['shortcode']['disable'] ) && $block['position']['shortcode']['disable'] ) return;

        // Run our display test (we need this due to the ability of PHP blocks to ignore the location tests)
        $display_test_results = array_count_values( $display_test[$id] );

        if ( array_key_exists( 0, $display_test_results ) ) {

            // Implies visibility or another test, other than location, has failed
            if ( $display_test_results[0] > 1 ) return;

            // If we only have one failure, check if it is a location failure, then see if we are ignoring the location test
            if ( $display_test_results[0] == 1 ) {
                if ( isset( $display_test[$id]['location'] ) && $display_test[$id]['location'] == 0 ) {
                    $ignore_location = isset( $block['position']['shortcode']['ignore_location'] ) ? $block['position']['shortcode']['ignore_location'] : 0;
                    if ( ! $ignore_location ) return;
                }
            } else {
                return;
            }
        }

        // Needed specifically for shortcodes
        // We need to use output buffering here to ensure the slider content is contained in the wrapper div
        ob_start();

        blox_frontend_content( null, array( $id, $block, $global ) );
        $output = ob_get_clean();

        return $output;
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
