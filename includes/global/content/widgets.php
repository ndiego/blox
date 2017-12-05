<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the widgets content section within the content tab and loads in all available options
 *
 * @since 	2.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Content_Widgets {

    /**
     * Holds the class object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public static $instance;


    /**
     * Path to the file.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $file = __FILE__;


    /**
     * Holds the base class object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public $base;


	/**
	 * Primary class constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

        // Load the base class object.
        $this->base = Blox_Main::get_instance();

		// Register the Blox widget area
		add_action( 'init', array( $this, 'register_blox_widget_area' ) );

		// Add the Widgets content type, format settings, and save...
		add_filter( 'blox_content_type', array( $this, 'add_widgets_content' ), 20 );
		add_action( 'blox_get_content_widgets', array( $this, 'get_widgets_content' ), 10, 4 );
		add_filter( 'blox_save_content_widgets', array( $this, 'save_widgets_content' ), 10, 3 );

		// Print widget content on the frontend
		add_action( 'blox_print_content_widgets', array( $this, 'print_widgets_content' ), 10, 4 );
	}


	/**
	 * Register the Blox widget area
	 *
	 * @since 2.0.0
	 */
	public function register_blox_widget_area() {

		// Only run if Genesis is active...
		if ( function_exists( 'genesis_pre' ) ) {

			// Use builtin Genesis function to register widget area
			genesis_register_widget_area(
				array(
					'id'            => 'blox-widgets',
					'name'          => __( 'Blox Widgets', 'blox' ),
					'description'   => __( 'Place all widgets you would like to make available to Blox here. When building a widget content block, you will be able to toggle which widgets you would like to use. However, the display order of the widgets is determined here.', 'blox' )
				)
			);
		}
	}


	/* Enables the "Widget" content option in the plugin
	 *
	 * @since 2.0.0
	 *
	 * @param array $content_types  An array of the content types available
	 */
	public function add_widgets_content( $content_types ) {
		$content_types['widgets'] = __( 'Widgets', 'blox' );
		return $content_types;
	}


	/* Prints all of the widget ralated settings fields
	 *
	 * @since 2.0.0
	 *
	 * @param int $id             The block id
	 * @param string $name_prefix The prefix for saving each setting
	 * @param string $get_prefix  The prefix for retrieving each setting
	 * @param bool $global        The block state
	 */
	public function get_widgets_content( $id, $name_prefix, $get_prefix, $global ) {

		global $wp_registered_widgets;
		?>

		<!-- Wordpress Editor Settings -->
		<table class="form-table blox-content-widgets blox-hidden">
			<tbody>
				<tr class="blox-content-title"><th scope="row"><?php _e( 'Widgets Settings', 'blox' ); ?></th><td><hr></td></tr>
				<tr>
					<th scope="row"><?php _e( 'Available Widgets', 'blox' ); ?></th>
					<td>
						<?php

						$sidebar_id       = 'blox-widgets';
						$sidebars_widgets = wp_get_sidebars_widgets();

						if ( ! empty ( $sidebars_widgets[$sidebar_id] ) ) {

						?>
						<div class="blox-checkbox-container">
							<ul>
							<?php

								foreach ( (array) $sidebars_widgets[$sidebar_id] as $widget_id ) {

									// Make sure our widget is in the registered widgets array
									if ( ! isset( $wp_registered_widgets[$widget_id] ) ) continue;
									?>
									<li>
										<label>

										<input type="checkbox" name="<?php echo $name_prefix; ?>[widgets][selection][]" value="<?php echo $widget_id; ?>" <?php echo ! empty( $get_prefix['widgets']['selection'] ) && in_array( $widget_id, $get_prefix['widgets']['selection'] ) ? 'checked="checked"' : ''; ?> /> <?php echo $wp_registered_widgets[$widget_id]['name']; ?>
										<?php
										if ( isset( $wp_registered_widgets[$widget_id]['params'][0]['number'] ) ) {

											// Retrieve optional set title if the widget has one (code thanks to qurl: Dynamic Widgets)
											$number      = $wp_registered_widgets[$widget_id]['params'][0]['number'];
											$option_name = $wp_registered_widgets[$widget_id]['callback'][0]->option_name;
											$option      = get_option( $option_name );

											// if a title was found, print it
											if ( ! empty( $option[$number]['title'] ) ) {
												echo ': <span class="in-widget-title">' . $option[$number]['title'] . '</span>';
											}
										}
										?>
										</label>
									</li>
								<?php } ?>
							</ul>
						</div>
						<div class="blox-checkbox-select-tools">
							<a class="blox-checkbox-select-all" href="#"><?php _e( 'Select All', 'blox' ); ?></a> <a class="blox-checkbox-select-none" href="#"><?php _e( 'Unselect All', 'blox' ); ?></a>
						</div>
						<div class="blox-description" style="margin-top:15px">
							<?php echo sprintf( __( 'To add more widgets, navigate to the admin %1$sWidgets%5$s page and place additional widgets in the %2$sBlox Widgets%3$s widget area. The order that selected widget are shown on the frontend is managed on the Widgets page. For more information, review the widgets %4$sdocumentation%5$s.', 'blox' ), '<a href="' . admin_url( 'widgets.php' ) . '">', '<strong>','</strong>', '<a href="https://www.bloxwp.com/documentation/widgets" target="_blank">', '</a>' );?>
						</div>
						<?php } else {
							echo '<div class="blox-alert">' . sprintf( __( 'It doesn\'t look like you have added any widgets yet. Head on over to the %1$sWidgets%5$s page and add a few widgets to the %2$sBlox Widgets%3$s widget area. They will then show up here and you can choose the ones you want to use. For more information, check out the %4$sWidgets Documentation%5$s', 'blox' ), '<a href="' . admin_url( 'widgets.php' ) . '">', '<strong>','</strong>', '<a href="https://www.bloxwp.com/documentation/widgets" target="_blank">', '</a>' ) . '</div>';
						} ?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}


	/* Saves all of the widget ralated settings
	 *
	 * @since 2.0.0
	 *
	 * @param string $name_prefix The prefix for saving each setting (this brings ...['editor'] with it)
	 * @param int $id             The block id
	 * @param bool $global        The block state
	 */
	public function save_widgets_content( $name_prefix, $id, $global ) {

		$settings = array();

		$settings['selection'] = isset( $name_prefix['selection'] ) ? array_map( 'esc_attr', $name_prefix['selection'] ) : '';

		return $settings;
	}


	/* Prints the widget content to the frontend
	 *
	 * @since 2.0.0
	 *
	 * @param array $content_data Array of all content data
	 * @param int $id             The block id
	 * @param array $block        NEED DESCRIPTION
	 * @param string $global      The block state
	 */
	public function print_widgets_content( $content_data, $block_id, $block, $global ) {

		$this->block_id_master = $block_id;

		// Check to see if the Blox Widgets area has widgets. If not, do nothing.
		if ( ! is_active_sidebar( 'blox-widgets' ) ) {
			return;
		}

		// Empty array of additional CSS classes
		$classes = array();

		// Empty array of blox widget area args
		$args = array();

		$defaults = apply_filters( 'blox_widget_area_defaults', array(
			'before'              => genesis_html5() ? '<aside class="blox-widgets widget-area ' . implode( ' ', apply_filters( 'blox_content_widgets_classes', $classes ) ) . '">' . genesis_sidebar_title( 'blox-widgets' ) : '<div class="widget-area">',
			'after'               => genesis_html5() ? '</aside>' : '</div>',
			'before_sidebar_hook' => 'blox_before_widget_area',
			'after_sidebar_hook'  => 'blox_after_widget_area',
		), 'blox-widgets', $args );

		// Merge our defaults and any "custom" args
		$args = wp_parse_args( $args, $defaults );

		// Opening widget area markup
		echo $args['before'];

		// Before widget area hook
		if ( $args['before_sidebar_hook'] ) {
			do_action( $args['before_sidebar_hook'] );
		}

		if ( ! empty( $content_data['widgets']['selection'] ) ) {

			// We need to outout buffer the widget contents
			ob_start();
			call_user_func( array( $this, 'blox_display_widgets' ), 'blox-widgets', $content_data, $block_id, $block, $global );
			$all_widgets = ob_get_clean();

			echo ( $all_widgets );

		} else {
			_e( 'You forgot to select some widgets to display!', 'blox' );
		}

		// After widget area hook
		if ( $args['after_sidebar_hook'] ) {
			do_action( $args['after_sidebar_hook'] );
		}

		// Closing widget area markup
		echo $args['after'];
	}


	/* Prints the widget content to the frontend
	 *
	 * @since 2.0.0
	 *
	 * @param string $index	      The slug for the widget array
	 * @param array $content_data Array of all content data
	 * @param int $id             The block id
	 * @param array $block        NEED DESCRIPTION
	 * @param string $global      The block state
	 */
	public function blox_display_widgets( $index, $content_data, $block_id, $block, $global ) {

		global $wp_registered_sidebars, $wp_registered_widgets;

		$widget_prefix    = 'blox_' . $block_id . '_';
		$sidebar 		  = $wp_registered_sidebars[$index];
		$sidebars_widgets = wp_get_sidebars_widgets();

		// Bail early if "blox-widgets" does not exist or if we have no widgets in the widget area
		if ( empty( $sidebar ) || empty( $sidebars_widgets[ $index ] ) || ! is_array( $sidebars_widgets[ $index ] ) ) {
			return;
		}

		// Loop through all the widgets in the Blox Widgets sidebar and determine whether to show or not
		foreach ( (array) $sidebars_widgets[$index] as $id ) {

			// If the widget is not in the registered widgets array, bail...
			if ( !isset( $wp_registered_widgets[$id] ) ) continue;

			// If the widget is not in our "selected" widgets array, bail...
			if ( ! in_array( $id, $content_data['widgets']['selection'] ) ) continue;

			// Build our array of widget parameters
			$params = array_merge(
				array( array_merge( $sidebar, array( 'widget_id' => $id, 'widget_name' => $wp_registered_widgets[$id]['name'] ) ) ),
				(array) $wp_registered_widgets[$id]['params']
			);

			// Substitute HTML id (with "blox_[id]_" prefix) and class attributes into before_widget
			$classname_ = '';
			foreach ( (array) $wp_registered_widgets[$id]['classname'] as $cn ) {
				if ( is_string( $cn ) ) {
					$classname_ .= '_' . $cn;
				} else if ( is_object( $cn ) ) {
					$classname_ .= '_' . get_class( $cn );
				}
			}
			$classname_ = ltrim( $classname_, '_' );
			$params[0]['before_widget'] = sprintf( $params[0]['before_widget'], $widget_prefix . $id, $classname_ );


			/**
			 * Filter the parameters passed to a widget's display callback.
			 *
			 * @since 2.0.0
			 *
			 * @param array $params {
			 *     @type array $args  {
			 *         @type string $name          Name of the sidebar the widget is assigned to.
			 *         @type string $id            ID of the sidebar the widget is assigned to.
			 *         @type string $description   The sidebar description.
			 *         @type string $class         CSS class applied to the sidebar container.
			 *         @type string $before_widget HTML markup to prepend to each widget in the sidebar.
			 *         @type string $after_widget  HTML markup to append to each widget in the sidebar.
			 *         @type string $before_title  HTML markup to prepend to the widget title when displayed.
			 *         @type string $after_title   HTML markup to append to the widget title when displayed.
			 *         @type string $widget_id     ID of the widget.
			 *         @type string $widget_name   Name of the widget.
			 *     }
			 *     @type array $widget_args {
			 *         An array of multi-widget arguments.
			 *
			 *         @type int $number Number increment used for multiples of the same widget.
			 *     }
			 * }
			 */
			$params = apply_filters( 'blox_widget_area_params', $params );

			// Make sure the widget callback function exists, then call it
			if ( is_callable( $wp_registered_widgets[$id]['callback'] ) ) {
				call_user_func_array( $wp_registered_widgets[$id]['callback'], $params );
			}
		}
	}


	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 2.0.0
	 *
	 * @return object The class object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Content_Widgets ) ) {
			self::$instance = new Blox_Content_Widgets();
		}

		return self::$instance;
	}
}

// Load the widgets content class.
$blox_content_widgets = Blox_Content_Widgets::get_instance();
