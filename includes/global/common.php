<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Common class.
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Common {

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
    }


    /**
     * Helper function for retrieving image sizes.
     *
     * @since 1.0.0
     *
     * @global array $_wp_additional_image_sizes Array of registered image sizes.
     * @return array                             Array of slider size data.
     */
    public function get_image_sizes() {

        $sizes = array(
            array(
                'value'  => 'full',
                'name'   => __( 'Default (Original Image Size)', 'blox' ),
                'width'  => 0,
                'height' => 0
            )
        );

        global $_wp_additional_image_sizes;

        $wp_sizes = get_intermediate_image_sizes();

        foreach ( (array) $wp_sizes as $size ) {
			if ( isset( $_wp_additional_image_sizes[$size] ) ) {
				$width 	= absint( $_wp_additional_image_sizes[$size]['width'] );
				$height = absint( $_wp_additional_image_sizes[$size]['height'] );
			} else {
				$width	= absint( get_option( $size . '_size_w' ) );
				$height	= absint( get_option( $size . '_size_h' ) );
			}

			if ( ! $width && ! $height ) {
				$sizes[] = array(
				    'value'  => $size,
				    'name'   => ucwords( str_replace( array( '-', '_' ), ' ', $size ) ),
				    'width'  => 0,
				    'height' => 0
				);
			} else {
			    $sizes[] = array(
				    'value'  => $size,
				    'name'   => ucwords( str_replace( array( '-', '_' ), ' ', $size ) ) . ' (' . $width . ' &#215; ' . $height . ')',
				    'width'  => $width,
				    'height' => $height
				);
            }
		}

        $sizes[] = array(
            'value'  => 'custom',
            'name'   => __( 'Custom', 'blox' ),
            'width'  => 0,
            'height' => 0
        );

        return apply_filters( 'blox_image_sizes', $sizes );
    }


    /**
     * Helper function for retrieving all available Genesis hooks.
     *
     * @since 1.0.0
     *
     * @return array Array of all Genesis hooks.
     */
    public function get_genesis_hooks() {

    	// All current Genesis Hooks broken into their respective categories - http://my.studiopress.com/docs/hook-reference/
    	$genesis_hooks = array(
            'doc_head' => array(
				'name'  => __( 'Document Head Action Hooks', 'blox' ),
				'hooks' => array(
					'genesis_title' => array( 'name'  => 'genesis_title', 'title' => __( 'This hook executes between tags and outputs the doctitle. You can find all doctitle related code in /lib/structure/header.php.', 'blox' ) ),
					'genesis_meta' 	=> array( 'name'  => 'genesis_meta', 'title' => __( 'This hook executes in the <head></head> section of the document source. By default, things like META descriptions and keywords are output using this hook, along with the default stylesheet and the reference to the favicon. This hook is very similar to wp_head', 'blox' ) )
				)
			),
			'structural' => array(
				'name'  => __( 'Structural Action Hooks', 'blox' ),
				'hooks' => array(
					'genesis_before' 						=> array( 'name'  => 'genesis_before', 'title' => __( 'This hook executes immediately after the opening tag in the document source.', 'blox' ) ),
					'genesis_before_header' 				=> array( 'name'  => 'genesis_before_header', 'title' => __( 'This hook executes immediately before the header (outside the #header div).', 'blox' ) ),
					'genesis_header' 						=> array( 'name'  => 'genesis_header', 'title' => __( 'By default, this hook outputs the header code, including the title, description, and widget area (if necessary).', 'blox' ) ),
					'genesis_header_right' 					=> array( 'name'  => 'genesis_header_right', 'title' => __( 'This hook executes immediately before the Header Right widget area inside div.widget-area.', 'blox' ) ),
					'genesis_after_header' 					=> array( 'name'  => 'genesis_after_header', 'title' => __( 'This hook executes immediately after the header (outside the #header div).', 'blox' ) ),
					'genesis_site_title' 					=> array( 'name'  => 'genesis_site_title', 'title' => __( 'This hook executes immediately after the opening tag in the document source.', 'blox' ) ),
					'genesis_site_description' 				=> array( 'name'  => 'genesis_site_description', 'title' => __( 'This hook executes immediately before the closing tag in the document source.', 'blox' ) ),
					'genesis_before_content_sidebar_wrap' 	=> array( 'name'  => 'genesis_before_content_sidebar_wrap', 'title' => __( 'This hook executes immediately before the div block that wraps the content and the primary sidebar (outside the #content-sidebar-wrap div).', 'blox' ) ),
					'genesis_after_content_sidebar_wrap' 	=> array( 'name'  => 'genesis_after_content_sidebar_wrap', 'title' => __( 'This hook executes immediately after the div block that wraps the content and the primary sidebar (outside the #content-sidebar-wrap div).', 'blox' ) ),
					'genesis_before_content' 				=> array( 'name'  => 'genesis_before_content', 'title' => __( 'This hook executes immediately before the content column (outside the #content div).', 'blox' ) ),
					'genesis_after_content' 				=> array( 'name'  => 'genesis_after_content', 'title' => __( 'This hook executes immediately after the content column (outside the #content div).', 'blox' ) ),
					'genesis_before_sidebar' 				=> array( 'name'  => 'genesis_before_sidebar', 'title' => __( 'This hook executes immediately before the primary sidebar column.', 'blox' ) ),
					'genesis_sidebar' 						=> array( 'name'  => 'genesis_sidebar', 'title' => __( 'This hook outputs the content of the primary sidebar, including the widget area output.', 'blox' ) ),
					'genesis_after_sidebar' 				=> array( 'name'  => 'genesis_after_sidebar', 'title' => __( 'This hook executes immediately after the primary sidebar column.', 'blox' ) ),
					'genesis_before_sidebar_widget_area' 	=> array( 'name'  => 'genesis_before_sidebar_widget_area', 'title' => __( 'This hook executes immediately before the primary sidebar widget area (inside the #sidebar div).', 'blox' ) ),
					'genesis_after_sidebar_widget_area' 	=> array( 'name'  => 'genesis_after_sidebar_widget_area', 'title' => __( 'This hook executes immediately after the primary sidebar widget area (inside the #sidebar div).', 'blox' ) ),
					'genesis_before_sidebar_alt' 			=> array( 'name'  => 'genesis_before_sidebar_alt', 'title' => __( 'This hook executes immediately before the alternate sidebar column.', 'blox' ) ),
					'genesis_sidebar_alt' 					=> array( 'name'  => 'genesis_sidebar_alt', 'title' => __( 'This hook outputs the content of the secondary sidebar, including the widget area output.', 'blox' ) ),
					'genesis_after_sidebar_alt' 			=> array( 'name'  => 'genesis_after_sidebar_alt', 'title' => __( 'This hook executes immediately after the alternate sidebar column.', 'blox' ) ),
					'genesis_before_sidebar_alt_widget_area' => array( 'name'  => 'genesis_before_sidebar_alt_widget_area', 'title' => __( 'This hook executes immediately before the alternate sidebar widget area (inside the #sidebar-alt div).', 'blox' ) ),
					'genesis_after_sidebar_alt_widget_area' => array( 'name'  => 'genesis_after_sidebar_alt_widget_area', 'title' => __( 'This hook executes immediately after the alternate sidebar widget area (inside the #sidebar-alt div).', 'blox' ) ),
					'genesis_before_footer' 				=> array( 'name'  => 'genesis_before_footer', 'title' => __( 'This hook executes immediately before the footer, outside the #footer div.', 'blox' ) ),
					'genesis_footer' 						=> array( 'name'  => 'genesis_footer', 'title' => __( 'This hook, by default, outputs the content of the footer, including the #footer div wrapper.', 'blox' ) ),
					'genesis_after_footer' 					=> array( 'name'  => 'genesis_after_footer', 'title' => __( 'This hook executes immediately after the footer, outside the #footer div.', 'blox' ) ),
                    'genesis_after' 						=> array( 'name'  => 'genesis_after', 'title' => __( 'This hook executes immediately before the closing tag in the document source.', 'blox' ) )
                )
			),
			'loop' => array(
				'name'  => __( 'Loop Action Hooks', 'blox' ),
				'hooks' => array(
					'genesis_before_loop' 			=> array( 'name'  => 'genesis_before_loop', 'title' => __( 'This hook executes immediately before all loop blocks. Therefore, this hook falls outside the loop, and cannot execute functions that require loop template tags or variables.', 'blox' ) ),
					'genesis_loop' 					=> array( 'name'  => 'genesis_loop', 'title' => __( 'This hook outputs the actual loop. See lib/structure/loop.php and lib/structure/post.php for more details.', 'blox' ) ),
					'genesis_after_loop' 			=> array( 'name'  => 'genesis_after_loop', 'title' => __( 'This hook executes immediately after all loop blocks. Therefore, this hook falls outside the loop, and cannot execute functions that require loop template tags or variables.', 'blox' ) ),
					'genesis_after_endwhile' 		=> array( 'name'  => 'genesis_after_endwhile', 'title' => __( 'This hook executes after the endwhile; statement in all loop blocks.', 'blox' ) ),
					'genesis_loop_else' 			=> array( 'name'  => 'genesis_loop_else', 'title' => __( 'This hook executes after the else : statement in all loop blocks.', 'blox' ) ),
					// HTML5 Hooks
					'genesis_before_entry' 			=> array( 'name'  => 'genesis_before_entry', 'title' => __( 'This hook executes before each entry in all loop blocks (outside the post_class() container).', 'blox' ) ),
					'genesis_entry_header' 			=> array( 'name'  => 'genesis_entry_header', 'title' => __( 'This hook executes before the entry content and generates the entry header content in all loop blocks.', 'blox' ) ),
					'genesis_before_entry_content' 	=> array( 'name'  => 'genesis_before_entry_content', 'title' => __( 'This hook executes before the .entry-content container in all loop blocks.', 'blox' ) ),
					'genesis_entry_content' 		=> array( 'name'  => 'genesis_entry_content', 'title' => __( 'This hook executes within the .entry-content container in all loop blocks.', 'blox' ) ),
					'genesis_after_entry_content' 	=> array( 'name'  => 'genesis_after_entry_content', 'title' => __( 'This hook executes after the .entry-content container in all loop blocks.', 'blox' ) ),
					'genesis_entry_footer' 			=> array( 'name'  => 'genesis_entry_footer', 'title' => __( 'This hook executes after the entry content and generates the entry footer content in all loop blocks.', 'blox' ) ),
					'genesis_after_entry' 			=> array( 'name'  => 'genesis_after_entry', 'title' => __( 'This hook executes after each entry in all loop blocks (outside the post_class() container).', 'blox' ) ),
				)
			),
			'comment' => array(
				'name'  => __( 'Comment Action Hooks', 'blox' ),
				'hooks' => array(
					'genesis_before_comments' 		=> array( 'name'  => 'genesis_before_comments', 'title' => __( 'This hook executes immediately before the comments block (outside the #comments div).', 'blox' ) ),
					'genesis_comments' 				=> array( 'name'  => 'genesis_comments', 'title' => __( 'This hook outputs the entire comments block, including the section title. It also executes the genesis_list_comments hook, which outputs the comment list.', 'blox' ) ),
					'genesis_list_comments' 		=> array( 'name'  => 'genesis_list_comments', 'title' => __( 'This hook executes inside the comments block, inside the .comment-list OL. By default, it outputs a list of comments associated with a post via the genesis_default_list_comments() function.', 'blox' ) ),
					'genesis_after_comments' 		=> array( 'name'  => 'genesis_after_comments', 'title' => __( 'This hook executes immediately after the comments block (outside the #comments div).', 'blox' ) ),
					'genesis_before_pings' 			=> array( 'name'  => 'genesis_before_pings', 'title' => __( 'This hook executes immediately before the pings block (outside the #pings div).', 'blox' ) ),
					'genesis_pings' 				=> array( 'name'  => 'genesis_pings', 'title' => __( 'This hook outputs the entire pings block, including the section title. It also executes the genesis_list_pings hook, which outputs the ping list.', 'blox' ) ),
					'genesis_list_pings' 			=> array( 'name'  => 'genesis_list_pings', 'title' => __( 'This hook executes inside the pings block, inside the .ping-list OL. By default, it outputs a list of pings associated with a post via the genesis_default_list_pings() function.', 'blox' ) ),
					'genesis_after_pings' 			=> array( 'name'  => 'genesis_after_pings', 'title' => __( 'This hook executes immediately after the pings block (outside the #pings div).', 'blox' ) ),
					'genesis_before_comment' 		=> array( 'name'  => 'genesis_before_comment', 'title' => __( 'This hook executes before the output of each individual comment (author, meta, comment text).', 'blox' ) ),
					'genesis_after_comment' 		=> array( 'name'  => 'genesis_after_comment', 'title' => __( 'This hook executes after the output of each individual comment (author, meta, comment text).', 'blox' ) ),
					'genesis_before_comment_form' 	=> array( 'name'  => 'genesis_before_comment_form', 'title' => __( 'This hook executes immediately before the comment form, outside the #respond div.', 'blox' ) ),
					'genesis_comment_form' 			=> array( 'name'  => 'genesis_comment_form', 'title' => __( 'This hook outputs the actual comment form, including the #respond div wrapper.', 'blox' ) ),
					'genesis_after_comment_form' 	=> array( 'name'  => 'genesis_after_comment_form', 'title' => __( 'This hook executes immediately after the comment form, outside the #respond div.', 'blox' ) ),
				)
			),
			
			// Also include the main Wordpress core hooks that are included in all Genesis themes built by StudioPress and most other premium Genesis themes
            'core' => array(
                'name'  => __( 'Wordpress Core Hooks', 'blox' ),
                'hooks' => array(
                    'wp_head' 	=> array( 'name' => 'wp_head', 'title' => __( 'This hook executes within the <head></head> section of the document source.', 'blox' ) ),
                    'wp_footer' => array( 'name' => 'wp_footer', 'title' => __( 'This hook executes near the </body> tag of the document source.', 'blox' ) )
                )
            ),
		);

    	return apply_filters( 'blox_genesis_hooks', $genesis_hooks );
    }


	/**
     * Helper function for retrieving all available content types.
     *
     * @since 1.0.0
     *
     * @return array Array of all available content types.
     */
    public function get_content_types() {

    	$content_types = array();

    	return apply_filters( 'blox_content_type', $content_types );
    }
        
    
    /**
     * Helper function for retrieving all active addons.
     *
     * @since 1.0.0
     *
     * @return array Array of all active addons.
     */
    public function get_active_addons() {

    	$addons = array();

    	return apply_filters( 'blox_active_addons', $addons );
    }


    /**
     * Helper method to minify a string of data. Courtesy of Thomas Griffin (Solilquy)
     *
     * @since 1.0.0
     *
     * @param string $string  String of data to minify.
     * @return string $string Minified string of data.
     */
    public function minify_string( $string ) {

        $clean = preg_replace( '/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', '', $string );
        $clean = str_replace( array( "\r\n", "\r", "\t", "\n", '  ', '    ', '     ' ), '', $clean );
        return apply_filters( 'blox_minified_string', $clean, $string );
    }


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Common ) ) {
            self::$instance = new Blox_Common();
        }

        return self::$instance;

    }

}

// Load the common class.
$blox_common = Blox_Common::get_instance();
