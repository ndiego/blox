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
				<tr class="blox-content-title"><th scope="row"><?php _e( 'Raw Content Settings', 'blox' ); ?></th><td><hr></td></tr>
				<tr>
					<th scope="row"><?php _e( 'Raw Content', 'blox' ); ?></th>
					<td>
						<textarea class="blox-textarea-code" name="<?php echo $name_prefix; ?>[raw][content]" rows="6" ><?php echo ! empty( $get_prefix['raw']['content'] ) ? esc_html( $get_prefix['raw']['content'] ) : ''; ?></textarea>
						<div class="blox-description">
							<?php _e( 'By default, the Raw Content box will accept practically anything except PHP. When PHP is enabled, make sure to use correct syntax and wrap all PHP code in ', 'blox' ); ?><code>&#60;?php</code><?php _e( ' and ', 'blox' ); ?><code>?&#62;</code>
						</div>

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
     * @param int $id             The block id
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param string $global      The block state
     */
	public function print_raw_content( $content_data, $block_id, $block, $global ) {

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
