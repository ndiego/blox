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

        $position_types_disabled = array(
            'hook'      => blox_get_option( $scope . '_disable_hook_positioning', false ),
            'shortcode' => blox_get_option( $scope . '_disable_shortcode_positioning', false ),
            'php'       => blox_get_option( $scope . '_disable_php_positioning', false )
        );

        if ( ! $position_types_disabled['hook'] ) {
            $this->print_position_hook_settings( $id, $name_prefix, $get_prefix, $global, $scope );
        }

        if ( ! $position_types_disabled['shortcode']) {
            $this->print_position_shortcode_settings( $id, $name_prefix, $get_prefix, $global, $scope );
        }

        // PHP positioning is only available for global blocks
        if ( ! $position_types_disabled['php'] && $scope == 'global' ) {
            $this->print_position_php_settings( $id, $name_prefix, $get_prefix, $global, $scope );
        }

        // Throw error message if the user disabled all of the positioning options.
        if ( ! in_array( false, $position_types_disabled ) ) {
            echo '<div class="blox-alert-box no-side-margin">' . sprintf( __( 'All block positioning options are disabled. Visit the Blox %1$sposition settings%2$s to correct this.', 'blox' ), '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=position' ) . '">', '</a>' ) . '</div>';
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

        $position = ! empty( $get_prefix['hook']['position'] ) ? esc_attr( $get_prefix['hook']['position'] )  : '';
        $priority = ! empty( $get_prefix['hook']['priority'] ) ? esc_attr( $get_prefix['hook']['priority'] )  : 15;

        // Handle settings from Blox v1.x
        if ( isset( $get_prefix['position_type'] ) ) {
            if ( $get_prefix['position_type'] == 'default' ) {
              $position = esc_attr( blox_get_option( 'global_default_position', 'genesis_after_header' ) );
              $priority = esc_attr( blox_get_option( 'global_default_priority', 15 ) );
            } else {
              $position = ! empty( $get_prefix['custom']['position'] ) ? esc_attr( $get_prefix['custom']['position'] ) : 'genesis_after_header';
              $priority = ! empty( $get_prefix['custom']['priority'] ) ? esc_attr( $get_prefix['custom']['priority'] ) : 15;
            }
        }

        ?>
        <table class="form-table position">
            <tbody>

            	<tr valign="top">
            		<th scope="row"><label><?php _e( 'Hook Positioning', 'blox' ); ?></label></th>
            		<td>
                        <div class="blox-checkbox first">
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
                        </div>
            		</td>
            	</tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e( 'Selected Hook', 'blox' ); ?></label></th>
                    <td>
                        <input type="text" readonly class="blox-selected-hook-position blox-half-text" name="<?php echo $name_prefix; ?>[hook][position]" id="blox_position_hook_position_<?php echo $id; ?>" value="<?php echo $position; ?>" placeholder="<?php _e( 'Choose a hook from the table below...', 'blox' ); ?>"/>
                        <span class="blox-help-text-icon">
                            <a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
                        </span>
                        <div class="blox-help-text top">
                            <?php echo sprintf( __( 'Use the hook selector menu below to choose the appropriate hook for this content block. To enable/disable certain hook options, or add your own custom hooks, visit the Blox %1$sposition settings%2$s.', 'blox' ), '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=position' ) . '">', '</a>' ); ?>
                        </div>
                        <?php
                        // Print hook availablity warning
                        if ( ! $this->is_hook_available( $position ) ) {
                            echo '<div class="blox-alert-box no-side-margin">' . sprintf( __( 'The current saved hook is no longer available. Choose a new one from the table below or check the Blox %1$sposition settings%2$s.', 'blox' ), '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=position' ) . '">', '</a>' ) . '</div>';
                        }
                        // Print hook selector
                        $this->print_hook_selector();
                        ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e( 'Hook Priority', 'blox' ); ?></label></th>
                    <td>
                        <input type="text" name="<?php echo $name_prefix; ?>[hook][priority]" id="blox_position_hook_priority_<?php echo $id; ?>" value="<?php echo $priority; ?>" class="blox-small-text"/>
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
                                        echo '<div class="blox-alert-box">' . sprintf( __( 'All hook sections for this hook type are disabled. Visit the Blox %1$sposition settings%2$s to re-enable hook sections.', 'blox' ), '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=position' ) . '">', '</a>' ) . '</div>';
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
        <table class="form-table position">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e( 'Shortcode Positioning', 'blox' ); ?></label></th>
                    <td>
                        <div class="blox-checkbox first before">
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
        <table class="form-table position">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e( 'PHP Positioning', 'blox' ); ?></label></th>
                    <td>
                        <div class="blox-checkbox first before">
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
        @TODO NEED to DELETE????
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

        $settings['hook']['disable']                = isset( $name_prefix['hook']['disable'] ) ? 1 : 0;
        $settings['hook']['position']               = isset( $name_prefix['hook']['position'] ) ? esc_attr( $name_prefix['hook']['position'] ) : '';
        $settings['hook']['priority']               = absint( $name_prefix['hook']['priority'] );

        $settings['shortcode']['disable']           = isset( $name_prefix['shortcode']['disable'] ) ? 1 : 0;
        $settings['shortcode']['ignore_location']   = isset( $name_prefix['shortcode']['ignore_location'] ) ? 1 : 0;

        $settings['php']['disable']                 = isset( $name_prefix['php']['disable'] ) ? 1 : 0;
        $settings['php']['ignore_location']         = isset( $name_prefix['php']['ignore_location'] ) ? 1 : 0;

		return apply_filters( 'blox_save_position_settings', $settings, $post_id, $name_prefix, $global );
	}


	/**
     * Add admin column for global blocks
     *
     * @since 1.0.0
     *
     * @param array $columns   An array of all available admin columns
     *
     * @return array $columns  An updated array of all available admin columns with position added
     */
    public function admin_column_title( $columns ) {
    	$columns['position'] = __( 'Position', 'blox' );
    	return $columns;
    }


    /**
     * Print the admin column data for global blocks.
     *
     * @since 1.0.0 (Heavily updated in 2.0.0)
     *
     * @param string $post_id    The block (post) id
     * @param array $block       Array of all block data
     */
    public function admin_column_data( $post_id, $block ) {

        $position_types = array( 'hook', 'shortcode', 'php' );

        // Hook type availability
        $position_types_globally_disabled = array();
        foreach ( $position_types as $type ) {
            $position_types_globally_disabled[$type] = blox_get_option( 'global_disable_' . $type . '_positioning', 0 );
        }

        // Check if all positioning has been globally disabled
        $globally_disabled = ! in_array( 0, $position_types_globally_disabled ) ? 1 : 0;

        // Check the position settings on the individual blocks
        $position_types_disabled = array();
        foreach ( $position_types as $type ) {
            $position_types_disabled[$type] = isset( $block['position'][$type]['disable'] ) ? $block['position'][$type]['disable'] : 0;
        }
        // Check the position settings on the individual blocks

        // Set the position and hook priority
        $position = ! empty( $block['position']['hook']['position'] ) ? esc_attr( $block['position']['hook']['position'] )  : '';
        $priority = ! empty( $block['position']['hook']['priority'] ) ? esc_attr( $block['position']['hook']['priority'] )  : 15;

        // Handle settings from Blox v1.x
        if ( isset( $block['position']['position_type'] ) ) {
            if ( $block['position']['position_type'] == 'default' ) {
              $position = esc_attr( blox_get_option( 'global_default_position', 'genesis_after_header' ) );
              $priority = esc_attr( blox_get_option( 'global_default_priority', 15 ) );
            } else {
              $position = ! empty( $block['position']['custom']['position'] ) ? esc_attr( $block['position']['custom']['position'] ) : 'genesis_after_header';
              $priority = ! empty( $block['position']['custom']['priority'] ) ? esc_attr( $block['position']['custom']['priority'] ) : 15;
            }
        }
        ?>
        <div class="blox-position-column-data">

            <?php
            // Throw error message if the user disabled all of the positioning options.
            if ( $globally_disabled ) {
                echo '<div class="blox-alert-box">' . sprintf( __( 'All positioning options are globally disabled. Visit the Blox %1$sposition settings%2$s to re-enable.', 'blox' ), '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=position' ) . '">', '</a>' ) . '</div>';
            } else if ( ! in_array( 0, $position_types_disabled ) ) {
                echo '<div class="blox-alert-box">' . sprintf( __( 'All positioning options are disabled. Edit the position settings for this block to re-enable.', 'blox' ), '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=position' ) . '">', '</a>' ) . '</div>';
            } else {
                ?>
                <div class="blox-column-data-controls">
                <?php
                    $this->position_admin_column_hook_control( $position, $position_types_globally_disabled['hook'], $position_types_disabled['hook'] );
                    $this->position_admin_column_shortcode_control( $position_types_globally_disabled['shortcode'], $position_types_disabled['shortcode'] );
                    $this->position_admin_column_php_control( $position_types_globally_disabled['php'], $position_types_disabled['php'] );
                ?>
                </div>
                <div class="blox-column-data-details">
                <?php
                    $this->position_admin_column_hook_details( $position, $priority, $position_types_globally_disabled['hook'], $position_types_disabled['hook'] );
                    $this->position_admin_column_shortcode_details( $post_id, $position_types_globally_disabled['shortcode'], $position_types_disabled['shortcode'] );
                    $this->position_admin_column_php_details( $post_id, $position_types_globally_disabled['php'], $position_types_disabled['php'] );
                ?>
                </div>
                <?php
            } ?>
        </div>
        <?php

        // Ensure that blocks without a hook position set are still shown
        $position_meta = empty( $position ) ? '-' : $position;

        // Set a different meta for error messages so they all display together on sort
        if ( $globally_disabled || ! in_array( 0, $position_types_disabled ) ) {
            $position_meta = '-error';
        }

		// Save our position meta values separately for sorting
		update_post_meta( $post_id, '_blox_content_blocks_position', $position_meta );
    }


    /**
     * Print hook position option control in the admin column
     *
     * @since 2.0.0
     *
     * @param string $postion         The set hook position
     * @param bool $globally_disabled Is this positioning option globally disabled
     * @param bool $disabled          Indicates if position option is disabled or not
     */
    public function position_admin_column_hook_control( $position, $globally_disabled, $disabled ){
        if ( ! $globally_disabled ){

            // If no hook is selected (i.e. new post), just show an mdash
            $position = empty( $position ) ? '—' : $position;
            $disabled = ( $disabled || ! $this->is_hook_available( $position ) ) ? 'disabled' : '';

            ?>
            <div class="blox-data-control hook">
                <div class="blox-position-hook-slug <?php echo $disabled;?>">
                    <?php echo $position;?>
                </div>
                <div class="blox-data-control-toggle blox-has-tooltip <?php echo $disabled;?>" data-details-type="hook" aria-label="<?php _e( 'View hook details', 'blox' );?>">
                    <span class="dashicons dashicons-info"></span>
                    <span class="screen-reader-text"><?php _e( 'View hook details');?></span>
                </div>
            </div>
            <?php
        }
    }


    /**
     * Print hook position option details in the admin column
     *
     * @since 2.0.0
     *
     * @param string $postion         The set hook position
     * @param string $priorty         The set hook priority
     * @param bool $globally_disabled Is this positioning option globally disabled
     * @param bool $disabled          Indicates if position option is disabled or not
     */
    public function position_admin_column_hook_details( $position, $priority, $globally_disabled, $disabled ) {
        if ( ! $globally_disabled ){

            $disabled = $disabled ? 'disabled' : '';
            ?>
            <div class="blox-data-details hook">
                <?php if ( $disabled ){ ?>
                    <div class="blox-alert-box">
                        <?php _e( 'Hook positioning is disabled. Edit this block to re-enable.', 'blox' );?>
                    </div>
                <?php } else {

                    // Print hook availablity warning
                    if ( ! $this->is_hook_available( $position ) && ! empty( $position ) ) {
                        echo '<div class="blox-alert-box">' . sprintf( __( 'The current saved hook is no longer available. It was likely disabled via the Blox %1$sposition settings%2$s. Choose a new hook or re-enable the saved one.', 'blox' ), '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=position' ) . '">', '</a>' ) . '</div>';
                    } else if ( empty( $position ) ) {
                        echo '<div class="blox-alert-box">' . sprintf( __( 'It does not appear that a position hook as been set for this block. Edit the block and choose a hook, or simply disable hook positioning to avoid this error message.', 'blox' ), '<a href="' . admin_url( 'edit.php?post_type=blox&page=blox-settings&tab=position' ) . '">', '</a>' ) . '</div>';
                    }
                    ?>
                    <div class="blox-data-details-sub-container">
                        <div class="title"><?php echo __( 'Priority', 'blox' );?></div>
                        <div class="meta"><?php echo $priority;?></div>
                    </div>
                <?php } ?>
            </div>
            <?php
        }
    }


    /**
     * Print shortcode position option control in the admin column
     *
     * @since 2.0.0
     *
     * @param bool $globally_disabled Is this positioning option globally disabled
     * @param bool $disabled          Indicates if position option is disabled or not
     */
    public function position_admin_column_shortcode_control( $globally_disabled, $disabled ){
        if ( ! $globally_disabled ){

            $disabled = $disabled ? 'disabled' : '';
            ?>
            <div class="blox-data-control shortcode">
                <div class="blox-data-control-toggle blox-has-tooltip <?php echo $disabled;?>" data-details-type="shortcode" aria-label="<?php _e( 'View block shortcode');?>">
                    <span class="blox-icon blox-icon-shortcode">
                        <?php echo file_get_contents( plugin_dir_url( __FILE__ ) . '../../assets/images/shortcode.svg' );?>
                    </span>
                    <span class="screen-reader-text"><?php _e( 'View block shortcode');?></span>
                </div>
            </div>
            <?php
        }
    }


    /**
     * Print shortcode position option details in the admin column
     *
     * @since 2.0.0
     *
     * @param string $post_id         The block (post) id
     * @param bool $globally_disabled Is this positioning option globally disabled
     * @param bool $disabled          Indicates if position option is disabled or not
     */
    public function position_admin_column_shortcode_details( $post_id, $globally_disabled, $disabled ) {
        if ( ! $globally_disabled ){
            ?>
            <div class="blox-data-details shortcode">
                <?php if ( $disabled ){ ?>
                    <div class="blox-alert-box">
                        <?php _e( 'Shortcode positioning is disabled. Edit this block to re-enable.', 'blox' );?>
                    </div>
                <?php } else { ?>
                    <div class="blox-code">[blox id="<?php echo 'global_' . $post_id; ?>"]</div>
                    <div class="blox-description">
                        <?php _e( 'Copy and paste the above shortcode anywhere that accepts a shortcode. Visibility and location settings are respected when using shortcode positioning.', 'blox' ); ?>
                    </div>
                <?php } ?>
            </div>
            <?php
        }
    }


    /**
     * Print php position option control in the admin column
     *
     * @since 2.0.0
     *
     * @param bool $globally_disabled Is this positioning option globally disabled
     * @param bool $disabled          Indicates if position option is disabled or not
     */
    public function position_admin_column_php_control( $globally_disabled, $disabled ){
        if ( ! $globally_disabled ){

            $disabled = $disabled ? 'disabled' : '';
            ?>
            <div class="blox-data-control php">
                <div class="blox-data-control-toggle blox-has-tooltip <?php echo $disabled;?>" data-details-type="php" aria-label="<?php _e( 'View block PHP insertion code', 'blox' );?>">
                    <span class="dashicons dashicons-editor-code"></span>
                    <span class="screen-reader-text"><?php _e( 'View block PHP insertion code');?></span>
                </div>
            </div>
            <?php
        }
    }


    /**
     * Print php position option details in the admin column
     *
     * @since 2.0.0
     *
     * @param string $post_id         The block (post) id
     * @param bool $globally_disabled Is this positioning option globally disabled
     * @param bool $disabled          Indicates if position option is disabled or not
     */
    public function position_admin_column_php_details( $post_id, $globally_disabled, $disabled ) {
        if ( ! $globally_disabled ){
            ?>
            <div class="blox-data-details php">
                <?php if ( $disabled ){ ?>
                    <div class="blox-alert-box">
                        <?php _e( 'PHP positioning is disabled. Edit this block to re-enable.', 'blox' );?>
                    </div>
                <?php } else { ?>
                    <div class="blox-code">blox_display_block( "<?php echo 'global_' . $post_id; ?>" );</div>
                    <div class="blox-description">
                        <?php _e( 'Copy and paste the above PHP code into any of your theme files. Visibility and location settings are respected when using PHP positioning.', 'blox' ); ?>
                    </div>
                <?php } ?>
            </div>
            <?php
        }
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
     * Helper method for retrieving all available hook types.
     *
     * @since 2.0.0
     *
     * @return array Array of all hook types.
     */
    public function get_hook_types() {

        $instance = Blox_Common::get_instance();
        return $instance->get_hook_types();

    }


    /**
     * Helper method for retrieving all active hooks.
     *
     * @since 2.0.0
     *
     * @return array Array of all active hooks.
     */
    public function get_active_hooks() {

        $instance = Blox_Common::get_instance();
        return $instance->get_active_hooks();
    }


    /**
     * Helper function testing if the passed hook is available to Blox.
     *
     * @since 2.0.0
     *
     * @param string $hook  The hook we want to test.
     *
     * @return bool         Is the hook available or not.
     */
    public function is_hook_available( $hook ){

        $instance = Blox_Common::get_instance();
        return $instance->is_hook_available( $hook );
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
