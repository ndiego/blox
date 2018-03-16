<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the visibility tab and loads in all the available options
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Visibility {

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

		// Setup visibility settings
		add_filter( 'blox_metabox_tabs', array( $this, 'add_visibility_tab' ), 10 );
		add_action( 'blox_get_metabox_tab_visibility', array( $this, 'get_metabox_tab_visibility' ), 10, 4 );
		add_filter( 'blox_save_metabox_tab_visibility', array( $this, 'save_metabox_tab_visibility' ), 10, 3 );

		// Add the admin column data for global blocks
		add_filter( 'blox_admin_column_titles', array( $this, 'admin_column_title' ), 4, 1 );
		add_action( 'blox_admin_column_data_visibility', array( $this, 'admin_column_data' ), 10, 2 );

		// Make admin column sortable
		add_filter( 'manage_edit-blox_sortable_columns', array( $this, 'admin_column_sortable' ), 5 );
        add_filter( 'request', array( $this, 'admin_column_orderby' ) );

        // Add quick edit & bulk edit settings
        add_action( 'blox_quickedit_settings_visibility', array( $this, 'quickedit_bulkedit_settings' ), 10, 2 );
		add_filter( 'blox_quickedit_save_settings', array( $this, 'quickedit_bulkedit_save_settings' ), 10, 3 );
        add_action( 'blox_bulkedit_settings_visibility', array( $this, 'quickedit_bulkedit_settings' ), 10, 2 );
        add_filter( 'blox_bulkedit_save_settings', array( $this, 'quickedit_bulkedit_save_settings' ), 10, 3 );

		// Adds visibility meta to local blocks
		add_action( 'blox_content_block_meta', array( $this, 'visibility_content_block_meta' ), 10, 1 );

		// Run visibilty test on the frontend
		add_filter( 'blox_display_test', array( $this, 'run_visibility_display_test' ), 5, 5 );
    }


	/**
	 * Add the Visibility tab
     *
     * @since 1.0.0
     *
     * @param array $tab  An array of the tabs available
     * @return array $tab The updated tabs array
     */
	public function add_visibility_tab( $tabs ) {

		$tabs['visibility'] = array(
			'title' => __( 'Visibility', 'blox' ),
			'scope' => 'all'  // all, local, or global
		);

		return $tabs;
	}


    /**
     * Creates the visibility settings fields
     *
     * @since 1.0.0
     *
     * @param array $data         An array of all block data
     * @param string $name_id 	  The prefix for saving each setting
     * @param string $get_id  	  The prefix for retrieving each setting
     * @param bool $global	      The block state
     */
 	public function get_metabox_tab_visibility( $data = null, $name_id, $get_id, $global ) {

		// $data array structure is different for global and local blocks, so we need to treat each case separately
		if ( $global ) {
			// Indicates where the visibility settings are saved
			$name_prefix = "blox_content_blocks_data[visibility]";
			$get_prefix = ! empty( $data['visibility'] ) ? $data['visibility'] : null;

		} else {
			// Indicates where the visibility settings are saved
			$name_prefix = "blox_content_blocks_data[$name_id][visibility]";

			// Used for retrieving the visibility settings
			// If $data = null, then there are no settings to get
			if ( $data == null ) {
				$get_prefix = null;
			} else {
				$get_prefix = ! empty( $data[$get_id]['visibility'] ) ? $data[$get_id]['visibility'] : null;
			}
		}

		// Get the content for the visibility tab
		$this->visibility_settings( $get_id, $name_prefix, $get_prefix, false );
    }


    /* Creates the visibility settings fields
     *
     * @since 1.0.0
	 *
     * @param int $id             The id of the content block, either global or individual (attached to post/page/cpt)
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param bool $global	      The block state
     */
    public function visibility_settings( $id, $name_prefix, $get_prefix, $global ) {
		?>

		<table class="form-table">
			<tbody>

				<tr class="blox-visibility-global-disable">
					<th scope="row"><?php _e( 'Global Visibility', 'blox' ); ?></th>
					<td>
						<label class="blox-single-checkbox">
							<input type="checkbox" name="<?php echo $name_prefix; ?>[global_disable]" id="blox_visibility_global_disable_<?php echo $id; ?>" value="1" <?php ! empty( $get_prefix['global_disable'] ) ? checked( $get_prefix['global_disable'] ) : ''; ?> />
							<?php _e( 'Check to globally disable', 'blox' ); ?>
						</label>
						<div class="blox-description">
							<?php _e( 'Disable this content block and it will no longer appear on the frontend of the website.', 'blox' ); ?>
						</div>
					</td>
				</tr>

				<tr class="blox-visibility-role_type">
					<th scope="row"><?php _e( 'Visibility by Role', 'blox' ); ?></th>
					<td>
						<select name="<?php echo $name_prefix; ?>[role][role_type]">
							<option value="all" title="<?php _e( 'Visible to everyone.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['role']['role_type'] ) ? selected( $get_prefix['role']['role_type'], 'all' ) : 'selected'; ?>><?php _e( 'Visible to All', 'blox' ); ?></option>
							<option value="public" title="<?php _e( 'Visible to everyone that is logged out.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['role']['role_type'] ) ? selected( $get_prefix['role']['role_type'], 'public' ) : ''; ?>><?php _e( 'Public Facing', 'blox' ); ?></option>
							<option value="private" title="<?php _e( 'Visible to everyone that is logged in.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['role']['role_type'] ) ? selected( $get_prefix['role']['role_type'], 'private' ) : ''; ?>><?php _e( 'Private Facing', 'blox' ); ?></option>
							<option value="restrict" title="<?php _e( 'Only visible to user roles that are selected below.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['role']['role_type'] ) ? selected( $get_prefix['role']['role_type'], 'restrict' ) : ''; ?>><?php _e( 'Restrict by User Role', 'blox' ); ?></option>
						</select>

						<div class="blox-description">
							<?php _e( 'Choose who should be able to view the content block on the frontend.', 'blox' ); ?>
						</div>
					</td>
				</tr>


				<tr class="blox-visibility-role-restrictions <?php if ( empty( $get_prefix['role']['role_type'] ) || $get_prefix['role']['role_type'] != 'restrict' ) echo ( 'blox-hidden' ); ?>">
					<th scope="row"><?php _e( 'Role Restriction Settings', 'blox' ); ?></th>
					<td>
						<div class="blox-checkbox-container">
							<ul class="blox-columns">
							<?php foreach ( get_editable_roles() as $role_name => $role_info ) { ?>
								<li>
									<label>
										<input type="checkbox" name="<?php echo $name_prefix; ?>[role][restrictions][<?php echo $role_name ?>]" value="1" <?php ! empty( $get_prefix['role']['restrictions'][$role_name] ) ? checked( $get_prefix['role']['restrictions'][$role_name] ) : ''; ?> >
										<?php echo ucfirst( $role_name ); ?>
									</label>
								</li>
							<?php } ?>
							</ul>
						</div>
						<div class="blox-checkbox-select-tools">
							<a class="blox-checkbox-select-all" href="#"><?php _e( 'Select All', 'blox' ); ?></a> <a class="blox-checkbox-select-none" href="#"><?php _e( 'Unselect All', 'blox' ); ?></a>
						</div>
						<div class="blox-description">
							<?php _e( 'If role restriction is enabled, the content block will only show on the frontend for the users selected above.', 'blox' ); ?>
						</div>
					</td>
				</tr>

				<?php do_action( 'blox_visibility_settings', $id, $name_prefix, $get_prefix, $global ); ?>

			</tbody>
		</table>

		<?php
    }


    /**
	 * Saves all of the visibility settings
     *
     * @since 1.0.0
     *
     * @param int $post_id        The global block id or the post/page/custom post-type id corresponding to the local block
     * @param string $name_prefix The prefix for saving each setting
     * @param bool $global        The block state
     *
     * @return array $settings    Return an array of updated settings
     */
    public function save_metabox_tab_visibility( $post_id, $name_prefix, $global ) {

		$settings = array();

		$settings['global_disable']      = isset( $name_prefix['global_disable'] ) ? 1 : 0;
		$settings['role']['role_type']   = esc_attr( $name_prefix['role']['role_type'] );

        // Need to check if function exists due to Jetpack conflict
        if ( function_exists( 'get_editable_roles' ) ) {
    		foreach ( get_editable_roles() as $role_name => $role_info ) {
    			$settings['role']['restrictions'][$role_name] = isset( $name_prefix['role']['restrictions'][$role_name] ) ? 1 : 0;
    		}
        }

		return apply_filters( 'blox_save_visibility_settings', $settings, $post_id, $name_prefix, $global );
	}


	/**
     * Add admin column for global blocks
     *
     * @since 1.0.0
     *
     * @param array $columns  Array of all admin columns for Global Blocks
     *
     * @return array $columns Return an updated array of all admin columns
     */
    public function admin_column_title( $columns ) {
    	$columns['visibility'] = __( 'Visibility', 'blox' );
    	return $columns;
    }


    /**
     * Print the admin column data for global blocks.
     *
     * @since 1.0.0
     *
     * @param string $post_id
     * @param array $block_data
     */
    public function admin_column_data( $post_id, $block_data ) {

        // Check if global blocks are enabled
		$global_enable = blox_get_option( 'global_enable', false );

		if ( $global_enable ) {

			if ( ! empty( $block_data['visibility']['global_disable'] ) && $block_data['visibility']['global_disable'] == 1 ) {
                $hidden    = '<input type="hidden" name="global_disable" value="1">';
                $content   = '<span style="color:#a00;font-style:italic;">' . __( 'Disabled', 'blox' ) . '</span>';
                $meta_data = '_disabled'; // Use _ to force disabled blocks to top or bottom on sort
			} else {

                $hidden = '<input type="hidden" name="global_disable" value="0">';

				$type = ! empty( $block_data['visibility']['role']['role_type'] ) ? $block_data['visibility']['role']['role_type'] : 'all';

				switch ( $type ) {
					case 'all' :
						$content = __( 'All', 'blox' );
						break;
					case 'public' :
						$content = __( 'Public', 'blox' );
						break;
					case 'private' :
						$content = __( 'Private', 'blox' );
						break;
					case 'restrict' :
						if ( ! empty( $block_data['visibility']['role']['restrictions'] ) ) {
							// Get all of the selected roles, make the first letter capitalized, then print to page
							$content =  implode( ", ", array_map( array( $this, 'uppercase_first' ), array_keys( $block_data['visibility']['role']['restrictions'], 1 ) ) );
						} else {
							$content = __( 'No Roles Selected', 'blox' );
						}
						break;
					default :
						$content = '<span style="color:#a00;font-style:italic;">' . __( 'Error', 'blox' ) . '</span>';
						break;
				}

                $meta_data = $type;
			}
		} else {
            $hidden    = '';
			$content   = '<span style="color:#a00;font-style:italic;">' . __( 'Globally Disabled', 'blox' ) . '</span>';
			$meta_data = '_disabled'; // Use _ to force disabled blocks to top or bottom on sort
		}

        // Build the output, hidden fields + visible content
        $output = $hidden . $content;

        // Print the column output, but first allow add-ons to filter in additional content
		echo apply_filters( 'blox_visibility_meta_data', $output, $block_data, true );

		// Save our visibility meta values separately to allow for sorting
		update_post_meta( $post_id, '_blox_content_blocks_visibility', $meta_data );
    }


    /**
     * Tell Wordpress that the visibility column is sortable
     *
     * @since 1.0.0
     *
     * @param array $vars  Array of query variables
     */
	public function admin_column_sortable( $sortable_columns ) {
		$sortable_columns[ 'visibility' ] = 'visibility';
		return $sortable_columns;
	}


	/**
     * Tell Wordpress how to sort the visibility column
     *
     * @since 1.0.0
     *
     * @param array $vars  Array of query variables
     */
	public function admin_column_orderby( $vars ) {

		if ( isset( $vars['orderby'] ) && 'visibility' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
				'meta_key' => '_blox_content_blocks_visibility',
				'orderby' => 'meta_value'
			) );
		}

		return $vars;
	}


    /**
     * Add visibility settings to the quick edit or bulk edit screen for Blox
     *
     * @since 1.3.0
     *
     * @param string $post_type  Current post type which will always be blox
     * @param string $type       Either 'bulk' or 'quick'
     */
    function quickedit_bulkedit_settings( $post_type, $type ) {
        ?>
        <fieldset id="blox_edit_visibility" class="inline-edit-col-right custom">
            <div class="inline-edit-col column-visibility">
                <span class="title"><?php _e( 'Visibility', 'blox' ); ?></span>
                <div class="quickedit-settings">
                    <label>
                        <input name="global_disable" type="checkbox" value="1"/>
                        <span><?php _e( 'Disable Block', 'blox' ); ?></span>
                    </label>
                    <?php
                    // Allow add-ons, or developers, to hook in additional settings
                    do_action( 'blox_quickedit_add_settings_visibility', $post_type );
                    ?>
                </div>
            </div>
        </fieldset>
        <?php
    }


    /**
     * Save quick edit or bulk edit visibility settings
     *
     * @since 1.3.0
     *
     * @param array $settings  Array of all current block settings
     * @param array $request   Array of all requested data ready for saving (uses $_REQUEST or $_POST)
     * @param string $type     Either 'bulk' or 'quick'
     *
     * @return array $settings Array of updated block settings
     */
    function quickedit_bulkedit_save_settings( $settings, $request, $type ) {

        $settings['visibility']['global_disable'] = ( $request['global_disable'] == 1 || $request['global_disable'] == 0 ) ? esc_attr( $request['global_disable'] ) : 0;

        return $settings;
    }


    /**
     * Print the visibility meta data for local blocks.
     *
     * @param array $block
     */
     public function visibility_content_block_meta( $block ) {

		if ( ! empty( $block['visibility']['global_disable'] ) && $block['visibility']['global_disable'] == 1 ) {
			$output = __( 'Disabled', 'blox' );
		} else {

			$type = ! empty( $block['visibility']['role']['role_type'] ) ? $block['visibility']['role']['role_type'] : 'all';

			switch ( $type ) {
				case 'all' :
					$visibility = __( 'All', 'blox' );
					break;
				case 'public' :
					$visibility = __( 'Public', 'blox' );
					break;
				case 'private' :
					$visibility = __( 'Private', 'blox' );
					break;
				case 'restrict' :
					if ( ! empty( $block['visibility']['role']['restrictions'] ) ) {
						// Get all of the selected roles, make the first letter capitalized, then print to page
						$visibility = implode( ", ", array_map( array( $this, 'uppercase_first' ), array_keys( $block_data['visibility']['role']['restrictions'], 1 ) ) );
					} else {
						$visibility = __( 'No Roles Selected', 'blox' );
					}
					break;
				default :
					$visibility = __( 'Error, Not Saved', 'blox' );
					break;
			}

			$output = '<span style="cursor:help" title="Visibility: ' . $visibility . '">' . __( 'Active', 'blox' ) . '</span>';
		}

		// Filter the visibility meta, useful for addons
		echo apply_filters( 'blox_visibility_meta_data', $output, $block, false );
	}


	/**
	 * Run the visibility test
	 *
     * @since 1.0.0
	 *
	 * @param bool $display_test     Test for determining whether the block should be displayed
	 * @param int $id                The block id, if global, id = $post->ID otherwise it is a random local id
	 * @param array $block           Contains all of our block settings data
	 * @param bool $global           Tells whether our block is global or local
     * @param string $position_type  Identifies for what position type you are running this test for
	 */
	public function run_visibility_display_test( $display_test, $id, $block, $global, $position_type ) {

		// If the display test is already false, bail...
		if ( $display_test == false ) {
			return $display_test;
		}

		// Get the visibility data
		$visibility_data = isset( $block['visibility'] ) ? $block['visibility'] : '';

		// If we have visibility data, run the visibility test...
		if ( ! empty( $visibility_data ) ) {

			// Need to make this "true" to continue
			$visibility_test = false;

			// If the block is globally disabled, bail. Otherwise continue the visibility test
			if ( $visibility_data['global_disable'] != 1 ) {

				if ( $visibility_data['role']['role_type'] == 'all' ) {
					$visibility_test = true;
				} else if ( $visibility_data['role']['role_type'] == 'public' && ! is_user_logged_in() ) {

					// The content block is public only display to those not logged in
					$visibility_test = true;

				} else if ( $visibility_data['role']['role_type'] == 'private' && is_user_logged_in() ) {

					// The content block is private and the user is logged in so display
					$visibility_test = true;

				} else if ( $visibility_data['role']['role_type'] == 'restrict' && is_user_logged_in() ) {

					// The user is logged in, so now we check what restrictions there are and if the user
					// has the permissions to view the content. Note: if no restrictions are set, don't show at all
					if ( ! empty( $visibility_data['role']['restrictions'] ) ) {

						// Create an array to hold our restrictions
						$restrictions = array();

						// Fill our restrictions array with the block's restrictions
						foreach ( $visibility_data['role']['restrictions'] as $restriction => $val ) {
                            if ( $val == 1 ) {
							    $restrictions[] = $restriction;
                            }
						}

						// Get info about the current user and bail if it's not an instance of WP_User
						$current_user = wp_get_current_user();
						if ( ! ( $current_user instanceof WP_User ) ) {
						   return;
						}

						// Get the user's role
						$user_roles = $current_user->roles;

						// See if user's role is one of the restricted ones. If so it will return an array
						// of matched roles. Count to make sure array length > 0. If so, show the block
						if ( count( array_intersect( $restrictions, $user_roles ) ) != 0 ) {
							$visibility_test = true;
						}
					}
				}
			}

			// Filter for modifying the visibility test. Used by addons.
			$visibility_test = apply_filters( 'blox_content_block_visibility_test', $visibility_test, $id, $block, $global );

			// If the block passes the visibility test, continue on. If not, the test fails.
			if ( $visibility_test == true ) {
				return true;
			} else {
				return false;
			}
		} else {

			// The visibility data does not exist, so move on to another test
			return true;
		}
	}


	/**
     * Helper function for making the first letter of a string uppercase
     *
     * Added this function so anonymous functions could be removed which were not compatible with PHP 5.2
     *
     * @since 1.2.0
     *
     * @return string The string with the first letter made uppercase
     */
	public function uppercase_first( $string ) {
    	return ucfirst( $string );
    }


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Visibility ) ) {
            self::$instance = new Blox_Visibility();
        }

        return self::$instance;
    }
}

// Load the visibility class.
$blox_visibility = Blox_Visibility::get_instance();
