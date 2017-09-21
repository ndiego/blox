<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates all Blox settings
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Settings {

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

        // Set default settings for a new install
        $this->set_default_settings();

		add_action( 'admin_menu', array( $this, 'add_menu_links' ), 10 );
		add_action( 'admin_init', array( $this, 'register_settings' ), 10 );

		// Enqueue Setting scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    }


    /**
	 * This function is used when the plugin is first installed. It checks if the option
	 * blox_settings is set, and if not it creates it an fills it with our default settings.
	 *
	 * @since 1.0.0
	 */
	public function set_default_settings() {

		if ( get_option( 'blox_settings' ) != false ) {

			// The option already exists so bail...
			return;
		} else {

			// The option does not exist, so add it.
			add_option( 'blox_settings' );

			// Get and set the default settings
        	$settings = $this->get_registered_settings();
            $settings = $this->get_registered_settings_degrouped( $settings );
        	$tabs     = $this->get_settings_tabs();
			$defaults = array();

			foreach ( $tabs as $tab => $tab_name ) {
				if ( ! empty( $settings[$tab] ) ) {
					foreach ( $settings[$tab] as $key => $value ) {
						if ( ! empty( $value[ 'default' ] ) ) {
							$defaults[$key] = $value[ 'default' ];
						}
					}
				}
			}

			// Update the option with the defaults
			update_option( 'blox_settings', $defaults );
		}
	}


    /**
     * Add the Settings menu link.
     *
     * @since 1.0.0
     */
    public function add_menu_links() {

		// Add our main settings menu link
		add_submenu_page( 'edit.php?post_type=blox', __( 'Blox Settings', 'blox' ), __( 'Settings', 'blox' ), 'manage_options', 'blox-settings', array( $this, 'print_settings_page' ) );
	}


	/**
     * Print settings page.
     *
     * @since 1.0.0
     */
	public function print_settings_page() {

		// Get the active tab
        $settings_tabs = $this->get_settings_tabs();
        $settings_tabs = empty( $settings_tabs ) ? array() : $settings_tabs;
        $active_tab    = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
        $active_tab    = array_key_exists( $active_tab, $settings_tabs ) ? $active_tab : 'general';
        $sections      = $this->get_settings_tab_sections( $active_tab );
        $key           = 'main'; // Default value for the section key

        if ( is_array( $sections ) ) {
            $key = key( $sections );
        }

        //NEED TO FINISH HERE
        $registered_sections = $this->get_settings_tab_sections( $active_tab );
	    $section             = isset( $_GET['section'] ) && ! empty( $registered_sections ) && array_key_exists( $_GET['section'], $registered_sections ) ? sanitize_text_field( $_GET['section'] ) : $key;

        // Unset 'main' if it's empty and default to the first non-empty if it's the chosen section
    	$all_settings = $this->get_registered_settings();

    	// Let's verify we have a 'main' section to show
    	$has_main_settings = true;
    	if ( empty( $all_settings[ $active_tab ]['main'] ) ) {
    		$has_main_settings = false;
    	}

    	// Check for old non-sectioned settings (see #4211 and #5171)
    	if ( ! $has_main_settings ) {
    		foreach( $all_settings[ $active_tab ] as $sid => $stitle ) {
    			if ( is_string( $sid ) && is_array( $sections ) && array_key_exists( $sid, $sections ) ) {
    				continue;
    			} else {
    				$has_main_settings = true;
    				break;
    			}
    		}
    	}

    	$override = false;
    	if ( false === $has_main_settings ) {
    		unset( $sections['main'] );

    		if ( 'main' === $section ) {
    			foreach ( $sections as $section_key => $section_title ) {
    				if ( ! empty( $all_settings[ $active_tab ][ $section_key ] ) ) {
    					$section  = $section_key;
    					$override = true;
    					break;
    				}
    			}
    		}
    	}

		ob_start();
		?>
		<div class="wrap">
			<h2><?php _e( 'Blox Settings', 'blox' ); ?></h2>

			<?php settings_errors( 'blox-notices' ); ?>

            <?php echo print_r(get_option( 'blox_settings' )) ; ?>

			<h2 class="nav-tab-wrapper">
				<?php foreach( $this->get_settings_tabs() as $tab_id => $tab_name ) {

					$tab_url = add_query_arg( array(
						'settings-updated' => false,
						'tab'              => $tab_id
					) );

                    // Remove the section from the tabs so we always end up at the main section
    				$tab_url = remove_query_arg( 'section', $tab_url );

					$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

					echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
						echo esc_html( $tab_name );
					echo '</a>';
				}
				?>
			</h2>

            <?php
            $number_of_sections = count( $sections );
    		$number = 0;
    		if ( $number_of_sections > 1 ) {
    			echo '<div><ul class="subsubsub">';
    			foreach( $sections as $section_id => $section_name ) {
    				echo '<li>';
    				$number++;
    				$tab_url = add_query_arg( array(
    					'settings-updated' => false,
    					'tab' => $active_tab,
    					'section' => $section_id
    				) );
    				$class = '';
    				if ( $section == $section_id ) {
    					$class = 'current';
    				}
    				echo '<a class="' . $class . '" href="' . esc_url( $tab_url ) . '">' . $section_name . '</a>';

    				if ( $number != $number_of_sections ) {
    					echo ' | ';
    				}
    				echo '</li>';
    			}
    			echo '</ul></div>';
    		}
            ?>

			<div id="tab_container">
				<form method="post" action="options.php">
					<?php do_action( 'blox_settings_form_top', $active_tab ); ?>
					<table class="form-table">
						<?php
						settings_fields( 'blox_settings' );
                        do_settings_sections( 'blox_settings_' . $active_tab . '_' . $section );
						?>
					</table>
					<?php do_action( 'blox_settings_form_bottom', $active_tab ); ?>
					<?php submit_button( __( 'Save Changes', 'blox' ) ); ?>
				</form>
			</div>
		</div>
		<?php
		echo ob_get_clean();
	}


	/**
	 * Retrieve our settings tabs
	 *
	 * @since 1.0.0
	 *
	 * @return array $tabs An array of all available tabs
	 */
	public function get_settings_tabs() {

		$tabs = array(
            'general'   => __( 'General', 'blox' ),
            'content'   => __( 'Content', 'blox' ),
            'position'  => __( 'Position', 'blox' ),
    		'style'     => __( 'Styles', 'blox' ),
    		'misc'      => __( 'Misc', 'blox' ),
        );

		return apply_filters( 'blox_settings_tabs', $tabs );
	}


    /**
     * Retrieve settings tabs
     *
     * @since 2.0
     * @return array $section
     */
    function get_settings_tab_sections( $tab = false ) {

    	$tabs     = false;
    	$sections = $this->get_registered_settings_sections();

    	if( $tab && ! empty( $sections[ $tab ] ) ) {
    		$tabs = $sections[ $tab ];
    	} else if ( $tab ) {
    		$tabs = false;
    	}

    	return $tabs;
    }


	/**
	 * Add all settings sections and fields
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_settings() {

		// If blox_settings does not exist, create it. It should always exist, so this is just a backup
		if ( false == get_option( 'blox_settings' ) ) {
			add_option( 'blox_settings' );
		}

		foreach( $this->get_registered_settings() as $tab => $sections ) {
            foreach ( $sections as $section => $settings) {

                // Check for backwards compatibility
    			$section_tabs = $this->get_settings_tab_sections( $tab );
    			if ( ! is_array( $section_tabs ) || ! array_key_exists( $section, $section_tabs ) ) {
    				$section = 'main';
    				$settings = $sections;
    			}

    			add_settings_section(
    				'blox_settings_' . $tab . '_' . $section,
    				__return_null(),
    				'__return_false',
    				'blox_settings_' . $tab . '_' . $section
    			);

    			foreach ( $settings as $option ) {

                    // For backwards compatibility
                    if ( empty( $option['id'] ) ) {
                        continue;
                    }

    				$name     = isset( $option['name'] ) ? $option['name'] : '';
    				$callback = method_exists( __CLASS__, $option['type'] . '_callback' ) ? array( $this, $option['type'] . '_callback' ) : array( $this, 'missing_callback' );

    				add_settings_field(
    					'blox_settings[' . $option['id'] . ']',
    					$name,
    					$callback,
    					'blox_settings_' . $tab . '_' . $section, // $page
    					'blox_settings_' . $tab . '_' . $section, // $section
    					array(
    						'section'     => $section,
    						'id'          => isset( $option['id'] )          ? $option['id']          : null,
    						'name'        => isset( $option['name'] )        ? $option['name']        : null,
    						'label' 	  => ! empty( $option['label'] )     ? $option['label']       : '',
    						'desc'        => ! empty( $option['desc'] )      ? $option['desc']        : '',
    						'size'        => isset( $option['size'] )        ? $option['size']        : null,
    						'options'     => isset( $option['options'] )     ? $option['options']     : '',
    						'min'         => isset( $option['min'] )         ? $option['min']         : null,
    						'max'         => isset( $option['max'] )         ? $option['max']         : null,
    						'step'        => isset( $option['step'] )        ? $option['step']        : null,
    						'placeholder' => isset( $option['placeholder'] ) ? $option['placeholder'] : null,
    						'class'       => isset( $option['class'] )       ? $option['class']       : null,
    						'default'     => isset( $option['default'] )     ? $option['default']     : '',
    						'sanitize'	  => isset( $option['sanitize'] )    ? $option['sanitize']    : '',
                            'settings'    => isset( $option['settings'] )    ? $option['settings']    : '',
    					)
    				);
    			}
            }
		}

		// Creates our settings in the options table
		register_setting( 'blox_settings', 'blox_settings', array( $this, 'settings_sanitize' ) );
	}


    /**
     * Get the settings sections for each tab
     * Uses a static to avoid running the filters on every request to this function
     *
     * @since  2.0.0
     * @return array Array of tabs and sections
     */
    function get_registered_settings_sections() {

        static $sections = false;

        if ( false !== $sections ) {
            return $sections;
        }

        $sections = array(
            'general'    => apply_filters( 'blox_settings_sections_general', array(
                'main'   => __( 'General Settings', 'blox' ),
            ) ),
            'content'    => apply_filters( 'blox_settings_sections_content', array(
                'main'   => __( 'Content Settings', 'blox' ),
                'raw' => __( 'Raw Content', 'blox'),
                'slideshow' => __( 'Slideshow', 'blox'),
            ) ),
            'position'   => apply_filters( 'blox_settings_sections_position', array(
                'main'              => __( 'Position Settings', 'blox' ),
                'custom_hooks'      => __( 'Custom Hooks', 'blox' ),
                'genesis_hooks'     => __( 'Genesis Hooks', 'blox' ),
                'woocommerce_hooks' => __( 'WooCommerce Hooks', 'blox' ),
                'wordpress_hooks'   => __( 'WordPress Hooks', 'blox' ),
            ) ),
            'style'      => apply_filters( 'blox_settings_sections_style', array(
                'main'   => __( 'Style Settings', 'blox' ),
            ) ),
            'misc'       => apply_filters( 'blox_settings_sections_misc', array(
                'main'   => __( 'Misc Settings', 'blox' ),
            ) ),
        );

        $sections = apply_filters( 'blox_registered_settings_sections', $sections );

        return $sections;
    }


	/**
	 * Retrieve the array of plugin settings
	 *
	 * @since 1.0.0
	 *
	 * @return array
	*/
	public function get_registered_settings() {

		/**
		 * Blox settings, filters are provided for each settings
		 * section to allow extensions and other plugins to add their own settings
		 */
		$blox_settings = array(

			/** General Settings */
			'general' => apply_filters( 'blox_settings_general',
				array(
                    'main' => array(
    					'general_global_header' => array(
    						'id' => 'general_global_header',
    						'name' => '<span class="title">' . __( 'Global Content Blocks', 'blox' ) . '</span>',
    						'desc' => '',
    						'type' => 'header'
    					),
    					'global_enable' => array(
    						'id'    => 'global_enable',
    						'name'  => __( 'Enable Global Blocks', 'blox' ),
    						'label' => __( 'Globally enable global content blocks', 'blox' ),
    						'desc'  => __( 'Turning off this setting will disable all global content blocks.', 'blox' ),
    						'type'  => 'checkbox',
    						'default' => true,
                            'sanitize'  => 'checkbox',
    					),
    					'global_permissions' => array(
    						'id'   => 'global_permissions',
    						'name' => __( 'Global Permissions', 'blox' ),
    						'desc' => __( 'Determines what type of user can manage global content blocks.', 'blox' ),
    						'type' => 'select',
    						'options' => array(
    							'manage_options' => __( 'Admins Only', 'blox' ),
    							'publish_pages'  => __( 'Admins and Editors', 'blox' ),
    							'publish_posts'  => __( 'Admins, Editors, and Authors', 'blox' ),
    						),
    						'default' => 'manage_options'
    					),
    					'general_local_header' => array(
    						'id'   => 'general_local_header',
    						'name' => '<span class="title">' . __( 'Local Content Blocks', 'blox' ) . '</span>',
    						'desc' => '',
    						'type' => 'header'
    					),
    					'local_enable' => array(
    						'id'    => 'local_enable',
    						'name'  => __( 'Enable Local Blocks', 'blox' ),
    						'label' => __( 'Globally enable local content blocks', 'blox' ),
    						'desc'  => __( 'Turning off this setting will disable local blocks on all post types.', 'blox' ),
    						'type'  => 'checkbox',
                            'sanitize'  => 'checkbox',
    					),
    					'local_enabled_pages' => array(
    						'id'    => 'local_enabled_pages',
    						'name'  => __( 'Enable Local Blocks On...', 'blox' ),
    						'desc'  => __( 'Enable local blocks on specific post types. Note that only "public" custom post types will be displayed above. Disabling local blocks on a specific post type will not remove any meta data.', 'blox' ),
    						'type'  => 'enabled_pages',
    						'default' => array( 'post', 'page' )
    					),
    					'local_permissions' => array(
    						'id'   => 'local_permissions',
    						'name' => __( 'Local Permissions', 'blox' ),
    						'desc' => __( 'Determines what type of user can manage local content blocks.', 'blox' ),
    						'type' => 'select',
    						'options' => array(
    							'manage_options' => __( 'Admins Only', 'blox' ),
    							'publish_pages'  => __( 'Admins and Editors', 'blox' ),
    							'publish_posts'  => __( 'Admins, Editors, and Authors', 'blox' ),
    						),
    						'default' => 'manage_options'
    					),
                        'local_metabox_title' => array(
                            'id'   => 'local_metabox_title',
                            'name' => __( 'Local Metabox Title', 'blox' ),
                            'desc' => __( 'This is the metabox title that is displayed on pages/posts/custom post types when local blocks are activated.', 'blox' ),
                            'type' => 'text',
                            'size' => 'large',
                            'placeholder' => __( 'e.g. Local Content Blocks', 'blox' ),
                            'default' => __( 'Local Content Blocks', 'blox' ),
                            'sanitize' => 'no_html',
                        ),
                    ),
				)
			),
            /** Content Settings */
			'content' => apply_filters( 'blox_settings_content',
				array(
                    'main' => array(
                        'defaults_position_header' => array(
                            'id'   => 'defaults_position_header',
                            'name' => '<span class="title">' . __( 'Content Defaults', 'blox' ) . '</span>',
                            'desc' => __( 'NEED CONTENT', 'blox' ),
                            'type' => 'header'
                        ),
                        'disable_content_types_local' => array(
                            'id'    => 'disable_content_types_local',
                            'name'  => sprintf( __( 'Disable Content Types: %1$sLocal Blocks%2$s', 'blox' ), '<br><em>', '</em>' ),
                            'desc'  => __( 'Enable local blocks on specific post types. Note that only "public" custom post types will be displayed above. Disabling local blocks on a specific post type will not remove any meta data.', 'blox' ),
                            'type'  => 'disabled_content_types',
                            'default' => array(),
                        ),
                        'disable_content_types_global' => array(
                            'id'    => 'disable_content_types_global',
                            'name'  => sprintf( __( 'Disable Content Types: %1$sGlobal Blocks%2$s', 'blox' ), '<br><em>', '</em>' ),
                            'desc'  => __( 'Enable local blocks on specific post types. Note that only "public" custom post types will be displayed above. Disabling local blocks on a specific post type will not remove any meta data.', 'blox' ),
                            'type'  => 'disabled_content_types',
                            'default' => array(),
                        ),
                    ),

                    'raw' => array(
                        'syntax_highlighting_header' => array(
                            'id'   => 'syntax_highlighting_header',
                            'name' => '<span class="title">' . __( 'Syntax Highlighting', 'blox' ) . '</span>',
                            'desc' => '',
                            'type' => 'header'
                        ),
                        'syntax_highlighting_disable' => array(
                            'id'      => 'syntax_highlighting_disable',
                            'name'    => __( 'Disable Highlighting', 'blox' ),
                            'label'   => __( 'Disable all syntax highlighting', 'blox' ),
                            'desc'    => __( 'Checking this setting will disable syntax highlighting in the raw content fullscreen modal.', 'blox' ),
                            'type'    => 'checkbox',
                            'default' => false
                        ),
                        'syntax_highlighting_theme' => array(
                            'id'   => 'syntax_highlighting_theme',
                            'name' => __( 'Visual Theme', 'blox' ),
                            'desc' => __( 'Choose the visual theme for when syntax highlighting is enabled.', 'blox' ),
                            'type' => 'select',
                            'options' => array(
                                'default'    => __( 'Default (Light)', 'blox' ),
                                'monokai'    => __( 'Monokai (Dark)', 'blox' ),
                                'spacegray'  => __( 'Spacegray (Dark)', 'blox' ),
                            ),
                            'default' => 'default'
                        ),
                    ),

                    'slideshow' => array(
                        'defaults_content_slideshow' => array(
                            'id'   => 'content_slideshow_header',
                            'name' => '<span class="title">' . __( 'Slideshow Defaults', 'blox' ) . '</span>',
                            'desc' => __( 'The slideshow functionality that is natively included with Blox has many settings. Here you can set defualt slideshow settings to ensure consistency with each new slideshow that you create.', 'blox' ),
                            'type' => 'header'
                        ),
                        'builtin_slideshow_animation' => array(
                            'id'    => 'builtin_slideshow_animation',
                            'name'  => __( 'Slideshow Animation', 'blox' ),
                            'label' => __( 'Slideshow Animation', 'blox' ),
                            'desc'  => '',
                            'type'  => 'select',
                            'options' => array(
                                'slide' => __( 'Slide', 'blox' ),
                                'fade'  => __( 'Fade', 'blox' ),
                            ),
                            'default' => 'slide'
                        ),
                        'builtin_slideshow_slideshowSpeed' => array(
                            'id'    => 'builtin_slideshow_slideshowSpeed',
                            'name'  => __( 'Slideshow Speed', 'blox' ),
                            'label' => __( 'Slideshow Speed (milliseconds)', 'blox' ),
                            'desc'  => '',
                            'type'  => 'text',
                            'size'  => 'small',
                            'default' => '7000',
                            'sanitize' => 'absint',
                        ),
                        'builtin_slideshow_animationSpeed' => array(
                            'id'    => 'builtin_slideshow_animationSpeed',
                            'name'  => __( 'Animation Speed', 'blox' ),
                            'label' => __( 'Animation Speed (milliseconds)', 'blox' ),
                            'desc'  => '',
                            'type'  => 'text',
                            'size'  => 'small',
                            'default' => '600',
                            'sanitize' => 'absint',
                        ),
                        'builtin_slideshow_slideshow' => array(
                            'id'    => 'builtin_slideshow_slideshow',
                            'name'  => __( 'Start Automatically', 'blox' ),
                            'label' => __( 'Start Slideshow Automatically', 'blox' ),
                            'desc'  => '',
                            'type'  => 'checkbox',
                            'default' => false,
                            'sanitize' => 'checkbox',
                        ),
                        'builtin_slideshow_animationLoop' => array(
                            'id'    => 'builtin_slideshow_animationLoop',
                            'name'  => __( 'Loop Slideshow', 'blox' ),
                            'label' => __( 'Loop Slideshow', 'blox' ),
                            'desc'  => '',
                            'type'  => 'checkbox',
                            'default' => false,
                            'sanitize' => 'checkbox',
                        ),
                        'builtin_slideshow_pauseOnHover' => array(
                            'id'    => 'builtin_slideshow_pauseOnHover',
                            'name'  => __( 'Pause On Hover', 'blox' ),
                            'label' => __( 'Enable Pause On Hover', 'blox' ),
                            'desc'  => '',
                            'type'  => 'checkbox',
                            'default' => false,
                            'sanitize' => 'checkbox',
                        ),
                        'builtin_slideshow_smoothHeight' => array(
                            'id'    => 'builtin_slideshow_smoothHeight',
                            'name'  => __( 'Slideshow Height Resizing', 'blox' ),
                            'label' => __( 'Enable Slideshow Height Resizing', 'blox' ),
                            'desc'  => '',
                            'type'  => 'checkbox',
                            'default' => false,
                            'sanitize' => 'checkbox',
                        ),
                        'builtin_slideshow_directionNav' => array(
                            'id'    => 'builtin_slideshow_directionNav',
                            'name'  => __( 'Directional Navigation', 'blox' ),
                            'label' => __( 'Disable Directional Navigation (i.e. arrows)', 'blox' ),
                            'desc'  => '',
                            'type'  => 'checkbox',
                            'default' => false,
                            'sanitize' => 'checkbox',
                        ),
                        'builtin_slideshow_controlNav' => array(
                            'id'    => 'builtin_slideshow_controlNav',
                            'name'  => __( 'Control Navigation', 'blox' ),
                            'label' => __( 'Disable Control Navigation (i.e. dots)', 'blox' ),
                            'desc'  => '',
                            'type'  => 'checkbox',
                            'default' => false,
                            'sanitize' => 'checkbox',
                        ),
                        'builtin_slideshow_caption' => array(
                            'id'    => 'builtin_slideshow_caption',
                            'name'  => __( 'Captions', 'blox' ),
                            'label' => __( 'Disable Captions', 'blox' ),
                            'desc'  => '',
                            'type'  => 'checkbox',
                            'default' => false,
                            'sanitize' => 'checkbox',
                        ),
                    ),
                )
            ),

            /** Position Settings */
            'position' => apply_filters( 'blox_settings_position',
				array(
                    'main' => array(
    					'position_enable_hook_positioning' => array(
    						'id'   => 'position_enable_hook_positioning',
    						'name'  => __( 'Enable Hook Positioning', 'blox' ),
    						'label' => __( 'Allow block to be positioned via action hook', 'blox' ),
    						'desc'  => '',
    						'type'  => 'checkbox',
    						'default' => true
    					),
                        'position_enable_shortcode_positioning' => array(
    						'id'   => 'position_enable_shortcode_positioning',
    						'name'  => __( 'Enable Shortcode Positioning', 'blox' ),
    						'label' => __( 'Allow block to be positioned via shortcode', 'blox' ),
    						'desc'  => '',
    						'type'  => 'checkbox',
    						'default' => true
    					),
                        'position_enable_php_positioning' => array(
                            'id'   => 'position_enable_php_positioning',
                            'name'  => __( 'Enable PHP Positioning', 'blox' ),
                            'label' => __( 'Allow block to be positioned via PHP function', 'blox' ),
                            'desc'  => '',
                            'type'  => 'checkbox',
                            'default' => true
                        ),
                        'defaults_position_header' => array(
                            'id'   => 'defaults_position_header',
                            'name' => '<span class="title">' . __( 'Position Defaults', 'blox' ) . '</span>',
                            'desc' => sprintf( __( 'Please refer to the %1$sBlox Documentation%2$s for hook reference. For priority, it is important to note that other plugins and themes can use Genesis Hooks to add content to a page. A low number tells Wordpress to try and add your custom content before all other content using the same Genesis Hook. A larger number will add the content later in the queue. (ex: Early=1, Medium=10, Late=100)', 'blox' ), '<a href="https://www.bloxwp.com/documentation/position-hook-reference/?utm_source=blox&utm_medium=plugin&utm_content=settings-links&utm_campaign=Blox_Plugin_Links" title="' . __( 'Blox Documentation', 'blox' ) . '" target="_blank">', '</a>' ),
                            'type' => 'header'
                        ),
                        'global_default_position' => array(
                            'id'   => 'global_default_position',
                            'name' => __( 'Global Block Position', 'blox' ),
                            'desc' => __( 'Set the default block position for all global content blocks.', 'blox' ),
                            'type' => 'select_hooks',
                            'default' => 'genesis_after_header'
                        ),
                        'global_default_priority' => array(
                            'id'   => 'global_default_priority',
                            'name' => __( 'Global Block Priority', 'blox' ),
                            'desc' => __( 'Set the default block priority for all global content blocks', 'blox' ),
                            'type' => 'text',
                            'size' => 'small',
                            'default' => '15',
                            'sanitize' => 'absint',
                        ),
                        'local_default_position' => array(
                            'id'   => 'local_default_position',
                            'name' => __( 'Local Block Position', 'blox' ),
                            'desc' => __( 'Set the default block position for all local content blocks.', 'blox' ),
                            'type' => 'select_hooks',
                            'default' => 'genesis_after_header'
                        ),
                        'local_default_priority' => array(
                            'id'   => 'local_default_priority',
                            'name' => __( 'Local Block Priority', 'blox' ),
                            'desc' => __( 'Set the default block priority for all local content blocks', 'blox' ),
                            'type' => 'text',
                            'size' => 'small',
                            'default' => '15',
                            'sanitize' => 'absint',
                        ),
                    ),
                    'custom_hooks' => array(
                        'custom_hook_control_header' => array(
                            'id'   => 'custom_hook_control_header',
                            'name' => '<span class="title">' . __( 'Custom Hook Control', 'blox' ) . '</span>',
                            'desc' => __( 'The following settings allow you add Custom Hooks that may not be natively supported by Blox. Many theme frameworks and plugins have their own hooks, or you might have a few of your own. Enter them here so that Blox can target them.', 'blox' ),
                            'type' => 'header'
                        ),
                        'custom_hooks_disable' => array(
                            'id'       => 'custom_hooks_disable',
                            'name'     => __( 'Disable Custom Hooks', 'blox' ),
                            'label'    => __( 'Disable Custom Hooks for all block positioning', 'blox' ),
                            'desc'     => __( 'When you disable Custom Hooks, they will no longer appear in the hook selector on the Postion tab of each block. If you have no use for Custom Hooks, disabling them simplifies the hook selector for users.', 'blox' ),
                            'type'     => 'checkbox',
                            'default'  => 0,
                            'sanitize' => 'checkbox',
                        ),
                        'default_custom_hooks' => array(
                            'id'       => 'default_custom_hooks',
                            'name'     => __( 'Add Custom Hooks', 'blox' ),
                            'desc'     => '',
                            'type'     => 'custom_hooks',
                            'sanitize' => 'hooks',
                        ),
                    ),
                    'genesis_hooks' => array(
                        'hook_control_header' => array(
    						'id'   => 'hook_control_header',
    						'name' => '<span class="title">' . __( 'Hook Control', 'blox' ) . '</span>',
    						'desc' => __( 'By default, Blox allows you to choose from over 50 Genesis hooks. Here you can pick and choose the ones you want to use, rename the hooks, or even add your own custom hooks to use with a third-party Genesis theme or plugin.', 'blox' ),
    						'type' => 'header'
    					),
    					'genesis_hooks' => array(
    						'id'       => 'genesis_hooks',
    						'name'     => __( 'Genesis Hooks', 'blox' ),
    						'desc'     => '',
    						'type'     => 'hooks',
    						'sanitize' => 'hooks',
    					),
                    ),
				)
			),

			/** Style Settings */
			'style' => apply_filters( 'blox_settings_styles',
				array(
                    'main' => array(
    					'global_custom_classes' => array(
    						'id'   => 'global_custom_classes',
    						'name' => __( 'Global Custom Classes', 'blox' ),
    						'desc' => __( 'Enter a space separated list of custom CSS classes to add to all global blocks.', 'blox' ),
    						'type' => 'text',
    						'size' => 'full',
    						'placeholder' => __( 'e.g. class-one class-two', 'blox' ),
    						'default' => '',
    						'sanitize' => 'no_html',
    					),
    					'local_custom_classes' => array(
    						'id'   => 'local_custom_classes',
    						'name' => __( 'Local Custom Classes', 'blox' ),
    						'desc' => __( 'Enter a space separated list of custom CSS classes to add to all local blocks.', 'blox' ),
    						'type' => 'text',
    						'size' => 'full',
    						'placeholder' => __( 'e.g. class-one class-two', 'blox' ),
    						'default' => '',
    						'sanitize' => 'no_html',
    					),
    					'custom_css' => array(
    						'id'   => 'custom_css',
    						'name' => __( 'Custom CSS', 'blox' ),
    						'desc' => sprintf( __( 'Add custom CSS that can affect all content blocks. For reference on content block frontend markup, please refer to the %1$sBlox Documentation%2$s.', 'blox' ), '<a href="https://www.bloxwp.com/documentation/frontend-markup/?utm_source=blox&utm_medium=plugin&utm_content=settings-links&utm_campaign=Blox_Plugin_Links" title="' . __( 'Blox Documentation', 'blox' ) . '" target="_blank">', '</a>' ),
    						'type' => 'textarea',
    						'class' => 'blox-textarea-code',
    						'size' => 10,
    						'default' => '',
    					),
    					'disable_default_css' => array(
    						'id'    => 'disable_default_css',
    						'name'  => __( 'Disable Default CSS', 'blox' ),
    						'label' => __( 'Globally disable all default styles', 'blox' ),
    						'desc'  => __( 'Blox includes default CSS to provide minimal block styling. If this option is left un-checked, default CSS can be disabled on each individual content block as needed.', 'blox' ),
    						'type'  => 'checkbox',
    						'default' => '',
    					),
                    ),
				)
			),

			/** Misc Settings */
			'misc' => apply_filters( 'blox_settings_misc',
				array(
                    'main' => array(
                        'other_header' => array(
                            'id'   => 'other_header',
                            'name' => '<span class="title">' . __( 'Additional Settings', 'blox' ) . '</span>',
                            'desc' => '',
                            'type' => 'header'
                        ),
    					'uninstall_on_delete' => array(
    						'id'    => 'uninstall_on_delete',
    						'name'  => __( 'Remove Data on Uninstall', 'blox' ),
    						'label' => __( 'Check to completely remove all plugin data when Blox is deleted', 'blox' ),
    						'desc'  => '',
    						'type'  => 'checkbox',
    						'default' => '',
    					),
                    ),
				)
			),
		);

		return apply_filters( 'blox_registered_settings', $blox_settings );
	}


    /**
     * Retrieve the array of plugin settings with the grouped settings appended NOT DONE!!!!
     *
     * @since 1.3.0
     *
     * @return array of degrouped settings
    */
    public function get_registered_settings_degrouped( $blox_settings ) {

        // Loop through each tab looking for grouped settings
        foreach ( $blox_settings as $tab_name => $tab ) {

            $degrouped_settings = array();

            foreach ( $tab as $key ) {

                // Get our grouped settings
                if ( ! empty( $key['type'] ) && $key['type'] === 'group' ) {
                    if ( ! empty( $key['settings'] ) && is_array( $key['settings'] ) ) {
                        $degrouped_settings = array_merge( $degrouped_settings, $key['settings'] );
                    }
                }
            }

            // Merge degrouped settings back onto the tab where they belong
            $blox_settings[$tab_name] = array_merge( $blox_settings[$tab_name], $degrouped_settings );
        }

        // Return new degrouped list of settings.
        return $blox_settings;
    }


	/**
	 * Settings Sanitization
	 *
	 * Adds a settings error (for the updated message)
	 * At some point this will validate input
	 *
	 * @since 1.0.0
	 *
	 * @param array $input The value inputted in the field
	 *
	 * @return string $output Sanitizied value
	 */
	public function settings_sanitize( $input = array() ) {

		global $blox_options;

        $doing_section = false;
        if ( ! empty( $_POST['_wp_http_referer'] ) ) {
            $doing_section = true;
        }

		if ( empty( $blox_options ) ) {
			$blox_options = array();
		}

        $sanitization_types = $this->get_registered_settings_sanitization_types();
        $input              = $input ? $input : array();

        if ( $doing_section ) {

            parse_str( $_POST['_wp_http_referer'], $referrer ); // Pull out the tab and section
            $tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
            $section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

            $sanitization_types = $this->get_registered_settings_sanitization_types( $tab, $section );

            // Run a general sanitization for the tab for special fields
            $input = apply_filters( 'blox_settings_' . $tab . '_sanitize', $input );

            // Run a general sanitization for the section so custom tabs with sub-sections can save special data
            $input = apply_filters( 'blox_settings_' . $tab . '-' . $section . '_sanitize', $input );
        }

        // Merge our new settings with the existing
        $output = array_merge( $blox_options, $input );

        foreach ( $sanitization_types as $key => $sanitization_type ) {

            if ( empty( $sanitization_type ) ) {
                continue;
            }

            if ( array_key_exists( $key, $output ) ) {

                // Check if santization method exists, if so, run sanitization.
                // This is only for special/complicated sanitizations.
                if ( method_exists( $this, $sanitization_type) ) {
                    $output[ $key ] = $this->$sanitization_type( $output[ $key ] );
                }
            }

            if ( $doing_section ) {
                switch( $sanitization_type ) {
                    case 'checkbox':
                        if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) || ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $input ) ) ) {
                            unset( $output[ $key ] );
                        }
                        break;
                    case 'safe_html':
                		$output[ $key ] = wp_kses_post( $output[ $key ] );
                	    break;
                    case 'no_html':
                        $output[ $key ] = strip_tags( $output[ $key ] );
                        break;
                    case 'absint':
                        $output[ $key ] = absint( $output[ $key ] );
                        break;
                    default:
                        // NEED NEW DEFAULT
                        if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) || ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $input ) ) ) {
                            unset( $output[ $key ] );
                        }
                        break;
                }
            } else {
                if ( empty( $input[ $key ] ) ) {
                    unset( $output[ $key ] );
                }
            }
        }

        /*
		// Loop through the whitelist and unset any that are empty for the tab being saved
		if ( ! empty( $settings[$tab] ) ) {
			foreach ( $settings[$tab] as $key => $value ) {
				if ( empty( $input[$key] ) ) {
					unset( $blox_options[$key] );
				}
			}
		}
        */


        if ( $doing_section ) {
            add_settings_error( 'blox-notices', '', __( 'Settings updated.', 'blox' ), 'updated' );
        }

        return $output;
	}


    /**
     * Flattens the set of registered settings and their sanitization type so we can easily sanitize all the settings
     * in a much cleaner set of logic
     *
     * @since 2.0.0
     *
     * @param $filtered_tab bool|string     A tab to filter setting types by.
     * @param $filtered_section bool|string A section to filter setting types by.
     * @return array Key is the setting ID, value is the type of sanitization that needs to be applied
     */
    function get_registered_settings_sanitization_types( $filtered_tab = false, $filtered_section = false ) {

        $settings           = $this->get_registered_settings();
    	$sanitization_types = array();

    	foreach ( $settings as $tab_id => $tab ) {

    		if ( false !== $filtered_tab && $filtered_tab !== $tab_id ) {
    			continue;
    		}

    		foreach ( $tab as $section_id => $section_or_setting ) {

    			// See if we have a setting registered at the tab level for backwards compatibility
    			if ( is_array( $section_or_setting ) && array_key_exists( 'sanitize', $section_or_setting ) ) {
    				$ssanitization_types[ $section_or_setting['id'] ] = $section_or_setting['sanitize'];
    				continue;
    			}

    			if ( false !== $filtered_section && $filtered_section !== $section_id ) {
    				continue;
    			}

    			foreach ( $section_or_setting as $section => $section_settings ) {

                    // Check to make sure the setting has a sanitize parameter
                    if ( is_array( $section_settings ) && array_key_exists( 'sanitize', $section_settings ) ) {
    				    $sanitization_types[ $section_settings['id'] ] = $section_settings['sanitize'];
                        continue;
        			}
    			}
    		}
    	}

    	return $sanitization_types;
    }


	/***********************************************
	 * Setting type callbacks
	 ***********************************************/


	/**
	 * Missing Callback. If a function is missing for settings callbacks alert the user.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments passed by the setting
	 * @return void
	 */
	public function missing_callback( $args ) {
		printf( __( 'The callback function used for the <strong>%s</strong> setting seems to be missing...', 'blox' ), $args['id'] );
	}


	/**
	 * Header Callback.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments passed by the setting
	 * @return void
	 */
	public function header_callback( $args ) {

		if ( empty( $args['desc'] ) ) {
			echo '<hr/>';
		} else {
			$html = '<div class="header-container"><hr/>';
			$html .= '<p class="description" style="padding-top:5px;">' . $args['desc'] . '</p>';
			$html .= '</div>';

			echo $html;
		}
	}


    /**
     * Group Container Callback.
     *
     * @since 1.3.0
     *
     * @param array $args Arguments passed by the setting
     * @return void
     */
    public function group_callback( $args ) {

        echo '<div class="blox-grouped-settings">';

        if ( ! empty( $args['settings'] ) ) {

            foreach ( $args['settings'] as $setting_args ) {

				$name     = isset( $setting_args['name'] ) ? $setting_args['name'] : '';
				$callback = method_exists( __CLASS__, $setting_args['type'] . '_callback' ) ? array( $this, $setting_args['type'] . '_callback' ) : array( $this, 'missing_callback' );

                // Run the setting callback function
                call_user_func( $callback, $setting_args );
            }
        }

        echo '</div>';
    }


	/**
	 * Checkbox Callback. Renders checkbox fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args    Arguments passed by the setting
	 * @global $blox_options Array of all the Blox settings
	 * @return void
	 */
	public function checkbox_callback( $args ) {

		global $blox_options;

        $id    = 'blox_settings[' . $args['id'] . ']';
        $name  = 'blox_settings[' . $args['id'] . ']';
        $value = isset( $blox_options[ $args['id'] ] ) ? $blox_options[ $args['id'] ] : '';

        $class = ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '';
        $label = ! empty( $args['label'] ) ? ( '<span class="label"> ' . $args['label'] . '</span>' ) : '';
        $desc  = ! empty( $args['desc'] ) ? ( '<p class="description">' . $args['desc'] . '</p>' ) : '';

		$checked = isset( $value ) ? checked( 1, esc_attr( $value ), false ) : '';

		$html  = '<label class="' . $class. '">';
        $html .= '<input type="checkbox" id="' . $id . '" name="' . $name . '" value="1" ' . $checked . '/> ';
        $html .= $label;
        $html .= '</label>';
		$html .= $desc;

		echo $html;
	}


	/**
	 * Text Callback. Renders text fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args   Arguments passed by the setting
	 * @global $blox_options Array of all the Blox settings
	 * @return void
	 */
	public function text_callback( $args ) {

		global $blox_options;

        $id    = 'blox_settings[' . $args['id'] . ']';
        $name  = 'blox_settings[' . $args['id'] . ']';
        $value = isset( $blox_options[ $args['id'] ] ) ? $blox_options[ $args['id'] ] : '';

        $class = ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '';
        $label = ! empty( $args['label'] ) ? ( '<span class="label"> '  . $args['label'] . '</span>' ) : '';
        $desc  = ! empty( $args['desc'] ) ? ( '<p class="description">' . $args['desc'] . '</p>' ) : '';
        $size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
        $placeholder = ! empty( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '';

        // The second check is to correct for absint text boxes
		if ( empty( $value ) && $value != 0 ) {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

        $html  = '<label class="' . $class. '">';
		$html .= '<input type="text" class="text-' . $size . '" id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
		$html .= $label;
        $html .= '</label>';
        $html .= $desc;

		echo $html;
	}


	/**
	 * Textarea Callback. Renders textarea fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args   Arguments passed by the setting
	 * @global $blox_options Array of all the Blox settings
	 * @return void
	 */
	public function textarea_callback( $args ) {

		global $blox_options;

        $id    = 'blox_settings[' . $args['id'] . ']';
        $name  = 'blox_settings[' . $args['id'] . ']';
        $value = isset( $blox_options[ $args['id'] ] ) ? $blox_options[ $args['id'] ] : '';

        $class = ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '';
        $desc  = ! empty( $args['desc'] ) ? ( '<p class="description">' . $args['desc'] . '</p>' ) : '';
        $size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 6;
        $placeholder = ! empty( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '';

		if ( empty( $value ) ) {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

		$html = '<textarea class="text-full ' . $class . '" rows="'  . $size . '" id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
		$html .= $desc;

		echo $html;
	}


	/**
	 * Select Callback. Renders select fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args    Arguments passed by the setting
	 * @global $blox_options Array of all the Blox settings
	 * @return void
	 */
	public function select_callback( $args ) {

		global $blox_options;

        $id    = 'blox_settings[' . $args['id'] . ']';
        $name  = 'blox_settings[' . $args['id'] . ']';
        $value = isset( $blox_options[ $args['id'] ] ) ? $blox_options[ $args['id'] ] : '';

        $class = ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '';
        $label = ! empty( $args['label'] ) ? ( '<span class="label"> '  . $args['label'] . '</span>' ) : '';
        $desc  = ! empty( $args['desc'] ) ? ( '<p class="description">' . $args['desc'] . '</p>' ) : '';

		if ( empty( $value ) ) {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

        $html  = '<label class="' . $class. '">';
		$html .= '<select id="' . $id . '" name="' . $name . '" />';

		foreach ( $args['options'] as $option => $name ) {
			$selected = selected( $option, esc_attr( $value ), false );
			$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
		}
        $html .= '</select>';
        $html .= $label;
        $html .= '</label>';
        $html .= $desc;

		echo $html;
	}


	/**
	 * Select Genesis Hooks Callback. Renders select fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args    Arguments passed by the setting
	 * @global $blox_options Array of all the Blox settings
	 * @return void
	 */
	public function select_hooks_callback( $args ) {

		global $blox_options;

		$instance        = Blox_Common::get_instance();
		$hooks           = $instance->get_genesis_hooks();
		$available_hooks = $instance->get_genesis_hooks_flattened();

		if ( isset( $blox_options[ $args['id'] ] ) ) {
			$value = $blox_options[ $args['id'] ];
		} else {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

		$html = '<select id="blox_settings[' . $args['id'] . ']" name="blox_settings[' . $args['id'] . ']" />';

		foreach ( $hooks as $sections => $section ) {
			$html .= '<optgroup label="' . $section['name'] . '">';
			foreach ( $section['hooks'] as $hooks => $hook ) {
				$selected = selected( $hooks, esc_attr( $value ), false );
				$html .= '<option value="' . $hooks . '" ' . $selected . '>' . $hook['name'] . '</option>';
			}
			$html .= '</optgroup>';
		}

		$html .= '</select>';
		$html .= ! empty( $args['desc'] ) ? ( '<p class="description">' . $args['desc'] . '</p>' ) : '';

		echo $html;

		// Print error if the saved hook is no longer available for some reason
		if ( ! array_key_exists( $value, $available_hooks ) ) {
			echo '<div class="blox-alert">' . sprintf( __( 'The current saved hook is no longer available. Choose a new one, or re-enable it on the %1$sHooks%2$s settings page.', 'blox' ), '<a href="' . admin_url( '/edit.php?post_type=blox&page=blox-settings&tab=hooks' ) . '">', '</a>' ) . '</div>';
		}
	}


	/**
	 * Enabled Pages Callback. Renders checkbox fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args    Arguments passed by the setting
	 * @global $blox_options Array of all the Blox settings
	 * @return void
	 */
	public function enabled_pages_callback( $args ) {

		global $blox_options;

		// Array of all enabled page types
		$enabled_pages = isset( $blox_options[ $args['id'] ] ) ? $blox_options[ $args['id'] ] : false;

		?>
		<label>
			<input type="checkbox" name="blox_settings[<?php echo $args['id']; ?>][]" value="page" <?php echo $enabled_pages && in_array( 'page', $enabled_pages ) ? 'checked="checked"' : ''; ?> />
			<?php _e( 'Pages', 'blox' ); ?>
		</label>
		<label>
			<input type="checkbox" name="blox_settings[<?php echo $args['id']; ?>][]" value="post" <?php echo $enabled_pages && in_array( 'post', $enabled_pages ) ? 'checked="checked"' : ''; ?> />
			<?php _e( 'Posts', 'blox' ); ?>
		</label>

		<?php
		// Get all custom post types in an array by name
		$custom_post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'names', 'and' );

		if ( ! empty( $custom_post_types ) ) {
			// Display checkbox for all available custom post types
			foreach ( $custom_post_types as $custom_post_type ) {
				// Get the full post object
				$post_name = get_post_type_object( $custom_post_type );
				?>
				<label>
					<input type="checkbox" name="blox_settings[<?php echo $args['id']; ?>][]" value="<?php echo $custom_post_type; ?>" <?php echo $enabled_pages && in_array( $custom_post_type, $enabled_pages ) ? 'checked="checked"' : ''; ?> />
					<?php echo $post_name->labels->name; ?> <span class="status">(<?php _e( 'custom', 'blox' ); ?>)</span>
				</label>
				<?php
			}
		}

		$description = ! empty( $args['desc'] ) ? ( '<p class="description" style="margin-top:15px;">' . $args['desc'] . '</p>' ) : '';
		echo $description;
	}


    /**
     * Disable content types callback. Renders checkbox fields.
     *
     * @since 2.0.0
     *
     * @param array $args    Arguments passed by the setting
     * @global $blox_options Array of all the Blox settings
     * @return void
     */
    public function disabled_content_types_callback( $args ) {

        global $blox_options;

        // Array of all disabled content types
        $disabled_content_types = isset( $blox_options[ $args['id'] ] ) ? $blox_options[ $args['id'] ] : false;

        $content_types = $this->get_content_types();

        if ( ! empty( $content_types ) ) {
            // Display checkbox for all available custom post types
            foreach ( $content_types as $content_type => $content_type_name ) {

                ?>
                <label>
                    <input type="checkbox" name="blox_settings[<?php echo $args['id']; ?>][]" value="<?php echo $content_type; ?>" <?php echo $disabled_content_types && in_array( $content_type, $disabled_content_types ) ? 'checked="checked"' : ''; ?> />
                    <?php echo $content_type_name; ?>
                </label>
                <?php
            }
        }

        $description = ! empty( $args['desc'] ) ? ( '<p class="description" style="margin-top:15px;">' . $args['desc'] . '</p>' ) : '';
        echo $description;
    }


	/**
	 * Hooks callback
	 *
	 * @since 1.1.0
	 *
	 * @global $blox_options Array of all the Blox settings
	 * @return void
	 */
	public function hooks_callback( $args ) {

		global $blox_options;

		if ( isset( $blox_options[ $args['id'] ] ) ) {
			$hooks = $blox_options[ $args['id'] ];
		} else {
			// Defaults
            $hook_type = strstr( $args['id'], '_', true );
            $all_hooks = $this->get_hooks();
			$hooks     = $all_hooks[ $hook_type ];
		}
        //echo $args['id'];
        //echo print_r($hooks);
		?>

    <!-- TO REMOVE
        <div id="default_hook_enable">
			<label><input type="checkbox" name="blox_settings[<?php echo $args['id']; ?>][enable]" value="1" <?php echo isset( $value['enable'] ) ? checked( 1, esc_attr( $value['enable'] ), false ) : '';?> /><?php _e( 'Limit Available Genesis Hooks', 'blox' );?></label>
		</div>
		<p class="description"><?php printf( __( 'This setting allows you to limit the number of Genesis hooks that are available and also rename them to improve UI. When enabling this option, any existing blocks using hooks that are not enabled will cease to display on the frontend. %1$sCheck the hooks you want to enable%2$s.', 'blox' ), '<strong>', '</strong>' );?></p>
    -->

        <div id="default_hook_settings">
		<?php
		foreach ( $hooks as $section_slug => $section ) {
			?>
			<div class="hook-section-title">
				<?php

				echo $section['name'];

				$section_title_name    = 'blox_settings[' . $args['id'] . '][' . $section_slug . '][name]';
				$section_title_value   = isset( $section['name'] ) ? esc_attr( $section['name'] ) : __( 'Missing Section Name', 'blox' );
                $section_disable_name  = 'blox_settings[' . $args['id'] . '][' . $section_slug . '][disable]';
    			$section_disable_value = isset( $section['disable'] ) ? checked( 1, esc_attr( $section['disable'] ), false ) : '';
				?>

				<input class="" type="text" name="<?php echo $section_title_name; ?>" placeholder="<?php _e( 'Enter a section name', 'blox' ); ?>" value="<?php echo $section_title_value; ?>" />
                <input class="" type="checkbox" name="<?php echo $section_disable_name; ?>" value="1" <?php echo $section_disable_value; ?>/>

                EDIT BUTTON HERE
			</div>

            <div class="blox-hook-table">
                <div class="row title-row">
                    <div class="hook-disable"><?php _e( 'Disable', 'blox' ); ?></div>
                    <div class="hook-slug"><?php _e( 'Hook', 'blox' ); ?></div>
                    <div class="hook-name"><?php _e( 'Hook Name', 'blox' ); ?></div>
                    <div class="hook-desc"><?php _e( 'Hook Description', 'blox' ); ?></div>
                </div>
				<?php
				foreach ( $section['hooks'] as $hooks => $hook ) {

                    $hook_disable_name  = 'blox_settings[' . $args['id'] . '][' . $section_slug . '][hooks][' . $hooks . '][disable]';
					$hook_disable_value = isset( $hook['disable'] ) ? checked( 1, esc_attr( $hook['disable'] ), false ) : '';
					$hook_name_name     = 'blox_settings[' . $args['id'] . '][' . $section_slug . '][hooks][' . $hooks . '][name]';
					$hook_name_value    = isset( $hook['name'] ) ? esc_attr( $hook['name'] ) : '';
					$hook_title_name    = 'blox_settings[' . $args['id'] . '][' . $section_slug . '][hooks][' . $hooks . '][title]';
					$hook_title_value   = isset( $hook['title'] ) ? esc_attr( $hook['title'] ) : '';


                    ?>
                    <div class="row hook-row">
                        <div class="hook-disable"><input type="checkbox" name="<?php echo $hook_disable_name; ?>" value="1" <?php echo $hook_disable_value; ?>/></div>
                        <div class="hook-slug"><span><?php echo $hooks; ?></span></div>
                        <div class="hook-name"><input class="hook-name" type="text" name="<?php echo $hook_name_name; ?>"  placeholder="<?php echo $hooks; ?>" value="<?php echo $hook_name_value; ?>" /></div>
                        <div class="hook-desc">
                            <span><?php echo $hook_title_value; ?></span>
                            <textarea class="hook-title blox-force-hidden" rows="1" name="<?php echo $hook_title_name; ?>" ><?php echo $hook_title_value; ?></textarea>
                        </div>
                    </div>
                    <?php
				}
				?>
            </div>

            <div class="blox-hook-tools">
                <a class="blox-hook-disable-all" href="#"><?php _e( 'Disable All', 'blox' ); ?></a> | <a class="blox-hook-enable-all" href="#"><?php _e( 'Enable All', 'blox' ); ?></a>
            </div>
            <p class="description">
                <?php _e( 'Please note that the Hook Name cannot contain HTML.', 'blox' ); ?>
            </p>
		<?php } ?>
		</div>
		<?php
	}



	/**
	 * Custom Hooks callback
	 *
	 * @since 1.1.0
	 *
	 * @global $blox_options Array of all the Blox settings
	 * @return void
	 */
	public function custom_hooks_callback( $args ) {

		global $blox_options;

		if ( isset( $blox_options[ $args['id'] ] ) ) {
			$value = $blox_options[ $args['id'] ];
		} else {
			// Defaults
			$value = array();
		}

		?>
		<div class="add-custom-button">
			<input type="text" class="custom-hook-entry" placeholder="<?php _e( 'Enter hook slug', 'blox' ); ?>" value="" /><a class="button button-secondary"><?php _e( 'Add Custom Hook', 'blox' ); ?></a>
			<p class="description"><?php _e( 'The hook slug can only be made up of letters, numbers, dashes and underscores.', 'blox' );?></p>
		</div>
		<div class="hook-section-title">
			<?php
            // NEED TO UPDATE ONCE MULTI SECTION IS ENABLED
            $section_title_name    = 'blox_settings['. $args['id'] . '][custom][name]';
            $section_title_value   = __( 'Custom Hooks', 'blox' );
			$section_disable_name  = 'blox_settings['. $args['id'] . '][custom][disable]';
			$section_disable_value = isset( $value['custom']['disable'] ) ? checked( 1, esc_attr( $value['custom']['disable'] ), false ) : '';

            echo $section_title_value;
			?>
			<input class="blox-force-hidden" type="text" name="<?php echo $section_title_name; ?>" value="<?php echo $section_title_value; ?>" />
            <input class="blox-force-hidden" type="checkbox" name="<?php echo $section_disable_name; ?>" value="1" <?php echo $section_disable_value; ?>/>
		</div>
		<div id="default_custom_hook_settings">
            <div class="blox-hook-table custom">
                <div class="row title-row">
                    <div class="hook-disable"><?php _e( 'Disable', 'blox' ); ?></div>
                    <div class="hook-slug"><?php _e( 'Hook', 'blox' ); ?></div>
                    <div class="hook-name"><?php _e( 'Hook Name', 'blox' ); ?></div>
                    <div class="hook-desc"><?php _e( 'Hook Description', 'blox' ); ?></div>
                    <div class="hook-delete"><?php _e( 'Delete', 'blox' ); ?></div>
                </div>
                <?php
                $custom_hooks = isset( $value['custom'] ) ? $value['custom'] : array( 'hooks' => array() );

                if ( ! empty( $custom_hooks['hooks'] ) ) {
                    foreach ( $custom_hooks['hooks'] as $hooks => $hook ) {

                        $hook_disable_name = 'blox_settings[' . $args['id'] . '][custom][hooks][' . $hooks . '][disable]';
						$hook_disable_value = isset( $hook['disable'] ) ? checked( 1, esc_attr( $hook['disable'] ), false ) : '';
						$hook_name_name	    = 'blox_settings[' . $args['id'] . '][custom][hooks][' . $hooks . '][name]';
						$hook_name_value    = isset( $hook['name'] ) ? esc_attr( $hook['name'] ) : '';
						$hook_title_name    = 'blox_settings[' . $args['id'] . '][custom][hooks][' . $hooks . '][title]';
						$hook_title_value   = isset( $hook['title'] ) ? esc_attr( $hook['title'] ) : '';
                        ?>
                        <div class="row hook-row">
                            <div class="hook-disable"><input type="checkbox" name="<?php echo $hook_disable_name; ?>" value="1" <?php echo $hook_disable_value; ?>/></div>
                            <div class="hook-slug"><span><?php echo $hooks; ?></span></div>
                            <div class="hook-name"><input class="hook-name" type="text" name="<?php echo $hook_name_name; ?>"  placeholder="<?php echo $hooks; ?>" value="<?php echo $hook_name_value; ?>" /></div>
                            <div class="hook-desc"><textarea class="hook-title" rows="1" name="<?php echo $hook_title_name; ?>" ><?php echo $hook_title_value; ?></textarea></div>
                            <div class="hook-delete"><a class="blox-custom-hook-delete dashicons right" href="#" title="<?php _e( 'Delete Hook', 'blox' );?>"></a></div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="blox-no-custom-hooks">' . __( 'Add a custom hook...', 'blox' ) . '</div>';
                }
                ?>
            </div>
            <div class="blox-hook-tools">
                <a class="blox-hook-disable-all" href="#"><?php _e( 'Disable All', 'blox' ); ?></a> | <a class="blox-hook-enable-all" href="#"><?php _e( 'Enable All', 'blox' ); ?></a>
                <a class="blox-hook-delete-all" href="#"><?php _e( 'Delete All', 'blox' ); ?></a>
            </div>
            <p class="description">
                <?php _e( 'Please note that the Hook Name and Hook Description cannot contain HTML.', 'blox' ); ?>
            </p>
		</div>
		<?php
	}


	/***********************************************
	 * Sanitization type callbacks
	 ***********************************************/


	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in
	 * @return integer 1 or 0.
	 */
	function one_zero( $new_value ) {
		return (int) (bool) $new_value;
	}


	/**
	 * Makes URLs safe
	 *
	 * @since 1.9.0
	 *
	 * @param string $new_value String, a URL, possibly unsafe
	 * @return string String a safe URL
	 */
	function url( $new_value ) {
		return esc_url_raw( $new_value );
	}


	/**
	 * Makes Email Addresses safe, via sanitize_email()
	 *
	 * @since 2.1.0
	 *
	 * @param string $new_value String, an email address, possibly unsafe
	 * @return string String a safe email address
	 */
	function email_address( $new_value ) {
		return sanitize_email( $new_value );
	}

	/**
	 * Removes HTML tags from all custom hook names
	 *
	 * @since 1.1.0
	 *
	 * @param array $new_value Array of all custom hook data
	 * @return array Array of all custom hook data without tags in it
	 */
	function hooks( $new_value ) {

		$available_hooks = isset( $new_value ) ? $new_value : false;
        $sanitized_hooks = array();

        if ( $available_hooks ) {
			foreach ( $available_hooks as $sections => $section ) {

                // Sanitize hook section slugs (only letters, number, dash, underscore)
                $sections = preg_replace( '/[^ \w \-]/', '', $sections );

                $sanitized_hooks[$sections] = array(
                    'disable' => isset( $section['disable'] ) ? esc_attr( $hook['disable'] ) : false,
                    'name'    => strip_tags( $section['name'] ),
                    'hooks'   => array(),
                );

				if ( isset( $section['hooks'] ) ) {
					foreach ( $section['hooks'] as $hooks => $hook ) {

						// Sanatize hook slugs (only letters, number, dash, underscore)
						$hooks = preg_replace( '/[^ \w \-]/', '', $hooks );

						$sanitized_hooks[$sections]['hooks'][$hooks] = array(
							'disable' => isset( $hook['disable'] ) ? esc_attr( $hook['disable'] ) : false,
							'name'    => strip_tags( $hook['name'] ),
							'title'   => esc_attr( $hook['title'] ),
						);
					}
				}
			}

			$new_value = $sanitized_hooks;
		}

		return $new_value;
	}


    /**
     * Helper method for retrieving all available hooks.
     *
     * @since 1.1.0
     *
     * @return array Array of all available hooks.
     */
    public function get_hooks() {

        $instance = Blox_Common::get_instance();
        return $instance->get_hooks();
    }


	/**
     * Helper method for retrieving all Genesis hooks.
     *
     * @since 1.1.0
     *
     * @return array Array of all Genesis hooks.
     */
    public function get_genesis_hooks_unfiltered() {

        $instance = Blox_Common::get_instance();
        return $instance->get_genesis_hooks_unfiltered();
    }


    /**
     * Helper method for retrieving all content.
     *
     * @since 2.0.0
     *
     * @return array Array of all Genesis hooks.
     */
    public function get_content_types() {

        $instance = Blox_Common::get_instance();
        return $instance->get_content_types();

    }


	/**
	 * Enqueue scripts and styles
	 *
	 * @since 1.0.0
	 */
	function admin_enqueue_scripts() {
		wp_enqueue_style( $this->base->plugin_slug . '-settings-styles', plugins_url( 'assets/css/settings.css' , dirname( dirname( __FILE__ ) ) ) ); // Need to us dirname twice due to file format of parent plugin

		wp_register_script( $this->base->plugin_slug . '-settings-scripts', plugins_url( 'assets/js/settings.js' , dirname( dirname( __FILE__ ) ) ) );
		wp_enqueue_script( $this->base->plugin_slug . '-settings-scripts' );

		wp_localize_script(
			$this->base->plugin_slug . '-settings-scripts',
			'blox_localize_settings_scripts',
			array(
				'custom_hook_title'        => __( 'Enter a hook name', 'blox' ),
				'delete_hook'              => __( 'Delete', 'blox' ),
				'confirm_delete_hook'      => __( 'Are you sure you want to delete this custom hook? This action cannot be undone.', 'blox' ),
                'confirm_delete_all_hooks' => __( 'Are you sure you want to delete all custom hooks? This action cannot be undone.', 'blox' ),
				'no_hooks'			       => __( 'Add a custom hook...', 'blox' ),
			)
		);
	}


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Settings ) ) {
            self::$instance = new Blox_Settings();
        }

        return self::$instance;
    }
}
// Load the settings class.
$blox_settings = Blox_Settings::get_instance();

// Create the Blox options global variable
global $blox_options;

// Set the $blox_options
$blox_options = blox_get_settings();


/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0.0
 * @return array All Blox settings
 */
function blox_get_settings() {

	$settings = get_option( 'blox_settings' );

	return apply_filters( 'blox_get_settings', $settings );
}


/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.0.0
 * @return mixed
 */
function blox_get_option( $key = '', $default = false ) {
	global $blox_options;
	$value = ! empty( $blox_options[ $key ] ) ? $blox_options[ $key ] : $default;
	$value = apply_filters( 'blox_get_option', $value, $key, $default );
	return apply_filters( 'blox_get_option_' . $key, $value, $key, $default );
}
