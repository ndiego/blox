<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Prints the content blocks to the frontend via PHP function
 *
 * @since 	2.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
function blox_display_block( $id ) {

    // Check if there is an id specified
    if ( empty( $id ) ) return;

    // Ge the set id
    $id = esc_attr( $id );

    // Define the scope. make sure it is global since only global blocks can use PHP positioning. If no scope, not a valid id, so return
    if ( strpos( $id, 'global' ) === false ) return;

    // If PHP positioning have been disabled for global blocks, return
    if ( blox_get_option( 'global_disable_php_positioning', false ) ) return;

    // Get the display test results from the frontend file
    $display_test = Blox_Frontend::get_instance()->display_test;

    // Make sure our block is represented in the test
    if ( ! array_key_exists( $id, $display_test ) || empty( $display_test[$id] ) ) return;

    // Trim the id to remove the scope and get the true id
    $true_id = substr( $id, strlen( 'global' ) + 1 );

    // Get the block data
    $block = get_post_meta( $true_id, '_blox_content_blocks_data', true );

    // If there is no block data associated with the id given, return
    if ( empty( $block ) ) return;

    // If the disable PHP setting is set, return
    if ( isset( $block['position']['php']['disable'] ) && $block['position']['php']['disable'] ) return;

    // Run our display test (we need this due to the ability of PHP blocks to ignore the location tests)
    $display_test_results = array_count_values( $display_test[$id] );

    if ( array_key_exists( 0, $display_test_results ) ) {

        // Implies visibility or another test, other than location, has failed
        if ( $display_test_results[0] > 1 ) return;

        // If we only have one failure, check if it is a location failure, then see if we are ignoring the location test
        if ( $display_test_results[0] == 1 ) {
            if ( isset( $display_test[$id]['location'] ) && $display_test[$id]['location'] == 0 ) {
                $ignore_location = isset( $block['position']['php']['ignore_location'] ) ? $block['position']['php']['ignore_location'] : 0;
                if ( ! $ignore_location ) return;
            }
        } else {
            return;
        }
    }

    // @TODO DO WE NEED THIS??? We need to use output buffering here to ensure the slider content is contained in the wrapper div
    //ob_start();

    blox_frontend_content( null, array( $id, $block, true ) );
    //$output = ob_get_clean();

    //return $output;
}
