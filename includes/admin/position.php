<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the position tab and loads in all the available options
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

        $scope = $global ? "global" : 'local';

        echo print_r($get_prefix);
		?>

                <div class="blox-toggle blox-toggle-has-container">
                    <span class="blox-toggle-wrap">
                        <input id="blox_position_enable_hook_<?php echo $id; ?>" name="<?php echo $name_prefix; ?>[hook][enable]" type="checkbox" value="1" <?php echo isset( $get_prefix['hook']['enable'] ) ? checked( $get_prefix['hook']['enable'], 1, false ) : ' checked="checked"'; ?> />
                        <label class="toggle" for="blox_position_enable_hook_<?php echo $id; ?>"></label>
                    </span>
                    <span class="title"><?php _e( 'Hook Positioning', 'blox' ); ?></span>
                </div>

                <div class="blox-toggle-container">


                    <?php
                    $genesis_hooks = $this->get_genesis_hooks();

                    //echo print_r($hooks['core']);

                    $genesis_enabled = true;

                    ?>
                    <div class="blox-hook-selector">
                        <div class="blox-hook-selector-menu">
                            <?php
                                if ( $genesis_enabled ) {
                                    ?>
                                    <div class="blox-hook-group">
                                        Genesis Hooks
                                    </div>
                                    <?php
                                }
                            ?>
                        </div>
                        <div class="blox-hook-selector-content">
                            <?php
                                if ( $genesis_enabled ) {
                                    foreach ( $this->get_genesis_hooks() as $sections => $section ) { ?>
                                        <div class="blox-hook-section">
                                            <div class="blox-hook-section-title"><?php echo $section['name']; ?></div>
                                            <?php foreach ( $section['hooks'] as $hooks => $hook ) { ?>
                                                <div class="blox-hook-item">
                                                    <div class="blox-hook-name"><?php echo $hook['name']; ?></div>
                                                    <div class="blox-hook-description"><?php echo $hook['title']; ?></div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    <?php }
                                }
                            ?>
                        </div>
                    </div>




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
                                    <span class="blox-help-text-icon">
                                        <a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
                                    </span>
                                    <div class="blox-help-text top">
                                        <?php echo sprintf( __( 'By default, Blox only allows positioning via action hooks. %1$sBlox Add-ons%2$s enable additional options.', 'blox' ), '<a href="http://www.bloxwp.com/add-ons" title="Blox Add-ons" target="_blank">', '</a>' ); ?>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="form-table blox-position-format-type hook">
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
                                        $custom_position = ! empty( $get_prefix['custom']['position'] ) ? $get_prefix['custom']['position'] : '';
                                        // Print error if the saved hook is no longer available for some reason
                                        if ( ! empty( $custom_position ) && ! in_array( $custom_position, $available_hooks ) ) {
                                            echo '<div class="blox-alert">' . sprintf( __( 'The current saved custom hook, %3$s, is no longer available. Choose a new one, or re-enable it on the %1$sHooks%2$s settings page.', 'blox' ), '<a href="' . admin_url( '/edit.php?post_type=blox&page=blox-settings&tab=hooks' ) . '">', '</a>', '<strong>' . $custom_position . '</strong>' ) . '</div>';
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

                        </tbody>
                    </table>



                </div>


                <div class="blox-toggle blox-toggle-has-container">
                    <span class="blox-toggle-wrap">
                        <input id="blox_position_enable_shortcode_<?php echo $id; ?>" name="<?php echo $name_prefix; ?>[shortcode][enable]" type="checkbox" value="1" <?php echo isset( $get_prefix['shortcode']['enable'] ) ? checked( $get_prefix['shortcode']['enable'], 1, false ) : ''; ?> />
                        <label class="toggle" for="blox_position_enable_shortcode_<?php echo $id; ?>"></label>
                    </span>
                    <span class="title"><?php _e( 'Shortcode Positioning', 'blox' ); ?></span>
                </div>

                <div class="blox-toggle-container">
                    <div class="blox-code">[blox id="<?php echo $scope . '_' . $id; ?>"]</div>
                    <div class="blox-description">
                        <?php
                            _e( 'Copy and paste this above shortcode anywhere that accepts a shortcode. Visibility and location settings are respected when using shortcode positioning.', 'blox-shortcodes' );
                            if ( ! $global ) {
                                echo ' ' . sprintf( __( 'Also note that regardless of position type, local blocks will %1$sonly%2$s display on the page, post, or custom post type that they were created on.', 'blox-shortcodes' ), '<strong>', '</strong>' );
                            }
                        ?>
                    </div>

                    <div class="blox-checkbox after">
                        <label>
                            <input type="checkbox" name="<?php echo $name_prefix; ?>[shortcode][ignore_location]" value="1" <?php echo isset( $get_prefix['shortcode']['ignore_location'] ) ? checked( $get_prefix['shortcode']['ignore_location'], 1, false ) : ''; ?> />
                            <?php _e( 'Check to ignore location settings', 'blox' ); ?>
                        </label>
                        <span class="blox-help-text-icon">
                            <a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
                        </span>
                        <div class="blox-help-text">
                            Test
                        </div>
                    </div>

                </div>

                <div class="blox-toggle blox-toggle-has-container">
                    <span class="blox-toggle-wrap">
                        <input id="blox_position_enable_php_<?php echo $id; ?>" name="<?php echo $name_prefix; ?>[php][enable]" type="checkbox" value="1" <?php echo isset( $get_prefix['php']['enable'] ) ? checked( $get_prefix['php']['enable'], 1, false ) : ''; ?> />
                        <label class="toggle" for="blox_position_enable_php_<?php echo $id; ?>"></label>
                    </span>
                    <span class="title"><?php _e( 'PHP Function Positioning', 'blox' ); ?></span>
                </div>

                <div class="blox-toggle-container last">
                    <div class="blox-code">blox_display_block( "<?php echo $scope . '_' . $id; ?>" );</div>
                    <div class="blox-description">
                        <?php
                            _e( 'Copy and paste this above shortcode anywhere that accepts a shortcode. Visibility and location settings are respected when using shortcode positioning.', 'blox-shortcodes' );
                            if ( ! $global ) {
                                echo ' ' . sprintf( __( 'Also note that regardless of position type, local blocks will %1$sonly%2$s display on the page, post, or custom post type that they were created on.', 'blox-shortcodes' ), '<strong>', '</strong>' );
                            }
                        ?>
                    </div>

                    <div class="blox-checkbox after">
                        <label>
                            <input type="checkbox" name="<?php echo $name_prefix; ?>[php][ignore_location]" value="1" <?php echo isset( $get_prefix['php']['ignore_location'] ) ? checked( $get_prefix['php']['ignore_location'], 1, false ) : ''; ?> />
                            <?php _e( 'Check to ignore location settings', 'blox' ); ?>
                        </label>
                        <span class="blox-help-text-icon">
                            <a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
                        </span>
                        <div class="blox-help-text">
                            Test
                        </div>
                    </div>

                </div>

				<?php do_action( 'blox_position_settings', $id, $name_prefix, $get_prefix, $global ); ?>

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

        $settings['hook']['enable']                 = isset( $name_prefix['hook']['enable'] ) ? 1 : 0;

		if ( $settings['position_type'] == 'default' ) {
		  $position = esc_attr( blox_get_option( 'global_default_position', 'genesis_after_header' ) );
		} else if ( $settings['custom'] ) {
		  $position = ! empty( $settings['custom']['position'] ) ? esc_attr( $settings['custom']['position'] ) : '';
		}

        $settings['shortcode']['enable']            = isset( $name_prefix['shortcode']['enable'] ) ? 1 : 0;
        $settings['shortcode']['ignore_location']   = isset( $name_prefix['shortcode']['ignore_location'] ) ? 1 : 0;

        $settings['php']['enable']                  = isset( $name_prefix['php']['enable'] ) ? 1 : 0;
        $settings['php']['ignore_location']         = isset( $name_prefix['php']['ignore_location'] ) ? 1 : 0;

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
        $position_format  = ! empty( $block_data['position']['position_format'] ) ? esc_attr( $block_data['position']['position_format'] ) : 'hook';
        $position_type    = esc_attr( $block_data['position']['position_type'] );
        $default_position = esc_attr( blox_get_option( 'global_default_position', 'genesis_after_header' ) );
        $custom_position  = esc_attr( $block_data['position']['custom']['position'] );
        $custom_priority  = esc_attr( $block_data['position']['custom']['priority'] );

        $title = '';

        if ( $position_format != 'hook' ) {

            $postion_options = apply_filters( 'blox_position_formats', array() );

            if ( array_key_exists( $position_format, $postion_options ) ) {

                $output = '–';

                $position  = apply_filters( 'blox_admin_column_output_position', $output, $position_format, $post_id, $block_data );
                $meta_data = '_' . $position_format;
            } else {
                $position  = false;
                $title     = sprintf( __( 'The position format on this block is currently set to %s, which has been disabled or is no longer available. Therefore, this block is not displaying. Edit the position to resolve this error.', 'blox' ), ucfirst($position_format) );
                $meta_data = '';
            }

        } else {
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

    				$title = $custom_position;

    				if( ! empty( $custom_position ) && array_key_exists( $block_data['position']['custom']['position'], $available_hooks ) ) {
                        $position  = esc_attr( $available_hooks[$custom_position] );
    					$meta_data = $custom_position;
    				} else {
    					$position  = false;
                        $title     = sprintf( __( 'This block is currently set to %s, which has been disabled or is no longer available. Therefore, this block is not displaying. Edit the position to resolve this error.', 'blox' ), $custom_position );
    					$meta_data = '';
    				}
    			}
    		} else {
    			$position  = false;
    			$meta_data = '';
    		}
        }

		$error = '<span style="color:#a00;font-style:italic;cursor: help" title="' . $title . '">' . __( 'Error', 'blox' ) . '</span>';

        $hidden = '<input type="hidden" name="position_format" value="' . $position_format . '">';
        $hidden .= '<input type="hidden" name="position_type" value="' . $position_type . '">';
        $hidden .= '<input type="hidden" name="custom_position" value="' . $custom_position . '">';
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
                    <div class="quickedit-position-format">
                        <label>
                            <select name="position_format">
                                <option value="hook"><?php _e( 'Hook', 'blox' ); ?></option>
                                <?php

                                $postion_options = apply_filters( 'blox_position_formats', array() );

                                if ( ! empty( $postion_options ) ) {
                                    foreach ( $postion_options as $format => $title ) {
                                        echo '<option value="' . $format . '">' . $title . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <span><?php _e( 'Format', 'blox' ); ?></span>
                        </label>
                    </div>
                    <div class="quickedit-position-format-type hook" style="display:none">

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

        $settings['position']['position_format']    = esc_attr( $request['position_format'] );
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
