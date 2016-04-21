<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Creates all notices for Blox
 *
 * @since 1.0.0
 *
 * @package Blox
 * @author  Nicholas Diego
 */
class Blox_Notices {
 
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
		
		add_filter( 'blox_settings_misc', array( $this, 'disable_license_notices' ) );

		add_action( 'blox_settings_form_top', array( $this, 'settings_upgrade_notice' ) );
		add_action( 'blox_tab_container_after', array( $this, 'settings_upgrade_notice' ) );
    }
    
    
    /**
     * Add setting option to disable all marketing notices
     *
     * @since 1.0.0
     */
    public function disable_license_notices( $misc_settings ) {
    
    	$misc_settings['disable_license_notices'] = array(
			'id'   => 'disable_license_notices',
			'name'  => __( 'License Key Notices', 'blox' ),
			'label' => __( 'Check to disable all license activation reminders', 'blox' ),
			'desc'  => sprintf( __( 'You are free to use Blox and all Addons without activated licenses, but valid licenses are required for automatic updates and support. It is recommended that you keep notices enabled as they will notify you if there are any issues with your license keys. Learn more about Blox %1$slicensing%2$s.', 'blox' ), '<a href="https://www.bloxwp.com/documentation/licensing/?utm_source=blox&utm_medium=plugin&utm_content=plugin-links&utm_campaign=Blox_Plugin_Links" target="_blank">', '</a>' ),
			'type'  => 'checkbox',
			'default' => false
		);
					
		return $misc_settings;
	}

    
    /**
     * Print upgrade notice on settings tabs
     *
     * @since 1.0.0
     */
    public function settings_upgrade_notice() {
    
    	$disable_notices = blox_get_option( 'disable_license_notices', '' );
    	
    	$addons   = blox_get_active_addons();
    	$licenses = array_merge( array( 'blox' => __( 'Blox', 'blox' ) ), $addons );
    	
    	foreach ( $licenses as $key => $title ) {
    	
    		$status = get_option( 'blox_' . $key . '_license_status' );

    		if ( $status['success'] != 1 ) {    			
    			$problems[$key] = array( 
					'title'   => $title,
					'license' => empty( $status['license'] ) ? __( 'No License Set', 'blox' ) : ucfirst( $status['license'] ),
    			); 
    		}
    	}
		
    	// Make sure noticed are not disabled and there are issues, then show notices
    	if ( ! $disable_notices && ! empty( $problems ) ) {
			?>
			<div class="blox-alert">
				<?php 
				echo sprintf( __( 'There seem to be problems with the following Blox license key(s). But don\'t worry, simply head over to the %1$sLicenses & Addons%2$s page to fix these issues.', 'blox' ), '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-licenses' ) . '">', '</a>' );
				echo '<div style="margin:8px 0;">';
				foreach ( $problems as $problem ) {
					echo '<strong>' . $problem['title'] . '</strong>: <em>' . $problem['license'] . '</em><br>';
				}
				echo '</div>';
				echo sprintf( __( 'Note that you can use Blox and all Addons without activated/valid licenses. However in doing so, you will not receive automatic updates or support. If you are fine with this, then you might as well turn off these notifications in the plugin %5$ssettings%4$s.', 'blox' ), '<strong>', '</strong>', '<a href="https://www.bloxwp.com/?utm_source=blox&utm_medium=plugin&utm_content=marketing-links&utm_campaign=Blox_Plugin_Links" target="_blank">', '</a>', '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=misc' ) . '">' ); 
				?>
			</div>
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

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Notices ) ) {
            self::$instance = new Blox_Notices();
        }

        return self::$instance;
    }
} 
// Load the class.
$blox_notices = Blox_Notices::get_instance();
  