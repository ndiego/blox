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
			'desc'  => sprintf( __( 'You are free to use Blox and all Addons without activated licenses, but valid licenses are required for automatic updates and support. It is recommended that you keep notices enabled as they will notify you when your licenses are about to expire or if there is any issues. %1$sLearn More%2$s.', 'blox' ), '<a href="https://www.bloxwp.com/documentation/licensing/?utm_source=blox&utm_medium=plugin&utm_content=plugin-links&utm_campaign=Blox_Plugin_Links" target="_blank">', '</a>' ),
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
    	$licenses_status = array();
    	
    	foreach ( $licenses as $key => $title ) {
    	
    		$status = get_option( 'blox_' . $key . '_license_status' );
    		
    		$licenses_status[$key] = array( 
    			'title'   => $title,
				'success' => $status['success'],
				'license' => $status['license'],
				'expires' => $status['expires'],
    		);
    	}
		
		// Step 1: Determine if a license has been saved

		
		// Step 2: Determine is any of the saved license are active, expired, invalid, etc. 
		
		foreach ( $licenses_status as $license ) {
			if ( $license['success'] != 1 ) {
			
			}
		}
		
		
    
    	if ( ! $disable_notices ) {
			?>
			<div class="blox-alert">
				<?php 
				//echo print_r( $licenses_status ) . '<br>';
				//echo sprintf( __( 'Enjoying %1$sBlox Lite%2$s but looking for more content options, visibility settings, priority support, frequent updates and more? Then you should consider %3$supgrading%4$s to %1$sBlox%2$s. Happy with the free version and have no need to upgrade? Then you might as well turn off these notifications in the plugin %5$ssettings%4$s.', 'blox' ), '<strong>', '</strong>', '<a href="https://www.bloxwp.com/?utm_source=blox-lite&utm_medium=plugin&utm_content=marketing-links&utm_campaign=Blox_Plugin_Links" target="_blank">', '</a>', '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=misc' ) . '">' ); ?>
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
  