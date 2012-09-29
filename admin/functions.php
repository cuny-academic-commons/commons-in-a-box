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
	// we haven't saved the revision date into the DB yet
	if ( ! cbox_get_installed_revision_date() )
		return false;

	// cbox is installed, but we just upgraded to a 
	// newer version of CBox
	if ( cbox_is_upgraded() )
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
 * Check to see if CBox has just upgraded.
 *
 * @uses cbox_get_installed_revision_date() Gets the CBox revision date from the DB
 * @uses cbox_get_current_revision_date() Gets the current CBox revision date from Commons_In_A_Box::setup_globals()
 * @return bool
 * @since 0.3
 */
function cbox_is_upgraded() {
	if ( cbox_get_installed_revision_date() && ( cbox_get_current_revision_date() > cbox_get_installed_revision_date() ) )
		return true;

	return false;
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

/**
 * Bumps the CBox revision date in the DB
 *
 * @since 0.3
 */
function cbox_bump_revision_date() {
	update_site_option( '_cbox_revision_date', cbox()->revision_date );
}

/**
 * Outputs the URL for the BP Admin Wizard page.
 *
 * @uses cbox_get_the_bp_admin_wizard_url() To get the URL for the BP Admin Wizard pgae.
 * @since 0.3
 */
function cbox_the_bp_admin_wizard_url() {
	echo cbox_get_the_bp_admin_wizard_url();
}

	/**
	 * Get the URL for the BP Admin Wizard page.
	 *
	 * This basically copies a section of code from
	 * {@link BP_Admin::admin_menus()}.
	 *
	 * @since 0.3
	 */
	function cbox_get_the_bp_admin_wizard_url() {
		if ( ! is_multisite() || bp_is_multiblog_mode() ) {
			return admin_url( 'index.php?page=bp-wizard' );
		} else {
			return network_admin_url( 'update-core.php?page=bp-wizard' );
		}
	}

