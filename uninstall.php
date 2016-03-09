<?php
/**
 * Uninstall Blox
 *
 * Deletes all the plugin data i.e.
 * 		1. Custom post types (Global Blocks)
 * 		2. Post metadata (Local Blocks)
 * 		3. Plugin settings.
 * 		4. License/Addon settings.
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Load main Blox file.
include_once( 'blox.php' );

global $wpdb;

$blox_settings = get_option( 'blox_settings' );

if ( $blox_settings['uninstall_on_delete'] == 1 ) {

	// Delete all Global Blocks
	$global_blocks = get_posts( array( 'post_type' => 'blox', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );

	if ( $global_blocks ) {
		foreach ( $global_blocks as $global_block ) {
			wp_delete_post( $global_block, true);
		}
	}
	
	// Delete all Local Blocks and the blocks count meta
	delete_metadata( 'post', 0, '_blox_content_blocks_data', '', true );
	delete_metadata( 'post', 0, '_blox_content_blocks_count', '', true );
	
	// Delete all Blox settings
	delete_option( 'blox_settings' );
	
	// Delete all Blox license settings
	delete_option( 'blox_licenses' );
}
