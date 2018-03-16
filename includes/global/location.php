<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the location tab (only on global blocks) and loads in all the available options
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Location {

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

		// Setup location settings
		add_filter( 'blox_metabox_tabs', array( $this, 'add_location_tab' ), 6 );
		add_action( 'blox_get_metabox_tab_location', array( $this, 'get_metabox_tab_location' ), 10, 4 );
		add_filter( 'blox_save_metabox_tab_location', array( $this, 'save_metabox_tab_location' ), 10, 3 );

		// Add the admin column data for global blocks
		add_filter( 'blox_admin_column_titles', array( $this, 'admin_column_title' ), 3, 1 );
		add_action( 'blox_admin_column_data_location', array( $this, 'admin_column_data' ), 10, 2 );

		// Make admin column sortable
		add_filter( 'manage_edit-blox_sortable_columns', array( $this, 'admin_column_sortable' ), 5 );
        add_filter( 'request', array( $this, 'admin_column_orderby' ) );

		// Run location test on the frontend.
		add_filter( 'blox_display_test', array( $this, 'run_location_display_test' ), 5, 5 );
    }


	/**
	 * Add the Location tab
     *
     * @since 1.0.0
     *
     * @param array $tab  An array of the tabs available
     * @return array $tab The updated tabs array
     */
	public function add_location_tab( $tabs ) {

		$tabs['location'] = array(
			'title' => __( 'Location', 'blox' ),
			'scope' => 'global' // all, local, or global
		);

		return $tabs;
	}


    /**
     * Creates the location settings fields
     *
     * @since 1.0.0
     *
     * @param array $data         An array of all block data
     * @param string $name_id 	  The prefix for saving each setting
     * @param string $get_id  	  The prefix for retrieving each setting
     * @param bool $global	      The block state
     */
	public function get_metabox_tab_location( $data = null, $name_id, $get_id, $global ) {

		if ( $global ) {
			// Indicates where the content settings are saved
			$name_prefix = "blox_content_blocks_data[location]";
			$get_prefix  = ! empty( $data['location'] ) ? $data['location'] : null;

		} else {
			// The location tab should not be displayed on local blocks
			return;
		}

		// Get the content for the content tab
		$this->location_settings( $name_id, $name_prefix, $get_prefix, $global );
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
    public function location_settings( $id, $name_prefix, $get_prefix, $global ) {

    	$location_data = null;

		// Get all custom post types in an array by name
		$custom_post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'names', 'and' );
		$builtin_post_types = get_post_types( array( 'public' => true, '_builtin' => true ), 'names', 'and' );

		$posts_with_archives = get_post_types( array( 'public' => true, 'has_archive' => true ), 'names', 'and' );

        ?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Block Location' ); ?></th>
					<td>
						<select id="blox_location_type" name="<?php echo $name_prefix; ?>[location_type]" class="blox-has-help">
							<option value="all" <?php echo ! empty( $get_prefix['location_type'] ) ? selected( esc_attr( $get_prefix['location_type'] ), 'all' ) : 'selected'; ?>><?php _e( 'All Pages', 'blox' ); ?></option>
							<option value="show_selected" <?php echo ! empty( $get_prefix['location_type'] ) ? selected( esc_attr( $get_prefix['location_type'] ), 'show_selected' ) : ''; ?>><?php _e( 'Show on Selected', 'blox' ); ?></option>
							<option value="hide_selected" <?php echo ! empty( $get_prefix['location_type'] ) ? selected( esc_attr( $get_prefix['location_type'] ), 'hide_selected' ) : ''; ?>><?php _e( 'Hide on Selected', 'blox' ); ?></option>
						</select>
						<span class="blox-help-text-icon">
							<a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
						</span>
						<div class="blox-help-text top">
							<?php echo sprintf( __( 'Choose "Show on Selected" or "Hide on Selected" to view additional options. "Show on Selected" will only display the content block on pages that match the citeria selected below. "Hide on Selected" does to opposite. Selecting this option will display the block on every page %1$sexcept%2$s for those that match the citeria selected below.', 'blox' ), '<strong>', '</strong>', '<a href="http://www.bloxwp.com/documentation" title="Blox Documentation">', '</a>' ); ?>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<table class="form-table blox-location-container <?php if ( empty( $get_prefix['location_type'] ) ||  $get_prefix['location_type'] == 'all' ) echo ('blox-hidden'); ?>">
			<tbody>
				<tr id="blox_location_selection">
					<th scope="row"></th>
					<td>
						<div class="blox-test-description blox-description">
							<?php
							if ( ! empty( $get_prefix['location_type'] ) && $get_prefix['location_type'] == 'show_selected' ) {
								echo sprintf( __( 'Choose the pages you would like the content block to be %1$svisible%2$s on.', 'blox' ), '<strong>', '</strong>' );
							} else if ( ! empty( $get_prefix['location_type'] ) && $get_prefix['location_type'] == 'hide_selected' ) {
								echo sprintf( __( 'Choose the pages you would like the content block to be %1$shidden%2$s on.', 'blox' ), '<strong>', '</strong>' );
							}
							?>
						</div>
						<div class="blox-location-selection">
							<div class="blox-checkbox-container">
								<ul class="blox-columns">
									<?php foreach ( $this->page_types() as $key => $label ) { ?>
										<li>
											<label>
												<input type="checkbox" name="<?php echo $name_prefix; ?>[selection][]" value="<?php echo $key; ?>" <?php echo ! empty( $get_prefix['selection'] ) && in_array( $key, $get_prefix['selection'] ) ? 'checked="checked"' : ''; ?> /> <?php echo $label ?>
											</label>
										</li>
									<?php } ?>
								</ul>
							</div>
							<div class="blox-checkbox-select-tools">
								<a class="blox-checkbox-select-all" href="#"><?php _e( 'Select All' ); ?></a> <a class="blox-checkbox-select-none" href="#"><?php _e( 'Unselect All' ); ?></a>
							</div>
						</div>
    				</td>
    			</tr>

    			<tr id="blox_location_singles" class="<?php if ( empty( $get_prefix['selection'] ) ||  ! in_array( 'singles', $get_prefix['selection'] ) ) echo ( 'blox-hidden' ); ?>">
					<th scope="row"><?php _e( 'Single Pages' ); ?></th>
					<td>
						<select class="blox-location-select_type" name="<?php echo $name_prefix; ?>[singles][select_type]" >
							<option value="all" <?php echo ! empty( $get_prefix['singles']['select_type'] ) ? selected( esc_attr( $get_prefix['singles']['select_type'] ), 'all' ) : 'selected'; ?>><?php _e( 'All Single Pages', 'blox' ); ?></option>
							<option value="selected" <?php echo ! empty( $get_prefix['singles']['select_type'] ) ? selected( esc_attr( $get_prefix['singles']['select_type'] ), 'selected' ) : ''; ?>><?php _e( 'Selected Single Pages', 'blox' ); ?></option>
						</select>

						<div class="blox-location-selected-container <?php if ( empty( $get_prefix['singles']['select_type'] ) ||  $get_prefix['singles']['select_type'] != 'selected' ) echo ( 'blox-hidden' ); ?>">

							<div class="blox-location-singles-selection">
								<div class="blox-description">
									<?php _e( 'Select a post type below to view additional options.', 'blox' ); ?>
								</div>
								<div class="blox-checkbox-container">
									<ul class="blox-columns">
									<?php if ( ! empty( $builtin_post_types ) ) { ?>
											<?php foreach ( $builtin_post_types as $builtin_post_type ) {
												// Get the full post object
												$post_object = get_post_type_object( $builtin_post_type );
												?>
												<li>
													<label>
														<input type="checkbox" name="<?php echo $name_prefix; ?>[singles][selection][]" value="<?php echo $post_object->name; ?>" <?php echo ! empty( $get_prefix['singles']['selection'] ) && in_array( $post_object->name, $get_prefix['singles']['selection'] ) ? 'checked="checked"' : ''; ?> /> <?php echo $post_object->labels->name; ?>
													</label>
												</li>
												<?php
											} ?>
									<?php } ?>

									<?php if ( ! empty( $custom_post_types ) ) { ?>
											<?php foreach ( $custom_post_types as $custom_post_type ) {
												// Get the full post object
												$post_object = get_post_type_object( $custom_post_type );
												?>
												<li>
													<label>
														<input type="checkbox" name="<?php echo $name_prefix; ?>[singles][selection][]" value="<?php echo $post_object->name; ?>" <?php echo ! empty( $get_prefix['singles']['selection'] ) && in_array( $post_object->name, $get_prefix['singles']['selection'] ) ? 'checked="checked"' : ''; ?> /> <?php echo $post_object->labels->name . ' <span class="blox-post-status">(custom)</span>'; ?>
													</label>
												</li>
												<?php
											} ?>
									<?php } ?>
									</ul>
								</div>
								<div class="blox-checkbox-select-tools">
									<a class="blox-checkbox-select-all" href="#"><?php _e( 'Select All' ); ?></a> <a class="blox-checkbox-select-none" href="#"><?php _e( 'Unselect All' ); ?></a>
								</div>
							</div>
							<?php

							if ( ! empty( $builtin_post_types ) ) {
								foreach ( $builtin_post_types as $builtin_post_type ) {
									// Get the full post object
									$post_object = get_post_type_object( $builtin_post_type );
									$this->blox_location_singles( $name_prefix, $get_prefix, $post_object );
								}
							}

							if ( ! empty( $custom_post_types ) ) {
								foreach ( $custom_post_types as $custom_post_type ) {
									// Get the full post object
									$post_object = get_post_type_object( $custom_post_type );
									$this->blox_location_singles(  $name_prefix, $get_prefix, $post_object );
								}
							}
							?>

						</div> <!-- end .blox-location-singles-wrapper -->

					</td>
				</tr>



				<tr id="blox_location_archive" class="<?php if ( empty( $get_prefix['selection'] ) ||  ! in_array( 'archive', $get_prefix['selection'] ) ) echo ('blox-hidden'); ?>">

					<?php
					// Get all public taxonomies
					$taxonomies = get_taxonomies( array( 'public' => true ), 'objects', 'and' );

					// Get all users with author privileges or higher
					$author_ids = get_users( array(
						'orderby' => 'names',
						'order'   => 'ASC',
						'fields'  => 'ids',
						'who'     => 'authors'
					) );
					?>

					<th scope="row"><?php _e( 'Archive Pages' ); ?></th>
					<td>
						<select class="blox-location-select_type" name="<?php echo $name_prefix; ?>[archive][select_type]" >
							<option value="all" <?php echo ! empty( $get_prefix['archive']['select_type'] ) ? selected( esc_attr( $get_prefix['archive']['select_type'] ), 'all' ) : 'selected'; ?>><?php _e( 'All Archive Pages', 'blox' ); ?></option>
							<option value="selected" <?php echo ! empty( $get_prefix['archive']['select_type'] ) ? selected( esc_attr( $get_prefix['archive']['select_type'] ), 'selected' ) : ''; ?>><?php _e( 'Selected Archive Types', 'blox' ); ?></option>
						</select>

						<div class="blox-location-selected-container <?php if ( empty( $get_prefix['archive']['select_type'] ) ||  $get_prefix['archive']['select_type'] != 'selected' ) echo ('blox-hidden'); ?>">

							<div class="blox-location-archive-selection">
								<div class="blox-description">
									<?php _e( 'Select an archive type below to view additional options.', 'blox' ); ?>
								</div>
								<div class="blox-checkbox-container">
									<ul class="blox-columns">
										<li><label><input type="checkbox" name="<?php echo $name_prefix; ?>[archive][selection][]" value="posttypes" <?php echo ! empty( $get_prefix['archive']['selection'] ) && in_array( 'posttypes', $get_prefix['archive']['selection'] ) ? 'checked="checked"' : ''; ?> /> <?php _e( 'Post Type', 'blox' ); ?></label></li>
										<li><label><input type="checkbox" name="<?php echo $name_prefix; ?>[archive][selection][]" value="authors" <?php echo ! empty( $get_prefix['archive']['selection'] ) && in_array( 'authors', $get_prefix['archive']['selection'] ) ? 'checked="checked"' : ''; ?> /> <?php _e( 'Author', 'blox' ); ?></label></li>
										<li><label><input type="checkbox" name="<?php echo $name_prefix; ?>[archive][selection][]" value="datetime" <?php echo ! empty( $get_prefix['archive']['selection'] ) && in_array( 'datetime', $get_prefix['archive']['selection'] ) ? 'checked="checked"' : ''; ?> /> <?php _e( 'Date & Time', 'blox' ); ?></label></li>
										<?php
										if ( $taxonomies ) {
										  	foreach ( $taxonomies as $taxonomy ) {
										  		if ( $taxonomy->name == 'post_format' && ! current_theme_supports( 'post-formats' ) ) {
													// Do nothing since the current theme does not support post formats, otherwise showing this option could be confusing
													// Info on post formats: https://codex.wordpress.org/Post_Formats
												} else {
													$assigned_postypes = implode( ', ', $taxonomy->object_type );
													?>
													<li><label><input type="checkbox" name="<?php echo $name_prefix; ?>[archive][selection][]" value="<?php echo $taxonomy->name;?>" <?php echo ! empty( $get_prefix['archive']['selection'] ) && in_array( $taxonomy->name, $get_prefix['archive']['selection'] ) ? 'checked="checked"' : ''; ?> /> <?php echo $taxonomy->labels->name ?> <span class="blox-post-status">(<?php echo $assigned_postypes;?>)</span></label></li>
													<?php
												}
											}
										}
										?>
									</ul>
								</div>
								<div class="blox-checkbox-select-tools">
									<a class="blox-checkbox-select-all" href="#"><?php _e( 'Select All' ); ?></a> <a class="blox-checkbox-select-none" href="#"><?php _e( 'Unselect All' ); ?></a>
								</div>
							</div>


							<div class="blox-location-archive-posttypes blox-subcontainer <?php if ( empty( $get_prefix['archive']['selection'] ) || ! in_array( 'posttypes', $get_prefix['archive']['selection'] ) ) echo ('blox-hidden'); ?>">
								<span class="blox-title"><?php _e( 'Post Type', 'blox' );?></span>

								<select class="blox-location-select_type" name="<?php echo $name_prefix; ?>[archive][posttypes][select_type]">
									<option value="all" title="<?php _e( 'Show or hide on all post type archives.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['archive']['posttypes']['select_type'] ) ? selected( esc_attr( $get_prefix['archive']['posttypes']['select_type'] ), 'all' ) : 'selected'; ?>><?php _e( 'All Post Type Archives', 'blox' ); ?></option>
									<option value="selected" title="<?php _e( 'Show or hide on selected post type archives.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['archive']['posttypes']['select_type'] ) ? selected( esc_attr( $get_prefix['archive']['posttypes']['select_type'] ), 'selected' ) : ''; ?>><?php _e( 'Selected Post Type Archives', 'blox' ); ?></option>
								</select>
								<div class="blox-description">
									<?php _e( 'Use this option to show/hide the content block on archive pages of post types. Note: If you are looking to target the "archive" page for Posts, use the Blog Page option in the main Block Location settings panel above.', 'blox' ); ?>
								</div>
								<div class="blox-location-selected-container <?php if ( empty( $get_prefix['archive']['posttypes']['select_type'] ) || $get_prefix['archive']['posttypes']['select_type'] == 'all' ) echo ( 'blox-hidden' ); ?>">

									<?php
									if ( $posts_with_archives ) { ?>
										<div class="blox-checkbox-container">
											<ul class="blox-columns">

												<?php foreach ( $posts_with_archives as $posts_with_archive ) {
													$post_object = get_post_type_object( $posts_with_archive ); ?>
												<li>
													<label>
														<input type="checkbox" name="<?php echo $name_prefix; ?>[archive][posttypes][selection][]" value="<?php echo $post_object->name; ?>"  <?php echo ! empty( $get_prefix['archive']['posttypes']['selection'] ) && in_array( $post_object->name, $get_prefix['archive']['posttypes']['selection'] ) ? 'checked="checked"' : ''; ?> />
														<?php echo $post_object->labels->name; ?>
													</label>
												</li>
												<?php } ?>

											</ul>
										</div>
										<div class="blox-checkbox-select-tools">
											<a class="blox-checkbox-select-all" href="#"><?php _e( 'Select All' ); ?></a> <a class="blox-checkbox-select-none" href="#"><?php _e( 'Unselect All' ); ?></a>
										</div>
									<?php } else { ?>
										<div class="blox-alert">
											<?php echo __( 'There does not appear to be any post types that have archives.', 'blox' ); ?>
										</div>
									<?php } ?>

								</div>
							</div>


							<div class="blox-location-archive-authors blox-subcontainer <?php if ( empty( $get_prefix['archive']['selection'] ) || ! in_array( 'authors', $get_prefix['archive']['selection'] ) ) echo ('blox-hidden'); ?>">
								<span class="blox-title"><?php _e( 'Author', 'blox' );?></span>

								<select class="blox-location-select_type" name="<?php echo $name_prefix; ?>[archive][authors][select_type]">
									<option value="all" title="<?php _e( 'Show or hide on all author archives.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['archive']['authors']['select_type'] ) ? selected( esc_attr( $get_prefix['archive']['authors']['select_type'] ), 'all' ) : 'selected'; ?>><?php _e( 'All Author Archives', 'blox' ); ?></option>
									<option value="selected" title="<?php _e( 'Show or hide on selected author archives.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['archive']['authors']['select_type'] ) ? selected( esc_attr( $get_prefix['archive']['authors']['select_type'] ), 'selected' ) : ''; ?>><?php _e( 'Selected Author Archives', 'blox' ); ?></option>
								</select>

								<div class="blox-location-selected-container <?php if ( empty( $get_prefix['archive']['authors']['select_type'] ) || $get_prefix['archive']['authors']['select_type'] == 'all' ) echo ( 'blox-hidden' ); ?>">

									<?php
									if ( $author_ids ) { ?>
										<div class="blox-checkbox-container">
											<ul class="blox-columns">

												<?php foreach ( $author_ids as $author_id ) {
													$author_name = get_the_author_meta( 'display_name', $author_id ); ?>
												<li>
													<label>
														<input type="checkbox" name="<?php echo $name_prefix; ?>[archive][authors][selection][]" value="<?php echo $author_id; ?>"  <?php echo ! empty( $get_prefix['archive']['authors']['selection'] ) && in_array( $author_id, $get_prefix['archive']['authors']['selection'] ) ? 'checked="checked"' : ''; ?> />
														<?php echo $author_name; ?>
													</label>
												</li>
												<?php } ?>

											</ul>
										</div>
										<div class="blox-checkbox-select-tools">
											<a class="blox-checkbox-select-all" href="#"><?php _e( 'Select All' ); ?></a> <a class="blox-checkbox-select-none" href="#"><?php _e( 'Unselect All' ); ?></a>
										</div>
									<?php } else { ?>
										<div class="blox-alert">
											<?php echo __( 'There does not appear to be any authors on this website.', 'blox' ); ?>
										</div>
									<?php } ?>

								</div>
							</div>


							<?php
							if ( $taxonomies ) {
								foreach ( $taxonomies as $taxonomy ) {
									if ( $taxonomy->name == 'post_format' && ! current_theme_supports( 'post-formats' ) ) {
										// Do nothing since the current theme does not support post formats, otherwise showing this option could be confusing
									} else {
										$assigned_postypes = implode( ', ', $taxonomy->object_type );
										?>

										<div class="blox-location-archive-<?php echo $taxonomy->name;?>  blox-subcontainer <?php if ( empty( $get_prefix['archive']['selection'] ) || ! in_array( $taxonomy->name, $get_prefix['archive']['selection'] ) ) echo ('blox-hidden'); ?>">
											<span class="blox-title"><?php echo $taxonomy->labels->name ?> <span class="blox-post-status">(<?php echo $assigned_postypes;?>)</span></span>

											<select class="blox-location-select_type" name="<?php echo $name_prefix; ?>[archive][<?php echo $taxonomy->name; ?>][select_type]">
												<option value="all" title="<?php _e( 'Show or hide on all taxonomy archives.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['archive'][$taxonomy->name]['select_type'] ) ? selected( esc_attr( $get_prefix['archive'][$taxonomy->name]['select_type'] ), 'all' ) : 'selected'; ?>><?php echo sprintf( __( 'All %1$s Archives', 'blox' ), ucfirst( $taxonomy->labels->singular_name ) ); ?></option>
												<option value="selected" title="<?php _e( 'Show or hide on selected taxonomy archives.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['archive'][$taxonomy->name]['select_type'] ) ? selected( esc_attr( $get_prefix['archive'][$taxonomy->name]['select_type'] ), 'selected' ) : ''; ?>><?php echo sprintf( __( 'Selected %1$s Archives', 'blox' ), ucfirst( $taxonomy->labels->singular_name ) ); ?></option>
											</select>

											<div class="blox-location-selected-container <?php if ( empty( $get_prefix['archive'][$taxonomy->name]['select_type'] ) || $get_prefix['archive'][$taxonomy->name]['select_type'] == 'all' ) echo ( 'blox-hidden' ); ?>">
												<?php
												$taxonomy_terms = get_terms( $taxonomy->name, array( 'orderby' => 'name', 'order' => 'ASC' ) );
												if ( $taxonomy_terms ) { ?>
													<div class="blox-checkbox-container">
														<ul class="blox-columns">
															<?php foreach ( $taxonomy_terms as $term ) { ?>
															<li>
																<label>
																	<input type="checkbox" name="<?php echo $name_prefix; ?>[archive][<?php echo $taxonomy->name; ?>][selection][]" value="<?php echo $term->term_id; ?>"  <?php echo ! empty( $get_prefix['archive'][$taxonomy->name]['selection'] ) && in_array( $term->term_id, $get_prefix['archive'][$taxonomy->name]['selection'] ) ? 'checked="checked"' : ''; ?> />
																	<?php echo apply_filters( 'the_title', $term->name, $term->term_id ); ?>
																</label>
															</li>
															<?php } ?>
														</ul>
													</div>
													<div class="blox-checkbox-select-tools">
														<a class="blox-checkbox-select-all" href="#"><?php _e( 'Select All' ); ?></a> <a class="blox-checkbox-select-none" href="#"><?php _e( 'Unselect All' ); ?></a>
													</div>
												<?php } else { ?>
													<div class="blox-alert">
														<?php echo sprintf( __( 'There does not appear to be any %1$s yet.', 'blox' ), $taxonomy->labels->name ); ?>
													</div>
												<?php } ?>
											</div>
										</div>

										<?php
									}
								}
							}
							?>
							</div>
						</div>
					</td>
				</tr>

				<!-- <tr id="blox_location_manual_show" class="<?php if ( empty( $get_prefix['location_type'] ) || $get_prefix['location_type'] != 'hide_selected' ) echo ('blox-hidden'); ?>"">
					<th scope="row"><?php _e( 'Manual Override (Show)' ); ?></th>
					<td>
						<input type="text" name="<?php echo $name_prefix; ?>[manual_override][manual_show_ids]" value="<?php echo ! empty( $get_prefix['manual_override']['manual_show_ids'] ) ? esc_attr( implode( ', ', $get_prefix['manual_override']['manual_show_ids'] ) )  : ''; ?>" class="blox-half-text" placeholder="215, 34, 79"/>
						<div class="blox-description">
							<?php _e( 'Enter a list of comma separated IDs (page, post, custom post type) to show.', 'blox' ); ?>
						</div>
					</td>
				</tr>
				<tr id="blox_location_manual_hide" class="<?php if ( empty( $get_prefix['location_type'] ) || $get_prefix['location_type'] != 'show_selected' ) echo ('blox-hidden'); ?>">
					<th scope="row"><?php _e( 'Manual Override (Hide)' ); ?></th>
					<td>
						<input type="text" name="<?php echo $name_prefix; ?>[manual_override][manual_hide_ids]" value="<?php echo ! empty( $get_prefix['manual_override']['manual_hide_ids'] ) ? esc_attr( implode( ', ', $get_prefix['manual_override']['manual_hide_ids'] ) )  : ''; ?>" class="blox-half-text" placeholder="215, 34, 79"/>
						<div class="blox-description">
							<?php _e( 'Enter a list of comma separated IDs (page, post, custom post type) to hide.', 'blox' ); ?>
						</div>
					</td>
				</tr> -->


			</tbody>
		</table>

        <?php

    }


	/**
     * Gets all single page location settings
     *
     * @since 1.0.0
     *
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param obj $post_object	  Contains all info about given post type
     */
    public function blox_location_singles( $name_prefix, $get_prefix, $post_object ) {

		$post_type = $post_object->name;
		$post_name = $post_object->labels->name;
		$post_name_singular = $post_object->labels->singular_name;

    	?>
		<div class="<?php if ( empty( $get_prefix['singles']['selection'] ) || ! in_array( $post_type, $get_prefix['singles']['selection'] ) ) echo ('blox-hidden'); ?> blox-subcontainer blox-location-singles-<?php echo $post_type; ?>">
			<span class="blox-title"><?php echo $post_name; ?></span>

			<select name="<?php echo $name_prefix; ?>[singles][<?php echo $post_type; ?>][select_type]" class="blox-singles-select_type">
				<option value="all" title="<?php _e( 'Show or hide all ' . $post_name . '.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['singles'][$post_type]['select_type'] ) ? selected( esc_attr( $get_prefix['singles'][$post_type]['select_type'] ), 'all' ) : 'selected'; ?>><?php _e( 'All ' . $post_name, 'blox' ); ?></option>
				<?php if ( $post_type == 'attachment' ) { ?>
					<option value="selected_posts" title="<?php _e( 'Show or hide selected ' . $post_name . '.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['singles'][$post_type]['select_type'] ) ? selected( esc_attr( $get_prefix['singles'][$post_type]['select_type'] ), 'selected_posts' ) : ''; ?>><?php _e( 'Selected ' . $post_name . ' IDs', 'blox' ); ?></option>
					<option value="selected_authors" title="<?php _e( 'Show or hide selected ' . $post_name . ' by Uploader (Author).', 'blox' ); ?>" <?php echo ! empty( $get_prefix['singles'][$post_type]['select_type'] ) ? selected( esc_attr( $get_prefix['singles'][$post_type]['select_type'] ), 'selected_authors' ) : ''; ?>><?php _e( 'Selected ' . $post_name . ' by Uploader (Author)', 'blox' ); ?></option>
				<?php } else { ?>
					<option value="selected_posts" title="<?php _e( 'Show or hide selected ' . $post_name . '.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['singles'][$post_type]['select_type'] ) ? selected( esc_attr( $get_prefix['singles'][$post_type]['select_type'] ), 'selected_posts' ) : ''; ?>><?php _e( 'Selected ' . $post_name, 'blox' ); ?></option>
					<option value="selected_taxonomies" title="<?php _e( 'Show or hide selected ' . $post_name . ' by Taxonomies.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['singles'][$post_type]['select_type'] ) ? selected( esc_attr( $get_prefix['singles'][$post_type]['select_type'] ), 'selected_taxonomies' ) : ''; ?>><?php _e( 'Selected ' . $post_name . ' by Taxonomies', 'blox' ); ?></option>
					<option value="selected_authors" title="<?php _e( 'Show or hide selected ' . $post_name . ' by Authors.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['singles'][$post_type]['select_type'] ) ? selected( esc_attr( $get_prefix['singles'][$post_type]['select_type'] ), 'selected_authors' ) : ''; ?>><?php _e( 'Selected ' . $post_name . ' by Authors', 'blox' ); ?></option>
				<?php } ?>
			</select>

			<div class="<?php if ( empty( $get_prefix['singles'][$post_type]['select_type'] ) || $get_prefix['singles'][$post_type]['select_type'] == 'all' ) echo ( 'blox-hidden' ); ?> blox-singles-container-inner">
				<?php
				if ( $post_type == 'attachment' ) {

					// For attachments, just display the manual restrict by id input
					?>
					<div class="<?php if ( empty( $get_prefix['singles'][$post_type]['select_type'] ) || $get_prefix['singles'][$post_type]['select_type'] != 'selected_posts' ) echo ( 'blox-hidden' ); ?> blox-singles-post-container">
						<input type="text" name="<?php echo $name_prefix; ?>[singles][attachment][selection]" value="<?php echo ! empty( $get_prefix['singles']['attachment']['selection'] ) ? esc_attr( implode( ', ', $get_prefix['singles']['attachment']['selection'] ) ) : ''; ?>" class="blox-half-text" placeholder="16, 34, 9"/>
						<div class="blox-description">
							<?php _e( 'Enter a list of comma separated Media IDs.', 'blox' ); ?>
						</div>
					</div>
					<?php

					$this->blox_location_singles_get_authors( $name_prefix, $get_prefix, $post_type, $post_name_singular );
				} else {

					// For all other builtin and custom post types, lets display them all
					$this->blox_location_singles_get_posts( $name_prefix, $get_prefix, $post_type, $post_name );
					$this->blox_location_singles_get_taxonomies( $name_prefix, $get_prefix, $post_type, $post_name_singular );
					$this->blox_location_singles_get_authors( $name_prefix, $get_prefix, $post_type, $post_name_singular );
				}
				?>
			</div>
		</div>
    	<?php
    }


	/**
     * Gets all posts/pages of the given post type.
     * This function actually serves as a wrapper function and wp_list_pages and blox_list_posts do all the heavy lifting.
     *
     * @since 1.0.0
     *
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param string $post_type	  The given post type (slug)
     * @param string $post_name	  The name of the given post type
     */
    public function blox_location_singles_get_posts( $name_prefix, $get_prefix, $post_type, $post_name ) {
    	?>
    	<div class="<?php if ( empty( $get_prefix['singles'][$post_type]['select_type'] ) || $get_prefix['singles'][$post_type]['select_type'] != 'selected_posts' ) echo ( 'blox-hidden' ); ?> blox-singles-post-container">
			<div class="blox-checkbox-container">
				<ul class="blox-columns">
				<?php
				if ( is_post_type_hierarchical( $post_type ) ) {
					// If the post type is hierarchical, then use wp_list_pages to display all posts/pages
					wp_list_pages( array( 'post_type' => $post_type, 'title_li' => '', 'post_status' => 'publish,future,draft,pending,private', 'walker' => new Blox_Page_Walker( $name_prefix, $get_prefix ) ) );
				} else {
					// If the post type is not hierarchical...
					$this->blox_list_posts( $name_prefix, $get_prefix, $post_type );
				}
				?>
				</ul>
			</div>
			<div class="blox-checkbox-select-tools">
				<a class="blox-checkbox-select-all" href="#"><?php _e( 'Select All' ); ?></a> <a class="blox-checkbox-select-none" href="#"><?php _e( 'Unselect All' ); ?></a>
			</div>
		</div>
		<?php
    }


    /**
     * Creates a list of all posts of the given $post_type as inputs with checkboxes
     * Designed for posts that are not heirarchical otherwise we would use wp_list_pages
     *
     * @since 1.0.0
 	 *
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param string $post_type	  The given post type (slug)
     */
    public function blox_list_posts( $name_prefix, $get_prefix, $post_type ) {

    	$args = array(
			'numberposts' => -1,
			'orderby'     => 'title',
			'order'       => 'ASC',
			'fields' 	  => array( 'ID', 'name' ),
			'post_type'   => $post_type,
			'post_status' => 'publish,future,draft,pending,private',
		);

		$posts_array = get_posts( $args );

		$output = '';

		foreach ( $posts_array as $post ) {
			$post_status = get_post_status( $post->ID );

			$output .= '<li><label><input type="checkbox" name="' . $name_prefix . '[singles][' . $post_type . '][selection][]" value="' . $post->ID . '" '. ( ! empty( $get_prefix['singles'][$post_type]['selection'] ) && in_array( $post->ID, $get_prefix['singles'][$post_type]['selection'] ) ? 'checked="checked"' : '' ) . ' /> ';
			$output .= apply_filters('the_title', $post->post_title, $post->ID);
			if ( $post_status == 'private' ) {
				$output .= ' <span class="blox-post-status">(Private)</span>';
			} else if ( $post_status == 'future' ) {
				$output .= ' <span class="blox-post-status">(Scheduled)</span>';
			} else if ( $post_status == 'draft' ) {
				$output .= ' <span class="blox-post-status">(Draft)</span>';
			} else if ( $post_status == 'pending' ) {
				$output .= ' <span class="blox-post-status">(Pending)</span>';
			}
			$output .= '</label></li>';
		}

    	echo $output;
    }


    /**
     * Gets all taxonomies of the given post type and displays them with necessary taxonomy settings
     *
     * @since 1.0.0
 	 *
     * @param string $name_prefix 		 The prefix for saving each setting
     * @param string $get_prefix  		 The prefix for retrieving each setting
     * @param string $post_type	  		 The given post type (slug)
     * @param string $post_name_singular The singular name of the given post type
     */
    public function blox_location_singles_get_taxonomies( $name_prefix, $get_prefix, $post_type, $post_name_singular ) {

		$taxonomy_objects = get_object_taxonomies( $post_type, 'object' );

		if ( ! empty( $taxonomy_objects ) ) {
			?>
			<div class="<?php if ( empty( $get_prefix['singles'][$post_type]['select_type'] ) || $get_prefix['singles'][$post_type]['select_type'] != 'selected_taxonomies' ) echo ( 'blox-hidden' ); ?> blox-singles-taxonomy-container-wrapper" >

				<span class="blox-title"><?php _e( 'Test Strength', 'blox' ); ?></span>


				<select name="<?php echo $name_prefix; ?>[singles][<?php echo $post_type; ?>][taxonomies][taxonomy_test]" class="blox-taxonomy-test blox-has-help">
					<option value="loose" title="<?php _e( 'Loose taxonomy test.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['singles'][$post_type]['taxonomies']['taxonomy_test'] ) ? selected( esc_attr( $get_prefix['singles'][$post_type]['taxonomies']['taxonomy_test'] ), 'loose' ) : 'selected'; ?>><?php _e( 'Loose', 'blox' ); ?></option>
					<option value="strict" title="<?php _e( 'Strict taxonomy test.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['singles'][$post_type]['taxonomies']['taxonomy_test'] ) ? selected( esc_attr( $get_prefix['singles'][$post_type]['taxonomies']['taxonomy_test'] ), 'strict' ) : ''; ?>><?php _e( 'Strict', 'blox' ); ?></option>
					<option value="binding" title="<?php _e( 'Binding taxonomy test.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['singles'][$post_type]['taxonomies']['taxonomy_test'] ) ? selected( esc_attr( $get_prefix['singles'][$post_type]['taxonomies']['taxonomy_test'] ), 'binding' ) : ''; ?>><?php _e( 'Binding', 'blox' ); ?></option>
				</select>

				<span class="blox-help-text-icon">
					<a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
				</span>
				<div class="blox-help-text top">
					<?php echo sprintf( __( 'The taxonomy test strength determines how the selection of different taxonomy terms interact with one another. A "Loose" test means that the block will show/hide so long as it has %1$sany%2$s of the selected taxonomy terms. A "Strict" test will only show/hide the block if it has %1$sall%2$s the selected taxonomy terms. A "Binding" test will only show/hide the block if it has %1$sall and only%2$s the selected taxonomy terms. The taxonomy test takes into account terms across all included taxonomies. Ignored taxonomies are not included in the test. Please see the %3$sBlox Documentation%4$s for further explanation.', 'blox' ), '<strong>', '</strong>', '<a href="https://www.bloxwp.com/documentation/location/?utm_source=blox&utm_medium=plugin&utm_content=location-links&utm_campaign=Blox_Plugin_Links" title="' . __( 'Blox Documentation', 'blox' ) . '" target="_blank">', '</a>' ); ?>
				</div>


				<?php
				foreach ( $taxonomy_objects as $taxonomy_object ) {
					$taxonomy_type  = $taxonomy_object->name;
					$taxonomy_name  = $taxonomy_object->labels->name;
					$taxonomy_terms = get_terms( $taxonomy_type, array( 'orderby' => 'name', 'order' => 'ASC' ) );

					if ( $taxonomy_type == 'post_format' && ! current_theme_supports( 'post-formats' ) ) {
						// Do nothing since the current theme does not support post formats, otherwise showing this option could be confusing
						// Info on post formats: https://codex.wordpress.org/Post_Formats
					} else {
						?>

						<div class="blox-singles-taxonomy-container blox_<?php echo $taxonomy_type; ?>">
							<span class="blox-sub-title"><?php echo $taxonomy_name; ?></span>

							<select name="<?php echo $name_prefix; ?>[singles][<?php echo $post_type; ?>][taxonomies][<?php echo $taxonomy_type; ?>][select_type]" class="blox-taxonomy-select_type">
								<option value="ignore" title="<?php _e( 'Choose to exclude ' . $taxonomy_name . ' from the taxonomy show/hide test.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['singles'][$post_type]['taxonomies'][$taxonomy_type]['select_type'] ) ? selected( esc_attr( $get_prefix['singles'][$post_type]['taxonomies'][$taxonomy_type]['select_type'] ), 'ignore' ) : ''; ?>><?php _e( 'Ignore ' . $taxonomy_name, 'blox' ); ?></option>
								<option value="selected_taxonomies" title="<?php _e( 'Show or hide only selected ' . $taxonomy_name . '.', 'blox' ); ?>" <?php echo ! empty( $get_prefix['singles'][$post_type]['taxonomies'][$taxonomy_type]['select_type'] ) ? selected( esc_attr( $get_prefix['singles'][$post_type]['taxonomies'][$taxonomy_type]['select_type'] ), 'selected_taxonomies' ) : ''; ?>><?php _e( 'Selected ' . $taxonomy_name, 'blox' ); ?></option>
							</select>

							<div class="<?php if ( empty( $get_prefix['singles'][$post_type]['taxonomies'][$taxonomy_type]['select_type'] ) || $get_prefix['singles'][$post_type]['taxonomies'][$taxonomy_type]['select_type'] != 'selected_taxonomies' ) echo ( 'blox-hidden' ); ?> blox-singles-taxonomy-container-inner">

								<?php if ( $taxonomy_terms ) { ?>
									<div class="blox-checkbox-container">
										<ul class="blox-columns">
											<?php foreach ( $taxonomy_terms as $term ) { ?>
											<li>
												<label>
													<input type="checkbox" name="<?php echo $name_prefix; ?>[singles][<?php echo $post_type; ?>][taxonomies][<?php echo $taxonomy_type; ?>][selection][]" value="<?php echo $term->term_id; ?>"  <?php echo ! empty( $get_prefix['singles'][$post_type]['taxonomies'][$taxonomy_type]['selection'] ) && in_array( $term->term_id, $get_prefix['singles'][$post_type]['taxonomies'][$taxonomy_type]['selection'] ) ? 'checked="checked"' : ''; ?> />
													<?php echo apply_filters( 'the_title', $term->name, $term->term_id ) . ' <span class="blox-post-status">(' . $term->count. ')</span>'; ?>
												</label>
											</li>
											<?php } ?>
										</ul>
									</div>
									<div class="blox-checkbox-select-tools">
										<a class="blox-checkbox-select-all" href="#"><?php _e( 'Select All' ); ?></a> <a class="blox-checkbox-select-none" href="#"><?php _e( 'Unselect All' ); ?></a>
									</div>
								<?php } else { ?>
									<div class="blox-alert">
										<?php echo sprintf( __( 'There does not appear to be any %1$s yet.', 'blox' ), $taxonomy_name ); ?>
									</div>
								<?php } ?>

							</div>
						</div>

						<?php
					}
				}
				?>
			</div>
			<?php
		} else {
			?>
			<div class="<?php if ( empty( $get_prefix['singles'][$post_type]['select_type'] ) || $get_prefix['singles'][$post_type]['select_type'] != 'selected_taxonomies' ) echo ( 'blox-hidden' ); ?> blox-singles-taxonomy-container-wrapper" >
				<div class="blox-singles-taxonomy-container">
					<div class="blox-alert">
						<?php echo sprintf( __( 'There does not appear to be any taxonomies associated with this post type yet.', 'blox' ) ); ?>
					</div>
				</div>
			</div>
			<?php
		}
	}


    /**
     * Gets all authors on the site
     *
     * @since 1.0.0
 	 *
     * @param string $name_prefix 		 The prefix for saving each setting
     * @param string $get_prefix  		 The prefix for retrieving each setting
     * @param string $post_type	  		 The given post type (slug)
     * @param string $post_name_singular The singular name of the given post type
     */
	public function blox_location_singles_get_authors( $name_prefix, $get_prefix, $post_type, $post_name_singular ) {
		?>

		<div class="<?php if ( empty( $get_prefix['singles'][$post_type]['select_type'] ) || $get_prefix['singles'][$post_type]['select_type'] != 'selected_authors' ) echo ( 'blox-hidden' ); ?> blox-singles-authors-container-wrapper" >

			<?php
			$author_ids = get_users( array(
				'orderby' => 'names',
				'order'   => 'ASC',
				'fields'  => 'ids',
				'who'     => 'authors'
			) );

			if ( ! empty( $author_ids ) ) { ?>
				<div class="blox-checkbox-container">
					<ul class="blox-columns">

						<?php foreach ( $author_ids as $author_id ) {
							$author_name = get_the_author_meta( 'display_name', $author_id );
							$count_posts = count_user_posts( $author_id, $post_type );
							?>
							<li>
								<label>
									<input type="checkbox" name="<?php echo $name_prefix; ?>[singles][<?php echo $post_type; ?>][authors][selection][]" value="<?php echo $author_id; ?>"  <?php echo ! empty( $get_prefix['singles'][$post_type]['authors']['selection'] ) && in_array( $author_id, $get_prefix['singles'][$post_type]['authors']['selection'] ) ? 'checked="checked"' : ''; ?> />
									<?php echo $author_name; ?> <span class="blox-post-status">(<? echo $count_posts; ?>)</span>
								</label>
							</li>
						<?php } ?>

					</ul>
				</div>
				<div class="blox-checkbox-select-tools">
					<a class="blox-checkbox-select-all" href="#"><?php _e( 'Select All' ); ?></a> <a class="blox-checkbox-select-none" href="#"><?php _e( 'Unselect All' ); ?></a>
				</div>
			<?php } else { ?>
				<div class="blox-description">
					<?php echo __( 'There does not appear to be any authors on this website.', 'blox' ); ?>
				</div>
			<?php } ?>
		</div>

		<?php
	}


    /**
	 * Saves all of the location settings
     *
     * @since 1.0.0
     *
     * @param int $post_id        The global block id or the post/page/custom post-type id corresponding to the local block
     * @param string $name_prefix The prefix for saving each setting
     * @param bool $global        The block state
     *
     * @return array $settings    Return an array of updated settings
     */
	public function save_metabox_tab_location( $post_id, $name_prefix, $global ) {

		$settings = array();

		$settings['location_type'] 	= esc_attr( $name_prefix['location_type'] );
		$settings['selection'] 		= isset( $name_prefix['selection'] ) ? array_map( 'esc_attr', $name_prefix['selection'] ) : '';

		// Singles
		$settings['singles']['select_type'] = esc_attr( $name_prefix['singles']['select_type'] );
		$settings['singles']['selection'] 	= isset( $name_prefix['singles']['selection'] ) ? array_map( 'esc_attr', $name_prefix['singles']['selection'] ) : '';

		// Get available post types
		$post_types = get_post_types( array( 'public' => true ), 'names', 'and' );

		foreach ( $post_types as $post ) {
			$settings['singles'][$post]['select_type'] 	= esc_attr( $name_prefix['singles'][$post]['select_type'] );

			// Get the single post type selections
			if ( $post == 'attachment' ) {

				// For attachments (Media), we only allow selection by id
				$settings['singles'][$post]['selection'] = array_filter( array_map( 'absint', explode( ",", preg_replace( '/\s+/', '', $name_prefix['singles'][$post]['selection'] ) ) ) );

			} else {
				$settings['singles'][$post]['selection'] = isset( $name_prefix['singles'][$post]['selection'] ) ? array_map( 'esc_attr', $name_prefix['singles'][$post]['selection'] ) : '';

				// Get the taxonomies of the given post type
				$taxonomies = get_object_taxonomies( $post );

				// Save the taxonomy settings for the given post type
				if ( ! empty( $taxonomies ) ) {

					$settings['singles'][$post]['taxonomies']['taxonomy_test'] 	= esc_attr( $name_prefix['singles'][$post]['taxonomies']['taxonomy_test'] );

					foreach ( $taxonomies as $taxonomy ) {

						// Get all of the terms in the taxonomy
						$taxonomy_terms = get_terms( $taxonomy, array( 'orderby' => 'name', 'order' => 'ASC' ) );

						// If terms actually exist, save our settings
						if ( ! empty( $taxonomy_terms ) && isset( $name_prefix['singles'][$post]['taxonomies'][$taxonomy] ) ) {
							$settings['singles'][$post]['taxonomies'][$taxonomy]['select_type'] = esc_attr( $name_prefix['singles'][$post]['taxonomies'][$taxonomy]['select_type'] );
							$settings['singles'][$post]['taxonomies'][$taxonomy]['selection'] 	= isset( $name_prefix['singles'][$post]['taxonomies'][$taxonomy]['selection'] ) ? array_map( 'esc_attr', $name_prefix['singles'][$post]['taxonomies'][$taxonomy]['selection'] ) : '';
						}
					}
				}

				// Save all selected authors
				$settings['singles'][$post]['authors']['selection'] = isset( $name_prefix['singles'][$post]['authors']['selection'] ) ? array_map( 'esc_attr', $name_prefix['singles'][$post]['authors']['selection'] ) : '';
			}
		} // end Singles


		// Archives
		$settings['archive']['select_type']              = esc_attr( $name_prefix['archive']['select_type'] );
		$settings['archive']['selection'] 				 = isset( $name_prefix['archive']['selection'] ) ? array_map( 'esc_attr', $name_prefix['archive']['selection'] ) : '';

		$settings['archive']['posttypes']['select_type'] = esc_attr( $name_prefix['archive']['posttypes']['select_type'] );
		$settings['archive']['posttypes']['selection']   = isset( $name_prefix['archive']['posttypes']['selection'] ) ? array_map( 'esc_attr', $name_prefix['archive']['posttypes']['selection'] ) : '';

		$settings['archive']['authors']['select_type']   = esc_attr( $name_prefix['archive']['authors']['select_type'] );
		$settings['archive']['authors']['selection']     = isset( $name_prefix['archive']['authors']['selection'] ) ? array_map( 'esc_attr', $name_prefix['archive']['authors']['selection'] ) : '';

		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects', 'and' );
		foreach ( $taxonomies as $taxonomy ) {
			if ( $taxonomy->name == 'post_format' && ! current_theme_supports( 'post-formats' ) ) {
				// Do nothing since the current theme does not support post formats, otherwise showing this option could be confusing
			} else {
				$settings['archive'][$taxonomy->name]['select_type'] = esc_attr( $name_prefix['archive'][$taxonomy->name]['select_type'] );
				$settings['archive'][$taxonomy->name]['selection']   = isset( $name_prefix['archive'][$taxonomy->name]['selection'] ) ? array_map( 'esc_attr', $name_prefix['archive'][$taxonomy->name]['selection'] ) : '';
			}
		} // end Archives


		/* Manual Overrides
		$settings['manual_override']['manual_show_ids'] = array_filter( array_map( 'absint', explode( ",", preg_replace( '/\s+/', '', $name_prefix['manual_override']['manual_show_ids'] ) ) ) );
        $settings['manual_override']['manual_hide_ids'] = array_filter( array_map( 'absint', explode( ",", preg_replace( '/\s+/', '', $name_prefix['manual_override']['manual_hide_ids'] ) ) ) );
		*/

		return apply_filters( 'blox_save_location_settings', $settings, $post_id, $name_prefix, $global );
	}


	/**
	 * Helper function for getting all major page types
     *
     * @since 1.0.0
	 *
	 * @return array $page_types  All main types of pages on a Wordpress website
	 */
    public function page_types(){

        $page_types = array(
            'front'     => __( 'Front Page', 'blox' ),
            'home'      => __( 'Posts Page', 'blox' ),
            'singles'   => __( 'Single Pages', 'blox' ),
            'archive'   => __( 'Archive Pages', 'blox' ),
            'search'    => __( 'Search Pages', 'blox' ),
            '404'       => __( '404 Error Page', 'blox' )
        );

        return $page_types;
    }


    /**
     * Add admin column for global blocks
     *
     * @param string $post_id
     * @param array $block_data
     */
    public function admin_column_title( $columns ) {
    	$columns['location'] = __( 'Location', 'blox' );
    	return $columns;
    }


    /**
     * Print the admin column data for global blocks.
     *
     * @param string $post_id
     * @param array $block_data
     */
    public function admin_column_data( $post_id, $block_data ) {
		$type = ! empty( $block_data['location']['location_type'] ) ? esc_attr( $block_data['location']['location_type'] ) : '';

        $meta_data = $type;

		// More location information to come...
		switch ( $type ) {
			case 'all' :
				$output = __( 'All', 'blox' );
				break;
			case 'show_selected' :
				$output = __( 'Show On Selected', 'blox' );
				break;
			case 'hide_selected' :
				$output = __( 'Hide On Selected', 'blox' );
				break;
			default :
				$output = '<span style="color:#a00;font-style:italic;">' . __( 'Error', 'blox' ) . '</span>';
				break;
		}

		echo $output;

		// Save our location meta values separately for sorting
		update_post_meta( $post_id, '_blox_content_blocks_location', $meta_data );
    }


    /**
     * Tell Wordpress that the location column is sortable
     *
     * @since 1.0.0
     *
     * @param array $vars  Array of query variables
     */
	public function admin_column_sortable( $sortable_columns ) {
		$sortable_columns[ 'location' ] = 'location';
		return $sortable_columns;
	}


	/**
     * Tell Wordpress how to sort the location column
     *
     * @since 1.0.0
     *
     * @param array $vars  Array of query variables
     */
	public function admin_column_orderby( $vars ) {

		if ( isset( $vars['orderby'] ) && 'location' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
				'meta_key' => '_blox_content_blocks_location',
				'orderby' => 'meta_value'
			) );
		}

		return $vars;
	}


	/**
	 * Run the location test
	 *
     * @since 1.0.0
	 *
	 * @param bool $display_test     Test for determining whether the block should be displayed
	 * @param int $id                The block id, if global, id = $post->ID otherwise it is a random local id
	 * @param array $block           Contains all of our block settings data
	 * @param bool $global           Tells whether our block is global or local
     * @param string $position_type  Identifies for what position type you are running this test for
	 */
	public function run_location_display_test( $display_test, $id, $block, $global, $position_type ) {

		// If the display test is already false, bail...
		if ( $display_test == false ) {
			return $display_test;
		}

		if ( ! $global ) {

			// This is a local block so no location testing is required, proceed to block positioning
			return $display_test;

		} else {

			// Get our location data
			$location_data = ! empty( $block['location'] ) ? $block['location'] : '';

			if ( ! empty( $location_data['location_type'] ) ) {

				if ( $location_data['location_type'] == 'show_selected' ) {

					// Run our show on selected test
					return $this->begin_location_test( $location_data, $id, $block, $global, 'show' );

				} else if ( $location_data['location_type'] == 'hide_selected' ) {

					// Run our hide on selected test
					return $this->begin_location_test( $location_data, $id, $block, $global, 'hide' );

				} else {

					// If no test is selected, proceed to block positioning
					return $display_test;
				}
			}
		}
	}


	/**
	 * Now we actually run the location test
	 *
	 * @since 1.0.0
	 *
	 * @param array $location_data   An array of all the location data/settings
	 * @param int $id       		 The block id, if global, id = $post->ID otherwise it is a random local id
	 * @param array $block  		 Contains all of our block settings data
	 * @param bool $global  		 Tells whether our block is global or local
	 * @param string $show_hide_test Either "show" or "hide"
	 */
	public function begin_location_test( $location_data, $id, $block, $global, $show_hide_test ) {

		// Need to try and make this true in order for the block to display on the page
		$location_test = false;

		if ( ! empty( $location_data['selection'] ) ) {

			if ( in_array( 'front', $location_data['selection'] ) && is_front_page() == true ) {

				// For the actual front page of the website
				$location_test = true;

			} else if ( in_array( 'home', $location_data['selection'] ) && is_home() == true ) {

				// For the blog index page (doesn't necessarily need to be the "homepage")
				$location_test = true;

			} else if ( in_array( 'search', $location_data['selection'] ) && is_search() == true ) {

				// For any search archive
				$location_test = true;

				// POSSIBLY ADD MORE SEARCH OPTIONS IN THE FUTURE

			} else if ( in_array( '404', $location_data['selection'] ) && is_404() == true ) {

				// For the 404 page
				$location_test = true;

			} else if ( in_array( 'archive', $location_data['selection'] ) && is_archive() == true ) {

				if ( $location_data['archive']['select_type'] == 'all' ) {

					// Show the block on any archive page
					$location_test = true;

					//echo 'hello';

				} else if ( $location_data['archive']['select_type'] == 'selected' ) {

					// If our archive selection set is not empty, proceed...
					if ( ! empty( $location_data['archive']['selection'] ) ) {

                		if ( in_array( 'datetime', $location_data['archive']['selection'] ) && is_date() ) {

                			// We are on a Date/Time archive, so proceed...
							$location_test = true;

                		} else if ( in_array( 'posttypes', $location_data['archive']['selection'] ) && is_post_type_archive() ) {

                		    if ( $location_data['archive']['posttypes']['select_type'] == 'all' ) {

								// Show the block on any post type archive page
								$location_test = true;

							} else if ( $location_data['archive']['posttypes']['select_type'] == 'selected' ) {

								if ( ! empty( $location_data['archive']['posttypes']['selection'] ) ) {

									$posttypes = $location_data['archive']['posttypes']['selection'];

                                    // Loop through each selected post type archive, if we are on this archive set to true, otherwise inherit previous test value (could be true or false)
									foreach ( $posttypes as $posttype ) {
										$location_test = is_post_type_archive( $posttype ) ? true : $location_test;
									}
								}
							}

                		} else if ( in_array( 'authors', $location_data['archive']['selection'] ) && is_author() ) {

                			if ( $location_data['archive']['authors']['select_type'] == 'all' ) {

								// Show the block on any author archive page
								$location_test = true;

							} else if ( $location_data['archive']['authors']['select_type'] == 'selected' ) {

								if ( ! empty( $location_data['archive']['authors']['selection'] ) ) {

									// Get author and sort through selection to check
									$author = get_userdata( get_query_var('author') );

									if ( in_array( $author->id, $location_data['archive']['authors']['selection'] ) ) {

										// This author archive is part of the selection, so proceed...
										$location_test = true;

									}
								}
							}

                		} else if ( in_array( 'category', $location_data['archive']['selection'] ) && is_category() ) {

							// Post categories need to be treated differently than normal taxonomies
                			if ( $location_data['archive']['category']['select_type'] == 'all' ) {

								// Show the block on any Post category archive page
								$location_test = true;

							} else if ( $location_data['archive']['category']['select_type'] == 'selected' ) {

								if ( ! empty( $location_data['archive']['category']['selection'] ) ) {

									// Get selected categories and loop through to see which category page we are on, if any
									$categories = $location_data['archive']['category']['selection'];

									foreach ( $categories as $category ) {
										$term_test[] = is_category( $category ) ? true : false;
									}

									$location_test = in_array( true, $term_test ) ? true : false;
								}
							}

                		} else if ( in_array( 'post_tag', $location_data['archive']['selection'] ) && is_tag() ) {

							// Post tags need to be treated differently than normal taxonomies
                			if ( $location_data['archive']['post_tag']['select_type'] == 'all' ) {

								// Show the block on any Post tag archive page
								$location_test = true;

							} else if ( $location_data['archive']['post_tag']['select_type'] == 'selected' ) {

								if ( ! empty( $location_data['archive']['post_tag']['selection'] ) ) {

									// Get selected tags and loop through to see which tag page we are on, if any
									$tags = $location_data['archive']['post_tag']['selection'];

									foreach ( $tags as $tag ) {
										$term_test[] = is_tag( $tag ) ? true : false;
									}

									$location_test = in_array( true, $term_test ) ? true : false;
								}
							}

                		} else {

                			// Remove Date/Time, Authors, Post Types, Post Tags, and Post Categories from the selection (if they are there)
							$taxonomy_archives = array_diff( $location_data['archive']['selection'],  array( 'datetime', 'authors', 'posttypes', 'category', 'post_tag' ) );

							if ( ! empty( $taxonomy_archives ) ) {
								foreach ( $taxonomy_archives as $taxonomy_archive ) {

									if ( $location_data['archive'][$taxonomy_archive]['select_type'] == 'all' && is_tax( $taxonomy_archive ) ) {

										// Show the block on any taxonomy's archive pages
										$location_test = true;

									} else if ( $location_data['archive'][$taxonomy_archive]['select_type'] == 'selected' ) {

										if ( ! empty( $location_data['archive'][$taxonomy_archive]['selection'] ) ) {

											// Get selected tags and loop through to see which tag page we are on, if any
											$terms = $location_data['archive'][$taxonomy_archive]['selection'];

											foreach ( $terms as $term ) {
												$term_object = get_term( $term, $taxonomy_archive );
												$term_test[] = is_tax( $taxonomy_archive, $term_object->slug ) ? true : false;
											}

											$location_test = in_array( true, $term_test ) ? true : false;
										}
									}
								}
							}

                		} // end archive test

                	}

				}

			} else if ( in_array( 'singles', $location_data['selection'] ) && is_singular() == true && is_front_page() == false ) {

				if ( $location_data['singles']['select_type'] == 'all' ) {

					// Show the block on any singles page
					$location_test = true;

				} else if ( $location_data['singles']['select_type'] == 'selected' ) {

					// If our singles selection set is not empty, proceed...
					if ( ! empty( $location_data['singles']['selection'] ) ) {

						// Our singles selection is not empty so now get the current page's id and post type
						$current_post_id   = get_the_ID();
						$current_post_type = get_post_type( $current_post_id );

						// If the current page's post type is in our selection, proceed...
						if ( in_array( $current_post_type, $location_data['singles']['selection'] ) ) {

							// Get our singles display type
							$display_type = ! empty( $location_data['singles'][$current_post_type]['select_type'] ) ? $location_data['singles'][$current_post_type]['select_type'] : false;

							if ( $display_type == 'all' ) {

								// Show all posts so proceed...
								$location_test = true;

							} else if ( $display_type == 'selected_posts' ) {

								// If our current post's id is one of the selected posts, proceed...
								if ( ! empty( $location_data['singles'][$current_post_type]['selection'] ) && in_array( $current_post_id, $location_data['singles'][$current_post_type]['selection'] ) ) {
									$location_test = true;
								}

							} else if ( $display_type == 'selected_taxonomies' ) {

								// Get all the taxonomies objects of the current post types
								$taxonomy_objects = get_object_taxonomies( $current_post_type, 'object' );

								// If the current post type actually has taxonomies, proceed...
								if ( ! empty( $taxonomy_objects ) ) {

									// Determine what taxonomy test we are running
									$taxonomy_test_type = ! empty( $location_data['singles'][$current_post_type]['taxonomies']['taxonomy_test'] ) ? $location_data['singles'][$current_post_type]['taxonomies']['taxonomy_test'] : false;

									// Setup our taxonomy test array
									$taxonomy_test = array();

									// Loop through all taxonomies and run the taxonomy test
									foreach ( $taxonomy_objects as $taxonomy_object ) {

										// Get the taxonomy type from the taxonomy object
										$taxonomy_type = $taxonomy_object->name;

										if ( ! empty( $location_data['singles'][$current_post_type]['taxonomies'][$taxonomy_type]['select_type'] ) ) {

											if ( $location_data['singles'][$current_post_type]['taxonomies'][$taxonomy_type]['select_type'] == 'selected_taxonomies' ) {

												// Get the taxonomy terms associated with the current post and the number of terms the current post has
												$current_post_term_list = wp_get_post_terms( $current_post_id, $taxonomy_type, array( "fields" => "ids" ) );
												$num_current_post_terms = count( $current_post_term_list );

												// Determine what taxonomy test we are running
												$taxonomy_test_type     = ! empty( $location_data['singles'][$current_post_type]['taxonomies']['taxonomy_test'] ) ? $location_data['singles'][$current_post_type]['taxonomies']['taxonomy_test'] : false;

												if ( ! empty( $location_data['singles'][$current_post_type]['taxonomies'][$taxonomy_type]['selection'] ) ) {

													// Get the number of selected terms
													$num_selected           = count( $location_data['singles'][$current_post_type]['taxonomies'][$taxonomy_type]['selection'] );

													// See how many of the custom post type's terms are part of the taxonomy selection
													$intersect_results      = array_intersect( $current_post_term_list, $location_data['singles'][$current_post_type]['taxonomies'][$taxonomy_type]['selection'] );
													$num_intersect_results  = ! empty( $intersect_results ) ? count( $intersect_results ) : null;

													if ( $taxonomy_test_type == 'loose' ) {

														if ( ! empty( $intersect_results ) ) {

															// If our current post's terms are part of the selected set (only one needs to match), proceed...
															$taxonomy_test[$taxonomy_type] = true;
														} else {

															// If our current post's terms are part NOT of the selected set (not even one matches), end...
															$taxonomy_test[$taxonomy_type] = false;
														}
													} else if ( $taxonomy_test_type == 'strict' ) {

														// All terms of the current post have to be in the selected set, but the post can have more terms than are selected
														if ( $num_selected <= $num_current_post_terms && $num_selected == $num_intersect_results ) {

															// If our current post's terms are part of the selected set (only one needs to match), proceed...
															$taxonomy_test[$taxonomy_type] = true;
														} else {

															// If our current post's terms are part NOT of the selected set (not even one matches), end...
															$taxonomy_test[$taxonomy_type] = false;
														}
													} else if ( $taxonomy_test_type == 'binding' ) {

														// Total number of terms the current post has, has to equal the number selected and the intersection of the current post's
														// terms and the selected terms has to equal the number of selected (i.e. all terms of the current post match those selected, no more, no less)
														if ( $num_selected == $num_current_post_terms && $num_selected == $num_intersect_results ) {

															// If our current post's terms are all part of the selected set (all need to match), proceed...
															$taxonomy_test[$taxonomy_type] = true;
														} else {

															// If our current post's terms are part NOT ALL of the selected set, end...
															$taxonomy_test[$taxonomy_type] = false;
														}
													}

												} else {

													// Determine what will happen if the user enabled "Selected by Taxonomy Terms" but didn't select any terms
													if ( $num_current_post_terms > 0 ) {

														if ( $taxonomy_test_type == 'binding' ) {
															// The webpage has terms but none were selected...so end
															$taxonomy_test[$taxonomy_type] = false;
														}

														// For "Loose" and "Strict" tests, a block can still be displayed if no terms are selected. They just need to have selected terms in other taxonomies...

													} else {

														// User enabled "Selected by Taxonomy Terms" but didn't select any terms, and the webpage has no terms...so end
														$taxonomy_test[$taxonomy_type] = false;
													}
												}
											}

											// If select type equals "ignore", we do not include the taxonomy as part of the taxonomy show/hide test

										}
									}

									// Determine the outcome of the taxonomy test
									if ( $taxonomy_test_type == 'loose' ) {

										// For the loose test, we only need to have one 'true'. If we passed the taxonomy test, proceed...
										if ( ! empty( $taxonomy_test ) && in_array( true, $taxonomy_test ) ) {
											$location_test = true;
										}
									} else if ( $taxonomy_test_type == 'strict' || $taxonomy_test_type == 'binding' ) {

										// For the strict and binding tests, we can have no 'false'. If we passed the taxonomy test, proceed...
										if ( ! empty( $taxonomy_test ) && ! in_array( false, $taxonomy_test ) ) {
											$location_test = true;
										}
									} // end taxonomy test


								}

							} else if ( $display_type == 'selected_authors' ) {

								// Get author of page and sort through selection to check
								$author = get_queried_object()->post_author;

								// If our current post's id is one of the selected posts, proceed...
								if ( ! empty( $location_data['singles'][$current_post_type]['authors']['selection'] ) && in_array( $author, $location_data['singles'][$current_post_type]['authors']['selection'] ) ) {
									$location_test = true;
								}

							}
						}
					}
				}
			}
		}

		// Determine whether to show or hide the block
		if ( $show_hide_test == 'show' ) {

			// Since we are running a show test, we only show the block if the location_test is true
			if ( $location_test == true ) {
				//echo 'hello';
				return true;
			} else {
				return false;
			}

		} else if ( $show_hide_test == 'hide' ) {

			// Since we are running a hide test, we only show the block if the location_test is false
			if ( $location_test == false ) {
				return true;
			} else {
				return false;
			}
		}
	}


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Location ) ) {
            self::$instance = new Blox_Location();
        }

        return self::$instance;
    }
}

// Load the location class.
$blox_location = Blox_Location::get_instance();





/**
 * Used for getting a list of all posts from a hierarchal post type, for example pages
 * The class prints an input (checkbox) for each
 *
 * This class is an extension of the WP core class Walker_Page
 *
 * @since 1.0.0
 */
class Blox_Page_Walker extends Walker_Page {

	private $post_page;

	public $name_prefix;
	public $get_prefix;

	function __construct( $name_prefix, $get_prefix ) {
		global $post;

		$this->post_page = get_option( 'page_for_posts' );

        $this->name_prefix = $name_prefix;
		$this->get_prefix  = $get_prefix;
	}

	function start_lvl( &$output, $depth = 0, $args = array() ) {

		// Calculate how much we want to indent child posts/pages based on depth
		$left = ( $depth + 1 ) * 15;

		$indent = str_repeat("\t", $depth);
		// $output .= "\n" . '<li class="child" style="left:' . $left . 'px"><label>' . "\n";
		$output .= '';
	}

	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
	}

	function start_el( &$output, $page, $depth = 0, $args = array(), $current_object_id = 0 ) {
		extract( $args, EXTR_SKIP );
		// Calculate how much we want to indent child posts/pages based on depth
		$left = ( $depth ) * 15;

		if ( $depth ) {
			// $indent = str_repeat( "\t", $depth );
			$indent = "\n" . '<li class="child" style="left:' . $left . 'px"><label>' . "\n";
		} else {
			$indent = '<li><label>';
		}

		if ( $page->ID <> $this->post_page ) {

			// Get the post status and type
			$post_status = get_post_status( $page->ID );
			$post_type	= get_post_type( $page->ID );

			$output .= $indent . '<input type="checkbox" name="' . $this->name_prefix . '[singles][' . $post_type . '][selection][]" value="' . $page->ID . '" ' . ( ! empty( $this->get_prefix['singles'][$post_type]['selection'] ) && in_array( $page->ID, $this->get_prefix['singles'][$post_type]['selection'] ) ? 'checked="checked"' : '' ) .' /> ';
			$output .= apply_filters( 'the_title', $page->post_title, $page->ID );
			if ( $post_status == 'private' ) {
				$output .= ' <span class="blox-post-status">(Private)</span>';
			} else if ( $post_status == 'future' ) {
				$output .= ' <span class="blox-post-status">(Scheduled)</span>';
			} else if ( $post_status == 'draft' ) {
				$output .= ' <span class="blox-post-status">(Draft)</span>';
			} else if ( $post_status == 'pending' ) {
				$output .= ' <span class="blox-post-status">(Pending)</span>';
			}
		}
	}

	function end_el( &$output, $page, $depth = 0, $args = array() ) {
		$output .= "</label></li>\n";
	}
}
