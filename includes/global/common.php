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

        add_filter( 'blox_genesis_hooks', array( $this, 'push_hook_defaults' ), 10 );
    }


    /**
     * Helper function for retrieving image sizes.
     *
     * @since 1.0.0
     *
     * @global array $_wp_additional_image_sizes Array of registered image sizes.
     * @return array $sizes                      Array of slider size data.
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
     * Helper function for retrieving all available Core WordPress hooks.
     *
     * @since 2.0.0
     *
     * @return array $hooks Array of all Core WordPress hooks.
     */
    public function get_wordpress_hooks_unfiltered() {

        // All current standardized WordPress Hooks broken into their respective categories
        $hooks = array(
            'core' => array(
                'name'  => __( 'Wordpress Core Hooks', 'blox' ),
                'hooks' => array(
                    'wp_head' 	=> array( 'disable' => '', 'name' => 'wp_head', 'title' => __( 'This hook executes within the <head></head> section of the document source.', 'blox' ) ),
                    'wp_footer' => array( 'disable' => '', 'name' => 'wp_footer', 'title' => __( 'This hook executes near the </body> tag of the document source.', 'blox' ) )
                )
            ),
        );

        return $hooks;
    }


    /**
     * Helper function for retrieving all available Genesis hooks.
     *
     * @since 1.0.0
     *
     * @return array $hooks Array of all Genesis hooks.
     */
    public function get_genesis_hooks_unfiltered() {

    	// All current Genesis Hooks broken into their respective categories - http://my.studiopress.com/docs/hook-reference/
    	$hooks = array(
            'doc_head' => array(
				'name'  => __( 'Document Head Action Hooks', 'blox' ),
				'hooks' => array(
					'genesis_title' => array( 'disable' => '', 'name'  => 'genesis_title', 'title' => __( 'This hook executes between tags and outputs the doctitle. You can find all doctitle related code in /lib/structure/header.php.', 'blox' ) ),
					'genesis_meta' 	=> array( 'disable' => '', 'name'  => 'genesis_meta', 'title' => __( 'This hook executes in the <head></head> section of the document source. By default, things like META descriptions and keywords are output using this hook, along with the default stylesheet and the reference to the favicon. This hook is very similar to wp_head', 'blox' ) )
				)
			),
			'structural' => array(
				'name'  => __( 'Structural Action Hooks', 'blox' ),
				'hooks' => array(
					'genesis_before' 						 => array( 'disable' => '', 'name'  => 'genesis_before', 'title' => __( 'This hook executes immediately after the opening tag in the document source.', 'blox' ) ),
					'genesis_before_header' 				 => array( 'disable' => '', 'name'  => 'genesis_before_header', 'title' => __( 'This hook executes immediately before the header (outside the #header div).', 'blox' ) ),
					'genesis_header' 						 => array( 'disable' => '', 'name'  => 'genesis_header', 'title' => __( 'By default, this hook outputs the header code, including the title, description, and widget area (if necessary).', 'blox' ) ),
					'genesis_header_right' 					 => array( 'disable' => '', 'name'  => 'genesis_header_right', 'title' => __( 'This hook executes immediately before the Header Right widget area inside div.widget-area.', 'blox' ) ),
					'genesis_after_header' 					 => array( 'disable' => '', 'name'  => 'genesis_after_header', 'title' => __( 'This hook executes immediately after the header (outside the #header div).', 'blox' ) ),
					'genesis_site_title' 					 => array( 'disable' => '', 'name'  => 'genesis_site_title', 'title' => __( 'This hook executes immediately after the opening tag in the document source.', 'blox' ) ),
					'genesis_site_description' 			 	 => array( 'disable' => '', 'name'  => 'genesis_site_description', 'title' => __( 'This hook executes immediately before the closing tag in the document source.', 'blox' ) ),
					'genesis_before_content_sidebar_wrap' 	 => array( 'disable' => '', 'name'  => 'genesis_before_content_sidebar_wrap', 'title' => __( 'This hook executes immediately before the div block that wraps the content and the primary sidebar (outside the #content-sidebar-wrap div).', 'blox' ) ),
					'genesis_after_content_sidebar_wrap' 	 => array( 'disable' => '', 'name'  => 'genesis_after_content_sidebar_wrap', 'title' => __( 'This hook executes immediately after the div block that wraps the content and the primary sidebar (outside the #content-sidebar-wrap div).', 'blox' ) ),
					'genesis_before_content' 				 => array( 'disable' => '', 'name'  => 'genesis_before_content', 'title' => __( 'This hook executes immediately before the content column (outside the #content div).', 'blox' ) ),
					'genesis_after_content' 			     => array( 'disable' => '', 'name'  => 'genesis_after_content', 'title' => __( 'This hook executes immediately after the content column (outside the #content div).', 'blox' ) ),
					'genesis_before_sidebar' 				 => array( 'disable' => '', 'name'  => 'genesis_before_sidebar', 'title' => __( 'This hook executes immediately before the primary sidebar column.', 'blox' ) ),
					'genesis_sidebar' 						 => array( 'disable' => '', 'name'  => 'genesis_sidebar', 'title' => __( 'This hook outputs the content of the primary sidebar, including the widget area output.', 'blox' ) ),
					'genesis_after_sidebar' 				 => array( 'disable' => '', 'name'  => 'genesis_after_sidebar', 'title' => __( 'This hook executes immediately after the primary sidebar column.', 'blox' ) ),
					'genesis_before_sidebar_widget_area' 	 => array( 'disable' => '', 'name'  => 'genesis_before_sidebar_widget_area', 'title' => __( 'This hook executes immediately before the primary sidebar widget area (inside the #sidebar div).', 'blox' ) ),
					'genesis_after_sidebar_widget_area' 	 => array( 'disable' => '', 'name'  => 'genesis_after_sidebar_widget_area', 'title' => __( 'This hook executes immediately after the primary sidebar widget area (inside the #sidebar div).', 'blox' ) ),
					'genesis_before_sidebar_alt' 			 => array( 'disable' => '', 'name'  => 'genesis_before_sidebar_alt', 'title' => __( 'This hook executes immediately before the alternate sidebar column.', 'blox' ) ),
					'genesis_sidebar_alt' 					 => array( 'disable' => '', 'name'  => 'genesis_sidebar_alt', 'title' => __( 'This hook outputs the content of the secondary sidebar, including the widget area output.', 'blox' ) ),
					'genesis_after_sidebar_alt' 			 => array( 'disable' => '', 'name'  => 'genesis_after_sidebar_alt', 'title' => __( 'This hook executes immediately after the alternate sidebar column.', 'blox' ) ),
					'genesis_before_sidebar_alt_widget_area' => array( 'disable' => '', 'name'  => 'genesis_before_sidebar_alt_widget_area', 'title' => __( 'This hook executes immediately before the alternate sidebar widget area (inside the #sidebar-alt div).', 'blox' ) ),
					'genesis_after_sidebar_alt_widget_area'  => array( 'disable' => '', 'name'  => 'genesis_after_sidebar_alt_widget_area', 'title' => __( 'This hook executes immediately after the alternate sidebar widget area (inside the #sidebar-alt div).', 'blox' ) ),
					'genesis_before_footer' 				 => array( 'disable' => '', 'name'  => 'genesis_before_footer', 'title' => __( 'This hook executes immediately before the footer, outside the #footer div.', 'blox' ) ),
					'genesis_footer' 						 => array( 'disable' => '', 'name'  => 'genesis_footer', 'title' => __( 'This hook, by default, outputs the content of the footer, including the #footer div wrapper.', 'blox' ) ),
					'genesis_after_footer' 					 => array( 'disable' => '', 'name'  => 'genesis_after_footer', 'title' => __( 'This hook executes immediately after the footer, outside the #footer div.', 'blox' ) ),
                    'genesis_after' 						 => array( 'disable' => '', 'name'  => 'genesis_after', 'title' => __( 'This hook executes immediately before the closing tag in the document source.', 'blox' ) )
                )
			),
			'loop' => array(
				'name'  => __( 'Loop Action Hooks', 'blox' ),
				'hooks' => array(
					'genesis_before_loop' 			=> array( 'disable' => '', 'name'  => 'genesis_before_loop', 'title' => __( 'This hook executes immediately before all loop blocks. Therefore, this hook falls outside the loop, and cannot execute functions that require loop template tags or variables.', 'blox' ) ),
					'genesis_loop' 					=> array( 'disable' => '', 'name'  => 'genesis_loop', 'title' => __( 'This hook outputs the actual loop. See lib/structure/loop.php and lib/structure/post.php for more details.', 'blox' ) ),
					'genesis_after_loop' 			=> array( 'disable' => '', 'name'  => 'genesis_after_loop', 'title' => __( 'This hook executes immediately after all loop blocks. Therefore, this hook falls outside the loop, and cannot execute functions that require loop template tags or variables.', 'blox' ) ),
					'genesis_after_endwhile' 		=> array( 'disable' => '', 'name'  => 'genesis_after_endwhile', 'title' => __( 'This hook executes after the endwhile; statement in all loop blocks.', 'blox' ) ),
					'genesis_loop_else' 			=> array( 'disable' => '', 'name'  => 'genesis_loop_else', 'title' => __( 'This hook executes after the else : statement in all loop blocks.', 'blox' ) ),
					// HTML5 Hooks
					'genesis_before_entry' 			=> array( 'disable' => '', 'name'  => 'genesis_before_entry', 'title' => __( 'This hook executes before each entry in all loop blocks (outside the post_class() container).', 'blox' ) ),
					'genesis_entry_header' 			=> array( 'disable' => '', 'name'  => 'genesis_entry_header', 'title' => __( 'This hook executes before the entry content and generates the entry header content in all loop blocks.', 'blox' ) ),
					'genesis_before_entry_content' 	=> array( 'disable' => '', 'name'  => 'genesis_before_entry_content', 'title' => __( 'This hook executes before the .entry-content container in all loop blocks.', 'blox' ) ),
					'genesis_entry_content' 		=> array( 'disable' => '', 'name'  => 'genesis_entry_content', 'title' => __( 'This hook executes within the .entry-content container in all loop blocks.', 'blox' ) ),
					'genesis_after_entry_content' 	=> array( 'disable' => '', 'name'  => 'genesis_after_entry_content', 'title' => __( 'This hook executes after the .entry-content container in all loop blocks.', 'blox' ) ),
					'genesis_entry_footer' 			=> array( 'disable' => '', 'name'  => 'genesis_entry_footer', 'title' => __( 'This hook executes after the entry content and generates the entry footer content in all loop blocks.', 'blox' ) ),
					'genesis_after_entry' 			=> array( 'disable' => '', 'name'  => 'genesis_after_entry', 'title' => __( 'This hook executes after each entry in all loop blocks (outside the post_class() container).', 'blox' ) ),
				)
			),
			'comment' => array(
				'name'  => __( 'Comment Action Hooks', 'blox' ),
				'hooks' => array(
					'genesis_before_comments' 		=> array( 'disable' => '', 'name'  => 'genesis_before_comments', 'title' => __( 'This hook executes immediately before the comments block (outside the #comments div).', 'blox' ) ),
					'genesis_comments' 				=> array( 'disable' => '', 'name'  => 'genesis_comments', 'title' => __( 'This hook outputs the entire comments block, including the section title. It also executes the genesis_list_comments hook, which outputs the comment list.', 'blox' ) ),
					'genesis_list_comments' 		=> array( 'disable' => '', 'name'  => 'genesis_list_comments', 'title' => __( 'This hook executes inside the comments block, inside the .comment-list OL. By default, it outputs a list of comments associated with a post via the genesis_default_list_comments() function.', 'blox' ) ),
					'genesis_after_comments' 		=> array( 'disable' => '', 'name'  => 'genesis_after_comments', 'title' => __( 'This hook executes immediately after the comments block (outside the #comments div).', 'blox' ) ),
					'genesis_before_pings' 			=> array( 'disable' => '', 'name'  => 'genesis_before_pings', 'title' => __( 'This hook executes immediately before the pings block (outside the #pings div).', 'blox' ) ),
					'genesis_pings' 				=> array( 'disable' => '', 'name'  => 'genesis_pings', 'title' => __( 'This hook outputs the entire pings block, including the section title. It also executes the genesis_list_pings hook, which outputs the ping list.', 'blox' ) ),
					'genesis_list_pings' 			=> array( 'disable' => '', 'name'  => 'genesis_list_pings', 'title' => __( 'This hook executes inside the pings block, inside the .ping-list OL. By default, it outputs a list of pings associated with a post via the genesis_default_list_pings() function.', 'blox' ) ),
					'genesis_after_pings' 			=> array( 'disable' => '', 'name'  => 'genesis_after_pings', 'title' => __( 'This hook executes immediately after the pings block (outside the #pings div).', 'blox' ) ),
					'genesis_before_comment' 		=> array( 'disable' => '', 'name'  => 'genesis_before_comment', 'title' => __( 'This hook executes before the output of each individual comment (author, meta, comment text).', 'blox' ) ),
					'genesis_after_comment' 		=> array( 'disable' => '', 'name'  => 'genesis_after_comment', 'title' => __( 'This hook executes after the output of each individual comment (author, meta, comment text).', 'blox' ) ),
					'genesis_before_comment_form' 	=> array( 'disable' => '', 'name'  => 'genesis_before_comment_form', 'title' => __( 'This hook executes immediately before the comment form, outside the #respond div.', 'blox' ) ),
					'genesis_comment_form' 			=> array( 'disable' => '', 'name'  => 'genesis_comment_form', 'title' => __( 'This hook outputs the actual comment form, including the #respond div wrapper.', 'blox' ) ),
					'genesis_after_comment_form' 	=> array( 'disable' => '', 'name'  => 'genesis_after_comment_form', 'title' => __( 'This hook executes immediately after the comment form, outside the #respond div.', 'blox' ) ),
				)
			)
		);

    	return $hooks;
    }


    /**
     * Helper function for retrieving all available WooCommerce hooks.
     *
     * @since 2.0.0
     *
     * @return array $hooks Array of all WooCommerce hooks.
     */
    public function get_woocommerce_hooks_unfiltered() {

        // All current WooCommerce Hooks broken into their respective categories
        $hooks = array(
            'single-product' => array(
                'name'  => __( 'Single Product Page', 'blox' ),
                'hooks' => array(
                    'woocommerce_before_single_product' 	    => array( 'disable' => '', 'name' => 'woocommerce_before_single_product', 'title' => __( 'This hook executes before all of the single product information.', 'blox' ) ),
                    'woocommerce_before_single_product_summary' => array( 'disable' => '', 'name' => 'woocommerce_before_single_product_summary', 'title' => __( 'This hook executes before the summary portion of the single product page.', 'blox' ) ),
                    'woocommerce_single_product_summary' 	    => array( 'disable' => '', 'name' => 'woocommerce_single_product_summary', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_before_add_to_cart_form' 	    => array( 'disable' => '', 'name' => 'woocommerce_before_add_to_cart_form', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_before_variations_form' 	    => array( 'disable' => '', 'name' => 'woocommerce_before_variations_form', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_before_add_to_cart_button' 	=> array( 'disable' => '', 'name' => 'woocommerce_before_add_to_cart_button', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_before_single_variation' 	    => array( 'disable' => '', 'name' => 'woocommerce_before_single_variation', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_single_variation' 	            => array( 'disable' => '', 'name' => 'woocommerce_single_variation', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_after_single_variation' 	    => array( 'disable' => '', 'name' => 'woocommerce_after_single_variation', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_after_add_to_cart_button' 	    => array( 'disable' => '', 'name' => 'woocommerce_after_add_to_cart_button', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_after_variations_form' 	    => array( 'disable' => '', 'name' => 'woocommerce_after_variations_form', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_after_add_to_cart_form' 	    => array( 'disable' => '', 'name' => 'woocommerce_after_add_to_cart_form', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_product_meta_start' 	        => array( 'disable' => '', 'name' => 'woocommerce_product_meta_start', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_product_meta_end' 	            => array( 'disable' => '', 'name' => 'woocommerce_product_meta_end', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_share' 	                    => array( 'disable' => '', 'name' => 'woocommerce_share', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_product_thumbnails' 	        => array( 'disable' => '', 'name' => 'woocommerce_product_thumbnails', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_after_single_product_summary'  => array( 'disable' => '', 'name' => 'woocommerce_after_single_product_summary', 'title' => __( 'This hook executes after the summary portion of the single product page.', 'blox' ) ),
                    'woocommerce_after_single_product' 	        => array( 'disable' => '', 'name' => 'woocommerce_after_single_product', 'title' => __( 'This hook executes after all of the single product information.', 'blox' ) ),
                )
            ),
            'checkout' => array(
                'name'  => __( 'Checkout Page', 'blox' ),
                'hooks' => array(
                    'woocommerce_before_checkout_form' 	            => array( 'disable' => '', 'name' => 'woocommerce_before_checkout_form', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_checkout_before_customer_details'  => array( 'disable' => '', 'name' => 'woocommerce_checkout_before_customer_details', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_before_checkout_billing_form' 	    => array( 'disable' => '', 'name' => 'woocommerce_before_checkout_billing_form', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_after_checkout_billing_form' 	    => array( 'disable' => '', 'name' => 'woocommerce_after_checkout_billing_form', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_before_checkout_shipping_form'     => array( 'disable' => '', 'name' => 'woocommerce_before_checkout_shipping_form', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_after_checkout_shipping_form' 	    => array( 'disable' => '', 'name' => 'woocommerce_after_checkout_shipping_form', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_before_order_notes' 	            => array( 'disable' => '', 'name' => 'woocommerce_before_order_notes', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_after_order_notes' 	            => array( 'disable' => '', 'name' => 'woocommerce_after_order_notes', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_checkout_after_customer_details'   => array( 'disable' => '', 'name' => 'woocommerce_checkout_after_customer_details', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_checkout_before_order_review' 	    => array( 'disable' => '', 'name' => 'woocommerce_checkout_before_order_review', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_review_order_before_cart_contents' => array( 'disable' => '', 'name' => 'woocommerce_review_order_before_cart_contents', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_review_order_after_cart_contents' 	=> array( 'disable' => '', 'name' => 'woocommerce_review_order_after_cart_contents', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_review_order_before_shipping' 	    => array( 'disable' => '', 'name' => 'woocommerce_review_order_before_shipping', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_review_order_after_shipping' 	    => array( 'disable' => '', 'name' => 'woocommerce_review_order_after_shipping', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_review_order_before_order_total' 	=> array( 'disable' => '', 'name' => 'woocommerce_review_order_before_order_total', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_review_order_after_order_total' 	=> array( 'disable' => '', 'name' => 'woocommerce_review_order_after_order_total', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_review_order_before_payment' 	    => array( 'disable' => '', 'name' => 'woocommerce_review_order_before_payment', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_review_order_before_submit' 	    => array( 'disable' => '', 'name' => 'woocommerce_review_order_before_submit', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_review_order_after_submit' 	    => array( 'disable' => '', 'name' => 'woocommerce_review_order_after_submit', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_review_order_after_payment' 	    => array( 'disable' => '', 'name' => 'woocommerce_review_order_after_payment', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_checkout_after_order_review' 	    => array( 'disable' => '', 'name' => 'woocommerce_checkout_after_order_review', 'title' => __( 'NEED', 'blox' ) ),
                    'woocommerce_after_checkout_form' 	            => array( 'disable' => '', 'name' => 'woocommerce_after_checkout_form', 'title' => __( 'NEED', 'blox' ) ),
                )
            ),
        );

        return $hooks;
    }


    /**
     * Helper function for retrieving all available Custom hooks.
     *
     * @since 2.0.0
     *
     * @return array $hooks Array of all Custom hooks.
     */
    public function get_custom_hooks_unfiltered() {

        // Pull custom hooks from settings, default to empty array
        $hooks = blox_get_option( 'default_custom_hooks', array() );

        return $hooks;
    }

    /**
     * Helper function for retrieving all available Genesis hooks post filtering
     *
     * @since 1.1.0
     *
     * @return array Array of all Genesis hooks after they have been filtered.
     */
    public function get_genesis_hooks() {

    	return apply_filters( 'blox_genesis_hooks', $this->get_genesis_hooks_unfiltered() );
    }


    /**
     * Helper function for retrieving all available Core Wordpress hooks post filtering
     *
     * @since 2.0.0
     *
     * @return array Array of all Core WordPress hooks after they have been filtered.
     */
    public function get_wordpress_hooks() {

        return apply_filters( 'blox_wordpress_hooks', $this->get_wordpress_hooks_unfiltered() );
    }


    /**
     * Helper function for retrieving all available hooks
     *
     * @since 2.0.0
     *
     * @return array Array of all hooks.
     */
    public function get_hooks() {

        $hooks = array(
            'woocommerce'   => blox_get_option( 'woocommerce_hooks', $this->get_woocommerce_hooks_unfiltered() ),
            'genesis'       => blox_get_option( 'genesis_hooks', $this->get_custom_hooks_unfiltered() ),
            'custom'        => blox_get_option( 'default_custom_hooks', $this->get_custom_hooks_unfiltered() ),
            'wordpress'     => blox_get_option( 'wordpress_hooks', $this->get_wordpress_hooks_unfiltered() ),
        );

        return apply_filters( 'blox_position_hooks', $hooks );
    }


    /**
     * Helper function for retrieving all active hooks
     *
     * @since 2.0.0
     *
     * @return array $hooks Array of all active hooks.
     */
    public function get_active_hooks() {

        $hooks      = $this->get_hooks();
        $hook_types = $this->get_hook_types();

        foreach ( $hooks as $type_slug => $type_sections ) {
            if ( $hook_types[$type_slug]['disable'] || ! $hook_types[$type_slug]['active'] ) {
                // If the hook type is disabled or not active, unset it
                unset( $hooks[$type_slug] );
            } else {
                foreach ( $type_sections as $section_slug => $section_args ) {
                    if ( isset( $section_args['disable'] ) && $section_args['disable'] ) {
                        // If the section is disabled, unset it
                        unset( $hooks[$type_slug][$section_slug] );
                    } else {
                        foreach ( $section_args['hooks'] as $hook_slug => $hook_args ) {
                            if ( isset( $hook_args['disable'] ) && $hook_args['disable'] ) {
                                // If the individual hook is disabled, unset it
                                unset( $hooks[$type_slug][$section_slug]['hooks'][$hook_slug] );
                            }
                        }
                    }
                }
            }
        }

        return $hooks;
    }


    /**
     * Helper function for retrieving all hooks in a flattened array with disabled flags
     *
     * @since 2.0.0
     *
     * @return array $hooks Array of all hooks in a flattened array.
     */
    public function get_active_hooks_flattened() {

        $hooks = $this->get_hooks();

        $flattened = array();

        $disable = 0;
        $hook_type_active = 0;
        $hook_type_disable = 0;
        $hook_section_disable = 0;


        foreach ( $hooks as $type_slug => $type_sections ) {
            if ( $hook_types[$type_slug]['disable'] || ! $hook_types[$type_slug]['active'] ) {
                // If the hook type is disabled or not active, unset it
                unset( $hooks[$type_slug] );
            } else {
                foreach ( $type_sections as $section_slug => $section_args ) {
                    if ( isset( $section_args['disable'] ) && $section_args['disable'] ) {
                        // If the section is disabled, unset it
                        unset( $hooks[$type_slug][$section_slug] );
                    } else {
                        foreach ( $section_args['hooks'] as $hook_slug => $hook_args ) {
                            if ( isset( $hook_args['disable'] ) && $hook_args['disable'] ) {
                                // If the individual hook is disabled, unset it
                                unset( $hooks[$type_slug][$section_slug]['hooks'][$hook_slug] );
                            }
                        }
                    }
                }
            }
        }

        $test = array(
            'hook_slug' =>  array(
                'disable' => 1,
                'hook_type_active' => 1,
                'hook_type_disable' => 1,
                'hook_section_disable' => 1,
            )
        );

        foreach( $unflattened as $sections => $section ) {
            foreach ( $section['hooks'] as $hooks => $hook ) {
                $flattened[$hooks] = $hook['name'];
            }
        }

        return $flattened;
    }


    /**
     * Returns list of all available hook types (filterable)
     *
     * @since 2.0.0
     */
    public function get_hook_types() {

        $hook_types = array(
            'genesis' => array(
                'disable' => blox_get_option( 'genesis_hooks_disable', 0 ),
                'active'  => function_exists( 'genesis_pre' ) ? 1 : 0, // Do we have a Genesis Theme?
                'title'   => __( 'Genesis Hooks', 'blox' ),
                'alert'   => __( 'It appears that the Genesis Framework is not active on this website. Therefore, the hooks below will not work. If you are not planning on using Genesis, these hook options can be disabled in the Position settings. For more information on hook positioning, visit the Blox documentation.', 'blox' )
            ),
            'woocommerce' => array(
                'disable' => blox_get_option( 'woocommerce_hooks_disable', 0 ),
                'active'  => class_exists( 'woocommerce' ) ? 1 : 0, // Is WooCommerce active?
                'title'   => __( 'WooCommerce Hooks', 'blox' ),
                'alert'   => __( 'It appears that the WooCommerce plugin is not active on this website. Therefore, the hooks below will not work. If you are not planning on using WooCommerce, these hook options can be disabled in the Position settings. For more information on hook positioning, visit the Blox documentation.', 'blox' )
            ),
            'custom' => array(
                'disable' => blox_get_option( 'custom_hooks_disable', 0 ),
                'active'  => 1, // Always active (obviously)
                'title'   => __( 'Custom Hooks', 'blox' ),
                'alert'   => __( 'It appears that Blox has experienced an error, please reach out to support.', 'blox' )
            ),
            'wordpress' => array(
                'disable' => blox_get_option( 'wordpress_hooks_disable', 0 ),
                'active'  => 1, // Always active (obviously)
                'title'   => __( 'WordPress Hooks', 'blox' ),
                'alert'   => __( 'It appears that Blox has experienced an error, please reach out to support.', 'blox' )
            ),
        );

        return apply_filters( 'blox_hook_types', $hook_types );
    }


    /**
     * Helper function for retrieving all available hooks post filtering in flattened, 1-dim, array REMOVE????
     *
     * @since 1.1.0
     *
     * @return array Array of all available hooks in 1-dimensional array
     */
    public function get_genesis_hooks_flattened() {

    	$unflattened = $this->get_genesis_hooks();

    	$flattened = array();

    	foreach( $unflattened as $sections => $section ) {
    		foreach ( $section['hooks'] as $hooks => $hook ) {
    			$flattened[$hooks] = $hook['name'];
    		}
    	}
    	return $flattened;
    }


    /**
     * Helper method for retrieving all Genesis hooks. REMOVE????
     *
     * @since 1.1.0
     *
     * @return array Array of all Genesis hooks.
     */
    public function push_hook_defaults() {

    	$default_hooks        = blox_get_option( 'default_hooks', array() );
    	$default_custom_hooks = blox_get_option( 'default_custom_hooks', array() );
    	$final_hooks          = array();

		// Make sure default hooks are enabled
		if ( ! empty( $default_hooks ) && isset( $default_hooks['enable'] ) && $default_hooks['enable'] == 1 ) {

			$available_hooks = $default_hooks['available_hooks'];

			foreach ( $available_hooks as $sections => $section ) {

				$enabled_hooks = array();

				foreach ( $section['hooks'] as $hooks => $hook ) {

					if ( isset( $hook['enable'] ) && $hook['enable'] == 1 ) {
						$enabled_hooks[$hooks] = array(
							'name'  => ! empty( $hook['name'] ) ? esc_attr( $hook['name'] ) : $hooks,
							'title' => ! empty( $hook['name'] ) ? '' : $hook['title'], // Don't need a title is using a custom name
						);
					}
				}

				if ( ! empty( $enabled_hooks ) ) {
					$final_hooks[$sections]['name']  = $section['name'];
					$final_hooks[$sections]['hooks'] = $enabled_hooks;
				}
			}
		} else {
			$final_hooks = $this->get_genesis_hooks_unfiltered();
		}


		// Run our custom hooks through the same process, but only after the Genesis hooks
		if ( ! empty( $default_custom_hooks ) && isset( $default_custom_hooks['enable'] ) && $default_custom_hooks['enable'] == 1 ) {

			$available_hooks = $default_custom_hooks['available_hooks'];

			foreach ( $available_hooks as $sections => $section ) {

				$enabled_hooks = array();

				foreach ( $section['hooks'] as $hooks => $hook ) {

					if ( isset( $hook['enable'] ) && $hook['enable'] == 1 ) {
						$enabled_hooks[$hooks] = array(
							'name'  => ! empty( $hook['name'] ) ? esc_attr( $hook['name'] ) : $hooks,
							'title' => ! empty( $hook['name'] ) ? '' : $hook['title'], // Don't need a title is using a custom name
						);
					}
				}

				if ( ! empty( $enabled_hooks ) ) {;
					$final_hooks[$sections]['name']  = $section['name'];
					$final_hooks[$sections]['hooks'] = $enabled_hooks;
				}
			}
		}

		// Return our modified array of hooks
		return $final_hooks;
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
     * Helper function that returns array of all active hook types
     *
     * @since 2.0.0
     *
     * @return array Array of all active hook types
     */
    public function get_active_hook_types() {
        $active_hook_types = array(
            'wordpress'   => true, // Always active
            'custom'      => true, // Always active
            'genesis'     => $this->is_genesis_active_test(),
            'woocommerce' => $this->is_woocommerce_active_test(),
        );

        return apply_filters( 'blox_get_active_hook_types', $active_hook_types );
    }


    /**
     * Helper function that checks if the Genesis Framework is active
     *
     * @since 2.0.0
     *
     * @return bool True if Genesis is active
     */
    public function is_genesis_active_test() {
        if ( function_exists( 'genesis_pre' ) ) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Helper function that checks if WooCommerce is active
     *
     * @since 2.0.0
     *
     * @return bool True if WooCommerce is active
     */
    public function is_woocommerce_active_test() {
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Helper method for retrieving the content defaults
     *
     * @since 1.2.0
     *
     * @return array Array of all enabled content types
     */
    public function push_content_defaults() {

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
