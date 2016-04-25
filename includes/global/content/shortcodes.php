<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates all available Blox shortcodes
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Content_Shortcodes {

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

		add_shortcode( 'blox-page-title', array( $this, 'page_title' ) );
		add_shortcode( 'blox-archive-title', array( $this, 'archive_title' ) );
		add_shortcode( 'blox-modified-date', array( $this, 'modified_date' ) );
		add_shortcode( 'blox-published-date', array( $this, 'published_date' ) );
		add_shortcode( 'blox-author', array( $this, 'author' ) );
		add_shortcode( 'blox-categories-list', array( $this, 'categories_list' ) );
		add_shortcode( 'blox-tags-list', array( $this, 'tags_list' ) );
    }


	/**
	 * Shortcode: Print page title
     *
     * @since 1.0.0
     *
     * @param array $atts  An array shortcode attributes
     */
	public function page_title( $atts ) {
		
		$atts = shortcode_atts( array( 
			'before' 	  	=> '',
			'after'	 	  	=> '',
			'singular_only' => '', 
		), $atts );
		
		if ( ! is_singular() && $atts['singular_only'] == 'true' ) {
			return;
		} else {
			return wp_kses_post( $atts['before'] ) . get_the_title() . wp_kses_post( $atts['after'] );
		}
	}
	
	
	/**
	 * Shortcode: Print the title of an archive page
     *
     * @since 1.0.0
     *
     * @param array $atts  An array shortcode attributes
     */
	public function archive_title( $atts ) {
		
		$atts = shortcode_atts( array( 
			'before' 	  	=> '',
			'after'	 	  	=> '', 
		), $atts );
		
		if ( is_archive() ) {
			return wp_kses_post( $atts['before'] ) . get_the_archive_title() . wp_kses_post( $atts['after'] );
		}
	}
	
	
	/**
	 * Shortcode: Print date/time the post was last modified
     *
     * @since 1.0.0
     *
     * @param array $atts  An array shortcode attributes
     */
	public function modified_date( $atts ) {
	
		$atts = shortcode_atts( array( 
			'format'		=> '',
			'before' 	  	=> '',
			'after'	 	  	=> '',
			'singular_only' => '', 
		), $atts );
	
		if ( ! is_singular() && $atts['singular_only'] == 'true' ) {
			return;
		} else {
			return wp_kses_post( $atts['before'] ) . get_the_modified_date( $atts['format'] ) . wp_kses_post( $atts['after'] );
		}
	}
	
	
	/**
	 * Shortcode: Print date/time the post was published
	 * Reference: https://codex.wordpress.org/Function_Reference/get_the_date
     *
     * @since 1.0.0
     *
     * @param array $atts  An array shortcode attributes
     */
	public function published_date( $atts ) {
		
		$atts = shortcode_atts( array( 
			'format'		=> '',
			'before' 	  	=> '',
			'after'	 	  	=> '',
			'singular_only' => '', 
		), $atts );
		
		if ( ! is_singular() && $atts['singular_only'] == 'true' ) {
			return;
		} else {
			return wp_kses_post( $atts['before'] ) . get_the_date( $atts['format'] ) . wp_kses_post( $atts['after'] );
		}
	}
	
	
	/**
	 * Shortcode: Print author of post
     *
     * @since 1.0.0
     *
     * @param array $atts  An array shortcode attributes
     */
	public function author( $atts ) {
		
		// Needed to get author id when outside the loop
    	global $post;
    	$author_id = $post->post_author;
    	
    	$atts = shortcode_atts( array( 
			'meta'			=> 'display_name',
			'before' 	  	=> '',
			'after'	 	  	=> '',
			'singular_only' => '', 
		), $atts );
     
		if ( ! is_singular() && $atts['singular_only'] == 'true' ) {
			return;
		} else {
    		return wp_kses_post( $atts['before'] ) . get_the_author_meta( $atts['meta'], $author_id ) . wp_kses_post( $atts['after'] );
		}
	}
	
	
	/**
	 * Shortcode: Print the post's categories
     *
     * @since 1.1.0
     *
     * @param array $atts  An array shortcode attributes
     */
	public function categories_list( $atts ) {
		
		$atts = shortcode_atts( array(
			'separator' 	=> '',
			'before' 	  	=> '',
			'after'	 	  	=> '',
			'singular_only' => '', 
		), $atts );
		
		$category_list = get_the_category_list( $atts['separator'] );
		
		if ( ! is_singular() && $atts['singular_only'] == 'true' ) {
			return;
		} else if ( ! empty( $category_list ) ){
			return wp_kses_post( $atts['before'] ) . $category_list . wp_kses_post( $atts['after'] );
		}
	}
	
	
	/**
	 * Shortcode: Print the post's tags
     *
     * @since 1.1.0
     *
     * @param array $atts  An array shortcode attributes
     */
	public function tags_list( $atts ) {
		
		$atts = shortcode_atts( array(
			'separator' 	=> '',
			'before' 	  	=> '',
			'after'	 	  	=> '',
			'singular_only' => '', 
		), $atts );
		
		$tag_list = get_the_tag_list( '', $atts['separator'], '' );
		
		if ( ! is_singular() && $atts['singular_only'] == 'true' ) {
			return;
		} else if ( ! empty( $tag_list ) ){
			return wp_kses_post( $atts['before'] ) . $tag_list . wp_kses_post( $atts['after'] );
		}
	}
	
	
	/*********** More Shortcodes to Come... ***********/
	
	
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Content_Shortcodes ) ) {
            self::$instance = new Blox_Content_Shortcodes();
        }

        return self::$instance;
    }
}

// Load the image content class.
$blox_content_image = Blox_Content_Shortcodes::get_instance();
