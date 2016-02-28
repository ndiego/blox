<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The main metabox class which creates all admin metaboxes
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public Licenses
 */
class Blox_Metaboxes {

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

        // Load metabox assets.
        add_action( 'admin_enqueue_scripts', array( $this, 'metabox_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'metabox_scripts' ) );
        
        // Add the add block ajax action
		add_action( 'wp_ajax_blox_add_block', array( $this, 'get_content_blocks' ) );
        
        // Load the metabox hooks and filters.
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 100 );

        // Add action to save metabox config options.
        add_action( 'save_post', array( $this, 'local_blocks_save_meta' ), 10, 2 );
        add_action( 'save_post', array( $this, 'global_block_save_meta' ), 10, 2 );
    }

    /**
     * Loads styles for our metaboxes.
     *
     * @since 1.0.0
     *
     * @return null Return early if not on the proper screen.
     */
    public function metabox_styles() {

        if ( isset( get_current_screen()->base ) && 'post' !== get_current_screen()->base ) {
            return;
        }
        
        // Return early if we are not on an approved post-type
        if ( isset( get_current_screen()->post_type ) && in_array( get_current_screen()->post_type, $this->get_skipped_posttypes() ) ) {
            return;
        }
        
        // Load necessary metabox styles
        wp_register_style( $this->base->plugin_slug . '-metabox-styles', plugins_url( 'assets/css/metabox.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-metabox-styles' );
        
        // If on an Blox post type, add custom CSS for hiding specific things.
        if ( isset( get_current_screen()->post_type ) && 'blox' == get_current_screen()->post_type ) {
            add_action( 'admin_head', array( $this, 'global_admin_css' ) );
        }
        
        // Fire a hook to load in custom metabox styles.
        do_action( 'blox_metabox_styles' );
    }
    
    
    /**
     * Hides unnecessary data in the Publish metabox on global Blox post type screens.
     *
     * @since 1.0.0
     */
    public function global_admin_css() {

        ?>
        <style type="text/css">.misc-pub-section:not(.misc-pub-post-status) { display: none; }</style>
        <?php

        // Fire action for CSS on global Blox post type screens.
        do_action( 'blox_global_admin_css' );
    }


    /**
     * Loads scripts for our metaboxes.
     *
     * @since 1.0.0
     *
     * @global int $id      The current post ID.
     * @global object $post The current post object..
     * @return null         Return early if not on the proper screen.
     */
    public function metabox_scripts( $hook ) {

        global $id, $post;

        if ( isset( get_current_screen()->base ) && 'post' !== get_current_screen()->base ) {
            return;
        }
        
        // Return early if we are not on an approved post-type
        if ( isset( get_current_screen()->post_type ) && in_array( get_current_screen()->post_type, $this->get_skipped_posttypes() ) ) {
            return;
        }

        // Set the post_id for localization. Not used currently, but keep for future use....
        // $post_id = isset( $post->ID ) ? $post->ID : (int) $id;

        // Load necessary metabox scripts
        wp_register_script( $this->base->plugin_slug . '-metabox-scripts', plugins_url( 'assets/js/metabox.js', $this->base->file ), array( 'jquery-ui-sortable' ), $this->base->version );
       	wp_enqueue_script( $this->base->plugin_slug . '-metabox-scripts' );
       	
       	// Used for adding local blocks via ajax 
        wp_localize_script( 
        	$this->base->plugin_slug . '-metabox-scripts', 
        	'blox_localize_metabox_scripts', 
        	array( 
        		'ajax_url' 		  	   		=> admin_url( 'admin-ajax.php' ), 
        		'blox_add_block_nonce' 		=> wp_create_nonce( 'blox_add_block_nonce' ) ,
        		'confirm_remove'			=> __( 'Are you sure you want to remove this content block? This action cannot be undone.', 'blox' ),
        		'location_test_hide'		=> sprintf( __( 'Choose the pages you would like the content block to be %1$shidden%2$s on.', 'blox' ), '<strong>', '</strong>' ),
        		'location_test_show'		=> sprintf( __( 'Choose the pages you would like the content block to be %1$svisible%2$s on.', 'blox' ), '<strong>', '</strong>' ),
        		
        		'image_media_title'			=> __( 'Choose or Upload an Image', 'blox' ),
        		'image_media_button'		=> __( 'Use Selected Image', 'blox' ),
        		
        		'editor_hide_html'			=> __( 'Hide HTML', 'blox' ),
        		'editor_show_html'			=> __( 'Show HTML', 'blox' ),
        		
        		'slideshow_media_title'		=> __( 'Choose or Upload an Image(s)', 'blox' ),
        		'slideshow_media_button'	=> __( 'Insert Image(s)', 'blox' ),
        		'slideshow_details'			=> __( 'Details', 'blox' ),
        		'slideshow_remove'			=> __( 'Remove', 'blox' ),
        		'slideshow_confirm_remove' 	=> __( 'Are you sure you want to remove this image from the slideshow? This action cannot be undone.', 'blox' ),
        	)
        );
        
        // Allow the use of the media uploader on global blocks pages
        wp_enqueue_media( 'blox' );
        
        // Fire a hook to load custom metabox scripts.
        do_action( 'blox_metabox_scripts' );
    }


    /**
     * Creates metaboxes for both local and global blocks
     *
     * @since 1.0.0
     */
    public function add_meta_boxes() {
		
		global $typenow;
        
        // Check if local blocks are enabled and user has permission to manage local blocks
		$local_enable      = blox_get_option( 'local_enable', false );
		$local_permissions = blox_get_option( 'local_permissions', 'manage_options' );		
       	
        if ( $local_enable && current_user_can( $local_permissions ) ) { 
       		
       		// Get all post types that are allowed to have local blocks
        	$local_enabled_pages = blox_get_option( 'local_enabled_pages', '' );
        	$local_metabox_title = blox_get_option( 'local_metabox_title', __( 'Local Content Blocks', 'blox' ) );

			// Loops through allowed post types and add the local block metabox
			if ( ! empty( $local_enabled_pages ) ) {
				foreach ( (array) $local_enabled_pages as $local_enabled_page ) {
					add_meta_box( 'local_blocks_metabox', $local_metabox_title, array( $this, 'local_blocks_metabox_callback' ), $local_enabled_page, 'normal', 'high' );
				}
			}
        }
		
		// Add the global block metabox
		if ( $typenow == 'blox' ) {
			
			// Remove all unnecessary metaboxes, ones not added by this plugin
        	$this->remove_all_the_metaboxes();
		
            add_meta_box( 'global_block_metabox', __( 'Block Settings', 'blox' ), array( $this, 'global_block_metabox_callback' ), 'blox', 'normal', 'low' );
		}
    }
    
    
    /**
     * Removes all the metaboxes except the ones needed on the global blox custom post type
     * This function was authored Thomas Griffin, thanks!
     *
     * @since 1.0.0
     *
     * @global array $wp_meta_boxes Array of registered metaboxes.
     */
    public function remove_all_the_metaboxes() {

        global $wp_meta_boxes;

        // The post type to target
        $post_type  = 'blox';

        // These are the metabox IDs we want to keep on the page
        $pass_over  = array( 'submitdiv', 'blox' );

        // Check all metabox contexts
        $contexts   = array( 'normal', 'advanced', 'side' );

        // Check all metabox priorities
        $priorities = array( 'high', 'core', 'default', 'low' );

        // Loop through and target each context
        foreach ( $contexts as $context ) {
            
            // Now loop through each priority and start the purging process
            foreach ( $priorities as $priority ) {
                if ( isset( $wp_meta_boxes[$post_type][$context][$priority] ) ) {
                    foreach ( (array) $wp_meta_boxes[$post_type][$context][$priority] as $id => $metabox_data ) {
                        
                        // If the metabox ID to pass over matches the ID given, remove it from the array and continue.
                        if ( in_array( $id, $pass_over ) ) {
                            unset( $pass_over[$id] );
                            continue;
                        }

                        // Otherwise, loop through the pass_over IDs and if we have a match, continue.
                        foreach ( $pass_over as $to_pass ) {
                            if ( preg_match( '#^' . $id . '#i', $to_pass ) ) {
                                continue;
                            }
                        }

                        // If we reach this point, remove the metabox completely.
                        unset( $wp_meta_boxes[$post_type][$context][$priority][$id] );
                    }
                }
            }
        }
    }
    
    
    /**
     * Callback for displaying content in the registered metabox.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
	public function global_block_metabox_callback( $post ) {
	
		$block_data = get_post_meta( $post->ID, '_blox_content_blocks_data', true );
	
		wp_nonce_field( 'blox_global_blocks', 'blox_global_blocks' );
		
		$data = $block_data;
		$get_id = $name_id = $post->ID;
		?>
		
		<div class="blox-settings-tabs global">
			<ul class="blox-tab-navigation">
			<?php 
				foreach( $this->metabox_tabs() as $tab => $tab_settings ) {
					
					if ( $tab_settings['scope'] == 'all' || $tab_settings['scope'] == 'global' ) { 
					?> 
					<li class="<?php echo $tab == 'content' ? 'current' : ''; ?>"><a href="#blox_tab_<?php echo $tab; ?>"><?php echo $tab_settings['title']; ?></a></li>
					<?php
					}
					
				}
			?>
			</ul>
			<div class="blox-tabs-container">
			
				<?php foreach( $this->metabox_tabs() as $tab => $tab_settings ) { 
				
					if ( $tab_settings['scope'] == 'all' || $tab_settings['scope'] == 'global' ) { 
					?>
					
					<div id="blox_tab_<?php echo $tab; ?>" class="blox-tab-content">
						<?php 
							do_action( 'blox_tab_container_before', $tab, $data, $name_id, $get_id, true ); 
							do_action( 'blox_get_metabox_tab_' . $tab, $data, $name_id, $get_id, true ); 
							do_action( 'blox_tab_container_after', $tab, $data, $name_id, $get_id, true );
						?>
					</div>

					<?php 
					}
				} ?>
				
			</div>
		</div>
		
		<?php
		
		// A hook to add any content modals that are needed, last parameter indicates if the block is global
		do_action( 'blox_metabox_modals', true );
	}
	
	
	 /**
	 * Save all global content blocks
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_id The id of the global content block
	 */
	public function global_block_save_meta( $post_id ) {

		if ( ! isset( $_POST['blox_global_blocks'] ) || ! wp_verify_nonce( $_POST['blox_global_blocks'], 'blox_global_blocks' ) ) {
			return;
		}
	
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( isset( $_POST[ 'blox_content_blocks_data' ] ) ) {
			
			$settings = array();
			
			foreach ( $this->metabox_tabs() as $tab => $title ) {
			
				if ( isset( $_POST['blox_content_blocks_data'][$tab] ) ) {
				
					$name_prefix = $_POST['blox_content_blocks_data'][$tab];
									
					$settings[$tab] = apply_filters( 'blox_save_metabox_tab_' . $tab, null, $name_prefix, true );
				}
			}
			
			update_post_meta( $post_id, '_blox_content_blocks_data', $settings );
			
		} else {
			delete_post_meta( $post_id, '_blox_content_blocks_data' );
		}
	}
	
	
	/**
     * Callback for displaying local blocks in the registered metabox.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function local_blocks_metabox_callback( $post ) {
        
		$blocks_data = get_post_meta( $post->ID, '_blox_content_blocks_data', true );
	
		wp_nonce_field( 'blox_local_blocks', 'blox_local_blocks' );
		
		?>
		<div id="blox_add_block_container">
			<a id="blox_add_block" class="button-primary button" href="#"><?php _e( 'Add Content Block' ); ?></a>
			<span class="blox-help-text-icon">
				<a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
			</span>	
			<div class="blox-help-text">	
				<?php _e( 'Click to add a local content block to this webpage. Local content blocks will only be visible on the page/post/custom post type that they are added to.', 'blox' ); ?>
			</div>		
		</div>
		<div id="blox_content_blocks_container">
			<?php
			if ( ! empty( $blocks_data ) ) {
				foreach ( $blocks_data as $id => $data) {
					$this->get_content_blocks( $blocks_data, $id, false );
				}
			}
			?>
		</div> <!-- end #blox_content_blocks_container -->
		
		<?php
		
		// A hook to add any content modals that are needed, last parameter indicates if the block is global (here it is not)
		do_action( 'blox_metabox_modals', false );
    }
    
    
    /**
     * Loads local content blocks. Also used for creating new local blocks and replicating local block via ajax 
     *
     * @since 1.0.0
     *
     * @param obj $data   The content block save data
     * @param int $id     The content blocks id (defaults to null for when function is called by ajax)
     * @param bool $ajax  Equals true if this function was called by ajax
     */
    public function get_content_blocks( $data = null, $id = null, $ajax = true ) {
		
		$rand_id   = str_pad( rand( 0, pow( 10, 4 ) - 1 ) , 4, '0', STR_PAD_LEFT );
    	$copy_text = '';
    	
    	// If the id is not set (i.e. block was generated via ajax) create an id
		if ( $ajax == true ) {
 
			// Check to see if the submitted nonce matches with the generated nonce we created earlier
			if ( ! wp_verify_nonce( $_POST['blox_add_block_nonce'], 'blox_add_block_nonce' ) ) {
				die ( 'Please try refreshing the page and try again...' );
			}
			
			if ( $_POST['type'] == 'copy' ) {
							
				$post_id     = $_POST['post_id'];
				$data        = get_post_meta( $post_id, '_blox_content_blocks_data', true );
				$name_id     = $rand_id;
				$get_id      = $_POST['block_id']; // Used to use (int) $_POST['block_id']; but breaks when ids start with 0
				$copy_text 	 = __( ' COPY', 'blox' );
				$block_title = ! empty( $data[$get_id]['title'] ) ? esc_attr( $data[$get_id]['title'] ) : 'nothing';
				
			} else if ( $_POST['type'] == 'new' )  {
			
			    $name_id     = $rand_id;
				$get_id      = $rand_id;
				$copy_text   = '';
				$block_title = ! empty( $data[$get_id]['title'] ) ? esc_attr( $data[$get_id]['title'] ) : '';
			}
		} else {
		
		 	$name_id     = $id;
			$get_id      = $id;
			$copy_text   = '';
			$block_title = ! empty( $data[$get_id]['title'] ) ? esc_attr( $data[$get_id]['title'] ) : '';
		}
				
    	?>
    	<div id="<?php echo $name_id; ?>" class="blox-content-block <?php echo ! empty( $data[$get_id]['editing'] ) ? 'editing' : ''; ?>">
			<input type="checkbox" class="blox-content-block-editing" name="blox_content_blocks_data[<?php echo $name_id; ?>][editing]"  value="1" <?php ! empty( $data[$get_id]['editing'] ) ? checked( $data[$get_id]['editing'] ) : ''; ?>>
			<div class="blox-content-block-header">
				<div class="blox-content-block-title-container">
					<div class="blox-content-block-title">
						<?php echo ! empty( $block_title ) ? $block_title . $copy_text : '<span class="no-title">No Title</span>' . $copy_text; ?>
					</div>
					<div class="blox-content-block-title-input">
						<input type="text" name="blox_content_blocks_data[<?php echo $name_id; ?>][title]" placeholder="<?php _e( 'Content Block Title' ); ?>" value="<?php echo $block_title . $copy_text; ?>">
					</div>
					<div class="blox-content-block-controls">
						<a class="blox-replicate-block" href="#"><?php _e( 'Replicate', 'blox' );?></a>
						<a class="blox-remove-block" href="#"><?php _e( 'Delete', 'blox' );?></a>
					</div>
				</div>
				<div class="blox-content-block-details">
					<div class="blox-content-block-details-wrap">
						<span class="blox-content-block-type">
							<?php 
								if ( empty( $data[$name_id] ) ) {
									_e( 'Not Saved', 'blox' );
								} else if ( ! empty( $data[$name_id]['content']['content_type'] ) ) {
									echo ucfirst( $data[$name_id]['content']['content_type'] );  
									if ( ! array_key_exists( $data[$name_id]['content']['content_type'], $this->get_content_types() ) ) {
										echo ' - <span class="blox-error">' . __( 'Error', 'blox' ) . '</span>';
									}
									
									// Add a dot to separate additional meta data
									echo '&nbsp;&nbsp;&middot;';
								} else {
									echo '<span class="blox-error">' . __( 'Error', 'blox' ) . '</span>';
								}
							?>
						</span>
						<?php 
							if ( ! empty( $data[$name_id] ) ) {
								echo '<span class="blox-content-block-meta">';
									// Hook in additional local block meta data
									do_action( 'blox_content_block_meta', $data[$name_id] );
								echo '</span>';
							}
						?>
					</div>
					<a class="blox-content-block-edit" title="<?php _e( 'Edit Content Block', 'blox' ); ?>" href="#"></a>
				</div>
			</div>
			<div class="blox-settings-tabs">
				<ul class="blox-tab-navigation">
				<?php 
					foreach( $this->metabox_tabs() as $tab => $tab_settings ) { 
						if ( $tab_settings['scope'] == 'all' || $tab_settings['scope'] == 'local' ) { 
							?> 
							<li class="<?php echo $tab == 'content' ? 'current' : ''; ?>"><a href="#blox_tab_<?php echo $tab; ?>"><?php echo $tab_settings['title']; ?></a></li>
							<?php
						}
					}
				?>
				</ul>
				<div class="blox-tabs-container">
				
					<?php foreach( $this->metabox_tabs() as $tab => $tab_settings ) { 
						if ( $tab_settings['scope'] == 'all' || $tab_settings['scope'] == 'local' ) { 
						?>
						
						<div id="blox_tab_<?php echo $tab; ?>" class="blox-tab-content">
							<?php 
								do_action( 'blox_tab_container_before', $tab, $data, $name_id, $get_id, false );
								do_action( 'blox_get_metabox_tab_' . $tab, $data, $name_id, $get_id, false );
								do_action( 'blox_tab_container_after', $tab, $data, $name_id, $get_id, false );
							?>
						</div>

						<?php 
						}
					} ?>
					
				</div>
			</div>
		
		</div> <!-- end .blox-content-block -->
		
		<?php
    	
    	// If we ran this function via ajax we need to call wp_die()
    	if ( $ajax == true ) {
    		wp_die();
    	}
    }
    
    
	/**
	 * Save all local content blocks
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_id The id of page/post/custom post type that the local blocks are attached to
	 */
	public function local_blocks_save_meta( $post_id ) {

		if ( ! isset( $_POST['blox_local_blocks'] ) || ! wp_verify_nonce( $_POST['blox_local_blocks'], 'blox_local_blocks' ) ) {
			return;
		}
	
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		
		if ( isset( $_POST[ 'blox_content_blocks_data' ] ) ) {
			
			$settings = array();
			
			foreach ( $_POST['blox_content_blocks_data'] as $id => $data ) {

				$name_prefix = $_POST['blox_content_blocks_data'][$id];
				
				$settings[$id] 				= array();
				$settings[$id]['editing'] 	= isset( $name_prefix['editing'] ) ? 1 : 0;
				$settings[$id]['title']		= trim( strip_tags( $name_prefix['title'] ) );
				
				foreach ( $this->metabox_tabs() as $tab => $title ) {
				
					if ( isset( $_POST['blox_content_blocks_data'][$id][$tab] ) ) {
					
						$name_prefix = $_POST['blox_content_blocks_data'][$id][$tab];
										
						$settings[$id][$tab] = apply_filters( 'blox_save_metabox_tab_' . $tab, $id, $name_prefix, false );
					}
				}
			}
			
			update_post_meta( $post_id, '_blox_content_blocks_data', $settings );
			
		} else {
			// If there are no custom block to add, delete the meta value otherwise an empty block will display after saving
			delete_post_meta( $post_id, '_blox_content_blocks_data' );
		}
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
     * Returns an array of all the blox metabox tabs 
     *
     * @since 1.0.0
     *
     * @return array Array of metabox tabs
     */    
    public function metabox_tabs() {
    
        $tabs = array();
        
        return apply_filters( 'blox_metabox_tabs', $tabs );
    }


    /**
     * Returns the post types to skip for loading Blox metaboxes 
     *
     * @since 1.0.0
     *
     * @param bool $blox Whether or not to include the blox post type
     * @return array     Array of skipped posttypes
     */
    public function get_skipped_posttypes( $blox = false ) {

        $post_types = get_post_types();
        $local_enabled_pages = blox_get_option( 'local_enabled_pages', '' );
        
        // Remove the blox post type from the "skipped" array
        if ( ! $blox ) {
            unset( $post_types['blox'] );
        }
        
        // Loop through all enabled post types and remove them from the "skipped" array 
        if ( ! empty( $local_enabled_pages ) ) {
			foreach ( $local_enabled_pages as $local_enabled_page ) {
				unset( $post_types[$local_enabled_page] );
			}
        }
        
        return apply_filters( 'blox_skipped_posttypes', $post_types );
    }


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Metaboxes ) ) {
            self::$instance = new Blox_Metaboxes();
        }

        return self::$instance;
    }
}

// Load the metabox class
$blox_metaboxes = Blox_Metaboxes::get_instance();