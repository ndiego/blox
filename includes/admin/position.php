<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the poistion tab and loads in all the available options
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Position {

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

		// Setup position settings
		add_filter( 'blox_metabox_tabs', array( $this, 'add_position_tab' ), 5 );
		add_action( 'blox_get_metabox_tab_position', array( $this, 'get_metabox_tab_position' ), 10, 4 );
		add_filter( 'blox_save_metabox_tab_position', array( $this, 'save_metabox_tab_position' ), 10, 3 );
		
		// Add the admin column data for global blocks
		add_filter( 'blox_admin_column_titles', array( $this, 'admin_column_title' ), 2, 1 );
		add_action( 'blox_admin_column_data_position', array( $this, 'admin_column_data' ), 10, 2 );
    
    	// Make admin column sortable
		add_filter( 'manage_edit-blox_sortable_columns', array( $this, 'admin_column_sortable' ), 5 );
        add_filter( 'request', array( $this, 'admin_column_orderby' ) );
    }


	/**
	 * Add the Position tab
     *
     * @since 1.0.0
     *
     * @param array $tab  An array of the tabs available
     * @return array $tab The updated tabs array
     */
	public function add_position_tab( $tabs ) {

		$tabs['position'] = array(
			'title' => __( 'Position', 'blox' ),
			'scope' => 'all'  // all, local, or global
		);

		return $tabs;
	}


    /**
     * Creates the position settings fields
     *
     * @since 1.0.0
     *
     * @param array $data         An array of all block data
     * @param string $name_id 	  The prefix for saving each setting
     * @param string $get_id  	  The prefix for retrieving each setting
     * @param bool $global	      The block state
     */
	public function get_metabox_tab_position( $data = null, $name_id, $get_id, $global ) {

    	if ( $global ) {

			// Indicates where the visibility settings are saved
			$name_prefix = "blox_content_blocks_data[position]";
			$get_prefix = ! empty( $data['position'] ) ? $data['position'] : null;

		} else {

			// Indicates where the position settings are saved
			$name_prefix = "blox_content_blocks_data[$name_id][position]";

			// Used for retrieving the position settings
			// If $data = null, then there are no settings to get
			if ( $data == null ) {
				$get_prefix = null;
			} else {
				$get_prefix = ! empty( $data[$get_id]['position'] ) ? $data[$get_id]['position'] : null;
			}

		}

		// Get the content for the position tab
		$this->position_settings( $name_id, $name_prefix, $get_prefix, $global );
    }


    /**
     * Creates all of the fields for our block positioning
     *
     * @since 1.0.0
     *
     * @param int $id             The id of the content block, either global or individual (attached to post/page/cpt)
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param bool $global	      Determines if the content being loaded for local or global blocks
     */
    public function position_settings( $id, $name_prefix, $get_prefix, $global ) {
		?>
		<table class="form-table">
			<tbody>
				<tr class="blox-position-type">
					<th scope="row"><?php echo __( 'Position Type', 'blox' ); ?></th>
					<td>
						<select name="<?php echo $name_prefix; ?>[position_type]" id="blox_position_type_<?php echo $id; ?>">
							<option value="default" <?php echo ! empty( $get_prefix['position_type'] ) ? selected( esc_attr( $get_prefix['position_type'] ), 'default' ) : 'selected'; ?>><?php _e( 'Default', 'blox' ); ?></option>
							<option value="custom" <?php echo ! empty( $get_prefix['position_type'] ) ? selected( esc_attr( $get_prefix['position_type'] ), 'custom' ) : ''; ?>><?php _e( 'Custom', 'blox' ); ?></option>
						</select>
						<div class="blox-position-default blox-description <?php if ( $get_prefix['position_type'] == 'custom' ) echo ( 'blox-hidden' ); ?>">
							<?php
								$default_position = $global ? esc_attr( blox_get_option( 'global_default_position', 'genesis_after_header' ) ) : esc_attr( blox_get_option( 'local_default_position', 'genesis_after_header' ) );
								$default_priority = $global ? esc_attr( blox_get_option( 'global_default_priority', 15 ) ) : esc_attr( blox_get_option( 'local_default_priority', 15 ) );

								echo sprintf( __( 'The default position is %1$s and the default priority is %2$s. You can change this default positioning by visiting the %3$sSettings Page%4$s, or use custom positioning to override this default.', 'blox' ), '<strong>' . $default_position . '</strong>', '<strong>' . $default_priority . '</strong>', '<a href="' . admin_url( 'edit.php?post_type=blox_block&page=blox-settings' ) . '">', '</a>' );
							?>
						</div>
					</td>
				</tr>
				<tr class="blox-position-custom-position blox-position-custom <?php if ( empty( $get_prefix['position_type'] ) || $get_prefix['position_type'] != 'custom' ) echo ( 'blox-hidden' ); ?>">
					<th scope="row"><?php _e( 'Position on Page', 'blox' ); ?></th>
					<td>
						<select name="<?php echo $name_prefix; ?>[custom][position]" id="blox_position_custom_position_<?php echo $id; ?>">
							<?php
							foreach ( $this->get_genesis_hooks() as $sections => $section ) { ?>
								<optgroup label="<?php echo $section['name']; ?>">
									<?php foreach ( $section['hooks'] as $hooks => $hook ) { ?>
										<option value="<?php echo $hooks; ?>" title="<?php echo $hook['title']; ?>" <?php echo ! empty( $get_prefix['custom']['position'] ) ? selected( esc_attr( $get_prefix['custom']['position'] ), $hooks ) : ''; ?>><?php echo $hook['name']; ?></option>
									<?php } ?>
								</optgroup>
							<?php } ?>
						</select>
						<div class="blox-description">
							<?php echo sprintf( __( 'Please refer to the %1$sBlox Documentation%2$s for hook reference.', 'blox' ), '<a href="https://www.bloxwp.com/documentation/position-hook-reference/?utm_source=blox&utm_medium=plugin&utm_content=position-tab-links&utm_campaign=Blox_Plugin_Links" title="' . __( 'Blox Documentation', 'blox' ) . '" target="_blank">', '</a>' ); ?>
						</div>
					</td>
				</tr>

				<tr class="blox-position-custom-priority blox-position-custom <?php if ( empty( $get_prefix['position_type'] ) || $get_prefix['position_type'] != 'custom' ) echo ( 'blox-hidden' ); ?>">
					<th scope="row"><?php _e( 'Priority', 'blox' ); ?></th>
					<td>
						<label>
							<input type="text" name="<?php echo $name_prefix; ?>[custom][priority]" id="blox_position_custom_priority_<?php echo $id; ?>" value="<?php echo ! empty( $get_prefix['custom']['priority'] ) ? esc_attr( $get_prefix['custom']['priority'] )  : '15'; ?>" class="blox-small-text"/>
							<?php _e( 'Enter a whole number greater than zero.', 'blox' ); ?>
						</label>
						<span class="blox-help-text-icon">
							<a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
						</span>
						<div class="blox-help-text top">
							<?php _e( 'Other plugins and themes can use Genesis Hooks to add content to a page. A low number tells Wordpress to try and add your custom content before all other content using the same Genesis Hook. A larger number will add the content later in the queue. (ex: Early=1, Medium=10, Late=100)', 'blox' ); ?>
						</div>
					</td>
				</tr>

				<?php do_action( 'blox_position_settings', $id, $name_prefix, $get_prefix, $global ); ?>

			</tbody>
		</table>

		<?php
    }


    /** 
	 * Saves all of the position settings
     *
     * @since 1.0.0
     *
     * @param int $post_id        The global block id or the post/page/custom post-type id corresponding to the local block 
     * @param string $name_prefix The prefix for saving each setting
     * @param bool $global        The block state
     *
     * @return array $settings    Return an array of updated settings
     */
    public function save_metabox_tab_position( $post_id, $name_prefix, $global ) {

		$settings = array();

		$settings['position_type']      = esc_attr( $name_prefix['position_type'] );
		$settings['custom']['position'] = esc_attr( $name_prefix['custom']['position'] );
		$settings['custom']['priority'] = absint( $name_prefix['custom']['priority'] );

		if ( $settings['position_type'] == 'default' ) {
		  $position = esc_attr( blox_get_option( 'global_default_position', 'genesis_after_header' ) );
		} else if ( $settings['custom'] ) {
		  $position = ! empty( $settings['custom']['position'] ) ? esc_attr( $settings['custom']['position'] ) : '';
		}
				
		return apply_filters( 'blox_save_position_settings', $settings, $post_id, $name_prefix, $global );
	}
	
	
	/**
     * Add admin column for global blocks
     *
     * @param string $post_id
     * @param array $block_data
     */
    public function admin_column_title( $columns ) {
    	$columns['position'] = __( 'Position', 'blox' );
    	return $columns; 
    }
    
    
    /**
     * Print the admin column data for global blocks.
     *
     * @param string $post_id
     * @param array $block_data
     */
    public function admin_column_data( $post_id, $block_data ) {
    
    	$error = '<span style="color:#a00;font-style:italic;">' . __( 'Error', 'blox' ) . '</span>';
    
		if ( ! empty( $block_data['position']['position_type'] ) ) {
			if ( $block_data['position']['position_type'] == 'default' ) {
				$default_position = esc_attr( blox_get_option( 'global_default_position', 'genesis_after_header' ) );
				if ( ! empty( $default_position ) ){
					$position = $meta_data = esc_attr( blox_get_option( 'global_default_position', 'genesis_after_header' ) );
				} else {
					$position  = $error;
					$meta_data = '';
				}
			} else if ( ! empty( $block_data['position']['custom'] ) ) {
				if( ! empty( $block_data['position']['custom']['position'] ) ) {
					$position = $meta_data = esc_attr( $block_data['position']['custom']['position'] );
				} else {
					$position = $error;
					$meta_data = '';
				}
			}
		} else {
			$position  = $error;
			$meta_data = '';
		}
		
		echo $position;
		
		// Save our position meta values separately for sorting
		update_post_meta( $post_id, '_blox_content_blocks_position', $meta_data );
    }
    
    
    /**
     * Tell Wordpress that the position column is sortable
     *
     * @since 1.0.0
     *
     * @param array $vars  Array of query variables
     */
	public function admin_column_sortable( $sortable_columns ) {
		$sortable_columns[ 'position' ] = 'position';
		return $sortable_columns;
	}
	
	
	/**
     * Tell Wordpress how to sort the position column
     *
     * @since 1.0.0
     *
     * @param array $vars  Array of query variables
     */
	public function admin_column_orderby( $vars ) {
		
		if ( isset( $vars['orderby'] ) && 'position' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
				'meta_key' => '_blox_content_blocks_position',
				'orderby' => 'meta_value'
			) );
		}
 
		return $vars;
	}


    /**
     * Helper method for retrieving all Genesis hooks.
     *
     * @since 1.0.0
     *
     * @return array Array of all Genesis hooks.
     */
    public function get_genesis_hooks() {

        $instance = Blox_Common::get_instance();
        return $instance->get_genesis_hooks();

    }


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Position ) ) {
            self::$instance = new Blox_Position();
        }

        return self::$instance;
    }
}

// Load the position class.
$blox_position = Blox_Position::get_instance();
