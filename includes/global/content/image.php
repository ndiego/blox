<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the image content section within the content tab and loads in all available options
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Content_Image {

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

		add_filter( 'blox_content_type', array( $this, 'add_image_content' ), 15 );
		add_action( 'blox_get_content_image', array( $this, 'get_image_content' ), 10, 4 );
		add_filter( 'blox_save_content_image', array( $this, 'save_image_content' ), 10, 3 );
		add_action( 'blox_print_content_image', array( $this, 'print_image_content' ), 10, 4 );
    }


	/**
	 * Enable the "image" content option in the plugin
     *
     * @since 1.0.0
     *
     * @param array $content_types  An array of the content types available
     */
	public function add_image_content( $content_types ) {
		$content_types['image'] = __( 'Static Image', 'blox' );
		return $content_types;
	}


	/**
	 * Generates all of the image ralated settings fields
     *
     * @since 1.0.0
     *
     * @param int $id             The block id
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param bool $global        The block state
     */
	public function get_image_content( $id, $name_prefix, $get_prefix, $global ) {

		// Check if current post type supports Featured Images (thumbnail), ignore on global blocks
		// If the block was generated via ajax (local blocks) we need to get the post type using the post_id
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ){

			$post_id   = is_numeric( $_POST['post_id'] ) ? $_POST['post_id'] : '';

			if ( $post_id ) {
				$thumbnail = post_type_supports( get_post_type( $_POST['post_id'] ), 'thumbnail' ) && ! $global ? true : false;
			} else {
				$thumbnail = false;
			}
		} else {
			$thumbnail = post_type_supports( get_post_type(), 'thumbnail' ) && ! $global ? true : false;
		}

		?>

		<table class="form-table blox-content-image blox-hidden" id="<?php echo $global ? $id : '';?>" >
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Image Type', 'blox' ); ?></th>
					<td>
						<select name="<?php echo $name_prefix; ?>[image][image_type]" class="blox-image-type blox-has-help">
							<option value="featured" <?php echo ! empty( $get_prefix['image']['image_type'] ) ? selected( esc_attr( $get_prefix['image']['image_type'] ), 'featured' ) : ''; ?> <?php if ( ! $global && ! $thumbnail ) echo 'disabled'; ?>><?php _e( 'Featured Image', 'blox' ); ?></option>
							<option value="custom" <?php echo ! empty( $get_prefix['image']['image_type'] ) ? selected( esc_attr( $get_prefix['image']['image_type'] ), 'custom' ) : ''; ?>><?php _e( 'Custom Image', 'blox' ); ?></option>

							<?php if ( $global ) { ?>
								<option value="featured-custom" <?php echo ! empty( $get_prefix['image']['image_type'] ) ? selected( esc_attr( $get_prefix['image']['image_type'] ), 'featured-custom' ) : ''; ?>><?php _e( 'Featured or Custom Image', 'blox' ); ?></option>
							<?php } ?>
						</select>
						<span class="blox-help-text-icon">
							<a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
						</span>
						<div class="blox-help-text top">
							<?php
							if ( $global ) {
								echo sprintf( __( 'Choose between the page\'s featured image or select a custom image from the media library. Otherwise, select %1$sFeatured or Custom Image%2$s. This setting will display a custom image in place of the featured image if the page does not support or have a featured image.', 'blox' ), '<strong>', '</strong>' );
							} else {
								if ( $thumbnail ) {
									_e( 'Choose between this page\'s featured image or select a custom image from the media library.', 'blox' );
								} else {
									_e( 'This post type does not support featured images, so select a custom image from the media library instead.', 'blox' );
								}
							}
							?>
						</div>

						<?php if ( $global ) { ?>
						<div class="blox-featured-singular-only blox-image-atts <?php echo $thumbnail ? '' : 'blox-hidden'; ?>">
							<label>
								<input type="checkbox" name="<?php echo $name_prefix; ?>[image][featured_singular_only]" value="1" <?php ! empty( $get_prefix['image']['featured_singular_only'] ) ? checked( esc_attr( $get_prefix['image']['featured_singular_only'] ) ) : ''; ?> />
								<?php _e( 'Check to only display featured images on singular pages', 'blox' ); ?>
								<span class="blox-help-text-icon">
									<a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
								</span>
								<div class="blox-help-text top">
									<?php
										echo sprintf( __( 'Only singular pages can have featured images. Singular pages include posts, pages, and custom post types. When the image type is set to %1$sFeatured Image%2$s or %1$sFeatured or Custom Image%2$s, some unexpected results may occur if the block is placed on a non-singular page such as an archive page. That said, if the block is placed within the loop on archive pages, it can effectively pull featured images if this option is left unchecked. So you the choice is yours. For more information, see the %3$sBlox Documentation%4$s.', 'blox' ), '<strong>', '</strong>', '<a href="https://www.bloxwp.com/documentation/static-image/?utm_source=blox&utm_medium=plugin&utm_content=content-tab-links&utm_campaign=Blox_Plugin_Links" title="' . __( 'Blox Documentation' ) . '" target="_blank">', '</a>' );
									?>
								</div>
							</label>
						</div>
						<?php } ?>

					</td>
				</tr>
				<tr class="blox-content-image-custom <?php if ( $thumbnail ) echo 'blox-hidden'; ?>">
					<th scope="row"><?php _e( 'Custom Image', 'blox' ); ?></th>
					<td>
						<input type="text" class="blox-force-hidden blox-custom-image-id" name="<?php echo $name_prefix; ?>[image][custom][id]" value="<?php echo isset( $get_prefix['image']['custom']['id'] ) ? esc_attr( $get_prefix['image']['custom']['id'] ) : ''; ?>" />
						<input type="text" class="blox-force-hidden blox-custom-image-url" name="<?php echo $name_prefix; ?>[image][custom][url]" value="<?php echo isset( $get_prefix['image']['custom']['url'] ) ? esc_attr( $get_prefix['image']['custom']['url'] ) : ''; ?>" />

						<input type="submit" class="button button-primary" name="blox_upload_button" id="blox_upload_button" value="<?php _e( 'Select an Image', 'blox' );?>" onclick="blox_staticImageUpload.uploader(<?php echo $id; ?>); return false;" /> &nbsp;
						<a class="button blox-remove-image"><?php _e( 'Remove Image', 'blox' ); ?></a><br/>

						<div class="blox-image-preview-wrapper">
							<div class="blox-image-preview-inner">
								<img class="blox-image-default <?php if ( !empty( $get_prefix['image']['custom']['url'] ) ) echo 'hidden'; ?>" src="<?php echo plugins_url( 'assets/images/default.png', $this->base->file ); ?>" />
								<img class="blox-image-preview <?php if ( empty( $get_prefix['image']['custom']['url'] ) ) echo 'hidden'; ?>" src="<?php echo isset( $get_prefix['image']['custom']['url'] ) ? esc_attr( $get_prefix['image']['custom']['url'] ) : ''; ?>" />
							</div>
						</div>

						<div class="blox-image-atts <?php if ( empty( $get_prefix['image']['custom']['url'] ) ) echo 'hidden'; ?>">
							<label class="blox-subtitle">
								<span><?php _e( 'Title', 'blox' ); ?></span>
								<input type="text" class="blox-custom-image-title" name="<?php echo $name_prefix; ?>[image][custom][title]" value="<?php echo isset( $get_prefix['image']['custom']['title'] ) ? esc_attr( $get_prefix['image']['custom']['title'] ) : ''; ?>" />
							</label>
							<label class="blox-subtitle">
								<span><?php _e( 'Alt', 'blox' ); ?></span>
								<input type="text" class="blox-custom-image-alt" name="<?php echo $name_prefix; ?>[image][custom][alt]" value="<?php echo isset( $get_prefix['image']['custom']['alt'] ) ? esc_attr( $get_prefix['image']['custom']['alt'] ) : ''; ?>" />
							</label>
							<label class="blox-subtitle">
								<span><?php _e( 'Class', 'blox' ); ?></span>
								<input type="text" class="blox-custom-image-css" name="<?php echo $name_prefix; ?>[image][custom][css]" value="<?php echo isset( $get_prefix['image']['custom']['css'] ) ? esc_attr( $get_prefix['image']['custom']['css'] ) : ''; ?>" placeholder="<?php _e( 'e.g. class-one class-two', 'blox' );?>"/>
								<div class="blox-description">
									<?php  _e( 'Enter a space separated list of custom CSS classes to add to the image.', 'blox' ); ?>
								</div>
							</label>
						</div>

					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Image Size', 'blox' ); ?></th>
					<td>
						<select class="genesis-image-size-selector blox-has-help" name="<?php echo $name_prefix; ?>[image][size][size_type]">
							<?php foreach ( (array) $this->get_image_sizes() as $i => $size ) {

								// Remove the new Custom option added in WP 4.4 for now. Could cause confusion...
								if ( $size['value'] != 'custom' ) {
								?>
									<option value="<?php echo $size['value']; ?>" <?php ! empty( $get_prefix['image']['size']['size_type'] ) ? selected( $size['value'], esc_attr( $get_prefix['image']['size']['size_type'] ) ) : '';?>><?php echo $size['name']; ?></option>
								<?php
								}
							} ?>
						</select>
						<span class="blox-help-text-icon">
							<a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
						</span>
						<div class="blox-help-text top">
							<?php echo sprintf( __( 'If you are using a %1$sCustom Image%2$s, note that the size selection will not be reflected in the image preview above.', 'blox' ), '<strong>', '</strong>' ); ?>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Image Link', 'blox' ); ?></th>
					<td>
						<label class="blox-image-link-enable">
							<input type="checkbox" name="<?php echo $name_prefix; ?>[image][link][enable]" value="1" <?php ! empty( $get_prefix['image']['link']['enable'] ) ? checked( esc_attr( $get_prefix['image']['link']['enable'] ) ) : ''; ?> />
							<?php _e( 'Check to enable', 'blox' ); ?>
						</label>
						<div class="blox-image-link">
							<label class="blox-subtitle">
								<span><?php _e( 'URL', 'blox' ); ?></span>
								<input type="text" name="<?php echo $name_prefix; ?>[image][link][url]" value="<?php echo ! empty( $get_prefix['image']['link']['url'] ) ? esc_attr( $get_prefix['image']['link']['url'] ) : 'http://'; ?>" />
							</label>
							<label class="blox-subtitle">
								<span><?php _e( 'Title', 'blox' ); ?></span>
								<input type="text" name="<?php echo $name_prefix; ?>[image][link][title]" value="<?php echo ! empty( $get_prefix['image']['link']['title'] ) ? esc_attr( $get_prefix['image']['link']['title'] ) : ''; ?>" />
							</label>
							<label>
								<input type="checkbox" name="<?php echo $name_prefix; ?>[image][link][target]" value="1" <?php ! empty( $get_prefix['image']['link']['target'] ) ? checked( esc_attr( $get_prefix['image']['link']['target'] ) ) : ''; ?> />
								<?php _e( 'Open link in new window/tab', 'blox' ); ?>
							</label>
							<label class="blox-subtitle">
								<span><?php _e( 'Rel', 'blox' ); ?></span>
								<input type="text" name="<?php echo $name_prefix; ?>[image][link][rel]" value="<?php echo ! empty( $get_prefix['image']['link']['rel'] ) ? esc_attr( $get_prefix['image']['link']['rel'] ) : ''; ?>" />
							</label>
							<label class="blox-subtitle">
								<span><?php _e( 'Class', 'blox' ); ?></span>
								<input type="text" name="<?php echo $name_prefix; ?>[image][link][css]" value="<?php echo ! empty( $get_prefix['image']['link']['css'] ) ? esc_attr( $get_prefix['image']['link']['css'] ) : ''; ?>" placeholder="<?php _e( 'e.g. class-one class-two', 'blox' );?>"/>
								<div class="blox-description">
									<?php  _e( 'Enter a space separated list of custom CSS classes to add to the image link.', 'blox' ); ?>
								</div>
							</label>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Image Caption', 'blox' ); ?></th>
					<td>
						<textarea class="blox-textarea-code" name="<?php echo $name_prefix; ?>[image][caption]" rows="2" ><?php echo ! empty( $get_prefix['image']['caption'] ) ? esc_attr( $get_prefix['image']['caption'] ) : ''; ?></textarea>
						<div class="blox-description">
							<?php _e( 'Only basic HTML and shortcodes are accepted. Leave blank for no caption.', 'blox' ); ?>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Set As Background', 'blox' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo $name_prefix; ?>[image][background]" value="1" <?php ! empty( $get_prefix['image']['background'] ) ? checked( esc_attr( $get_prefix['image']['background'] ) ) : ''; ?> />
							<?php _e( 'Check to enable', 'blox' ); ?>
						</label>
						<span class="blox-help-text-icon">
							<a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
						</span>
						<div class="blox-help-text top">
							<?php echo sprintf( __( 'Set image as a background image. When this setting is enabled, the CSS class %1$sblox-image-background%2$s is added to the content block. Additional custom CSS may be required to attain your desired effect.', 'blox' ), '<code>', '</code>' ); ?>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<?php
	}


	/**
	 * Saves all of the image ralated settings
     *
     * @since 1.0.0
	 *
	 * @param string $name_prefix The prefix for saving each setting (this brings ...['image'] with it)
	 * @param int $id             The block id
	 * @param bool $global        The block state
	 */
	public function save_image_content( $name_prefix, $id, $global ) {

		$settings = array();

		$settings['image_type'] 				= esc_attr( $name_prefix['image_type'] );
		$settings['featured_singular_only'] 	= isset( $name_prefix['featured_singular_only'] ) ? 1 : 0;
		$settings['custom']['id'] 				= trim( strip_tags( $name_prefix['custom']['id'] ) );
		$settings['custom']['url']				= esc_url( $name_prefix['custom']['url'] );
		$settings['custom']['title']			= trim( strip_tags( $name_prefix['custom']['title'] ) );
		$settings['custom']['alt']				= trim( strip_tags( $name_prefix['custom']['alt'] ) );
		$settings['custom']['css']				= trim( strip_tags( $name_prefix['custom']['css'] ) );
		$settings['size']['size_type']			= esc_attr( $name_prefix['size']['size_type'] );
		$settings['link']['enable']				= isset( $name_prefix['link']['enable'] ) ? 1 : 0;
		$settings['link']['url']				= $name_prefix['link']['url'] == 'http://' ? '' : esc_url( $name_prefix['link']['url'] );
		$settings['link']['title']				= trim( strip_tags( $name_prefix['link']['title'] ) );
		$settings['link']['target']				= isset( $name_prefix['link']['target'] ) ? 1 : 0;
		$settings['link']['rel']				= trim( strip_tags( $name_prefix['link']['rel'] ) );
		$settings['link']['css']				= trim( strip_tags( $name_prefix['link']['css'] ) );
		$settings['caption']					= wp_kses_post( $name_prefix['caption'] );
		$settings['background']					= isset( $name_prefix['background'] ) ? 1 : 0;

		return $settings;
	}


	/**
	 * Prints all of the image content to the frontend
     *
     * @since 1.0.0
     *
     * @param array $content_data  All the content data for the block
     * @param int $id              The block id
     * @param array $block         All the block data
     * @param string $global       The block state, either "global" or "local"
     */
	public function print_image_content( $content_data, $id, $block, $global ) {

		// If we have chosen to only show featured images on singular pages, run the test, otherwise try and see if there is a thumbnail somewhere on the page
		if ( ! empty( $content_data['image']['featured_singular_only'] ) && $content_data['image']['featured_singular_only'] == 1 && ! is_singular() ) {

			// Disable for non-singular pages because has_post_thumbnail can return true on archive pages, search pages, etc.
			$thumbnail = false;
		} else {
			$thumbnail = has_post_thumbnail( get_the_ID() );
		}

		// Aquire some misc settings
		$content_type = $content_data['image']['image_type'] != '' ? $content_data['image']['image_type'] : null;
		$background   = ! empty( $content_data['image']['background'] ) ? true : false;
		$caption      = ! empty( $content_data['image']['caption'] ) ? ( '<div class="blox-caption-container"><div class="blox-caption-wrap">' . do_shortcode( wp_kses_post( $content_data['image']['caption'] ) ) . '</div></div>' ) : '';

		// Get our image
		$image = '';

		if ( $content_type == 'custom' || ( $thumbnail == false && $content_type == 'featured-custom' ) ) {

			if ( $background ) {
				$image =  esc_url( $content_data['image']['custom']['url'] );
			} else {
				if ( ! empty( $content_data['image']['custom']['id'] ) ) {
					$image = wp_get_attachment_image( $content_data['image']['custom']['id'], $content_data['image']['size']['size_type'], false, array( 'class' => $content_data['image']['custom']['css'], 'title' => $content_data['image']['custom']['title'], 'alt' => $content_data['image']['custom']['alt'] ) );
				}
			}

		} else if ( $thumbnail == true && ( $content_type == 'featured' || $content_type == 'featured-custom' ) ) {

			if ( $background ) {
				$thumb_id = get_post_thumbnail_id();
				$thumb_url_array = wp_get_attachment_image_src( $thumb_id, 'full', false );
				$image = $thumb_url_array[0];
			} else {
				$image = genesis_get_image( array(
					'format'  => 'html',
					'size'    =>  isset( $content_data['image']['size']['size_type'] ) ? $content_data['image']['size']['size_type'] : 'full',
					'context' => '',
					'attr'    => '',
				) );
			}

		}

		// Get our image link if enabled
		if ( ! empty( $content_data['image']['link']['url'] ) && $content_data['image']['link']['enable'] ) {

			$target = ! empty( $content_data['image']['link']['target'] ) ? '_blank' : '_self';

			$link_start = '<a href="' . $content_data['image']['link']['url'] . '" target="' . $target . '" title="' . $content_data['image']['link']['title'] . '" class="' . $content_data['image']['link']['css'] . '" rel="' . $content_data['image']['link']['rel'] . '">';
			$link_end   = '</a>';
		} else {
			$link_start = '';
			$link_end   = '';
		}

		// Array of additional CSS classes
		$classes = array();
		?>

		<div class="blox-image-container <?php echo ! empty( $content_data['image']['image_type'] ) ? $content_data['image']['image_type'] : '';?> <?php echo implode( ' ', apply_filters( 'blox_content_image_classes', $classes ) ); ?>">
			<?php
				if ( $image ) {
					if ( empty( $content_data['image']['background'] ) ) {
						?>
						<div class="blox-image-wrap">
							<?php echo $link_start . $image . $link_end . $caption; ?>
						</div>
						<?php
					} else {
						?>
						<div class="blox-image-wrap blox-image-background" style="background-image: url(<?php echo $image; ?>)">
							<?php echo $link_start . $link_end . $caption; ?>
						</div>
						<?php
					}
				}
			?>
		</div>

		<?php
	}


	/**
     * Helper method for retrieving image sizes.
     *
     * @since 1.0.0
     *
     * @return array Array of image size data.
     */
    public function get_image_sizes() {

        $instance = Blox_Common::get_instance();
        return $instance->get_image_sizes();
    }


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Content_Image ) ) {
            self::$instance = new Blox_Content_Image();
        }

        return self::$instance;
    }
}

// Load the image content class.
$blox_content_image = Blox_Content_Image::get_instance();
