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

        // Add quick edit & bulk edit settings
        add_action( 'blox_quickedit_settings_position', array( $this, 'quickedit_bulkedit_settings' ), 10, 2 );
        add_filter( 'blox_quickedit_save_settings', array( $this, 'quickedit_bulkedit_save_settings' ), 10, 3 );
        // add_action( 'blox_bulkedit_settings_position', array( $this, 'quickedit_bulkedit_settings' ), 10, 2 );
        // add_filter( 'blox_bulkedit_save_settings', array( $this, 'quickedit_bulkedit_save_settings' ), 10, 3 );
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

    	$instance        = Blox_Common::get_instance();
		$available_hooks = $instance->get_genesis_hooks_flattened();

		?>
		<table class="form-table">
			<tbody>
                <tr class="blox-position-format">
                    <th scope="row"><?php echo __( 'Position Type', 'blox' ); ?></th>
                    <td>
                        <select name="<?php echo $name_prefix; ?>[position_format]" id="blox_position_format_<?php echo $id; ?>">
                            <option value="hook" <?php echo ! empty( $get_prefix['position_format'] ) ? selected( esc_attr( $get_prefix['position_format'] ), 'hook' ) : 'selected'; ?>><?php _e( 'Hook', 'blox' ); ?></option>
                            <?php

                            $postion_options = apply_filters( 'blox_position_formats', array() );

                            if ( ! empty( $postion_options ) ) {
                                foreach ( $postion_options as $format => $title ) {
                                    echo '<option value="' . $format . '" ' . selected( esc_attr( $get_prefix['position_format'] ), $format ) . ' >' . $title . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="form-table blox-position-format hook">
        	<tbody>
				<tr class="blox-position-type">
					<th scope="row"><?php echo __( 'Hook Type', 'blox' ); ?></th>
					<td>
						<select name="<?php echo $name_prefix; ?>[position_type]" id="blox_position_type_<?php echo $id; ?>">
							<option value="default" <?php echo ! empty( $get_prefix['position_type'] ) ? selected( esc_attr( $get_prefix['position_type'] ), 'default' ) : 'selected'; ?>><?php _e( 'Default', 'blox' ); ?></option>
							<option value="custom" <?php echo ! empty( $get_prefix['position_type'] ) ? selected( esc_attr( $get_prefix['position_type'] ), 'custom' ) : ''; ?>><?php _e( 'Custom', 'blox' ); ?></option>
						</select>
						<div class="blox-position-default <?php if ( $get_prefix['position_type'] == 'custom' ) echo ( 'blox-hidden' ); ?>">
							<div class="blox-description">
								<?php
									$default_position = $global ? esc_attr( blox_get_option( 'global_default_position', 'genesis_after_header' ) ) : esc_attr( blox_get_option( 'local_default_position', 'genesis_after_header' ) );
									$default_priority = $global ? esc_attr( blox_get_option( 'global_default_priority', 15 ) ) : esc_attr( blox_get_option( 'local_default_priority', 15 ) );

									echo sprintf( __( 'The default position is %1$s and the default priority is %2$s. You can change this default positioning by visiting the %3$sDefaults%4$s setting page, or use custom positioning to override this default.', 'blox' ), '<strong>' . $default_position . '</strong>', '<strong>' . $default_priority . '</strong>', '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=default' ) . '">', '</a>' );
								?>
							</div>
							<?php
								// Print error if the saved hook is no longer available for some reason
								if ( ! in_array( $default_position, $available_hooks ) ) {
									echo '<div class="blox-alert">' . sprintf( __( 'The current saved default hook is no longer available. Choose a new one, or re-enable it on the %1$sHooks%2$s settings page.', 'blox' ), '<a href="' . admin_url( '/edit.php?post_type=blox&page=blox-settings&tab=hooks' ) . '">', '</a>' ) . '</div>';
								}
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
						<?php
							$custom_postion = ! empty( $get_prefix['custom']['position'] ) ? $get_prefix['custom']['position'] : '';
							// Print error if the saved hook is no longer available for some reason
							if ( ! empty( $custom_postion ) && ! in_array( $custom_postion, $available_hooks ) ) {
								echo '<div class="blox-alert">' . sprintf( __( 'The current saved custom hook, %3$s, is no longer available. Choose a new one, or re-enable it on the %1$sHooks%2$s settings page.', 'blox' ), '<a href="' . admin_url( '/edit.php?post_type=blox&page=blox-settings&tab=hooks' ) . '">', '</a>', '<strong>' . $custom_postion . '</strong>' ) . '</div>';
							}
						?>
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

        $settings['position_format']    = isset( $name_prefix['position_format'] ) ? esc_attr( $name_prefix['position_format'] ) : 'hook';

        // Hook specific settings
		$settings['position_type']      = esc_attr( $name_prefix['position_type'] );
		$settings['custom']['position'] = isset( $name_prefix['custom']['position'] ) ? esc_attr( $name_prefix['custom']['position'] ) : '';
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

        $instance        = Blox_Common::get_instance();
		$available_hooks = $instance->get_genesis_hooks_flattened();

		//echo print_r( $available_hooks );
        $position_type    = esc_attr( $block_data['position']['position_type'] );
        $default_position = esc_attr( blox_get_option( 'global_default_position', 'genesis_after_header' ) );
        $custom_postion   = esc_attr( $block_data['position']['custom']['position'] );
        $custom_priority  = esc_attr( $block_data['position']['custom']['priority'] );

		if ( ! empty( $block_data['position']['position_type'] ) ) {

			if ( $block_data['position']['position_type'] == 'default' ) {

				$title = $default_position;

				if ( ! empty( $default_position ) && array_key_exists( $default_position, $available_hooks ) ){
					$position  = esc_attr( $available_hooks[$default_position] );
					$meta_data = $default_position;
				} else {
					$position  = false;
					$title     = sprintf( __( 'This block is currently set to %s, which has been disabled or is no longer available. Therefore, this block is not displaying. Edit the position to resolve this error.', 'blox' ), $default_position );
					$meta_data = '';
				}
			} else if ( ! empty( $block_data['position']['custom'] ) ) {

				$title = $custom_postion;

				if( ! empty( $custom_postion ) && array_key_exists( $block_data['position']['custom']['position'], $available_hooks ) ) {
                    $position  = esc_attr( $available_hooks[$custom_postion] );
					$meta_data = $custom_postion;
				} else {
                    $hidden   .= '<input type="hidden" name="custom_position" value="">';
					$position  = false;
					$meta_data = '';
				}
			}
		} else {
			$position  = false;
			$meta_data = '';
		}

		$error = '<span style="color:#a00;font-style:italic;cursor: help" title="' . $title . '">' . __( 'Error', 'blox' ) . '</span>';

        $hidden = '<input type="hidden" name="position_type" value="' . $position_type . '">';
        $hidden .= '<input type="hidden" name="custom_position" value="' . $custom_postion . '">';
        $hidden .= '<input type="hidden" name="custom_priority" value="' . $custom_priority . '">';

        echo $hidden;
		echo $position ? $position : $error;

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
     * Add position settings to the quickedit screen for Blox
     *
     * @since 1.3.0
     *
     * @param string $post_type  Current post type which will always be blox
     * @param string $type       Either 'bulk' or 'quick'
     */
    function quickedit_bulkedit_settings( $post_type, $type ) {

        $default_position = esc_attr( blox_get_option( 'global_default_position', 'genesis_after_header' ) );
        $default_priority = esc_attr( blox_get_option( 'global_default_priority', 15 ) );

        ?>
        <fieldset class="inline-edit-col-left custom">
            <div class="inline-edit-col column-position">

                <span class="title"><?php _e( 'Position', 'blox' ); ?></span>

                <div class="quickedit-settings">
                    <div class="quickedit-position-hook">

                        <label>
                            <select name="position_type">
                                <option value="default"><?php _e( 'Default', 'blox' ); ?></option>
                                <option value="custom"><?php _e( 'Custom', 'blox' ); ?></option>
                            </select>
                            <span><?php _e( 'Hook Type', 'blox' ); ?></span>
                        </label>

                        <div class="quickedit-position-hook-default" style="display:none">
                            <p class="description">
                                <?php echo sprintf( __( 'The default position is %1$s and the default priority is %2$s. Modify defaults by visiting the %3$sDefaults%4$s setting page.', 'blox' ), '<strong>' . $default_position . '</strong>', '<strong>' . $default_priority . '</strong>', '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=default' ) . '">', '</a>' ); ?>
                            </p>
                        </div>

                        <div class="quickedit-position-hook-custom" style="display:none">
                            <select name="custom_position">
                                <?php
                                foreach ( $this->get_genesis_hooks() as $sections => $section ) { ?>
                                    <optgroup label="<?php echo $section['name']; ?>">
                                        <?php foreach ( $section['hooks'] as $hooks => $hook ) { ?>
                                            <option value="<?php echo $hooks; ?>" title="<?php echo $hook['title']; ?>"><?php echo $hook['name']; ?></option>
                                        <?php } ?>
                                    </optgroup>
                                <?php } ?>
                            </select>

                            <label>
                                <input type="text" name="custom_priority" class="small" value="" />
                                <span><?php _e( 'Priority', 'blox' ); ?></span>
                            </label>
                        </div>
                    </div>

                    <?php
                    // Allow add-ons, or developers, to hook in additional settings
                    do_action( 'blox_quickedit_add_settings_position', $post_type );
                    ?>
                </div>
            </div>
        </fieldset>
        <?php
    }


    /**
     * Save quickedit position settings
     *
     * @since 1.3.0
     *
     * @param array $settings  Array of all current block settings
     * @param array $request   Array of all requested data ready for saving (uses $_REQUEST)
     * @param string $type       Either 'bulk' or 'quick'
     *
     * @return array $settings Array of updated block settings
     */
    function quickedit_bulkedit_save_settings( $settings, $request, $type ) {

        $settings['position']['position_type']      = esc_attr( $request['position_type'] );
		$settings['position']['custom']['position'] = isset( $request['custom_position'] ) ? esc_attr( $request['custom_position'] ) : 'genesis_after_header';
		$settings['position']['custom']['priority'] = isset( $request['custom_position'] ) ? absint( $request['custom_priority'] ) : 15;

        return $settings;
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
