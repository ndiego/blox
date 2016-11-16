<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the editor content (WP Editor) section within the content tab and loads in all available options
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Content_Editor {

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

		add_filter( 'blox_content_type', array( $this, 'add_editor_content' ), 11 );
		add_action( 'blox_get_content_editor', array( $this, 'get_editor_content' ), 10, 4 );
		add_filter( 'blox_save_content_editor', array( $this, 'save_editor_content' ), 10, 3 );
		add_action( 'blox_print_content_editor', array( $this, 'print_editor_content' ), 10, 4 );

		// Add the editor modal to the admin page
        add_action( 'blox_metabox_modals', array( $this, 'add_editor_modal' ), 10, 1 );
    }


	/**
	 * Enable the "custom" content (i.e. WP Editor) option in the plugin
     *
     * @since 1.0.0
     *
     * @param array $content_types  An array of the content types available
     */
	public function add_editor_content( $content_types ) {
		$content_types['editor'] = __( 'Editor Content', 'blox' );
		return $content_types;
	}


	/**
	 * Prints all of the editor ralated settings fields
     *
     * @since 1.0.0
     *
     * @param int $id             The block id
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param bool $global        The block state
     */
	public function get_editor_content( $id, $name_prefix, $get_prefix, $global ) {
		?>

		<!-- Wordpress Editor Settings -->
		<table class="form-table blox-content-editor blox-hidden">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Editor Content', 'blox' ); ?></th>
					<td>
						<?php if ( $global == false ) { ?>
							<a class="blox-editor-add button-primary button" href="#blox_editor"><?php ! empty( $get_prefix['editor']['content'] ) ? _e( 'Edit Content', 'blox' ) : _e( 'Add Content', 'blox' ); ?></a><a class="blox-editor-show-source button" href="#"><?php _e( 'Show HTML', 'blox' );?></a>
							<div class="blox-editor-output-wrapper">
								<textarea class="blox-editor-output blox-textarea-code" name="<?php echo $name_prefix; ?>[editor][content]"rows="6" placeholder="<?php _e( 'No content yet... Use button above, or type HTML content in manually.', 'blox' ); ?>"><?php echo ! empty( $get_prefix['editor']['content'] ) ? esc_attr( $get_prefix['editor']['content'] ) : ''; ?></textarea>
								<div class="blox-description">
									<?php _e( 'The Editor Content option will not accept any scripts, iframes, unsafe HTML, or PHP. Use the Raw Content option for this type of content.', 'blox' ); ?>
								</div>
							</div>
						<?php } else { ?>
							<?php
							$blox_editor_settings = array(
								'media_buttons' => true,
								'quicktags'     => true,
								'teeny'         => false,
								'textarea_rows' => get_option( 'default_post_edit_rows', 6 ),
								'textarea_name' => $name_prefix . '[editor][content]'
							);
							wp_editor( $get_prefix['editor']['content'], 'blox_editor_master', $blox_editor_settings );
							?>

							<div class="blox-description">
								<?php _e( 'The editor will not accept any scripts, iframes, unsafe HTML, or PHP. Use the Raw Content option for this type of content.', 'blox' ); ?>
							</div>
						<?php } ?>
					</td>
				</tr>
			</tbody>
		</table>

		<?php
	}


	/**
	 * Saves all of the editor ralated settings
     *
     * @since 1.0.0
     *
     * @param string $name_prefix The prefix for saving each setting (this brings ...['editor'] with it)
     * @param int $id             The block id
     * @param bool $global        The block state
     */
	public function save_editor_content( $name_prefix, $id, $global ) {

		$settings = array();

		$settings['content'] = wpautop( wp_kses_post( $name_prefix['content'] ) );

		return $settings;
	}


	/**
	 * Added the editor modal to the page
     *
     * @since 1.0.0
	 *
	 * @param bool $global The block state
     */
	public function add_editor_modal( $global ) {

		// Only load modal if we are working with local content blocks
		if ( $global == false ) {
		?>
			<!--Content Editor Modal-->
			<div id="blox_editor" class="blox-hidden blox-modal" title="<?php _e( 'Content Editor', 'blox' );?>">

				<!-- Header -->
				<div class="blox-modal-titlebar">
					<span class="blox-modal-title"><?php _e( 'Content Editor', 'blox' );?> – <span id="editor-title"></span></span>
					<button type="button" class="blox-modal-close" title="<?php _e( 'Close', 'blox' );?>">
						<span class="blox-modal-close-icon"></span>
						<span class="blox-modal-close-text"><?php _e( 'Close', 'blox' );?></span>
					</button>
				</div>

				<input type="text" id="blox_editor_master_id" class="blox-force-hidden" value="" />

				<!-- Body -->
				<div class="blox-form-container">
					<?php
					// Editor tinymce settings
					$blox_editor_settings = array(
						'media_buttons' => true,
						'tinymce'		=> true,
						'quicktags'     => true,
						'textarea_rows' => get_option('default_post_edit_rows', 30),
						'teeny'         => false,
						'tinymce' 		=> array( 'resize' => true, 'wp_autoresize_on' => false ),
						'textarea_name' => ''
					);
					?>
					<div id="blox_editor_master_wrapper">
						<?php wp_editor( '', 'blox_editor_master', $blox_editor_settings ); ?>
						<div class="blox-description">
							<?php _e( 'The editor will not accept any scripts, iframes, unsafe HTML, or PHP. Use the Raw Content option for this type of content.', 'blox' ); ?>
						</div>
					</div>
				</div>

				<!-- Footer -->
				<div class="blox-modal-footer">
					<div class="blox-modal-buttonpane">
						<button id="blox_editor_insert" type="button" class="button button-primary blox-modal-button">
							<?php _e( 'Apply Content', 'blox' );?>
						</button>
					</div>
				</div>

			</div>
		<?php
		}
	}


	/**
	 * Prints the editor content to the frontend
     *
     * @since 1.0.0
     *
     * @param int $id             The block id
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param string $global      The block state
     */
	public function print_editor_content( $content_data, $block_id, $block, $global ) {

		// Array of additional CSS classes
		$classes = array();
		?>
		<div class="blox-editor-container <?php echo implode( ' ', apply_filters( 'blox_content_editor_classes', $classes ) ); ?>">
			<div class="blox-editor-wrap">
				<?php echo do_shortcode( wp_kses_post( $content_data['editor']['content'] ) ); ?>
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

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Content_Editor ) ) {
            self::$instance = new Blox_Content_Editor();
        }

        return self::$instance;
    }
}

// Load the editor content class.
$blox_content_editor = Blox_Content_Editor::get_instance();
