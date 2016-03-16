<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the license and addon settings page and loads in all the available options
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_License_Settings {
    
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
    
    	add_action( 'admin_menu', array( $this, 'add_menu_links' ), 10 );
    	add_action( 'admin_init', array( $this, 'register_settings' ), 10 );
    }
    
    
    /**
     * Add the Licenses menu link.
     *
     * @since 1.0.0
     */
    public function add_menu_links() {
		
		// Add our licenses menu link
		add_submenu_page( 'edit.php?post_type=blox', __( 'Licenses & Addons', 'blox' ), __( 'Licenses & Addons', 'blox' ), 'manage_options', 'blox-licenses', array( $this, 'print_licenses_page' ) );
	}
	
	
	/**
	 * Add all settings sections and fields
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_settings() {
	
		// If blox_licenses does not exist, create it. It should always exist, so this is just a backup
		if ( false == get_option( 'blox_licenses' ) ) {
			add_option( 'blox_licenses' );
		}
		
		// Add the main license settings section, we only need one section
		//add_settings_section( 'blox_licenses_main', __return_null(), '__return_false', 'blox_licenses_main' );

		foreach( $this->get_registered_licenses() as $tab => $licenses ) {

			foreach ( $licenses as $option ) {

				$name     = isset( $option['name'] ) ? $option['name'] : '';
				$callback = method_exists( __CLASS__, $option['type'] . '_callback' ) ? array( $this, $option['type'] . '_callback' ) : array( $this, 'missing_callback' );

				add_settings_field(
					'blox_licenses[' . $option['id'] . ']',
					$name,
					$callback,
					'blox_licenses_main', // $page
					'blox_licenses_main', // $section
					array(
						'section'     => $tab,
						'id'          => isset( $option['id'] )          ? $option['id']          : null,
						'name'        => isset( $option['name'] )        ? $option['name']        : null,
						'desc'        => ! empty( $option['desc'] )      ? $option['desc']        : '',
						'size'        => isset( $option['size'] )        ? $option['size']        : null,
						'options'     => isset( $option['options'] )     ? $option['options']     : '',
						'placeholder' => isset( $option['placeholder'] ) ? $option['placeholder'] : null,
						'class'       => isset( $option['class'] )       ? $option['class']       : null,
						'default'     => isset( $option['default'] )     ? $option['default']     : '',
						'sanitize'	  => isset( $option['sanitize'] )    ? $option['sanitize']    : '',
					) 
				);
			}
		}

		// Creates our settings in the options table
		register_setting( 'blox_licenses', 'blox_licenses', array( $this, 'licenses_sanitize' ) );
	}
	
	
	/**
	 * Retrieve the array of all licenses
	 *
	 * @since 1.0.0
	 *
	 * @return array
	*/
	public function get_registered_licenses() {
	
		/**
		 * Blox licenses, filters are provided for each license type
		 * but the only one that should really be used is the addons one
		 */
		$blox_licenses = array(
			
			// Main Blox license
			'main' => apply_filters( 'blox_licenses_main',
				array(
					'blox_license_header' => array(
						'id' => 'blox_license_header',
						'name' => '<span class="title">' . __( 'Primary Blox License', 'blox' ) . '</span>',
						'desc' => '',
						'type' => 'header'
					),	
				)
			),
		
			// Addon licenses
			'addons' => apply_filters( 'blox_licenses_addons',
				array(
					'addon_license_header' => array(
						'id' => 'addon_license_header',
						'name' => '<span class="title">' . __( 'Addon Licensing', 'blox' ) . '</span>',
						'desc' => '',
						'type' => 'header'
					),	
				)
			),
		);

		return apply_filters( 'blox_registered_licenses', $blox_licenses );
	}
    
    
    /**
     * Print licenses settings page.
     *
     * @since 1.0.0
     */
	public function print_licenses_page() {

		// Get the active tab
		$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->get_licenses_tabs() ) ? $_GET['tab'] : 'main';

		ob_start();
		?>
		<div class="wrap">
			<h2><?php _e( 'Licenses & Addons', 'blox' ); ?></h2>
		
			<?php settings_errors( 'blox-notices' ); // Shouldn't have to use this... ?>
		
			<h2 class="nav-tab-wrapper">
				<?php foreach( $this->get_licenses_tabs() as $tab_id => $tab_name ) {
				
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
				<?php if ( $active_tab != 'addons' ) { ?>
				
					<form method="post" action="options.php">
						<table class="form-table">
							<?php
							settings_fields( 'blox_licenses' );
							do_settings_fields( 'blox_licenses_' . $active_tab, 'blox_licenses_' . $active_tab );
							?>
						</table>
						<?php submit_button( __( 'Save Licenses', 'blox' ) ); ?>
					</form>
				
				<?php } else { 
				
					$addons = $this->get_addons();
					?>
					<div class="blox-addon-container">
						<p><?php echo sprintf( __( 'Addons enhance Blox and make it more versatile. To download and install addons, you must have purchased a %1$sMultisite Bundle%2$s or %1$sDeveloper Bundle%2$s. Head on over to your bloxwp.com %3$saccount page%4$s to download, or upgrade your license.', 'blox' ), '<strong>', '</strong>', '<a href="" title="' . __( 'Your Account', 'blox' ) . '">', '</a>' );?><p>
						<?php foreach ( $addons as $addon ) { ?>
							<div class="blox-addon">
								<?php echo $addon['uc'] == 1 ? '<span class="blox-addon-uc">' . __( 'Coming Soon', 'blox' ) . '</span>' : ''; ?>
								<h3 class="blox-addon-title"><?php echo $addon['name'];?></h3>
								<img class="blox-addon-image" src="<?php echo $addon['image'];?>" alt="<?php echo $addon['name'];?>" />
								<p><?php echo $addon['desc'];?></p>
								<a class="button-secondary" href="<?php echo $addon['link'];?>" title="<?php echo $addon['name'];?>"><?php echo $addon['uc'] == 1 ? __( 'Learn More', 'blox' ) : __( 'Get this Addon', 'blox' ); ?></a>
							</div>					
						<?php } ?>
					</div>
					
				<?php } ?>				
				
			</div><!-- #tab_container-->
		</div><!-- .wrap -->
		<?php
		echo ob_get_clean();
	}
	
	
	/**
	 * Retrieve our licenses tabs
	 *
	 * @since 1.0.0
	 *
	 * @return array $tabs An array of all available tabs
	 */
	public function get_licenses_tabs() {

		$tabs             = array();
		$tabs['main']     = __( 'Licenses', 'blox' );
		$tabs['addons']   = __( 'Addons', 'blox' );

		return apply_filters( 'blox_licenses_tabs', $tabs );
	}
	
	
	/**
	 * Retrieve all available addons
	 *
	 * @since 1.0.0
	 *
	 * @return array $addons An array of all available addons
	 */
	public function get_addons() {
		
		$addons = array(
			'blox-widgets' => array(
				'id'    => 'blox-widgets',
				'name'  => __( 'Widgets Addon', 'blox' ),
				'desc'  => __( 'Allows you to add any widget, and any number of widgets, to your content blocks.', 'blox' ),
				'image' => 'https://www.bloxwp.com/wp-content/uploads/2015/12/Blox-Widgets-2.png',
				'link'  => '',
				'uc'    => 1
			),	
			'blox-sandbox' => array(
				'id'    => 'blox-sandbox',
				'name'  => __( 'Sandbox Addon', 'blox' ),
				'desc'  => __( 'Creates a new settings page that acts like your theme\'s functions.php file. Oh the possibilities...', 'blox' ),
				'image' => 'https://www.bloxwp.com/wp-content/uploads/2015/12/Blox-Sandbox-2.png',
				'link'  => '',
				'uc'    => 1
			),
			'blox-scheduler' => array(
				'id'    => 'blox-scheduler',
				'name'  => __( 'Scheduler Addon', 'blox' ),
				'desc'  => __( 'Schedule blocks to show/hide based on the date and time. Great for promotions!', 'blox' ),
				'image' => 'https://www.bloxwp.com/wp-content/uploads/2015/12/Blox-Scheduler-2.png',
				'link'  => '',
				'uc'    => 1
			),
		);
		
		return apply_filters( 'blox_addons', $addons );
	}
	

	/**
	 * License Sanitization
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
	public function licenses_sanitize( $input = array() ) {
	
		global $blox_licenses_array;
		
		if ( empty( $blox_licenses_array ) ) {
			$blox_licenses_array = array();
		}
		
		if ( empty( $_POST['_wp_http_referer'] ) ) {
			return $input;
		}

		parse_str( $_POST['_wp_http_referer'], $referrer );

		// If we are preforming a normal save, proceed
		if ( isset( $_POST['submit'] ) ) {
		
			$input = $input ? $input : array();

			// Make sure each license does not include any tags
			foreach ( $input as $key => $value ) {
				$output[$key] = strip_tags( $value );
			}
		
			add_settings_error( 'blox-notices', '', __( 'Licenses have been saved.', 'blox' ), 'updated' );
			
			return $output;
			
		} else {
			
			// We are not saveing or resetting, so return previously saved licenses, i.e. don't save anything new.
			return blox_get_licenses();
		}
	}
	
	
	/**
	 * Missing Callback. If a function is missing for license callbacks alert the user.
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
	 * License Key Callback
	 *
	 * @since 1.0.0
	 *
	 * @param array $args   Arguments passed by the setting
	 * @global $edd_options Array of all the Blox settings
	 * @return void
	 */
	public function license_key_callback( $args ) {		
		
		global $blox_licenses_array;
		
		//echo print_r( $blox_licenses_array ) . '<br>';

		if ( isset( $blox_licenses_array[ $args['id'] ] ) ) {
			$value = $blox_licenses_array[ $args['id'] ];
		} else {
			$value = '';
		}

		$name = 'name="blox_licenses[' . $args['id'] . ']"';
		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	
		wp_nonce_field( $args['id'] . '-nonce', $args['id'] . '-nonce' );
	
		// Get our license data. get_option returns an object so typecast it to an array
		$license_data = (array) get_option( $args['options']['license_status_option'] );

		//echo print_r($license_data) . '<br>';
		
		if ( ! empty( $value ) ) { 
			
			if ( ( ! empty( $license_data['license'] ) && 'valid' == $license_data['license'] ) ) { 
				echo '<input type="text" class="no-edit text-' . $size . '" id="blox_licenses[' . $args['id'] . ']"' . $name . ' placeholder="' . $args['placeholder'] . '" value="' . esc_attr( $value ) . '"/>';
				?>
				<input type="submit" class="button button-primary" name="<?php echo $args['id']; ?>_check" value="<?php _e( 'Check License',  'blox' ); ?>"/>
				<input type="submit" class="button button-secondary" name="<?php echo $args['id']; ?>_deactivate" value="<?php _e( 'Deactivate License',  'blox' ); ?>"/>
				<p class="description"><?php echo sprintf( __( 'This license key is %1$sactive%2$s, and expires on %3$s. Click to deactivate. Once deactivated, you will no longer receive automatic updates for %4$s.', 'blox' ), '<span style="color: green">', '</span>', $license_data['expires'], '<strong>' . $license_data['item_name'] . '</strong>' ); ?></p>
				<?php 
			} else { 
				echo '<input type="text" class="no-edit text-' . $size . '" id="blox_licenses[' . $args['id'] . ']"' . $name . ' placeholder="' . $args['placeholder'] . '" value="' . esc_attr( $value ) . '"/>';
				?>
				<input type="submit" class="button button-primary" name="<?php echo $args['id']; ?>_activate" value="<?php _e( 'Activate License',  'blox' ); ?>"/>
				<a class="edit-license button button-secondary"><?php _e( 'Edit', 'blox' ); ?></a>
				<p class="description"><?php echo sprintf( __( 'This license key is %1$snot activated%2$s, so you are not receiving automatic updates. Click to activate or edit the license key.', 'blox' ), '<span style="color:#a00">', '</span>' ); ?></p>
				<p class="edit-license description"><?php _e( 'Edited license keys need to be resaved before they can be activated.', 'blox' ); ?></p>
				<?php
			}
			?>

			<?php
		
		} else { 
			echo '<input type="text" class="text-' . $size . '" id="blox_licenses[' . $args['id'] . ']"' . $name . ' placeholder="' . $args['placeholder'] . '" value="' . esc_attr( $value ) . '"/>';

			?>
			<p class="description"><?php _e( 'Enter your license key and save. Once saved, you will be able to activate and begin receiving automatic updates.', 'blox' ); ?></p>
			<?php
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

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_License_Settings ) ) {
            self::$instance = new Blox_License_Settings();
        }

        return self::$instance;
    }
}
// Load the settings class.
$blox_license_settings = Blox_License_Settings::get_instance();


// Create the Blox licenses array global variable
global $blox_licenses_array;

// Set the $blox_licenses_array
$blox_licenses_array = blox_get_licenses();


/**
 * Get Licenses   
 *
 * Retrieves all plugin and addon licenses
 *
 * @since 1.0.0
 * @return array All Blox settings
 */
function blox_get_licenses() {

	$licenses = get_option( 'blox_licenses' );

	return apply_filters( 'blox_get_licenses', $licenses );
}


/**
 * Get Addons   
 *
 * Retrieves all addons that are active on the site
 *
 * @since 1.0.0
 * @return array All Blox Addons
 */
function blox_get_active_addons() {

	$addons = array();

	return apply_filters( 'blox_get_active_addons', $addons );
}
