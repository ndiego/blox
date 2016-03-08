<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the style tab and loads in all the available options
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Style {
 
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
		
		// Setup style settings
		add_filter( 'blox_metabox_tabs', array( $this, 'add_style_tab' ), 30 );
		add_action( 'blox_get_metabox_tab_style', array( $this, 'get_metabox_tab_style' ), 10, 4 );
		add_filter( 'blox_save_metabox_tab_style', array( $this, 'save_metabox_tab_style' ), 10, 3 );
    }


	/**
	 * Add the Style tab
     *
     * @since 1.0.0
     *
     * @param array $tab  An array of the tabs available
     * @return array $tab The updated tabs array
     */
	public function add_style_tab( $tabs ) {
		
		$tabs['style'] = array( 
			'title' => __( 'Style', 'blox' ),
			'scope' => 'all'  // all, local, or global
		);
		
		return $tabs;
	}
	
	
	/**
     * Creates the style settings fields
     *
     * @since 1.0.0
     *
     * @param array $data         An array of all block data
     * @param string $name_id 	  The prefix for saving each setting
     * @param string $get_id  	  The prefix for retrieving each setting
     * @param bool $global	      The block state
     */
	public function get_metabox_tab_style( $data = null, $name_id, $get_id, $global ) {
	
		if ( $global ) {
			// Indicates where the style settings are saved
			$name_prefix = "blox_content_blocks_data[style]";
			$get_prefix = ! empty( $data['style'] ) ? $data['style'] : null;
			
		} else {
			// Indicates where the style settings are saved
			$name_prefix = "blox_content_blocks_data[$name_id][style]";
		
			// Used for retrieving the style settings
			// If $data = null, then there are no settings to get
			if ( $data == null ) {
				$get_prefix = null;
			} else {
				$get_prefix = ! empty( $data[$get_id]['style'] ) ? $data[$get_id]['style'] : null;
			}		
		}
	
		// Get the content for the style tab
		$this->style_settings( $get_id, $name_prefix, $get_prefix, $global );
    }
    
    
	
    /**
     * Creates all of the fields for our style settings
     *
     * @since 1.0.0
     *
     * @param int $id             The block id 
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param bool $global	      Determines if the content being loaded for local or global blocks
     */
    public function style_settings( $id, $name_prefix, $get_prefix, $global ) {
    
    	// Check is default Blox CSS is globally disabled
    	$global_disable_default_css = blox_get_option( 'disable_default_css' );
    	
    	// Get the type of block we are working with
		$block_scope = $global ? 'global' : 'local';

		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Custom Block Classes', 'blox' ); ?></th>
					<td>
						<input type="text" class="blox-half-text" name="<?php echo $name_prefix; ?>[custom_classes]" value="<?php echo ! empty( $get_prefix['custom_classes'] ) ? esc_attr( $get_prefix['custom_classes'] ) : ''; ?>" placeholder="e.g. class-one class-two"/>
						<div class="blox-description">
							<?php _e( 'Enter a space separated list of custom CSS classes to add to this content block.', 'blox' ); ?>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Custom Block CSS', 'blox' ); ?></th>
					<td>
						<textarea class="blox-textarea-code" name="<?php echo $name_prefix; ?>[custom_css]" rows="6" placeholder="<?php echo 'e.g. #blox_' . $block_scope . '_' . $id . ' { border: 1px solid green; }'; ?>"><?php echo ! empty( $get_prefix['custom_css'] ) ? esc_html( $get_prefix['custom_css'] ) : ''; ?></textarea>	
						<div class="blox-description">
							<?php echo __( 'All custom CSS for this block should begin with ', 'blox' ) . '<code>#blox_' . $block_scope . '_' . $id . '</code>. ' . sprintf( __( 'Otherwise the custom CSS could interfere with other content blocks. For reference on content block frontend markup, please refer to the %1$sBlox Documentation%2$s.', 'blox' ), '<a href="https://www.bloxwp.com/documentation/frontend-markup/?utm_source=blox-lite&utm_medium=plugin&utm_content=style-tab-links&utm_campaign=Blox_Plugin_Links" title="' . __( 'Blox Documentation', 'blox' ) . '" target="_blank"target="_blank">', '</a>' ); ?>
						</div>
					</td>
				</tr>
				<tr class="<?php echo ! empty( $global_disable_default_css ) ? 'blox-hidden' : '';?>">
					<th scope="row"><?php _e( 'Disable Default CSS', 'blox' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo $name_prefix; ?>[disable_default_css]" value="1" <?php ! empty( $get_prefix['disable_default_css'] ) ? checked( esc_attr( $get_prefix['disable_default_css'] ) ) : ''; ?> />
							<?php _e( 'Check to disable all default styles on this block', 'blox' ); ?>
						</label>
						<span class="blox-help-text-icon">
							<a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
						</span>
						<div class="blox-help-text top">
							<?php echo __( 'Blox includes default CSS to provide minimal styling. This option will remove all default styling from this block, which can be useful when custom CSS is used extensively.', 'blox' ); ?>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Enable Wrap', 'blox' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo $name_prefix; ?>[enable_wrap]" value="1" <?php ! empty( $get_prefix['enable_wrap'] ) ? checked( esc_attr( $get_prefix['enable_wrap'] ) ) : ''; ?> />
							<?php echo sprintf( __( 'Check to include the %1$swrap%2$s CSS selector in the block markup.', 'blox' ), '<code>', '</code>' ); ?>
						</label>
						<span class="blox-help-text-icon">
							<a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
						</span>
						<div class="blox-help-text top">
							<?php _e( 'Many Genesis child themes use this selector and enabling it can assist with block styling.', 'blox' ); ?>
						</div>
					</td>
				</tr>
				
				<?php do_action( 'blox_style_settings', $id, $name_prefix, $get_prefix, $global ); ?>	
				
			</tbody>
		</table>
		<?php
	}


    /** 
	 * Saves all of the style settings
     *
     * @since 1.0.0
     *
     * @param int $post_id        The global block id or the post/page/custom post-type id corresponding to the local block 
     * @param string $name_prefix The prefix for saving each setting
     * @param bool $global        The block state
     *
     * @return array $settings    Return an array of updated settings
     */
    public function save_metabox_tab_style( $post_id, $name_prefix, $global ) {
		
		$settings = array();
		
		$settings['custom_classes'] 	 = trim( strip_tags( $name_prefix['custom_classes'] ) );
		$settings['custom_css']     	 = isset( $name_prefix['custom_css'] ) ? trim( esc_html( $name_prefix['custom_css'] ) ) : '';
		$settings['enable_wrap']    	 = isset( $name_prefix['enable_wrap'] ) ? 1 : 0;
		$settings['disable_default_css'] = isset( $name_prefix['disable_default_css'] ) ? 1 : 0;

		return apply_filters( 'blox_save_style_settings', $settings, $post_id, $name_prefix, $global );
	}


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Style ) ) {
            self::$instance = new Blox_Style();
        }

        return self::$instance;
    }
}

// Load the style class.
$blox_style = Blox_Style::get_instance();
