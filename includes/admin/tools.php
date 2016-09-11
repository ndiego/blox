<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates all Blox settings
 *
 * @since 	1.2.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Tools {
 
    /**
     * Holds the class object.
     *
     * @since 1.2.0
     *
     * @var object
     */
    public static $instance;


    /**
     * Path to the file.
     *
     * @since 1.2.0
     *
     * @var string
     */
    public $file = __FILE__;


    /**
     * Holds the base class object.
     *
     * @since 1.2.0
     *
     * @var object
     */
    public $base;


    /**
     * Primary class constructor.
     *
     * @since 1.2.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Blox_Main::get_instance();
        		
		add_action( 'admin_menu', array( $this, 'add_menu_links' ), 10 );
		//add_action( 'admin_init', array( $this, 'register_settings' ), 10 );
		
		// Enqueue Setting scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );


		add_action( 'blox_tools_system_info', array( $this, 'print_system_info' ) );
		//add_action( 'blox_tools_system_info', array( $this, 'download_system_info' ) );
		
		add_action( 'blox_tools_import_export', array( $this, 'print_import_export' ) );
		
		add_action( 'blox_download_system_info', array( $this, 'download_system_info' ) );

    }
    
    

    
    /**
     * Add the Settings menu link.
     *
     * @since 1.2.0
     */
    public function add_menu_links() {
		
		// Add our main settings menu link
		add_submenu_page( 'edit.php?post_type=blox', __( 'Blox Tools', 'blox' ), __( 'Tools', 'blox' ), 'manage_options', 'blox-tools', array( $this, 'print_tools_page' ) );
	}
	
	
	/**
     * Print settings page.
     *
     * @since 1.2.0
     */
	public function print_tools_page() {

		// Get the active tab
		$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->get_settings_tabs() ) ? $_GET['tab'] : 'system_info';

		ob_start();
		?>
		<div class="wrap">
			<h2><?php _e( 'Blox Tools', 'blox' ); ?></h2>
		
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
				
					<?php do_action( 'blox_tools_form_top', $active_tab ); ?>
					
						<?php
						do_action( 'blox_tools_' . $active_tab, $active_tab );
						?>
					</table>
					<?php do_action( 'blox_tools_form_bottom', $active_tab ); ?>
			</div><!-- #tab_container-->
		</div><!-- .wrap -->
		<?php
		echo ob_get_clean();
	}

	
	/**
	 * Retrieve our settings tabs
	 *
	 * @since 1.2.0
	 *
	 * @return array $tabs An array of all available tabs
	 */
	public function get_settings_tabs() {

		// $settings = blox_get_registered_settings();
	
		$settings = array();

		$tabs             = array();
		$tabs['system_info']    = __( 'System Info', 'blox' );
		//$tabs['import_export']  = __( 'Import/Export', 'blox' );

		return apply_filters( 'blox_tools_tabs', $tabs );
	}
	
	
	public function print_system_info( $active_tab ) {
		
		?>
		<form action="<?php echo esc_url( admin_url( 'edit.php?post_type=blox&page=blox-tools&tab=system_info' ) ); ?>" method="post" dir="ltr">
			
			<div class="system-info-textarea">
				<textarea readonly="readonly" onclick="this.focus(); this.select()" name="blox-system-info"><?php 
					$output = '## Begin System Info ##' . "\n";
				
					foreach ( $this->get_system_info( 'text' ) as $section ) {
						if ( ! empty( $section ) ) { 

							$output .= "\n" . '–– ' . $section['title'] . "\n\n";

							foreach ( $section['atts'] as $att ) {
								$output .= $att['title'] . ': ' . $att['val'] . "\n"; 
							}
						}
					}
					$output .= "\n" . '### End System Info ###';
				
					echo $output;
				?></textarea>
			
			<p class="description">
				<?php _e( 'Copy the above system information and include it with your support request.', 'blox' ); ?>
			</p>
			</div>
			
			<div class="get-system-info">
				<p class="submit">
					<a class="button button-primary" onclick="jQuery('.system-info-textarea').toggle();"><?php _e( 'Copy System Info', 'blox' ); ?></a>
				</p>
				<p class="submit">
					<input type="hidden" name="blox-action" value="download_system_info" />
					<?php submit_button( __( 'Download System Info', 'blox' ), false, 'download-system-info', false ); ?>
				</p>			
			</div>	
		</form>
			
		<table class="system-info-table" cellspacing="0">
			<?php
			foreach ( $this->get_system_info( 'html' ) as $section ) {
				if ( ! empty( $section ) ) {
					?>
					<thead>
						<tr><th colspan="2"><?php echo $section['title']; ?></th></tr>
					</thead>
					<tbody>
						<?php foreach ( $section['atts'] as $att ) { ?>
							<tr>
								<td><?php echo $att['title']; ?></td>
								<td><?php echo $att['val']; ?></td>
							</tr>
						<?php } ?>
					</tbody>
					<?php
				}	
			}			
			?>
		</table>
		<?php
	}
	
	
	
	/**
	 * Generates the System Info Download File
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function download_system_info() {
		nocache_headers();

		header( "Content-type: text/plain" );
		header( 'Content-Disposition: attachment; filename="blox-system-info.txt"' );

		echo wp_strip_all_tags( $_POST['blox-system-info'] );
		exit();
	}


	
	public function get_system_info( $type ) {
	
		global $wpdb;
	
		$break = $type == 'html' ? '<br>' : "\n";
		
		// Get all plugin info
		$updates   		= get_plugin_updates();
		$plugins   		= get_plugins();
		$muplugins 		= get_mu_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		$blox_data      = $plugins['blox/blox.php'];
		$blox_settings  = get_option( 'blox_settings' );
		
		if ( $this->base->plugin_slug == 'blox' ) {
			foreach( blox_get_licenses() as $name => $license ) {
				$licenses[] = $name . ': ' . $license;
			}
			$licenses = implode( $break, $licenses );
		} else {
			$licenses = '-';
		}
		
		$custom_post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'names', 'and' );
	
		// Get theme info
		$theme_data = wp_get_theme();
		
		$theme_name = ( $theme_data->get( 'ThemeURI' ) == '' || $type != 'html' ) ? $theme_data->get( 'Name' ) : '<a href="' . $theme_data->get( 'ThemeURI' ) . '" target="_blank">' . $theme_data->get( 'Name' ) . '</a>';
		$theme_author_name = ( $theme_data->get( 'AuthorURI' ) == '' || $type != 'html' ) ? $theme_data->get( 'Author' ) : '<a href="' . $theme_data->get( 'AuthorURI' ) . '" target="_blank">' . $theme_data->get( 'Author' ) . '</a>';
		$theme = $theme_name . ' by ' . $theme_author_name . ': ' . __( 'Version', 'blox' ) . ' ' . $theme_data->Version;
		
		$front_page_id = get_option( 'page_on_front' );
		$blog_page_id  = get_option( 'page_for_posts' );
		
		/* Make sure wp_remote_post() is working NEED TO GET WORKING AND UNDERSTAND!!!!
		$request['cmd'] = '_notify-validate';

		$params = array(
			'sslverify'     => false,
			'timeout'       => 60,
			'user-agent'    => 'EDD/' . EDD_VERSION,
			'body'          => $request
		);

		$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

		if( !is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
			$WP_REMOTE_POST = 'wp_remote_post() works';
		} else {
			$WP_REMOTE_POST = 'wp_remote_post() does not work';
		}
		*/
		
		$info = array(
			'wordpress-environment' => array(
				'title' => __( 'WordPress Environment', 'blox' ),
				'atts'  => array(
					'site-url' => array(
						'title' => __( 'Site URL', 'blox' ),
						'val'	=> site_url(),
					),
					'home-url' => array(
						'title' => __( 'Home URL', 'blox' ),
						'val'	=> home_url(),
					),
					'multisite' => array(
						'title' => __( 'Multisite Enabled', 'blox' ),
						'val'	=> ( is_multisite() ? __( 'Enabled', 'blox' ) : '-' ),
					),
					'version' => array(
						'title' => __( 'WordPress Version', 'blox' ),
						'val'	=> get_bloginfo( 'version' ),
					),
					'genesis' => array(
						'title' => __( 'Genesis Version', 'blox' ),
						'val'	=> PARENT_THEME_VERSION,
					),
					'child' => array(
						'title' => __( 'Active Child Theme', 'blox' ),
						'val'	=> is_child_theme() ? $theme : __( 'No active Genesis child theme', 'blox' ),
					),
					'language' => array(
						'title' => __( 'Language', 'blox' ),
						'val'	=> defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US',
					),
					'font-displays' => array(
						'title' => __( 'Front Page Displays', 'blox' ),
						'val'	=> get_option( 'show_on_front' ) == 'page' ? __( 'A static page', 'blox' ) : __( 'Your latest posts', 'blox' ),
					),
					'font-page' => array(
						'title' => __( 'Front Page', 'blox' ),
						'val'	=> ( get_option( 'show_on_front' ) == 'page' && $front_page_id != 0 ) ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : '-',
					),
					'posts-page' => array(
						'title' => __( 'Posts Page', 'blox' ),
						'val'	=> ( get_option( 'show_on_front' ) == 'page' && $blog_page_id != 0 ) ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : '-',
					),
					'permalink' => array(
						'title' => __( 'Permalink Structure', 'blox' ),
						'val'	=> get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default',
					),
					'ABSPATH' => array(
						'title' => __( 'ABSPATH', 'blox' ),
						'val'	=> ABSPATH,
					),
					'debug' => array(
						'title' => __( 'Debug', 'blox' ),
						'val'	=> defined( 'WP_DEBUG' ) ? WP_DEBUG ? __( 'Enabled', 'blox' ) : '-' : '-',
					),
					'memory' => array(
						'title' => __( 'Memory Limit', 'blox' ),
						'val'	=> WP_MEMORY_LIMIT,
					),
					/*'remote-post' => array(
						'title' => __( 'Remote Post', 'blox' ),
						'val'	=> '',
					),*/
				),
			),
			'blox' => array(
				'title' => __( 'Blox Settings', 'blox' ),
				'atts'  => array(
					'type' => array(
						'title' => __( 'Type', 'blox' ),
						'val'	=> $this->base->plugin_name,
					),
					'version' => array(
						'title' => __( 'Version', 'blox' ),
						'val'	=> $this->base->version,
					),
					'global-enable' => array(
						'title' => __( 'Global Blocks', 'blox' ),
						'val'	=> ! empty( $blox_settings['global_enable'] ) ? __( 'Enabled', 'blox' ) : '-',
					),
					'local-enable' => array(
						'title' => __( 'Local Blocks', 'blox' ),
						'val'	=> ! empty( $blox_settings['local_enable'] ) ? __( 'Enabled', 'blox' ) : '-',
					),
					'local-enabled-on' => array(
						'title' => __( 'Local Blocks Enabled On', 'blox' ),
						'val'	=> ! empty( $blox_settings['local_enabled_pages'] ) ? implode( ', ', $blox_settings['local_enabled_pages'] ) : '-',
					),
					'available-post-types' => array(
						'title' => __( 'Available Post Types', 'blox' ),
						'val'	=> 'post, page, ' . implode( ', ', get_post_types( array( 'public' => true, '_builtin' => false ), 'names', 'and' ) ),
					),
					'active-addons' => array(
						'title' => __( 'Active Addons', 'blox' ),
						'val'	=> $this->base->plugin_slug == 'blox-lite' ? '-' : implode( $break, blox_get_active_addons() ),
					),
					'licenses' => array(
						'title' => __( 'Licenses', 'blox' ),
						'val'	=> $licenses,
					),

				)
			),
			'plugins' => array(
				'title' => __( 'Plugins', 'blox' ),
				'atts'  => array()
			),
			'server-environment' => array(
				'title' => __( 'Server Environment', 'blox' ),
				'atts'  => array(
					'server-info' => array(
						'title' => __( 'Server Info', 'blox' ),
						'val'	=> $_SERVER['SERVER_SOFTWARE'],
					),
					'php-version' => array(
						'title' => __( 'PHP Version', 'blox' ),
						'val'	=> PHP_VERSION,
					),
					'mysql-version' => array(
						'title' => __( 'MySQL Version', 'blox' ),
						'val'	=> $wpdb->db_version(),
					),
					'php-post-max-size' => array(
						'title' => __( 'Post Max Size', 'blox' ),
						'val'	=> ini_get( 'post_max_size' ),
					),
					'max-upload-size' => array(
						'title' => __( 'Max Upload Size', 'blox' ),
						'val'	=> ini_get( 'upload_max_filesize' ),
					),
					'php-time-limit' => array(
						'title' => __( 'Time Limit', 'blox' ),
						'val'	=> ini_get( 'max_execution_time' ),
					),
					'php-max-input-vars' => array(
						'title' => __( 'Max Input Vars', 'blox' ),
						'val'	=> ini_get( 'max_input_vars' ),
					),
					'cURL' => array(
						'title' => __( 'cURL', 'blox' ),
						'val'	=> function_exists( 'curl_init' ) ? 'Supported' : '-',
					),
					'fsockopen' => array(
						'title' => __( 'fsockopen', 'blox' ),
						'val'	=> function_exists( 'fsockopen' ) ? 'Supported' : '-',
					),
					'soapclient' => array(
						'title' => __( 'SoapClient', 'blox' ),
						'val'	=> class_exists( 'SoapClient' ) ? 'Installed' : '-',
					),
					'suhosin' => array(
						'title' => __( 'SUHOSIN', 'blox' ),
						'val'	=> extension_loaded( 'suhosin' ) ? 'Installed' : '-',
					),
				),
			),
		);

		
		// Get all must-use plugins (these doesn't show updates so don't worry about that)
		$must_use = '';
		
		if( count( $muplugins ) > 0 ) {
			foreach( $muplugins as $plugin => $plugin_data ) {
				$must_use .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . $break;
			}
		}
		
		if ( $must_use != '' ) {
			$info['plugins']['atts']['must-use'] = array(
				'title' => __( 'Must-Use Plugins', 'blox' ),
				'val'	=> $must_use,
			);
		}
		
		// Get all active plugins
		$active = '';
		
		foreach( $plugins as $plugin_path => $plugin ) {
			if( ! in_array( $plugin_path, $active_plugins ) ) {
				continue;
			}
			
			$plugin_name        = ( empty( $plugin['PluginURI'] ) || $type != 'html' ) ? $plugin['Name'] : '<a href="' . $plugin['PluginURI'] . '" target="_blank">' . $plugin['Name'] . '</a>';
			$plugin_author_name = ( empty( $plugin['AuthorURI'] ) || $type != 'html' ) ? $plugin['AuthorName'] : '<a href="' . $plugin['AuthorURI'] . '" target="_blank">' . $plugin['AuthorName'] . '</a>';

			$update   = ( array_key_exists( $plugin_path, $updates ) ) ? ' (' . __( 'update available', 'blox' ) . ' - ' . $updates[$plugin_path]->update->new_version . ')' : '';
			$active .= $plugin_name . ' by ' . $plugin_author_name . ': ' . __( 'Version', 'blox' ) . ' ' . $plugin['Version'] . $update . $break;
		}
		
		if ( $active != '' ) {
			$info['plugins']['atts']['active'] = array(
				'title' => __( 'Active Plugins', 'blox' ),
				'val'	=> $active,
			);
		}
		
		// Get all inactive plugins
		$inactive = '';
		
		foreach( $plugins as $plugin_path => $plugin ) {
			if( in_array( $plugin_path, $active_plugins ) ) {
				continue;
			}
			
			$plugin_name        = ( empty( $plugin['PluginURI'] ) || $type != 'html' ) ? $plugin['Name'] : '<a href="' . $plugin['PluginURI'] . '" target="_blank">' . $plugin['Name'] . '</a>';
			$plugin_author_name = ( empty( $plugin['AuthorURI'] ) || $type != 'html' ) ? $plugin['AuthorName'] : '<a href="' . $plugin['AuthorURI'] . '" target="_blank">' . $plugin['AuthorName'] . '</a>';

			$update   = ( array_key_exists( $plugin_path, $updates ) ) ? ' (' . __( 'update available', 'blox' ) . ' - ' . $updates[$plugin_path]->update->new_version . ')' : '';
			$inactive .= $plugin_name . ' by ' . $plugin_author_name . ': ' . __( 'Version', 'blox' ) . ' ' . $plugin['Version'] . $update . $break;
		}
		
		if ( $inactive != '' ) {
			$info['plugins']['atts']['inactive'] = array(
				'title' => __( 'Inactive Plugins', 'blox' ),
				'val'	=> $inactive,
			);
		}
		
		// Get all network active plugins (if on multisite)
		if ( is_multisite() ) {

			$plugins = wp_get_active_network_plugins();
			$active_plugins = get_site_option( 'active_sitewide_plugins', array() );
			
			$network_active = '';

			foreach( $plugins as $plugin_path ) {
				$plugin_base = plugin_basename( $plugin_path );

				if( ! array_key_exists( $plugin_base, $active_plugins ) ) {
					continue;
				}

				$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (' . __( 'update available', 'blox' ) . ' - ' . $updates[$plugin_path]->update->new_version . ')' : '';
				$plugin = get_plugin_data( $plugin_path );
				$plugin_name = ( empty( $plugin['PluginURI'] ) || $type != 'html' ) ? $plugin['Name'] : '<a href="' . $plugin['PluginURI'] . '" target="_blank">' . $plugin['Name'] . '</a>';
				$plugin_author_name = ( empty( $plugin['AuthorURI'] ) || $type != 'html' ) ? $plugin['AuthorName'] : '<a href="' . $plugin['AuthorURI'] . '" target="_blank">' . $plugin['AuthorName'] . '</a>';
				$network_active .= $plugin_name . ' by ' . $plugin_author_name . ': ' . __( 'Version', 'blox' ) . ' ' . $plugin['Version'] . $update . $break;
			}
	
			if ( $network_active != '' ) {
				$info['plugins']['atts']['network-active'] = array(
					'title' => __( 'Network Active Plugins', 'blox' ),
					'val'	=> $network_active,
				);
			}
		}
		
		return $info;
	
	}


	
	/**
	 * Enqueue scripts and styles
	 *
	 * @since 1.2.0
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
     * @since 1.2.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Tools ) ) {
            self::$instance = new Blox_Tools();
        }

        return self::$instance;
    }
}
// Load the settings class.
$blox_tools = Blox_Tools::get_instance();


