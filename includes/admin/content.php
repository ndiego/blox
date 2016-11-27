<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the content tab and loads in all the available options
 *
 * @since 1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Content {

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

		// Setup content settings
		add_filter( 'blox_metabox_tabs', array( $this, 'add_content_tab' ), 4 );
		add_action( 'blox_get_metabox_tab_content', array( $this, 'get_metabox_tab_content' ), 10, 4 );
		add_filter( 'blox_save_metabox_tab_content', array( $this, 'save_metabox_tab_content' ), 10, 3 );

		// Run content check to make sure all content types are available, otherwise print messages
		add_action( 'blox_tab_container_before', array( $this, 'content_check' ), 10, 5 );

        // Add content defaults
		add_filter( 'blox_settings_defaults', array( $this, 'add_content_defaults' ), 10, 1 );

		// Add the admin column data for global blocks
		add_filter( 'blox_admin_column_titles', array( $this, 'admin_column_title' ), 1, 1 );
		add_action( 'blox_admin_column_data_content', array( $this, 'admin_column_data' ), 10, 2 );

		// Make admin column sortable
		add_filter( 'manage_edit-blox_sortable_columns', array( $this, 'admin_column_sortable' ), 5 );
        add_filter( 'request', array( $this, 'admin_column_orderby' ) );
    }


    /* Add content defaults
     *
     * @since 1.3.0
     *
     * @param array $defaults An array of all default settings
     */
    public function add_content_defaults( $defaults ) {

        $content_defaults = apply_filters( 'blox_settings_defaults_content',
            array(
                'defaults_content_header' => array(
                    'id'   => 'defaults_content_header',
                    'name' => '<span class="title">' . __( 'Content Defaults', 'blox' ) . '</span>',
                    'desc' => __( 'When a new block is created, the following content defaults will be applied.', 'blox' ),
                    'type' => 'header',
                ),
                'content_test' => array(
                    'id'    => 'content_test',
                    'name'  => __( 'Testing', 'blox' ),
                    'label' => __( 'This is a test', 'blox' ),
                    'desc'  => __( 'this is a test', 'blox' ),
                    'type'  => 'checkbox',
                    'group' => 'content_defaults',
                    'subgroup' => 'slideshow',
                    'default' => true
                ),
            )
        );

        // We want the content settings to appear first
        return $content_defaults + $defaults;
    }


	/* Add the Content tab
     *
     * @since 1.0.0
     *
     * @param array $tab An array of the tabs available
     */
	public function add_content_tab( $tabs ) {

		$tabs['content'] = array(
			'title' => __( 'Content', 'blox' ),
			'scope' => 'all'  // all, local, or global
		);

		return $tabs;
	}


    /**
     * Creates the content settings fields
     *
     * @since 1.0.0
     *
     * @param array $data         An array of all block data
     * @param string $name_id 	  The prefix for saving each setting
     * @param string $get_id  	  The prefix for retrieving each setting
     * @param bool $global	      The block state
     */
	public function get_metabox_tab_content( $data = null, $name_id, $get_id, $global ) {

		if ( $global ) {
			// Indicates where the content settings are saved
			$name_prefix = "blox_content_blocks_data[content]";
			$get_prefix = ! empty( $data['content'] ) ? $data['content'] : null;

		} else {
			// Indicates where the content settings are saved
			$name_prefix = "blox_content_blocks_data[$name_id][content]";

			// Used for retrieving the content settings
			// If $data = null, then there are no settings to get
			if ( $data == null ) {
				$get_prefix = null;
			} else {
				$get_prefix = ! empty( $data[$get_id]['content'] ) ? $data[$get_id]['content'] : null;
			}

		}

		// Get the content for the content tab
		$this->content_settings( $name_id, $name_prefix, $get_prefix, $global );
    }


    /**
     * Creates all of the fields for our block content
     *
     * @since 1.0.0
     *
     * @param int $id             The block id
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param bool $global	      Determines if the content being loaded for local or global blocks
     */
    public function content_settings( $id, $name_prefix, $get_prefix, $global ) {
    	?>
    	<table class="form-table blox-content-type-container">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Content Type' ); ?><span class="icon-stop2"></span></th>
					<td>
						<select name="<?php echo $name_prefix; ?>[content_type]" id="blox_content_type" class="blox-content-type">
							<?php foreach ( $this->get_content_types() as $type => $title ) { ?>
								<option value="<?php echo $type; ?>" <?php echo ! empty( $get_prefix['content_type'] ) ? selected( $get_prefix['content_type'], $type ) : ''; ?>><?php echo $title; ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>

		<?php
		foreach ( $this->get_content_types() as $type => $title ) {

			// Get all the available content options
			do_action( 'blox_get_content_' . $type, $id, $name_prefix, $get_prefix, $global );
		}
		?>
    	<?php
    }


    /**
	 * Saves all of the content settings
     *
     * @since 1.0.0
     *
     * @param int $post_id        The global block id or the post/page/custom post-type id corresponding to the local block
     * @param string $name_prefix The prefix for saving each setting
     * @param bool $global        The block state
     *
     * @return array $settings    Return an array of updated settings
     */
	public function save_metabox_tab_content( $post_id, $name_prefix, $global ) {

		$settings = array();

		$settings['content_type'] = esc_attr( $name_prefix['content_type'] );

		foreach ( $this->get_content_types() as $type => $title ) {
			if ( $global ) {
				$name_prefix = ! empty( $_POST['blox_content_blocks_data']['content'][$type] ) ? $_POST['blox_content_blocks_data']['content'][$type] : '';
				$settings[$type] = apply_filters( 'blox_save_content_' . $type, $name_prefix, $post_id, true );
			} else {
				$name_prefix = ! empty( $_POST['blox_content_blocks_data'][$post_id]['content'][$type] ) ? $_POST['blox_content_blocks_data'][$post_id]['content'][$type] : '';
				$settings[$type] = apply_filters( 'blox_save_content_' . $type, $name_prefix, $post_id, false );
			}
		}

		update_post_meta( $post_id, '_blox_content_blocks_type', $settings['content_type'] );

		return $settings;
	}


    /**
     * Helper function. If the current content option is no longer available (ie the Extension was deactivated), provide a notice
     *
     * @since 1.0.0
     *
     * @param string $tab     Name of the tab to show the message on
     * @param array $data     String of all setting data associated with the current block
     * @param string $name_id The content blocks id (might be random id if a local block that was added via ajax)
     * @param string $get_id  The content blocks id (might be random id if a local block that was added via ajax)
     * @param bool $global    Indicates if the block is global
     */
	public function content_check( $tab, $data, $name_id, $get_id, $global ) {

		// Only display content check error on the content tab
		if ( $tab == 'content' ) {

			$data = ! empty( $data ) ? $data : array();

			// Need to handle $data differently for local blocks
			if ( $global && isset( $data['content']['content_type'] ) ) {
				$set_content_type = $data['content']['content_type'];
			} else if ( isset( $data[$name_id]['content']['content_type'] ) ) {
				$set_content_type = $data[$name_id]['content']['content_type'];
			}

			$available_content_types = $this->get_content_types();

			if ( isset( $set_content_type ) && ! array_key_exists( $set_content_type, $available_content_types ) ) {
				?>
				<div class="blox-alert blox-alert-error narrow">
					<?php echo sprintf( __( 'The content type of this block is currently set to %1$s, which no longer exists. Therefore, this block is currently not visible on your site. Perhaps you deactivated a Blox Addon by mistake, or switched to Blox Lite. Choose a new content type and publish to get this block displaying again.', 'blox' ), '<strong>' . $set_content_type . '</strong>' ); ?>
				</div>
				<?php
			}
		}
	}


    /**
     * Add admin column for global blocks
     *
     * @param string $post_id
     * @param array $block_data
     */
    public function admin_column_title( $columns ) {
    	$columns['content'] = __( 'Content', 'blox' );
    	return $columns;
    }


    /**
     * Print the admin column data for global blocks.
     *
     * @param string $post_id
     * @param array $block_data
     */
    public function admin_column_data( $post_id, $block_data ) {
    	if (! empty( $block_data['content']['content_type'] ) ) {
    		$content   = ucfirst( esc_attr( $block_data['content']['content_type'] ) );
    		$meta_data = esc_attr( $block_data['content']['content_type'] );
		} else {
			$content   = '<span style="color:#a00;font-style:italic;">' . __( 'Error', 'blox' ) . '</span>';
			$meta_data = '';
		}

		echo $content;

		// Save our content meta values separately for sorting
		update_post_meta( $post_id, '_blox_content_blocks_content', $meta_data );
	}


	/**
     * Tell Wordpress that the content column is sortable
     *
     * @since 1.0.0
     *
     * @param array $vars  Array of query variables
     */
	public function admin_column_sortable( $sortable_columns ) {
		$sortable_columns[ 'content' ] = 'content';
		return $sortable_columns;
	}


	/**
     * Tell Wordpress how to sort the content column
     *
     * @since 1.0.0
     *
     * @param array $vars  Array of query variables
     */
	public function admin_column_orderby( $vars ) {

		if ( isset( $vars['orderby'] ) && 'content' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
				'meta_key' => '_blox_content_blocks_content',
				'orderby' => 'meta_value'
			) );
		}

		return $vars;
	}


    /**
     * Helper function for retrieving the available content types.
     *
     * @since 1.0.0
     *
     * @return array Array of all content types.
     */
    public function get_content_types() {

        $instance = Blox_Common::get_instance();
        return $instance->get_content_types();
    }


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Content ) ) {
            self::$instance = new Blox_Content();
        }

        return self::$instance;
    }
}

// Load the content class.
$blox_content = Blox_Content::get_instance();
