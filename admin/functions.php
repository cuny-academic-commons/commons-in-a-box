<?php
/**
 * CBox Admin Common Functions
 *
 * @since 0.3
 *
 * @package Commons_In_A_Box
 * @subpackage Adminstration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Check to see if CBox is correctly setup.
 *
 * @since 0.3
 *
 * @uses cbox_get_installed_revision_date() Get the CBox revision date from the DB
 * @uses cbox_is_upgraded() Check to see if CBox just upgraded
 * @uses cbox_is_bp_maintenance_mode() Check to see if BuddyPress is in maintenance mode
 * @return bool
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
	if ( cbox_is_bp_maintenance_mode() )
		return false;

	return true;
}

/**
 * Check to see if CBox has just upgraded.
 *
 * @since 0.3
 *
 * @uses cbox_get_installed_revision_date() Gets the CBox revision date from the DB
 * @uses cbox_get_current_revision_date() Gets the current CBox revision date from Commons_In_A_Box::setup_globals()
 * @return bool
 */
function cbox_is_upgraded() {
	if ( cbox_get_installed_revision_date() && ( cbox_get_current_revision_date() > cbox_get_installed_revision_date() ) )
		return true;

	return false;
}

/**
 * Outputs the CBox version
 *
 * @since 0.3
 *
 * @uses cbox_get_version() To get the CBox version
 */
function cbox_version() {
	echo cbox_get_version();
}
	/**
	 * Return the CBox version
	 *
	 * @since 0.3
	 *
	 * @return string The CBox version
	 */
	function cbox_get_version() {
		return cbox()->version;
	}

/**
 * Returns the current CBox revision date as set in
 * {@link Commons_In_A_Box::setup_globals()}.
 *
 * @since 0.3
 *
 * @return int The current CBox revision date as a unix timestamp.
 */
function cbox_get_current_revision_date() {
	return strtotime( cbox()->revision_date );
}

/**
 * Returns the CBox revision date from the current CBox install.
 *
 * @since 0.3
 *
 * @return mixed Integer of the installed CBox unix timestamp on success.  Boolean false on failure.
 */
function cbox_get_installed_revision_date() {
	return strtotime( get_site_option( '_cbox_revision_date' ) );
}

/**
 * Bumps the CBox revision date in the DB
 *
 * @since 0.3
 *
 * @return mixed String of date on success. Boolean false on failure
 */
function cbox_bump_revision_date() {
	update_site_option( '_cbox_revision_date', cbox()->revision_date );
}

/**
 * Get the current CBox setup step.
 *
 * This should only be used if cbox_is_setup() returns false.
 *
 * @since 0.3
 *
 * @uses cbox_is_bp_maintenance_mode() Check to see if BuddyPress is in maintenance mode
 * @return string The current CBox setup step.
 */
function cbox_get_setup_step() {
	// see if BuddyPress is activated
	// @todo BP_VERSION doesn't work in BP 1.7 yet
	// @todo should also check the BP DB version...
	if ( ! defined( 'BP_VERSION' ) ) {
		$step = 'no-buddypress';

	// buddypress is activated
	} else {
		// buddypress needs to finish setup
		if ( cbox_is_bp_maintenance_mode() ) {
			$step = 'buddypress-wizard';

		// buddypress is setup
		} else {
			$step = 'last-step';
		}
	}

	return $step;
}

/**
 * Outputs the URL for the BP Admin Wizard page.
 *
 * @since 0.3
 *
 * @uses cbox_get_the_bp_admin_wizard_url() To get the URL for the BP Admin Wizard page.
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
	 *
	 * @uses is_multisite() Check to see if WP is in network mode.
	 * @uses bp_is_multiblog() Check to see if BuddyPress is in multiblog mode.
	 * @return string of the BP wizard URL
	 */
	function cbox_get_the_bp_admin_wizard_url() {
		if ( ! is_multisite() || bp_is_multiblog_mode() ) {
			return admin_url( 'index.php?page=bp-wizard' );
		} else {
			return network_admin_url( 'update-core.php?page=bp-wizard' );
		}
	}

/**
 * Check to see if BuddyPress is in maintenance mode.
 *
 * @since 0.3
 *
 * @uses bp_get_maintenance_mode() Exists in BP 1.6 and up.
 * @return bool
 */
function cbox_is_bp_maintenance_mode() {
	// BP 1.6+
	if ( function_exists( 'bp_get_maintenance_mode' ) && bp_get_maintenance_mode() )
		return true;

	// BP 1.5
	global $bp;

	if ( ! empty( $bp->maintenance_mode ) )
		return true;

	return false;
}

/**
 * Check to see if the current theme is BuddyPress-compatible.
 *
 * @since 0.3
 *
 * @uses wp_get_theme() To get the current theme's info
 * @return bool
 */
function cbox_is_theme_bp_compatible() {
	global $bp;

	// buddypress isn't installed, so stop!
	if ( empty( $bp ) )
		return false;

	// if we're on BP 1.7, we don't need to worry about theme compatibility
	if ( function_exists( 'bp_get_template_part' ) )
		return true;

	// get current theme
	$theme = wp_get_theme();

	$theme_tags = ! empty( $theme->tags ) ? $theme->tags : array();

	// BP is < 1.7, check to see if the 'buddypress' tag is in the theme or if
	// stylesheet is 'bp-default'
	$retval = in_array( 'buddypress', $theme_tags ) || $theme->get_stylesheet() == 'bp-default';

	// still false? do some other checks
	if ( empty( $retval ) ) {
		// BP Template Pack check
		if ( function_exists( 'bp_tpack_theme_setup' ) )
			$retval = true;

		// some themes might have did a straight-out copy and paste of bp-default
		// without declaring themselves as a child theme of bp-default
		// to detect these instances, we check to see if the members loop template exists
		// this is done because the members component is required
		elseif ( file_exists( $theme->get_stylesheet_directory() . '/members/members-loop.php' ) )
			$retval = true;
	}

	return $retval;
}
