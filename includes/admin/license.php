<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * License handler for Blox and all Addons
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego (heavily modified version of file supplied by East Digital Downloads)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_License {

	private $file;
	private $license;
	private $item_name;
	private $item_shortname;
	private $version;
	private $author;
	private $api_url = 'https://www.bloxwp.com';
	private $scope;

	/**
	 * Class constructor
	 *
	 * @param string  $_file
	 * @param string  $_item_name
	 * @param string  $_version
	 * @param string  $_author
	 * @param string  $_optname
	 * @param string  $_api_url
	 * @param string  $_scope
	 */
	function __construct( $_file, $_item, $_version, $_author, $_optname = null, $_api_url = null, $_scope = 'main' ) {

		$licenses = get_option( 'blox_licenses' );

		$this->file           = $_file;
		$this->item_name      = $_item;
		$this->item_shortname = 'blox_' . str_replace( ' ', '_', strtolower( $this->item_name ) );
		$this->version        = $_version;
		$this->license        = trim( $licenses[$this->item_shortname . '_license_key'] );
		$this->author         = $_author;
		$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;
		$this->scope          = empty( $_scope ) ? 'main' : $_scope;

		$license_status = get_option( $this->item_shortname . '_license_status' );
		
		$this->status         = $license_status['license'];

		$this->hooks();
	}


	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {
	
		// Register settings
		add_filter( 'blox_licenses_' . $this->scope, array( $this, 'add_license' ), 1 );

		// Activate license key on settings save
		add_action( 'admin_init', array( $this, 'activate_license' ) );

		// Deactivate license key
		add_action( 'admin_init', array( $this, 'deactivate_license' ) );
		
		// Check license key
		add_action( 'admin_init', array( $this, 'check_license' ) );

		// Updater
		add_action( 'admin_init', array( $this, 'auto_updater' ), 0 );
		
		// Notices used during license activities, deactiviation, etc.
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}
	
	
	/**
	 * Add license field to license settings
	 *
	 * @since 1.0.0
	 *
	 * @param array $licenses An array of all other license settings to which we merge the license settings
	 * @return array of all settings
	 */
	public function add_license( $licenses ) {
		
		$id = $this->item_shortname . '_license_key';
		
		$license_settings = array(
			 $id => array(
				'id'      => $id,
				'name'    => sprintf( __( '%1$s License Key', 'blox' ), $this->item_name ),
				'desc'    => __( '' , 'blox' ),
				'placeholder' => __( 'Enter License Key', 'blox' ),
				'type'    => 'license_key',
				'options' => array( 'license_status_option' => $this->item_shortname . '_license_status' ), // Separately stored option for license status
				'size'    => 'large',
			)
		);

		return array_merge( $licenses, $license_settings );
	}
	

	/**
	 * Auto updater
	 *
	 * @since 1.0.0
	 */
	public function auto_updater() {

		// Don't bother trying to update if license is not valid
		if ( 'valid' !== $this->status ) {
			return;
		}
						
		$args = array(
			'version'   => $this->version,
			'license'   => $this->license,
			'author'    => $this->author,
			'url'       => home_url()
		);

		if( ! empty( $this->item_id ) ) {
			$args['item_id']   = $this->item_id;
		} else {
			$args['item_name'] = $this->item_name;
		}

		// Setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater(
			$this->api_url,
			$this->file,
			$args
		);
	}


	/**
	 * Activate the license key
	 *
	 * @since 1.0.0
	 */
	public function activate_license() {

		if ( ! isset( $_POST['blox_licenses'] ) ) {
			return;
		}

		if ( ! isset( $_POST['blox_licenses'][ $this->item_shortname . '_license_key'] ) ) {
			return;
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		foreach( $_POST as $key => $value ) {
			if ( false !== strpos( $key, 'license_key_deactivate' ) ) {
				// Don't activate a key when deactivating a different key
				return;
			}
		}

		if( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce'], $this->item_shortname . '_license_key-nonce' ) ) {
			wp_die( __( 'Nonce verification failed', 'blox' ), __( 'Error', 'blox' ), array( 'response' => 403 ) );
		}
		
		// Run on activate button press
		if ( isset( $_POST[ $this->item_shortname . '_license_key_activate'] ) ) {
			
			// This is sort of a backup because the Activate button should never show when the status is valid
			if ( 'valid' === $this->status ) {
				return;
			}

			$license = sanitize_text_field( $_POST['blox_licenses'][ $this->item_shortname . '_license_key'] );
			
			// This is another backup, because the Activate button should never show when there is no license key
			if ( empty( $license ) ) {
				return;
			}

			// Data to send to the API
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url()
			);

			// Call the API
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				)
			);
			
			// Make sure there are no errors
			if ( is_wp_error( $response ) ) {				
				wp_die( sprintf( __( 'There has been an error retrieving the license information from the server, perhaps you are no longer connected to the internet. Please contact %1$ssupport%2$s for assistance if needed.', 'blox' ), '<a href="https://www.bloxwp.com/contact">', '</a>' ), __( 'Error', 'blox' ), array( 'response' => 403 ) );
			}
			
			// Tell WordPress to look for updates
			set_site_transient( 'update_plugins', null );

			// Decode license data and append the API request type
			$license_data = (array) json_decode( wp_remote_retrieve_body( $response ) );
			$license_data['request_type'] = 'activate';

			update_option( $this->item_shortname . '_license_status', $license_data );

			set_transient( 'blox_license_notices', $license_data, 1000 );
		}
	}


	/**
	 * Deactivate the license key
	 *
	 * @since 1.0.0
	 */
	public function deactivate_license() {

		if ( ! isset( $_POST['blox_licenses'] ) ) {
			return;
		}

		if ( ! isset( $_POST['blox_licenses'][ $this->item_shortname . '_license_key'] ) ) {
			return;
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		if( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce'], $this->item_shortname . '_license_key-nonce' ) ) {
			wp_die( __( 'Nonce verification failed', 'blox' ), __( 'Error', 'blox' ), array( 'response' => 403 ) );
		}

		// Run on deactivate button press
		if ( isset( $_POST[ $this->item_shortname . '_license_key_deactivate'] ) ) {
		
			$license = sanitize_text_field( $_POST['blox_licenses'][ $this->item_shortname . '_license_key'] );
			
			// This is another backup, because the Activate button should never show when there is no license key
			if ( empty( $license ) ) {
				return;
			}

			// Data to send to the API
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url()
			);

			// Call the API
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				)
			);

			// Make sure there are no errors
			if ( is_wp_error( $response ) ) {
				wp_die( sprintf( __( 'There has been an error retrieving the license information from the server, perhaps you are no longer connected to the internet. Please contact %1$ssupport%2$s for assistance if needed.', 'blox' ), '<a href="https://www.bloxwp.com/contact">', '</a>' ), __( 'Error', 'blox' ), array( 'response' => 403 ) );
			}

			// Decode the license data and append the API request type
			$license_data = (array) json_decode( wp_remote_retrieve_body( $response ) );
			$license_data['request_type'] = 'deactivate';

			update_option( $this->item_shortname . '_license_status', $license_data );
			//delete_option( $this->item_shortname . '_license_status' );

			set_transient( 'blox_license_notices', $license_data, 1000 );
		}
	}


	/**
	 * Check/Verify the license key
	 *
	 * @since 1.0.0
	 */
	public function check_license() {

		if ( ! isset( $_POST['blox_licenses'] ) ) {
			return;
		}

		if ( ! isset( $_POST['blox_licenses'][ $this->item_shortname . '_license_key'] ) ) {
			return;
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		if( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce'], $this->item_shortname . '_license_key-nonce' ) ) {
			wp_die( __( 'Nonce verification failed', 'blox' ), __( 'Error', 'blox' ), array( 'response' => 403 ) );
		}

		// Run on deactivate button press
		if ( isset( $_POST[ $this->item_shortname . '_license_key_check'] ) ) {
		
			$license = sanitize_text_field( $_POST['blox_licenses'][ $this->item_shortname . '_license_key'] );
			
			// This is another backup, because the Activate button should never show when there is no license key
			if ( empty( $license ) ) {
				return;
			}

			// Data to send to the API
			$api_params = array(
				'edd_action' => 'check_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url()
			);

			// Call the API
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				)
			);

			// Make sure there are no errors
			if ( is_wp_error( $response ) ) {
				wp_die( sprintf( __( 'There has been an error retrieving the license information from the server, perhaps you are no longer connected to the internet. Please contact %1$ssupport%2$s for assistance if needed.', 'blox' ), '<a href="https://www.bloxwp.com/contact">', '</a>' ), __( 'Error', 'blox' ), array( 'response' => 403 ) );
			}

			// Decode the license data and append the API request type
			$license_data = (array) json_decode( wp_remote_retrieve_body( $response ) );
			$license_data['request_type'] = 'check';

			update_option( $this->item_shortname . '_license_status', $license_data );

			set_transient( 'blox_license_notices', $license_data, 1000 );
		}
	}


	/**
	 * Admin notices for errors
	 *
	 * @since 1.0.0
	 */
	public function notices() {

		if ( ! isset( $_GET['page'] ) || 'blox-licenses' !== $_GET['page'] ) {
			return;
		}

		$license_notices = get_transient( 'blox_license_notices' );
		
		if ( $license_notices === false ) {
			return;
		} 
				
		if ( $license_notices['request_type'] == 'activate' ) {
		
			if ( ! empty( $license_notices['error'] ) ) {
				
				// Figure out what the error is and print message
				switch( $license_notices['error'] ) {
					case 'item_name_mismatch' :
						$message = __( 'This license does not belong to the product you have entered it for.', 'blox' );
						break;
					case 'no_activations_left' :
						$message = __( 'This license does not have any activations left', 'blox' );
						break;
					case 'expired' :
						$message = __( 'This license key has expired. Please renew it.', 'blox' );
						break;
					default :
						$message = sprintf( __( 'There was a problem activating your license key, please try again or contact support. Error code: %s', 'blox' ), $license_notices['error'] );
						break;
				}

				$message_type = 'error';
				
			} else {
			
				$message      = __( 'The license key was successfully activated.', 'blox' );
				$message_type = 'updated';
			}
			
		} else if ( $license_notices['request_type'] == 'deactivate' ) {
		
			if ( ! empty( $license_notices['error'] ) ) {
				
				// Figure out what the error is and print message
				switch( $license_notices['error'] ) {
					case 'item_name_mismatch' :
						$message = __( 'This license does not belong to the product you have entered it for.', 'blox' );
						break;
					case 'no_activations_left' :
						$message = __( 'This license does not have any activations left', 'blox' );
						break;
					case 'expired' :
						$message = __( 'This license key has expired. Please renew it.', 'blox' );
						break;
					default :
						$message = sprintf( __( 'There was a problem deactivating your license key, please try again or contact support. Error code: %s', 'blox' ), $license_error->error );
						break;
				}

				$message_type = 'error';
				
			} else {
			
				$message      = __( 'The license key was successfully deactivated.', 'blox' );
				$message_type = 'updated';
			}
			
		} else if ( $license_notices['request_type'] == 'check' ) {
		
			if ( ! empty( $license_notices['error'] ) ) {
				
				$message = sprintf( __( 'There was a problem checking your license key, please try again or contact support. Error code: %s', 'blox' ), $license_error->error );
				$message_type = 'error';
				
			} else {
				if ( $license_notices['license'] == 'valid' ) {
					$message      = __( 'The license key is valid.', 'blox' );
					$message_type = 'updated';
				} else {
					$message      = __( 'The license key is no longer valid. You will need to edit/update the key and reactivate. For assistance please contact support.', 'blox' );
					$message_type = 'error';
				}
			}
		}

		// Print the notice
		if( ! empty( $message ) ) {
			echo '<div class="'. $message_type . '">';
				echo '<p>' . $message . '</p>';
			echo '</div>';
		}
		
		// Remove the transient, we don't need it anymore...
		delete_transient( 'blox_license_notices' );
	}
}
