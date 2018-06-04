<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the raw content section within the content tab and loads in all available options
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Content_Raw {

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

		add_filter( 'blox_content_type', array( $this, 'add_raw_content' ), 10 );
		add_action( 'blox_get_content_raw', array( $this, 'get_raw_content' ), 10, 4 );
		add_filter( 'blox_save_content_raw', array( $this, 'save_raw_content' ), 10, 3 );
		add_action( 'blox_print_content_raw', array( $this, 'print_raw_content' ), 10, 4 );

        // Add the fullscreen raw content modal to the admin page
        add_action( 'blox_metabox_modals', array( $this, 'add_raw_content_modal' ), 10, 1 );

        add_action( 'blox_metabox_scripts', array( $this, 'enqueue_raw_admin_scripts_styles' ), 10 );
    }


    /**
     * Add required slideshow scripts to the front-end
     *
     * @since 1.3.0
     */
    public function enqueue_raw_admin_scripts_styles() {

        $syntax_highlighting_disable = blox_get_option( 'syntax_highlighting_disable', false );
        $syntax_highlighting_theme   = blox_get_option( 'syntax_highlighting_theme', 'default' );

        if ( $syntax_highlighting_disable != true ) {

            // Load codemirror js
            wp_enqueue_script( $this->base->plugin_slug . '-codemirror-scripts', plugins_url( 'assets/plugins/codemirror/lib/codemirror.js', $this->base->file ), array(), $this->base->version );

            // Load codemirror modes
            wp_enqueue_script( $this->base->plugin_slug . '-codemirror-scripts-clike', plugins_url( 'assets/plugins/codemirror/mode/clike/clike.js', $this->base->file ), array(), $this->base->version );
            wp_enqueue_script( $this->base->plugin_slug . '-codemirror-scripts-css', plugins_url( 'assets/plugins/codemirror/mode/css/css.js', $this->base->file ), array(), $this->base->version );
            wp_enqueue_script( $this->base->plugin_slug . '-codemirror-scripts-htmlmixed', plugins_url( 'assets/plugins/codemirror/mode/htmlmixed/htmlmixed.js', $this->base->file ), array(), $this->base->version );
            wp_enqueue_script( $this->base->plugin_slug . '-codemirror-scripts-javascript', plugins_url( 'assets/plugins/codemirror/mode/javascript/javascript.js', $this->base->file ), array(), $this->base->version );
            wp_enqueue_script( $this->base->plugin_slug . '-codemirror-scripts-php', plugins_url( 'assets/plugins/codemirror/mode/php/php.js', $this->base->file ), array(), $this->base->version );
            wp_enqueue_script( $this->base->plugin_slug . '-codemirror-scripts-xml', plugins_url( 'assets/plugins/codemirror/mode/xml/xml.js', $this->base->file ), array(), $this->base->version );

            // Load codemirror addons
            // NONE YET

            // Load base codemirror styles
            wp_register_style( $this->base->plugin_slug . '-codemirror-styles', plugins_url( 'assets/plugins/codemirror/lib/codemirror.css', $this->base->file ), array(), $this->base->version );
            wp_enqueue_style( $this->base->plugin_slug . '-codemirror-styles' );

            // Optionally load a codemirror theme
            if ( $syntax_highlighting_theme != 'default' ) {
                wp_register_style( $this->base->plugin_slug . '-codemirror-' . $syntax_highlighting_theme . '-styles', plugins_url( 'assets/plugins/codemirror/theme/' . $syntax_highlighting_theme . '.css', $this->base->file ), array(), $this->base->version );
                wp_enqueue_style( $this->base->plugin_slug . '-codemirror-' . $syntax_highlighting_theme . '-styles' );
            }
        }
    }


	/**
	 * Enable the "raw" content option in the plugin
     *
     * @since 1.0.0
     *
     * @param array $content_types  An array of the content types available
     */
	public function add_raw_content( $content_types ) {
		$content_types['raw'] = __( 'Raw Content', 'blox' );
		return $content_types;
	}


	/**
	 * Prints all of the raw content ralated settings fields
     *
     * @since 1.0.0
     *
     * @param int $id             The block id
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param string $global      The block state
     */
	public function get_raw_content( $id, $name_prefix, $get_prefix, $global ) {

		// Get the type of block we are working with
		$block_scope = $global ? 'global' : 'local';
		?>

		<table class="form-table blox-content-raw blox-hidden">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Raw Content', 'blox' ); ?></th>
					<td>
                        <div class="blox-toolbar">
                            <div class="blox-toolbar-wrap">
                                <div class="blox-toolbar-button blox-raw-expand right" tabindex="-1" role="button" aria-label="<?php _e( 'Fullscreen', 'blox' );?>">
                                    <button role="presentation" type="button" tabindex="-1"><i class="fullscreen"></i></button>
                                </div>
                                <div style="clear: both;"></div>
                            </div>
                        </div>
                        <textarea class="blox-raw-output blox-enable-tab" name="<?php echo $name_prefix; ?>[raw][content]" rows="8" wrap="off" placeholder="<?php _e( 'Enter your content here...', 'blox' ); ?>"><?php echo ! empty( $get_prefix['raw']['content'] ) ? esc_html( $get_prefix['raw']['content'] ) : ''; ?></textarea>
                        <div class="blox-description">
                            <?php _e( 'By default, the Raw Content box will accept practically anything except PHP. When PHP is enabled, make sure to use correct syntax and wrap all PHP code in ', 'blox' ); ?><code>&#60;?php</code><?php _e( ' and ', 'blox' ); ?><code>?&#62;</code>
                        </div>
                    </td>
                </tr>
                <tr>
        			<th scope="row"><?php _e( 'Raw Settings', 'blox' ); ?></th>
        			<td>
						<label>
							<input type="checkbox" name="<?php echo $name_prefix; ?>[raw][shortcodes]" value="1" <?php ! empty( $get_prefix['raw']['shortcodes'] ) ? checked( esc_attr( $get_prefix['raw']['shortcodes'] ) ) : ''; ?> />
							<?php _e( 'Check to enable shortcodes', 'blox' ); ?>
						</label>
						<label>
							<input type="checkbox" name="<?php echo $name_prefix; ?>[raw][php]" value="1" <?php ! empty( $get_prefix['raw']['php'] ) ? checked( esc_attr( $get_prefix['raw']['php'] ) ) : ''; ?> />
							<?php _e( 'Check to enable PHP code', 'blox' ); ?>
						</label>
						<label class="last">
							<input type="checkbox" name="<?php echo $name_prefix; ?>[raw][disable_markup]" value="1" <?php ! empty( $get_prefix['raw']['disable_markup'] ) ? checked( esc_attr( $get_prefix['raw']['disable_markup'] ) ) : ''; ?> />
							<?php _e( 'Check to disable all markup', 'blox' ); ?>
						</label>
						<span class="blox-help-text-icon">
							<a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
						</span>
						<div class="blox-help-text">
							<?php _e( 'By default, Raw Content is wrapped in the following markup:', 'blox' ); ?> <code>&lt;div id="blox_<?php echo $block_scope . '_' . $id; ?>" class="blox-container ..."&gt;&lt;div class="blox-wrap"&gt;&lt;div class="blox-raw-container"&gt;&lt;div class="blox-raw-wrap"&gt; <?php _e( 'Your Raw Content ', 'blox' ); ?>&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;</code>. <?php _e( 'This setting removes all wrapper divs. Note that by removing all markup, most of the Style settings will no longer apply.', 'blox' ); ?>
						</div>

					</td>
				</tr>
			</tbody>
		</table>

		<?php
	}


	/**
	 * Saves all of the raw content ralated settings
     *
     * @since 1.0.0
     *
     * @param string $name_prefix The prefix for saving each setting (this brings ...['raw'] with it)
     * @param int $id             The block id
     * @param bool $global        The block state
     */
	public function save_raw_content( $name_prefix, $id, $global ) {

		$settings = array();

		// Encode the content before saving to the database
		$settings['content'] 		= htmlentities( $name_prefix['content'], ENT_QUOTES, 'UTF-8' );
		$settings['shortcodes'] 	= isset( $name_prefix['shortcodes'] ) ? 1 : 0;
		$settings['php'] 			= isset( $name_prefix['php'] ) ? 1 : 0;
		$settings['disable_markup'] = isset( $name_prefix['disable_markup'] ) ? 1 : 0;

		return $settings;
	}


	/**
	 * Prints all of the image content to the frontend
     *
     * @since 1.0.0
     *
     * @param array $content_data  All the content data for the block
     * @param int $id              The block id
     * @param array $block         All the block data
     * @param string $global       The block state, either "global" or "local"
     */
	public function print_raw_content( $content_data, $id, $block, $global ) {

		// Decode the content from the database so we can use scripts, php, etc.
		$content = html_entity_decode( $content_data['raw']['content'], ENT_QUOTES, 'UTF-8' );



		// The final output
		$output  = $content_data['raw']['shortcodes'] ? do_shortcode( $content ) : $content;

		// Array of additional CSS classes
		$classes = array();

		// If markup is disabled, not print any...
		echo $content_data['raw']['disable_markup'] == 1 ? '' : '<div class="blox-raw-container ' . implode( ' ', apply_filters( 'blox_content_raw_classes', $classes ) ) .'"><div class="blox-raw-wrap">';

			if ( $content_data['raw']['php'] ) {
				eval( '?>' . $output . '<?php ' ); // Extra space after <?php is crucial, otherwise error is thrown
			} else {
				echo $output;
			}

		echo $content_data['raw']['disable_markup'] == 1 ? '' : '</div></div>';
	}

    /**
	 * Checks to see if the block content contains a shortcode or php function for the same block, which would cause a site breaking loop
     *
     * @since 2.0.0
     *
     * @param string $content The content to be tested
     * @param int $id         The block id
     * @param string $global  The block state
     *
     * @return bool           The loop exists or not
     */
    public function check_for_shortcode_php_loop_existance( $content, $id, $global ) {

        $scope = $global ? 'global' : 'local';

        if ( empty( $content ) ) return false;

        // Check for shortcode loop
        $shortcode      = '[blox id="' . $scope . '_' . $block_id . '"]';
        $shortcode_test = strpos( $content, $shortcode );

        // Check for PHP loop
        $php      = 'blox_display_block( "' . $scope . '_' . $id . '" );';
        $php_test = $shortcode_test = strpos( $content, $php );

        // Site breaking loop exists?
        $result = ( $shortcode_test === true || $php_test === true ) ? true : false;

        return $result;
    }

    public function shortcode_php_loop_existance_alert() {
        ?>
        <div class="blox-alert">
            <p><?php _e( 'A potentially site breaking error has been detected. Please check this block\'s content to ensure it does not contain a shortcode or PHP function for the same block. Doing so creates a loop and would cause the site to break.', 'blox' );?></p>
        </div>
        <?php
    }

    /**
     * Adds the fullscreen raw content modal to the page
     *
     * @since 1.3.0
     *
     * @param bool $global The block state
     */
    public function add_raw_content_modal() {

        $syntax_highlighting_disable = blox_get_option( 'syntax_highlighting_disable', false );
        $syntax_highlighting_theme   = blox_get_option( 'syntax_highlighting_theme', 'default' );


        ?>
        <!--Raw Content Modal-->
        <div id="blox_raw" class='blox-hidden blox-modal'>

            <!-- Header -->
            <div class="blox-modal-titlebar">
                <span class="blox-modal-title"><?php _e( 'Raw Content', 'blox' ); ?></span>
                <button type="button" class="blox-modal-close">
					<span class="blox-modal-icon">
                        <span class="screen-reader-text"><?php _e( 'Close', 'blox' ); ?></span>
                    </span>
				</button>
            </div>

            <input type="text" id="blox_raw_block_type" class="blox-force-hidden" value="" />
            <input type="text" id="blox_raw_block_id" class="blox-force-hidden" value="" />

            <!-- Body -->
            <div class="blox-form-container">
                <div class="blox-modal-raw-container">
                    <div class="blox-modal-raw-header">
                    </div>
                    <textarea id="blox_raw_content" class="blox-enable-tab" wrap="off"></textarea>
                    <div class="blox-modal-raw-footer">
                        <div class="blox-description">
                            <?php _e( 'By default, the Raw Content box will accept practically anything except PHP. When PHP is enabled, make sure to use correct syntax and wrap all PHP code in ', 'blox' ); ?><code>&#60;?php</code><?php _e( ' and ', 'blox' ); ?><code>?&#62;</code>
                        </div>
                    </div>
                    <?php if ( $syntax_highlighting_disable != true ) { ?>
                    <script>
                        var blox_raw_fullscreen_editor = CodeMirror.fromTextArea(document.getElementById("blox_raw_content"), {
                            lineNumbers: true,
                            mode: "application/x-httpd-php",
                            indentUnit: 4,
                            indentWithTabs: true,
                            theme: "<?php echo $syntax_highlighting_theme; ?>",
                        });
                    </script>
                    <?php } ?>
                </div>
            </div>

            <!-- Footer -->
            <div class="blox-modal-footer">
                <div class="blox-modal-buttonpane">
                    <button id="blox_raw_insert" type="button" class="button button-primary blox-modal-button">
                        <?php _e( 'Apply Content', 'blox' );?>
                    </button>
                </div>
            </div>

        </div>
        <?php
    }



    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Content_Raw ) ) {
            self::$instance = new Blox_Content_Raw();
        }

        return self::$instance;
    }
}

// Load the raw content class.
$blox_content_raw = Blox_Content_Raw::get_instance();
