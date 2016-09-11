<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin Actions
 *
 * @since 1.2.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Processes all Blox actions sent via POST and GET by looking for the 'blox-action'
 * request and running do_action() to call the function
 *
 * Code humbly borrowed from Easy Digital Downloads by Pippin Williamson
 *
 * @since 1.2.0
 * @return void
 */
function blox_process_actions() {
	if ( isset( $_POST['blox-action'] ) ) {
		do_action( 'blox_' . $_POST['blox-action'], $_POST );
	}

	if ( isset( $_GET['blox-action'] ) ) {
		do_action( 'blox_' . $_GET['blox-action'], $_GET );
	}
}
add_action( 'admin_init', 'blox_process_actions' );
