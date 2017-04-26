<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Prints the content blocks to the frontend
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Frontend {

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
     * Holds an array of our active block content types
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $active_content_types = array();


    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

    	// Load the base class object.
        $this->base = Blox_Main::get_instance();

    	add_action( 'wp', array( $this, 'display_content_block' ), 1 );
    }



	/**
     * Prints our content blocks on the frontend is a series of tests are passed
     *
     * @since 1.0.0
     */
	public function display_content_block() {

		global $post;

		// Check if global blocks are enabled
		$global_enable = blox_get_option( 'global_enable', false );

		if ( $global_enable ) {

			// Get all of the Global Content Blocks
			$global_blocks = get_posts( array(
				'post_type'        => 'blox',
				'post_status'  	   => 'publish',
				'numberposts'      => -1,     // We want all global blocks
				'suppress_filters' => false   // For WPML compatibility
			) );

			// echo print_r( $global_blocks );

			if ( ! empty( $global_blocks ) ) {
				foreach ( $global_blocks as $block ) {
					$id     = $block->ID;
					$block  = get_post_meta( $id, '_blox_content_blocks_data', true );
					$global = true;

					// The display test begins as true
					$display_test = true;

					// Let all available tests filter the test parameter
					$display_test = apply_filters( 'blox_display_test', $display_test, $id, $block, $global );

					// If the test parameter is still true, proceed with block positioning
					if ( $display_test == true ) {
						$this->position_content_block( $id, $block, $global );
					}
				}
			}
		}

		// Check if local blocks are enabled
		$local_enable = blox_get_option( 'local_enable', false );

		// Local blocks only run on singular pages, so make sure it is a singular page before proceding and also that local blocks are enabled
		if ( $local_enable && is_singular() ) {

			// Get the post type of the current page, and our array of enabled post types
			$post_type     = get_post_type( get_the_ID() );
			$enabled_pages = blox_get_option( 'local_enabled_pages', '' );
			$global 	   = false;

			// Make sure local blocks are allowed on this post type
			if ( ! empty( $enabled_pages ) && in_array( $post_type, $enabled_pages ) ) {

				// Get all of the Local Content Blocks
				$local_blocks = get_post_meta( $post->ID, '_blox_content_blocks_data', true );

				if ( ! empty( $local_blocks ) ) {
					foreach ( $local_blocks as $id => $block ) {

						// The display test begins as true
						$display_test = true;

						// Let all available tests filter the test parameter
						$display_test = apply_filters( 'blox_display_test', $display_test, $id, $block, $global );

						// If the test parameter is still true, proceed with block positioning
						if ( $display_test == true ) {
							$this->position_content_block( $id, $block, $global );
						}
					}
				}
			}
		}

		// Now that our blocks have been added (maybe), check to see if we should run wp_enqueue_scripts
		if ( ! empty( $this->active_content_types ) ) {

			// We have active content blocks so enqueue the needed stypes and scripts
   			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts_styles' ) );

   			// Also load our global custom CSS if there is any...
   			add_action( 'wp_head', array( $this, 'print_global_custom_css' ), 10 );
		}
	}


	/**
	 * Position the block
	 *
     * @since 1.0.0
	 *
	 * @param int $id       The block id, if global, id = $post->ID otherwise it is a random local id
	 * @param array $block  Contains all of our block settings data
	 * @param bool $global  Tells whether our block is global or local
	 */
	public function position_content_block( $id, $block, $global ) {

        // Get block position meta data
		$position_data = $block['position'];

        // Get the position format, default to 'hook' if nothing is set
        $position_format = ! empty( $position_data['position_format'] ) ? esc_attr( $position_data['position_format'] ) : 'hook';

        // Since this block passed all previous tests, it is considered active so pass it's content type to $active_content_types
        array_push( $this->active_content_types, $block['content']['content_type'] );

        if ( $position_format != 'hook' ) {

            // Do something for non-hook position formats. Most formats will take care of this themselves

        } else {

    		// Determine if we are using the default position or a custom position, and then set position and priority
    		if ( empty( $position_data['position_type'] ) || $position_data['position_type'] == 'default' ) {
    			$position = $global ? blox_get_option( 'global_default_position', 'genesis_after_header' ) : blox_get_option( 'local_default_position', 'genesis_after_header' );
    			$priority = $global ? blox_get_option( 'global_default_priority', 15 ) : blox_get_option( 'local_default_priority', 15 );
    		} else {
    			$position = ! empty( $position_data['custom']['position'] ) ? $position_data['custom']['position'] : 'genesis_after_header';
    			$priority = ! empty( $position_data['custom']['priority'] ) ? $position_data['custom']['priority'] : 1;
    		}

    		// If hook defaults are enabled we need to make sure the block is set to a position that is one of the defaults
    		$default_hooks_enabled = blox_get_option( 'default_hooks', false );

    		// Make sure hook defaults are enabled, and if so, run test
        	if ( isset( $default_hooks_enabled['enable'] ) && $default_hooks_enabled['enable'] == 1 ) {

        		$hook_default_test = array();

        		foreach ( $this->get_genesis_hooks() as $sections => $section ) {
    				if ( isset( $section['hooks'][$position] ) ) {
    					$hook_default_test[] = 'true';
    				}
        		}

        		// If the block is set to a postition that is not apart of the hook defaults, bail out
        		if ( ! in_array( 'true', $hook_default_test ) ) {
        			return;
        		}
        	}

    		// Action hook for modifying/adding position settings
    		do_action( 'blox_content_block_position', $id, $block, $global );

    		// Allows you to disable blocks with code if location and visibility settings are not doing it for you
    		$disable = apply_filters( 'blox_disable_content_blocks', false, $position, $id, $block, $global );

    		if ( ! $disable ) {
    			// Load the final "printing" function
    			add_action( $position, array( new Blox_Action_Storage( array( $id, $block, $global ) ), 'blox_frontend_content' ), $priority, 1 );
    		}
        }
	}


	/**
     * Loads styles and scripts for our content blocks
     *
     * @since 1.0.0
     */
    public function frontend_scripts_styles() {

 		// Check to see if default css is globally disabled
        $global_disable_default_css = blox_get_option( 'disable_default_css', '' );

    	if ( empty( $global_disable_default_css ) ) {

        	// Load the Blox default frontend styles.
        	wp_register_style( $this->base->plugin_slug . '-default-styles', plugins_url( 'assets/css/default.css', $this->base->file ), array(), $this->base->version );
        	wp_enqueue_style( $this->base->plugin_slug . '-default-styles' );
		}

		// Fire a hook to load in custom metabox scripts and styles.
        do_action( 'blox_frontend_main_scripts_styles' );

		// Get all active content types, strip out any duplicates
		$active_content_types = array_unique( $this->active_content_types );

		// Now that critical scripts and styles have been enqueued, conditionally load content specific scripts and styles
		foreach ( $active_content_types as $type ) {
			do_action( 'blox_frontend_' . $type . '_scripts_styles' );
		}
    }


    /**
     * Print global custom CSS if there is any...
     *
     * @since 1.0.0
     */
    public function print_global_custom_css() {

		$custom_css = blox_get_option( 'custom_css', '' );

		if ( $custom_css ){
			echo '<style type="text/css">'. $custom_css . '</style>';
		}
	}


	/**
     * Helper method for retrieving all filtered Genesis hooks.
     *
     * @since 1.1.0
     *
     * @return array Array of all filtered Genesis hooks.
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

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Frontend ) ) {
            self::$instance = new Blox_Frontend();
        }

        return self::$instance;
    }
}

// Load the frontend class.
$blox_frontend = Blox_Frontend::get_instance();



/**
 * Helper function that get the content from the content block
 * Needs to remain outside the Blox_Frontend class due to Blox_Action_Storage ---> Possibly find work around...
 *
 * @since 1.0.0
 *
 * @param array $args       These are any args associated to the action hook by default
 * @param array $parameters Additional args that we are passing to the action hook (whole point of using Block_Action_Storage)
 */
function blox_frontend_content( $args, $parameters ) {

	// Reassign the parameters
	$id 	= $parameters[0];
	$block	= $parameters[1];
	$global = $parameters[2];

	// Get the type of block we are working with
	$block_scope = $global ? 'global' : 'local';

	// Get block settings
	$content_data = $block['content'];
	$style_data   = $block['style'];

	// Get access to some of our helper functions
	$instance = Blox_Common::get_instance();

	// Get our style setting variables
    $global_custom_classes      = blox_get_option( 'global_custom_classes', '' );
    $local_custom_id            = blox_get_option( 'local_custom_id', '' );
    $local_custom_classes       = blox_get_option( 'local_custom_classes', '' );
    $global_disable_default_css = blox_get_option( 'disable_default_css', '' );

	// Start with no theme
	$blox_theme = '';

	// Should we include our default styles? If so, add the default theme
	if ( empty( $global_disable_default_css ) ) {
		if ( empty( $style_data['disable_default_css'] ) || ! $style_data['disable_default_css'] ) {
			$blox_theme = 'blox-theme-default';
		}
	}

	// If this block has its own custom css, add that before the block is displayed on the page
	if ( ! empty( $style_data['custom_css'] ) ) {
		echo '<style type="text/css">' . $instance->minify_string( html_entity_decode( $style_data['custom_css'] ) ) . '</style>';
	}

	// Make sure a content type is selected and then print our content block
	if ( ! empty( $content_data['content_type'] ) ) {

        // Raw content block can be printed without the standard markup, so check for that
		if ( $content_data['content_type'] == 'raw' && $content_data['raw']['disable_markup'] == 1 ) {

			// Get the block content
			do_action( 'blox_print_content_' . $content_data['content_type'], $content_data, $id, $block, $global );

		} else {

            $block_id = ! empty( $style_data['custom_id'] ) ? ( $style_data['custom_id'] ) : 'blox_' . $block_scope . '_' . esc_attr( $id );;

            $block_class  = 'blox-content-' . esc_attr( $content_data['content_type'] );
            $block_class .= ' ' . $blox_theme;
            $block_class .= ' ' . 'blox-scope-' . $block_scope;
            $block_class .= ! empty( $style_data['custom_classes'] ) ? ( ' ' . $style_data['custom_classes'] ) : '';
            $block_class .= $block_scope == 'global' ? ( ' ' . $global_custom_classes ) : ( ' ' . $local_custom_classes );

            $enable_wrap = $style_data['enable_wrap'] == 1 ? 'wrap' : '';
            ?>

			<div id="<?php echo $block_id; ?>" class="blox-container <?php echo $block_class; ?>">
				<div class="blox-wrap <?php echo $enable_wrap; ?>">
					<?php do_action( 'blox_print_content_' . $content_data['content_type'], $content_data, $id, $block, $global ); ?>
				</div>
			</div>

			<?php
		}
	}
}
