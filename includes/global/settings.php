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
		$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->get_settings_tabs() ) ? $_GET['tab'] : 'general';

		ob_start();
		?>
		<div class="wrap">
			<h2><?php _e( 'Blox Settings', 'blox' ); ?></h2>
		
			<?php settings_errors( 'blox-notices' ); ?>
		
			<h2 class="nav-tab-wrapper">
				<?php foreach( $this->get_settings_tabs() as $tab_id => $tab_name ) {
				
					$tab_url = add_query_arg( array(
						'settings-updated' => false,
						'tab' => $tab_id
					) );

					$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

					echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
						echo esc_html( $tab_name );
					echo '</a>';
				}
				?>
			</h2>
			<div id="tab_container">
				<form method="post" action="options.php">
					<?php do_action( 'blox_settings_form_top', $active_tab ); ?>
					<table class="form-table">
						<?php
						settings_fields( 'blox_settings' );
						do_settings_fields( 'blox_settings_' . $active_tab, 'blox_settings_' . $active_tab );
						?>
					</table>
					<?php do_action( 'blox_settings_form_bottom', $active_tab ); ?>
					<?php 
						submit_button( sprintf( __( 'Save %1$s Settings', 'blox' ), ucfirst( $active_tab ) ) ); 
						submit_button( sprintf( __( 'Reset %1$s Settings', 'blox' ), ucfirst( $active_tab ) ), 'secondary', 'reset', true, array( 'id' => 'reset' ) );
					?>
				</form>
			</div><!-- #tab_container-->
		</div><!-- .wrap -->
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

		// $settings = blox_get_registered_settings();
	
		$settings = array();

		$tabs             = array();
		$tabs['general']  = __( 'General', 'blox' );
		$tabs['default']  = __( 'Defaults', 'blox' );
		$tabs['hooks']    = __( 'Hooks', 'blox' );
		$tabs['style']    = __( 'Styles', 'blox' );
		$tabs['misc']     = __( 'Misc', 'blox' );

		return apply_filters( 'blox_settings_tabs', $tabs );
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

		foreach( $this->get_registered_settings() as $tab => $settings ) {

			add_settings_section(
				'blox_settings_' . $tab,
				__return_null(),
				'__return_false',
				'blox_settings_' . $tab
			);

			foreach ( $settings as $option ) {

				$name     = isset( $option['name'] ) ? $option['name'] : '';
				$callback = method_exists( __CLASS__, $option['type'] . '_callback' ) ? array( $this, $option['type'] . '_callback' ) : array( $this, 'missing_callback' );

				add_settings_field(
					'blox_settings[' . $option['id'] . ']',
					$name,
					$callback,
					'blox_settings_' . $tab, // $page
					'blox_settings_' . $tab, // $section
					array(
						'section'     => $tab,
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
					) 
				);
			}
		}

		// Creates our settings in the options table
		register_setting( 'blox_settings', 'blox_settings', array( $this, 'settings_sanitize' ) );
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
						'default' => true
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
						'default' => true
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
				)
			),
		
			/** Default Settings */
			'default' => apply_filters( 'blox_settings_defaults',
				array(
					/*'defaults_content_header' => array(
						'id' => 'defaults_content_header',
						'name' => '<span class="title">' . __( 'Default Content Types', 'blox' ) . '</span>',
						'desc' => '',
						'type' => 'header'
					),
					'enable_content_restrict' => array(
						'id'    => 'enable_content_restrict',
						'name'  => __( 'Restrict Content Types', 'blox' ),
						'label' => __( 'Check to restrict the available content types.', 'blox' ),
						'desc'  => __( 'Blocks with restricted content types will no longer appear on the frontend of your site.', 'blox' ),
						'type'  => 'checkbox',
						'default' => true
					),*/
					'defaults_position_header' => array(
						'id' => 'defaults_position_header',
						'name' => '<span class="title">' . __( 'Default Block Position', 'blox' ) . '</span>',
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
				)
			),
			
			'hooks' => apply_filters( 'blox_settings_hooks',
					array(
						'hook_control_header' => array(
						'id'   => 'hook_control_header',
						'name' => '<span class="title">' . __( 'Hook Control', 'blox' ) . '</span>',
						'desc' => __( 'By default, Blox allows you to choose from over 50 Genesis hooks. Here you can pick and choose the ones you want to use, rename the hooks, or even add your own custom hooks to use with a third-party Genesis theme or plugin.', 'blox' ),
						'type' => 'header'
					),
					'default_hooks' => array(
						'id'       => 'default_hooks',
						'name'     => __( 'Genesis Hooks', 'blox' ),
						'desc'     => '',
						'type'     => 'hooks',
						'sanitize' => 'default_hooks',
					),
					'default_custom_hooks' => array(
						'id'       => 'default_custom_hooks',
						'name'     => __( 'Custom Hooks', 'blox' ),
						'desc'     => '',
						'type'     => 'custom_hooks',
						'sanitize' => 'default_hooks',
					),
				)
			),
		
			/** Style Settings */
			'style' => apply_filters( 'blox_settings_styles',
				array(
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
				)
			),
		
			/** Misc Settings */
			'misc' => apply_filters('blox_settings_misc',
				array(
					'local_metabox_title' => array(
						'id'   => 'local_metabox_title',
						'name' => __( 'Local Metabox Title', 'blox' ),
						'desc' => __( 'This is the metabox title that is displayed on pages/posts/custom post types when local blocks are activated.', 'blox' ),
						'type' => 'text',
						'size' => 'full',
						'placeholder' => __( 'e.g. Local Content Blocks', 'blox' ),
						'default' => __( 'Local Content Blocks', 'blox' ),
						'sanitize' => 'no_html',
					),
					'uninstall_on_delete' => array(
						'id'    => 'uninstall_on_delete',
						'name'  => __( 'Remove Data on Uninstall', 'blox' ),
						'label' => __( 'Check to completely remove all plugin data when Blox is deleted', 'blox' ),
						'desc'  => '',
						'type'  => 'checkbox',
						'default' => '',
					),
				)
			),
		);

		return apply_filters( 'blox_registered_settings', $blox_settings );
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
	 * @return string $input Sanitizied value
	 */
	public function settings_sanitize( $input = array() ) {

		global $blox_options;
		
		if ( empty( $blox_options ) ) {
			$blox_options = array();
		}
		
		if ( empty( $_POST['_wp_http_referer'] ) ) {
			return $input;
		}

		parse_str( $_POST['_wp_http_referer'], $referrer );

		$settings = $this->get_registered_settings();
		$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

		// If we are preforming a normal save, proceed
		if ( isset( $_POST['submit'] ) ) {
		
			$input = $input ? $input : array();
			$input = apply_filters( 'blox_settings_' . $tab . '_sanitize', $input );

			// Loop through each setting being saved and pass it through a sanitization filter
			foreach ( $input as $key => $value ) {

				// Get the setting sanitization type (no_html, select, etc)
				$sanitize_type = isset( $settings[$tab][$key]['sanitize'] ) ? $settings[$tab][$key]['sanitize'] : false;

				if ( $sanitize_type ) {
					// If the setting has a sanitization filter, run it...
					$input[$key] =  $this->$sanitize_type( $value );
				}
			}

			// Loop through the whitelist and unset any that are empty for the tab being saved
			if ( ! empty( $settings[$tab] ) ) {
				foreach ( $settings[$tab] as $key => $value ) {
					if ( empty( $input[$key] ) ) {
						unset( $blox_options[$key] );
					}
				}
			}

			// Merge our new settings with the existing
			$output = array_merge( $blox_options, $input );
		
			add_settings_error( 'blox-notices', '', __( 'Settings updated.', 'blox' ), 'updated' );
			
			return $output;
			
		} else if ( isset( $_POST['reset'] ) ) {
			
			$defaults = array();
			
			if ( ! empty( $settings[$tab] ) ) {
			
				foreach ( $settings[$tab] as $key => $value ) {
					if ( ! empty( $value[ 'default' ] ) ) {
						$defaults[$key] = $value[ 'default' ];
					} else {
						// Sets all empty settings, note this will pull along things like 
						// headers, but the unset process later takes care of these
						$defaults[$key] = '';
					}
				}
			}
			
			// Replace all existing settings with the defaults
			$output = array_merge( $blox_options, $defaults );
			
			// Loop output and unset any empty settings before we reset
			if ( ! empty( $settings[$tab] ) ) {
				foreach ( $settings[$tab] as $key => $value ) {
					if ( empty( $output[$key] ) ) {
						unset( $output[$key] );
					}
				}
			}
			
			add_settings_error( 'blox-notices', '', __( 'Settings have been reset.', 'blox' ), 'updated' );
			
			return $output;
		
		} else {
			
			// We are not saveing or reseting, so return previously saved settings, i.e. don't save anything new.
			return blox_get_settings();
		}
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
	 * Header Callback. If a function is missing for settings callbacks alert the user.
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

		$checked = isset( $blox_options[ $args['id'] ] ) ? checked( 1, esc_attr( $blox_options[ $args['id'] ] ), false ) : '';
		$html = '<label><input type="checkbox" id="blox_settings[' . $args['id'] . ']" name="blox_settings[' . $args['id'] . ']" value="1" ' . $checked . '/> ' . $args['label'] . '</label>';
		$html .= ! empty( $args['desc'] ) ? ( '<p class="description">' . $args['desc'] . '</p>' ) : '';

		echo $html;
	}


	/**
	 * Text Callback. Renders text fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args   Arguments passed by the setting
	 * @global $edd_options Array of all the Blox settings
	 * @return void
	 */
	public function text_callback( $args ) {
	
		global $blox_options;

		if ( isset( $blox_options[ $args['id'] ] ) ) {
			$value = $blox_options[ $args['id'] ];
		} else {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

		$name = 'name="blox_settings[' . $args['id'] . ']"';
		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	
		$html     = '<input type="text" class="text-' . $size . '" id="blox_settings[' . $args['id'] . ']"' . $name . ' placeholder="' . $args['placeholder'] . '" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
		$html    .= '<span class="description"> '  . $args['desc'] . '</span>';

		echo $html;
	}


	/**
	 * Textarea Callback. Renders textarea fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args   Arguments passed by the setting
	 * @global $edd_options Array of all the Blox settings
	 * @return void
	 */
	public function textarea_callback( $args ) {
	
		global $blox_options;

		if ( isset( $blox_options[ $args['id'] ] ) ) {
			$value = $blox_options[ $args['id'] ];
		} else {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

		$html = '<textarea class="text-full ' . $args['class'] . '" rows="'  . $args['size'] . '" id="blox_settings[' . $args['id'] . ']" name="blox_settings[' . $args['id'] . ']" placeholder="' . $args['placeholder'] . '">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
		$html .= ! empty( $args['desc'] ) ? ( '<p class="description">' . $args['desc'] . '</p>' ) : '';

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

		if ( isset( $blox_options[ $args['id'] ] ) ) {
			$value = $blox_options[ $args['id'] ];
		} else {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

		$html = '<select id="blox_settings[' . $args['id'] . ']" name="blox_settings[' . $args['id'] . ']" />';

		foreach ( $args['options'] as $option => $name ) {
			$selected = selected( $option, esc_attr( $value ), false );
			$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
		}

		$html .= '</select>';
		$html .= ! empty( $args['desc'] ) ? ( '<p class="description">' . $args['desc'] . '</p>' ) : '';

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
			$value = $blox_options[ $args['id'] ];
		} else {
			// Defaults
			$value = array( 
				'enable'   		  => '',
				'available_hooks' => array()
			);
		}
	
		?>
		<div id="default_hook_enable">
			<label><input type="checkbox" name="blox_settings[<?php echo $args['id']; ?>][enable]" value="1" <?php echo isset( $value['enable'] ) ? checked( 1, esc_attr( $value['enable'] ), false ) : '';?> /><?php _e( 'Limit Available Genesis Hooks', 'blox' );?></label>
		</div>
		<p class="description"><?php printf( __( 'This setting allows you to limit the number of Genesis hooks that are available and also rename them to improve UI. When enabling this option, any existing blocks using hooks that are not enabled will cease to display on the frontend. %1$sCheck the hooks you want to enable%2$s.', 'blox' ), '<strong>', '</strong>' );?></p>
		<div id="default_hook_settings">
		<?php
		foreach ( $this->get_genesis_hooks_unfiltered() as $sections => $section ) { 
			?>
			<div class="hook-section-title">
				<?php 
				
				echo $section['name'];
				
				$section_name  = 'blox_settings[' . $args['id'] . '][available_hooks][' . $sections . '][name]';
				$section_value = isset( $value['available_hooks'][$sections]['name'] ) ? esc_attr( $value['available_hooks'][$sections]['name'] ) : $section['name'];
				?>
				
				<input class="blox-force-hidden" type="text" name="<?php echo $section_name; ?>" placeholder="<?php echo $section['name']; ?>" value="<?php echo $section_value; ?>" />

			</div>
			<div>
			<div class="blox-checkbox-container">
				<ul class="blox-columns">
				<?php
				foreach ( $section['hooks'] as $hooks => $hook ) {
			
					$enable_name  = 'blox_settings[' . $args['id'] . '][available_hooks][' . $sections . '][hooks][' . $hooks . '][enable]';
					$enable_value = isset( $value['available_hooks'][$sections]['hooks'][$hooks]['enable'] ) ? checked( 1, esc_attr( $value['available_hooks'][$sections]['hooks'][$hooks]['enable'] ), false ) : '';
					$name_name    = 'blox_settings[' . $args['id'] . '][available_hooks][' . $sections . '][hooks][' . $hooks . '][name]';
					$name_value   = isset( $value['available_hooks'][$sections]['hooks'][$hooks]['name'] ) ? esc_attr( $value['available_hooks'][$sections]['hooks'][$hooks]['name'] ) : '';
					$title_name   = 'blox_settings[' . $args['id'] . '][available_hooks][' . $sections . '][hooks][' . $hooks . '][title]';
					$title_value  = isset( $hook['title'] ) ? esc_attr( $hook['title'] ) : '';
					?>
					<li>
						<span>
							<input type="checkbox" name="<?php echo $enable_name; ?>" value="1" <?php echo $enable_value; ?>/>
							<input style="width:300px" type="text" name="<?php echo $name_name; ?>" placeholder="<?php echo $hooks; ?>" value="<?php echo $name_value; ?>" />
							<input class="blox-force-hidden" type="text" name="<?php echo $title_name; ?>" value="<?php echo $title_value; ?>" />
						</span>
					</li>
					<?php
				}
				?>
				</ul>
			</div>
			<div class="blox-checkbox-select-tools">
				<a class="blox-checkbox-select-all" href="#"><?php _e( 'Enable All' ); ?></a> <a class="blox-checkbox-select-none" href="#"><?php _e( 'Disable All' ); ?></a>
			</div>
			</div>
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
			$value = array( 
				'enable'   		  => '',
				'available_hooks' => array()
			);
		}
		
		?>
		<div id="default_custom_hook_enable">
			<label><input type="checkbox" name="blox_settings[<?php echo $args['id']; ?>][enable]" value="1" <?php echo isset( $value['enable'] ) ? checked( 1, esc_attr( $value['enable'] ), false ) : '';?> /><?php _e( 'Enable Custom Hooks', 'blox' );?></label>
		</div>
		<p class="description"><?php _e( 'This setting allows you add your own custom hooks. Many themes and plugins have their own hooks. Enter them here so that Blox can target them.', 'blox' );?></p>
		
		<div class="add-custom-button">
			<input type="text" class="custom-hook-entry" style="width:300px" placeholder="<?php _e( 'Enter hook slug', 'blox' ); ?>" value="" /><a class="button button-secondary"><?php _e( 'Add Custom Hook', 'blox' ); ?></a>
			<p class="description"><?php _e( 'The hook slug can only be made up of letters, numbers, dashes and underscores.', 'blox' );?></p>			
		</div>
		<div class="hook-section-title">
			<?php 
			$custom_section_name  = 'blox_settings['. $args['id'] . '][available_hooks][custom][name]';
			$custom_section_value = __( 'Custom Hooks', 'blox' );
			echo $custom_section_value;
			?>
			<input class="blox-force-hidden" type="text" name="<?php echo $custom_section_name; ?>" value="<?php echo $custom_section_value; ?>" />	
		</div>
		<div id="default_custom_hook_settings">
			<div class="blox-checkbox-container">
				<ul class="custom-hooks blox-columns">
				<?php
				$custom_hooks = isset( $value['available_hooks']['custom'] ) ? $value['available_hooks']['custom'] : array( 'hooks' => array() );
				
				if ( ! empty( $custom_hooks['hooks'] ) ) {
					foreach ( $custom_hooks['hooks'] as $hooks => $hook ) {
			
						$hook_name	  = 'blox_settings[' . $args['id'] . '][available_hooks][custom][hooks][' . $hooks . ']';
						$hook_value   = $hooks;
						$enable_name  = 'blox_settings[' . $args['id'] . '][available_hooks][custom][hooks][' . $hooks . '][enable]';
						$enable_value = isset( $hook['enable'] ) ? checked( 1, esc_attr( $hook['enable'] ), false ) : '';
						$name_name    = 'blox_settings[' . $args['id'] . '][available_hooks][custom][hooks][' . $hooks . '][name]';
						$name_value   = isset( $hook['name'] ) ? esc_attr( $hook['name'] ) : '';
						$title_name   = 'blox_settings[' . $args['id'] . '][available_hooks][custom][hooks][' . $hooks . '][title]';
						$title_value  = isset( $hook['title'] ) ? esc_attr( $hook['title'] ) : '';
						?>
						<li>
							<span>
								<input class="blox-force-hidden" disabled type="text" name="<?php echo $hook_name; ?>" placeholder="<?php _e( 'Hook slug (No spaces allowed)', 'blox' ); ?>" value="<?php echo $hook_value; ?>" />
								<input type="checkbox" name="<?php echo $enable_name; ?>" value="1" <?php echo $enable_value; ?>/>
								<input class="hook-name" type="text" name="<?php echo $name_name; ?>" placeholder="<?php echo $hook_value; ?>" value="<?php echo $name_value; ?>" />
								<input class="blox-force-hidden" type="text" name="<?php echo $title_name; ?>" value="<?php echo $title_value; ?>" />
								<a class="delete-custom-hook"><?php _e( 'Delete', 'blox' ); ?></a>
							</span>
						</li>
						<?php
					}
				} else {
					echo '<li class="no-hooks">' . __( 'Add a custom hook...', 'blox' ) . '</li>';
				}
				?>
				</ul>
			</div>
			<div class="blox-checkbox-select-tools">
				<a class="blox-checkbox-select-all" href="#"><?php _e( 'Enable All' ); ?></a> <a class="blox-checkbox-select-none" href="#"><?php _e( 'Disable All' ); ?></a>
			</div>
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
	 * Returns a positive integer value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $new_value Should ideally be a positive integer.
	 * @return integer Positive integer.
	 */
	function absint( $new_value ) {
		return absint( $new_value );
	}


	/**
	 * Removes HTML tags from string.
	 *
	 * @since 1.0.0
	 *
	 * @param string $new_value String, possibly with HTML in it
	 * @return string String without HTML in it.
	 */
	function no_html( $new_value ) {
		return strip_tags( $new_value );
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
	 * Removes unsafe HTML tags, via wp_kses_post().
	 *
	 * @since 1.0.0
	 *
	 * @param string $new_value String with potentially unsafe HTML in it
	 * @return string String with only safe HTML in it
	 */
	function safe_html( $new_value ) {
		return wp_kses_post( $new_value );
	}


	/**
	 * Keeps the option from being updated if the user lacks unfiltered_html
	 * capability.
	 *
	 * @since 1.0.0
	 *
	 * @param string $new_value New value
	 * @param string $old_value Previous value
	 * @return string New or previous value, depending if user has correct
	 * capability or not.
	 */
	function requires_unfiltered_html( $new_value, $old_value ) {
		if ( current_user_can( 'unfiltered_html' ) ) {
			return $new_value;
		} else {
			return $old_value;
		}
	}
	
	
	/**
	 * Removes HTML tags from all custom hook names
	 *
	 * @since 1.1.0
	 *
	 * @param array $new_value Array of all custom hook data 
	 * @return array Array of all custom hook data without tags in it
	 */
	function default_hooks( $new_value ) {
			
		$available_hooks = isset( $new_value['available_hooks'] ) ? $new_value['available_hooks'] : false;
				
		if ( $available_hooks ) {
			foreach ( $available_hooks as $sections => $section ) {
				
				$enabled_hooks = array();
			
				if ( isset( $section['hooks'] ) ) {
					foreach ( $section['hooks'] as $hooks => $hook ) {
						
						// Sanatize custom hook entries (only letters, number, dash, underscore)
						$hooks = preg_replace( '/[^ \w \-]/', '', $hooks );
					
						$enabled_hooks[$hooks] = array(
							'enable' => isset( $hook['enable'] ) ? esc_attr( $hook['enable'] ) : '',
							'name'   => strip_tags( $hook['name'] ),
							'title'  => esc_attr( $hook['title'] ),
						);
					}
				}
			
				$filtered_hooks[$sections]['name']  = strip_tags( $section['name'] );
				$filtered_hooks[$sections]['hooks'] = $enabled_hooks;
			}
		
			$new_value['available_hooks'] = $filtered_hooks;
		}
		
		return $new_value;
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
				'reset'               => __( 'Are you sure you want to reset these settings? This action cannot be undone.', 'blox' ),
				'custom_hook_title'   => __( 'Enter a hook name', 'blox' ),
				'delete_hook'         => __( 'Delete', 'blox' ),
				'confirm_delete_hook' => __( 'Are you sure you want to delete this hook? This action cannot be undone.', 'blox' ),
				'no_hooks'			  => __( 'Add a custom hook...', 'blox' ),
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
