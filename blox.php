<?php
/**
 * Plugin Name: Blox
 * Plugin URI:  https://www.bloxwp.com
 * Description: Easily customize themes built on the Genesis Framework
 * Author:      Nick Diego
 * Author URI:  http://www.outermostdesign.com
 * Version:     1.4.1
 * Text Domain: blox
 * Domain Path: languages
 *
 * Blox is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Blox is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Blox. If not, visit <http://www.gnu.org/licenses/>.
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main plugin class.
 *
 * @since 1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 */
class Blox_Main {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $version = '1.4.1';

    /**
     * The name of the plugin.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_name = 'Blox';

    /**
     * The unique slug of the plugin.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_slug = 'blox';

    /**
     * Plugin file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Fire a hook before the class is setup.
        do_action( 'blox_pre_init' );

        // Make sure that Genesis is active before enabling the plugin
       	register_activation_hook( __FILE__ , array( $this, 'activation_check' ) );

       	// Disable the plugin if Genesis is not the active theme
		add_action( 'admin_init', array( $this, 'disable_check' ) );

        // Load the plugin textdomain.
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        // Load the plugin.
        add_action( 'init', array( $this, 'init' ), 0 );

        // Add additional links to the plugin's row on the admin plugin page
        add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
    }


	/**
	 * This function runs on plugin activation. It checks to make sure the required
	 * minimum Genesis version is installed. If not, it deactivates the plugin.
	 *
	 * @since 1.0.0
	 */
	public function activation_check() {

		$latest = '2.0';
		$theme_info = wp_get_theme( 'genesis' );

		if ( ! function_exists( 'genesis_pre' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate plugin
			wp_die( sprintf( __( 'Sorry, you can\'t activate %1$sBlox%2$s unless you have installed the %3$sGenesis Framework%4$s. Go back to the %5$sPlugins Page%4$s.', 'blox' ), '<em>', '</em>', '<a href="http://www.studiopress.com/themes/genesis" target="_blank">', '</a>', '<a href="javascript:history.back()">' ) );
		}

		if ( version_compare( $theme_info['Version'], $latest, '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate plugin
			wp_die( sprintf( __( 'Sorry, you can\'t activate %1$sBlox%2$s unless you have installed the %3$sGenesis %4$s%5$s. Go back to the %6$sPlugins Page%5$s.', 'blox' ), '<em>', '</em>', '<a href="http://www.studiopress.com/themes/genesis" target="_blank">', $latest, '</a>', '<a href="javascript:history.back()">' ) );
		}
	}


	/**
	 * This function runs on admin_init and checks to make sure Genesis is active, if not, it
	 * disables the plugin. This is useful for when users switch to non-Genesis themes. It does
	 * not "deactivate" the plugin, so as soon as you switch to a Genesis theme, the plugin
	 * works again.
	 *
	 * @since 1.0.0
	 */
	public function disable_check() {

		if ( ! function_exists('genesis_pre') ) {
			return;
		}
	}


    /**
     * Loads the plugin textdomain for translation.
     *
     * @since 1.0.0
     */
    public function load_textdomain() {

        load_plugin_textdomain( 'blox', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }


    /**
     * Loads the plugin into WordPress.
     *
     * @since 1.0.0
     */
    public function init() {

        // Run hook once Blox has been initialized.
        do_action( 'blox_init' );

       	// Plugin utility classes
        require plugin_dir_path( __FILE__ ) . 'includes/global/common.php';
        require plugin_dir_path( __FILE__ ) . 'includes/global/posttype.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/action-storage.php';

		// Settings class
		require plugin_dir_path( __FILE__ ) . 'includes/global/settings.php';

		// Content block settings classes that need to be global in scope
		require plugin_dir_path( __FILE__ ) . 'includes/global/visibility.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/location.php';

		// Content classes
		require plugin_dir_path( __FILE__ ) . 'includes/global/content/editor.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/content/image.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/content/raw.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/content/slideshow.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/content/shortcodes.php';

        // Load admin only components.
        if ( is_admin() ) {

			// Main admin classes
			require plugin_dir_path( __FILE__ ) . 'includes/admin/posttype.php';
			require plugin_dir_path( __FILE__ ) . 'includes/admin/metaboxes.php';
			require plugin_dir_path( __FILE__ ) . 'includes/admin/actions.php';

		    // Licensing and automatic updater classes
		    require plugin_dir_path( __FILE__ ) . 'includes/admin/license-settings.php';
			require plugin_dir_path( __FILE__ ) . 'includes/admin/license.php';
			require plugin_dir_path( __FILE__ ) . 'includes/admin/updater.php';

			// Load tools page
			require plugin_dir_path( __FILE__ ) . 'includes/admin/tools.php';

			// Content block settings classes
			require plugin_dir_path( __FILE__ ) . 'includes/admin/content.php';
			require plugin_dir_path( __FILE__ ) . 'includes/admin/position.php';
			require plugin_dir_path( __FILE__ ) . 'includes/admin/style.php';

			// All plugin notices, primarily license activation reminders
			require plugin_dir_path( __FILE__ ) . 'includes/admin/notices.php';
        }

        // Load frontend only components.
        if ( ! is_admin() ) {

        	// Class for generating all frontend markup
			require plugin_dir_path( __FILE__ ) . 'includes/frontend/frontend.php';
        }

        // Setup the Blox license
    	if ( class_exists( 'Blox_License' ) ) {
			$blox_blox_license = new Blox_License( __FILE__, $this->plugin_name, $this->version, 'Nicholas Diego', 'blox_blox_license_key' );
		}
    }


    /**
	 * Adds link to General Settings page in plugin row action links
	 *
	 * @since 1.0.0
	 *
	 * @param array $links  Already defined action links
	 * @param string $file  Plugin file path and name being processed
	 * @return array $links The new array of action links
	 */
	public function plugin_action_links( $links, $file ) {
		$settings_link = '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings' ) . '">' . __( 'Settings', 'blox' ) . '</a>';

		if ( $file == 'blox/blox.php' ) {
			array_unshift( $links, $settings_link );
		}

		return $links;
	}


	/**
	 * Adds additional links to the plugin row meta links
	 *
	 * @since 1.0.0
	 *
	 * @param array $links   Already defined meta links
	 * @param string $file   Plugin file path and name being processed
	 * @return array $links  The new array of meta links
	 */
	public function plugin_row_meta( $links, $file ) {

		// If we are not on the correct plugin, abort
		if ( $file != 'blox/blox.php' ) {
			return $links;
		}

		$docs_link = esc_url( add_query_arg( array(
				'utm_source'   => 'blox',
				'utm_medium'   => 'plugin',
				'utm_campaign' => 'Blox_Plugin_Links',
				'utm_content'  => 'plugins-page-link'
			), 'https://www.bloxwp.com/documentation/' )
		);

		$new_links = array(
			'<a href="' . $docs_link . '" target="_blank">' . esc_html__( 'Documentation', 'blox' ) . '</a>',
		);

		$links = array_merge( $links, $new_links );

		return $links;
	}


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Main ) ) {
            self::$instance = new Blox_Main();
        }

        return self::$instance;

    }
}

// Load the main plugin class.
$blox_main = Blox_Main::get_instance();
