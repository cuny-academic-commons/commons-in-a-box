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
	// get the installed revision date
	$installed_revision_date = cbox_get_installed_revision_date();

	// if empty, this means CBox never finished setting up
	if ( empty( $installed_revision_date ) )
		return false;

	// if we're at this point, cbox is installed, but we just upgraded to a 
	// newer version of CBox
	if ( cbox_get_current_revision_date() > $installed_revision_date )
		return false;

	// if BuddyPress doesn't exist, stop now
	if ( ! defined( 'BP_VERSION' ) )
		return false;

	// check if BuddyPress is in maintenance mode
	// this means BuddyPress hasn't finished setting up yet
	if ( bp_get_maintenance_mode() )
		return false;

	return true;
}

/**
 * Outputs the CBox version
 *
 * @uses cbox_get_version() To get the CBox version
 * @since 0.3
 */
function cbox_version() {
	echo cbox_get_version();
}
	/**
	 * Return the CBox version
	 *
	 * @since 0.3
	 * @return string The CBox version
	 */
	function cbox_get_version() {
		return cbox()->version;
	}

/**
 * Returns the current CBox revision date as set in 
 * {@link Commons_In_A_Box::setup_globals()}.
 *
 * @return int The current CBox revision date as a unix timestamp.
 * @since 0.3
 */
function cbox_get_current_revision_date() {
	return strtotime( cbox()->revision_date );
}

/**
 * Returns the CBox revision date from the current CBox install.
 *
 * @return mixed Integer of the installed CBox unix timestamp on success.  Boolean false on failure.
 * @since 0.3
 */
function cbox_get_installed_revision_date() {
	return strtotime( get_site_option( '_cbox_revision_date' ) );
}
