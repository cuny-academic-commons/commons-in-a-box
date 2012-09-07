<?php
/**
 * CBox Admin Common Functions
 *
 * @package Commons_In_A_Box
 * @subpackage Adminstration
 * @since 0.3
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Check to see if CBox is correctly setup.
 *
 * @return bool
 * @since 0.3
 */
function cbox_is_setup() {
	// check constant( 'CBOX_VERSION' ) compared to cbox DB version

	// check if BuddyPress exists
	if ( ! defined( 'BP_VERSION' ) )
		return false;

	// check if BuddyPress is in maintenance mode
	if ( bp_get_maintenance_mode() )
		return false;

	return true;
}