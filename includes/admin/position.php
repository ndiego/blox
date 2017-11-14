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
        // TODO investigate this
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

        $scope = $global ? 'global' : 'local';

        if ( ! blox_get_option( $scope . '_disable_hook_positioning', false ) ) {
            $this->print_position_hook_settings( $id, $name_prefix, $get_prefix, $global, $scope );
        }

        if ( ! blox_get_option( $scope . '_disable_shortcode_positioning', false ) ) {
            $this->print_position_shortcode_settings( $id, $name_prefix, $get_prefix, $global, $scope );
        }

        if ( ! blox_get_option( $scope . '_disable_php_positioning', false ) ) {
            $this->print_position_php_settings( $id, $name_prefix, $get_prefix, $global, $scope );
        }

        do_action( 'blox_position_settings', $id, $name_prefix, $get_prefix, $global );
    }

    /**
     * Creates all of the fields for the position hook settings
     *
     * @since 2.0.0
     *
     * @param int $id             The id of the content block, either global or individual (attached to post/page/cpt)
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param bool $global	      Determines if the content being loaded for local or global blocks
     * @param string $scope       Either Global or Local
     */
    public function print_position_hook_settings( $id, $name_prefix, $get_prefix, $global, $scope ) {
        ?>
        <table class="form-table blox-table-border-bottom">
            <tbody>

            	<tr valign="top">
            		<th scope="row"><label><?php _e( 'Hook Positioning', 'blox' ); ?></label></th>
            		<td>
                        <label>
                            <input type="checkbox" name="<?php echo $name_prefix; ?>[hook][disable]" id="blox_position_disable_hook_positioning_<?php echo $id; ?>" value="1" <?php ! empty( $get_prefix['hook']['disable'] ) ? checked( $get_prefix['hook']['disable'] ) : ''; ?> />
                            <?php _e( 'Disable hook positioning', 'blox' ); ?>
                        </label>
                        <span class="blox-help-text-icon">
                            <a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
                        </span>
                        <div class="blox-help-text">
                            <?php _e( 'When hook positioning is disabled, the content block will no longer display via the selected action hook.', 'blox' ); ?>
                        </div>
            		</td>
            	</tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e( 'Selected Hook', 'blox' ); ?></label></th>
                    <td>
                        <input type="text" readonly class="blox-selected-hook-position blox-half-text" name="<?php echo $name_prefix; ?>[hook][position]" id="blox_position_hook_position_<?php echo $id; ?>" value="<?php echo ! empty( $get_prefix['hook']['position'] ) ? esc_attr( $get_prefix['hook']['position'] )  : 'genesis_after_header'; ?>" />
                        <span class="blox-help-text-icon">
                            <a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
                        </span>
                        <div class="blox-help-text top">
                            <?php echo sprintf( __( 'Use the hook selector menu below to choose the appropriate hook for this content block. To enable/disable certain hook options, or add your own custom hooks, visit the Blox %1$sposition settings%2$s.', 'blox' ), '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=position' ) . '">', '</a>' ); ?>
                        </div>
                        <?php
                        // Print hook selector
                        $this->print_hook_selector();
                        ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e( 'Hook Priority', 'blox' ); ?></label></th>
                    <td>
                        <input type="text" name="<?php echo $name_prefix; ?>[hook][priority]" id="blox_position_hook_priority_<?php echo $id; ?>" value="<?php echo ! empty( $get_prefix['hook']['priority'] ) ? esc_attr( $get_prefix['hook']['priority'] )  : '15'; ?>" class="blox-small-text"/>
                        <span class="blox-help-text-icon">
                            <a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
                        </span>
                        <div class="blox-help-text top">
                            <?php _e( 'Other plugins and themes can use action hooks to add content to your website. A low number tells WordPress to try and add your custom content before all other content using the same action hook. A larger number will add the content later in the queue. (ex: Early=1, Medium=10, Late=100)', 'blox' ); ?>
                        </div>
                    </td>
                </tr>

            </tbody>
        </table>
		<?php
    }


    /**
     * Creates all of the fields for the position shortcode settings
     *
     * @since 2.0.0
     *
     * @param array $hook_types An array of all available hook types
     */
    public function print_hook_selector() {

        $hook_types = $this->get_hook_types();
        ?>
        <div class="blox-hook-selector">
            <div class="blox-hook-selector-menu">
                <ul class="blox-hook-type-list">
                <?php
                    if ( ! empty( $hook_types ) ) {
                        $i = 0;
                        foreach ( $hook_types as $slug => $atts ) {
                            if ( ! $atts['disable'] ) {
                                // Set the first type to current
                                $current = $i == 0 ? 'current' : '';
                                ?>
                                <li class="blox-hook-type <?php echo $current; ?>" data-hook-type="<? echo $slug; ?>">
                                    <?php
                                        // If hook type is not disabled, but the source is inactive (Genesis, WooComerce, etc), display warning
                                        if ( ! $atts['active'] ) {
                                            echo '<div class="blox-hook-dashicon blox-hooks-disabled dashicons dashicons-warning"></div>';
                                        } else {
                                            echo '<div class="blox-hook-dashicon blox-' . $slug .'-dashicon dashicons-before"></div>';
                                        }
                                    ?>
                                    <span class="blox-hook-type-name"><?php echo $atts['title']; ?></span>
                                </li>
                                <?php
                                // Only increment if the hook type is actually enabled
                                $i++;
                            }
                        }
                    }
                ?>
                </ul>
                <div class="blox-show-hook-descriptions">
                    <span class="blox-help-text-icon">
                        <a href="#" class="dashicons dashicons-editor-help"></a>
                        <span class="blox-show-hook-descriptions-text"><?php _e( 'Toggle Hook Descriptions', 'blox' ); ?></span>
                    </span>
                </div>
            </div>
            <div class="blox-hook-selector-content">
                <?php

                // Get all available hooks
                $all_available_hooks = $this->get_active_hooks();

                if ( ! empty( $hook_types ) && ! empty( $all_available_hooks ) ) {
                    $i = 0;
                    foreach ( $hook_types as $slug => $atts ) {
                        if ( ! $atts['disable'] ) {
                            ?>
                            <div class="blox-hooks <?php echo $slug; ?>">
                                <?php
                                // If hook type is not disabled, but the source is inactive (Genesis, WooComerce, etc), display alert
                                if ( ! $atts['active'] ) {
                                    ?>
                                    <div class="blox-alert-box"><?php echo $atts['alert']; ?></div>
                                    <?php
                                } else {
                                    if ( ! empty( $all_available_hooks[$slug] ) ) {
                                        // Start displaying hook sections if active and there are actually sections
                                        foreach ( $all_available_hooks[$slug] as $sections => $section ) {
                                            ?>
                                            <div class="blox-hook-section">
                                                <div class="blox-hook-section-title-label"><?php echo $section['name']; ?></div>
                                                <?php
                                                if ( empty( $section['hooks'] ) ) {
                                                    echo '<div class="blox-alert-box">' . sprintf( __( 'There are no hooks to display. They have either all been disabled, or you need to create custom hooks for this section. Visit the Blox %1$sposition settings%2$s to enable or add hooks. You can also disable the section entirely to avoid this alert.', 'blox' ), '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=position' ) . '">', '</a>' ) . '</div>';
                                                } else {
                                                    foreach ( $section['hooks'] as $hooks => $hook ) {
                                                        ?>
                                                        <div class="blox-hook-item" data-hook="<? echo $hooks; ?>">
                                                            <div class="blox-hook-name"><?php echo $hook['name']; ?></div>
                                                            <div class="blox-hook-description"><?php echo $hook['title']; ?></div>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        echo '<div class="blox-alert-box">' . sprintf( __( 'All hook sections for this hook type seem to have been disabled. Visit the Blox %1$sposition settings%2$s to enable hook sections.', 'blox' ), '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=position' ) . '">', '</a>' ) . '</div>';
                                    }
                                } ?>
                            </div>
                            <?php

                            // Only increment if the hook type is actually enabled
                            $i++;
                        }
                    }
                }
                ?>

            </div>
        </div>
        <?php
    }


    /**
     * Creates all of the fields for the position shortcode settings
     *
     * @since 2.0.0
     *
     * @param int $id             The id of the content block, either global or individual (attached to post/page/cpt)
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param bool $global	      Determines if the content being loaded for local or global blocks
     * @param string $scope       Either Global or Local
     */
    public function print_position_shortcode_settings( $id, $name_prefix, $get_prefix, $global, $scope ) {
        ?>
        <table class="form-table blox-table-border-bottom">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e( 'Shortcode Positioning', 'blox' ); ?></label></th>
                    <td>
                        <div class="blox-checkbox before">
                            <label>
                                <input type="checkbox" name="<?php echo $name_prefix; ?>[shortcode][disable]" id="blox_position_disable_shortcode_positioning_<?php echo $id; ?>" value="1" <?php ! empty( $get_prefix['shortcode']['disable'] ) ? checked( $get_prefix['shortcode']['disable'] ) : ''; ?> />
                                <?php _e( 'Disable shortcode positioning', 'blox' ); ?>
                            </label>
                            <span class="blox-help-text-icon">
                                <a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
                            </span>
                            <div class="blox-help-text">
                                <?php _e( 'When shortcode positioning is disabled, any shortcodes that were placed for this content block will cease to work.', 'blox' ); ?>
                            </div>
                        </div>

                        <div class="blox-code">[blox id="<?php echo $scope . '_' . $id; ?>"]</div>
                        <div class="blox-description">
                            <?php
                                _e( 'Copy and paste the above shortcode anywhere that accepts a shortcode. Visibility and location settings are respected when using shortcode positioning.', 'blox' );
                                if ( ! $global ) {
                                    echo ' ' . sprintf( __( 'Also note that regardless of position type, local blocks will %1$sonly%2$s display on the page, post, or custom post type that they were created on.', 'blox' ), '<strong>', '</strong>' );
                                }
                            ?>
                        </div>

                        <div class="blox-checkbox after">
                            <label>
                                <input type="checkbox" id="blox_position_shortcode_ignore_location_<?php echo $id; ?>" name="<?php echo $name_prefix; ?>[shortcode][ignore_location]" value="1" <?php echo isset( $get_prefix['shortcode']['ignore_location'] ) ? checked( $get_prefix['shortcode']['ignore_location'], 1, false ) : ''; ?> />
                                <?php _e( 'Check to ignore location settings', 'blox' ); ?>
                            </label>
                            <span class="blox-help-text-icon">
                                <a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
                            </span>
                            <div class="blox-help-text">
                                <?php _e( 'This option can be helpful in certain circumstances, especially when creating copies of the content block. You may want to position the block via action hook, which uses the location settings, but then place a copy elsewhere on your site via shortcode. Often this additional placement will violate the location settings.', 'blox' ); ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }


    /**
     * Creates all of the fields for the position php settings
     *
     * @since 2.0.0
     *
     * @param int $id             The id of the content block, either global or individual (attached to post/page/cpt)
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param bool $global	      Determines if the content being loaded for local or global blocks
     * @param string $scope       Either Global or Local
     */
    public function print_position_php_settings(  $id, $name_prefix, $get_prefix, $global, $scope ) {
        ?>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e( 'PHP Positioning', 'blox' ); ?></label></th>
                    <td>
                        <div class="blox-checkbox before">
                            <label>
                                <input type="checkbox" name="<?php echo $name_prefix; ?>[php][disable]" id="blox_position_disable_php_positioning_<?php echo $id; ?>" value="1" <?php ! empty( $get_prefix['php']['disable'] ) ? checked( $get_prefix['php']['disable'] ) : ''; ?> />
                                <?php _e( 'Disable PHP positioning', 'blox' ); ?>
                            </label>
                            <span class="blox-help-text-icon">
                                <a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
                            </span>
                            <div class="blox-help-text">
                                <?php _e( 'When PHP positioning is disabled, any PHP code that was placed to display this block will cease to work.', 'blox' ); ?>
                            </div>
                        </div>

                        <div class="blox-code">blox_display_block( "<?php echo $scope . '_' . $id; ?>" );</div>
                        <div class="blox-description">
                            <?php
                                _e( 'Copy and paste the above PHP code into any of your theme files. Visibility and location settings are respected when using PHP positioning.', 'blox' );
                                if ( ! $global ) {
                                    echo ' ' . sprintf( __( 'Also note that regardless of position type, local blocks will %1$sonly%2$s display on the page, post, or custom post type that they were created on.', 'blox' ), '<strong>', '</strong>' );
                                }
                            ?>
                        </div>

                        <div class="blox-checkbox after">
                            <label>
                                <input type="checkbox" id="blox_position_php_ignore_location_<?php echo $id; ?>" name="<?php echo $name_prefix; ?>[php][ignore_location]" value="1" <?php echo isset( $get_prefix['php']['ignore_location'] ) ? checked( $get_prefix['php']['ignore_location'], 1, false ) : ''; ?> />
                                <?php _e( 'Check to ignore location settings', 'blox' ); ?>
                            </label>
                            <span class="blox-help-text-icon">
                                <a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
                            </span>
                            <div class="blox-help-text">
                                <?php _e( 'This option can be helpful in certain circumstances, especially when creating copies of the content block. You may want to position the block via action hook, which uses the location settings, but then place a copy elsewhere on your site via PHP. Often this additional placement will violate the location settings.', 'blox' ); ?>
                            </div>
                        </div>
                    </td>
                </tr>
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

        /* Depracated settings as of v2.0
        $settings['position_format']    = isset( $name_prefix['position_format'] ) ? esc_attr( $name_prefix['position_format'] ) : 'hook';
		$settings['position_type']      = esc_attr( $name_prefix['position_type'] );
		$settings['custom']['position'] = isset( $name_prefix['custom']['position'] ) ? esc_attr( $name_prefix['custom']['position'] ) : '';
		$settings['custom']['priority'] = absint( $name_prefix['custom']['priority'] );

        if ( $settings['position_type'] == 'default' ) {
		  $position = esc_attr( blox_get_option( 'global_default_position', 'genesis_after_header' ) );
		} else if ( $settings['custom'] ) {
		  $position = ! empty( $settings['custom']['position'] ) ? esc_attr( $settings['custom']['position'] ) : '';
		}
        */

        $settings['hook']['enable']                 = isset( $name_prefix['hook']['enable'] ) ? 1 : 0;
        $settings['hook']['position']               = isset( $name_prefix['hook']['position'] ) ? esc_attr( $name_prefix['hook']['position'] ) : '';
        $settings['hook']['priority']               = absint( $name_prefix['hook']['priority'] );

        $settings['shortcode']['enable']            = isset( $name_prefix['shortcode']['enable'] ) ? 1 : 0;
        $settings['shortcode']['ignore_location']   = isset( $name_prefix['shortcode']['ignore_location'] ) ? 1 : 0;

        $settings['php']['enable']                  = isset( $name_prefix['php']['enable'] ) ? 1 : 0;
        $settings['php']['ignore_location']         = isset( $name_prefix['php']['ignore_location'] ) ? 1 : 0;

		return apply_filters( 'blox_save_position_settings', $settings, $post_id, $name_prefix, $global );
	}


	/**
     * Add admin column for global blocks
     *
     * @since 1.0.0
     *
     * @param string $post_id
     * @param array $block_data
     */
    public function admin_column_title( $columns ) {
    	$columns['position'] = __( 'Position', 'blox' );
    	return $columns;
    }


    /**
     * Print the admin column data for global blocks. NEED UPDATING
     *
     * @param string $post_id
     * @param array $block_data
     */
    public function admin_column_data( $post_id, $block_data ) {

        $instance        = Blox_Common::get_instance();
		$available_hooks = $instance->get_genesis_hooks_flattened(); // WRONG

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
     * Add position settings to the quickedit screen for Blox NEED UPDATING!!!!!!!!!!!!!!
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


    public function get_hooks() {

        $instance = Blox_Common::get_instance();
        return $instance->get_hooks();

    }


    public function get_hook_types() {

        $instance = Blox_Common::get_instance();
        return $instance->get_hook_types();

    }

    public function get_active_hooks() {

        $instance = Blox_Common::get_instance();
        return $instance->get_active_hooks();

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
