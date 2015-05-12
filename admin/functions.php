<?php
/**
 * CBOX Admin Common Functions
 *
 * @since 0.3
 *
 * @package Commons_In_A_Box
 * @subpackage Adminstration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Check to see if CBOX is correctly setup.
 *
 * @since 0.3
 *
 * @uses cbox_get_installed_revision_date() Get the CBOX revision date from the DB
 * @uses cbox_is_upgraded() Check to see if CBOX just upgraded
 * @uses cbox_is_bp_maintenance_mode() Check to see if BuddyPress is in maintenance mode
 * @return bool
 */
function cbox_is_setup() {
	// we haven't saved the revision date into the DB yet
	if ( ! cbox_get_installed_revision_date() )
		return false;

	// CBOX is installed, but we just upgraded to a
	// newer version of CBOX
	if ( cbox_is_upgraded() )
		return false;

	// if BuddyPress doesn't exist, stop now
	if ( ! defined( 'BP_VERSION' ) )
		return false;

	// check if BuddyPress is in maintenance mode
	// this means BuddyPress hasn't finished setting up yet
	if ( cbox_is_bp_maintenance_mode() )
		return false;

	// theme needs an update
	if ( cbox_get_theme_to_update() ) {
		return false;
	}

	return true;
}

/**
 * Check to see if CBOX has just upgraded.
 *
 * @since 0.3
 *
 * @uses cbox_get_installed_revision_date() Gets the CBOX revision date from the DB
 * @uses cbox_get_current_revision_date() Gets the current CBOX revision date from Commons_In_A_Box::setup_globals()
 * @return bool
 */
function cbox_is_upgraded() {
	if ( cbox_get_installed_revision_date() && ( cbox_get_current_revision_date() > cbox_get_installed_revision_date() ) )
		return true;

	return false;
}

/**
 * Get the CBOX theme that needs to be updated.
 *
 * @since 1.0.8
 *
 * @return string|bool Returns the theme name needing an update; otherwise
 *  boolean false if theme is already updated or if current theme is not
 *  bundled with CBOX.
 */
function cbox_get_theme_to_update() {
	if ( isset( cbox()->theme_to_update ) ) {
		return cbox()->theme_to_update;
	}

	if ( is_multisite() ) {
		// sanity check
		if ( ! defined( 'BP_VERSION' ) ) {
			return false;
		}

		switch_to_blog( bp_get_root_blog_id() );
		$theme = wp_get_theme();
		restore_current_blog();
	} else {
		$theme = wp_get_theme();
	}

	// include the CBOX Theme Installer
	if ( ! class_exists( 'CBox_Theme_Installer' ) ) {
		require( CBOX_PLUGIN_DIR . 'admin/theme-install.php' );
	}

	$retval = CBOX_Theme_Specs::get_upgrades( $theme );

	// set marker so we don't have to do this again
	cbox()->theme_to_update = $retval;

	return $retval;
}

/**
 * Outputs the CBOX version
 *
 * @since 0.3
 *
 * @uses cbox_get_version() To get the CBOX version
 */
function cbox_version() {
	echo cbox_get_version();
}
	/**
	 * Return the CBOX version
	 *
	 * @since 0.3
	 *
	 * @return string The CBOX version
	 */
	function cbox_get_version() {
		return cbox()->version;
	}

/**
 * Returns the current CBOX revision date as set in
 * {@link Commons_In_A_Box::setup_globals()}.
 *
 * @since 0.3
 *
 * @return int The current CBOX revision date as a unix timestamp.
 */
function cbox_get_current_revision_date() {
	return strtotime( cbox()->revision_date );
}

/**
 * Returns the CBOX revision date from the current CBOX install.
 *
 * @since 0.3
 *
 * @return mixed Integer of the installed CBOX unix timestamp on success.  Boolean false on failure.
 */
function cbox_get_installed_revision_date() {
	return strtotime( get_site_option( '_cbox_revision_date' ) );
}

/**
 * Bumps the CBOX revision date in the DB
 *
 * @since 0.3
 *
 * @return mixed String of date on success. Boolean false on failure
 */
function cbox_bump_revision_date() {
	update_site_option( '_cbox_revision_date', cbox()->revision_date );
}

/**
 * Get the current CBOX setup step.
 *
 * This should only be used if {@link cbox_is_setup()} returns false.
 *
 * @since 0.3
 *
 * @uses cbox_is_bp_maintenance_mode() Check to see if BuddyPress is in maintenance mode
 * @return string The current CBOX setup step.
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

		// theme needs an update?
		} elseif ( cbox_get_theme_to_update() ) {
			$step = 'theme-update';

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
	if ( class_exists( 'BP_Theme_Compat' ) )
		return true;

	// If the theme supports 'buddypress', we're good!
	if ( current_theme_supports( 'buddypress' ) ) {
		return true;

	// If the theme doesn't support BP, do some additional checks
	} else {
		// Bail if theme is a derivative of bp-default
		if ( in_array( 'bp-default', array( get_template(), get_stylesheet() ) ) ) {
			return true;
		}

		// Bruteforce check for a BP template
		// Examples are clones of bp-default
		if ( locate_template( 'members/members-loop.php', false, false ) ) {
			return true;
		}
	}

	// Theme doesn't support BP
	return false;
}

/**
 * Do stuff after BuddyPress is installed for new installs only.
 *
 * After we've completed the BP wizard, we do our checks to remove the Forums
 * component and the Forums page from BuddyPress.
 *
 * @since 1.0-beta4
 *
 * @see CBox_Admin::bp_wizard_redirect()
 */
function cbox_bp_after_version_bump() {
	// if this isn't a new BP install, stop now!
	if ( ! get_option( '_cbox_bp_never_installed' ) )
		return;

	/** remove forums component ***************************************/

	// get active BP components
	$active_components = bp_get_option( 'bp-active-components' );

	// get rid of forums component if it's enabled
	if ( isset( $active_components['forums'] ) ) {
		unset( $active_components['forums'] );

		// update active components
		bp_update_option( 'bp-active-components', $active_components );
	}

	/** remove forums directory page **********************************/

	// get all BP directory pages
	$bp_pages = bp_core_get_directory_page_ids();

	// get rid of forums page if it exists
	if ( isset( $bp_pages['forums'] ) ) {
		// if bbPress is installed, let's use the bbPress forum shortcode
		// for the now-orphaned BP forums directory page
		if ( function_exists( 'bbpress' ) ) {
			wp_update_post( array(
				'ID'           => $bp_pages['forums'],
				'post_content' => '[bbp-forum-index]'
			) );
		}

		// remove the forums component
		unset( $bp_pages['forums'] );

		// update active components
		bp_update_option( 'bp-pages', $bp_pages );
	}

	// remove DB marker
	delete_option( '_cbox_bp_never_installed' );

}

/** HOOK-RELATED ***************************************************/

/**
 * Turn off SSL certificate verification when downloading from Github.
 *
 * Github uses HTTPS links, so we need to turn off SSL verification otherwise
 * WordPress kills the download.
 *
 * Hooked to the 'http_request_args' filter.
 * We use this function during plugin / theme installation.
 *
 * @since 0.3
 *
 * @param array $args Request args.
 * @param str $url The URL we want to download.
 * @return array Request args.
 */
function cbox_disable_ssl_verification( $args, $url ) {
	// disable SSL verification for Github links
	if ( strpos( $url, 'github.com' ) !== false )
		$args['sslverify'] = false;

	return $args;
}

/**
 * Renames downloaded Github folder to a cleaner directory name.
 *
 * Why? Because Github names their directories with the Github repo name and
 * branch name. So we want to rename the theme directory so WP can pick up the
 * parent theme and so it's more palatable.
 *
 * Hooked to the 'upgrader_source_selection' filter.
 * We use this function during plugin / theme installation.
 *
 * @since 0.3
 *
 * @param str $source The temporary folder where the ZIP file was extracted.
 * @param str $remote_source The filepath to the temporary ZIP file.
 * @param obj $obj The object initiating the download.
 * @uses get_class() To find out what object is initiating the download.
 * @uses rename() To rename a file or directory.
 * @return str Filepath to temporary folder.
 */
function cbox_rename_github_folder( $source, $remote_source, $obj ) {
	$class_name = get_class( $obj );

	switch ( $class_name ) {
		case 'CBox_Theme_Installer' :
			// if download url is not from github or a local install, stop now!
			if ( strpos( $obj->skin->options['url'], 'github.com' ) === false && ( ! empty( $obj->options['url'] ) && strpos( $obj->options['url'], 'commons-in-a-box/includes/zip' ) === false ) ) {
				return $source;
			}

			global $wp_filesystem;

			// rename the theme folder to get rid of github's funky naming
			$new_location = $remote_source . '/cbox-theme/';

			// now rename the folder
			$rename = $wp_filesystem->move( $source, $new_location );

			// return our directory
			// being extra cautious here
			if ( $rename === false ) {
				return $source;

			// if rename was successful, return the new location
			} else {
				return $new_location;
			}

			break;

		case 'CBox_Plugin_Upgrader' :
			// if download url is not from github or a local install, stop now!
			if ( strpos( $obj->skin->options['url'], 'github.com' ) === false && strpos( $obj->skin->options['url'], 'commons-in-a-box/includes/zip' ) === false ) {
				return $source;
			}

			global $wp_filesystem;

			// get position of last hyphen in github directory
			$pos = strrpos( $source, '-' );

			// get the previous character to the hyphen
			$previous = substr( $source, $pos - 1, 1 );

			// see if previous character is numeric.
			// if so, we need to strip further back
			if ( is_numeric( $previous ) ) {
				$from_back = strlen( $source ) - $pos + 1;
				$pos = strrpos( $source, '-', -$from_back );
			}

			// get rid of branch name in github directory
			$new_location = trailingslashit( substr( $source, 0, $pos ) );

			// now rename the folder
			$rename = $wp_filesystem->move( $source, $new_location );

			// return our directory
			// being extra cautious here
			if ( $rename === false ) {
				return $source;

			// if rename was successful, return the new location
			} else {
				return $new_location;
			}

			break;

		// not a CBOX install? return the regular $source now!
		default :
			return $source;

			break;
	}

}

/**
 * Check if certain plugins are installed during CBOX activation.
 *
 * @since 1.0-beta4
 */
function cbox_plugin_check() {

	/** BuddyPress ****************************************************/

	// do check for multisite
	if ( is_multisite() ) {
		$bp_root_blog = defined( 'BP_ROOT_BLOG' ) ? constant( 'BP_ROOT_BLOG' ) : 1;

		$option = get_blog_option( $bp_root_blog, 'bp-active-components' );

	// single WP
	} else {
		$option = get_option( 'bp-active-components' );
	}

	// if BP was never installed, save a marker so we can reference later
	if ( false === $option ) {
		update_option( '_cbox_bp_never_installed', 1 );
	}

}
add_action( 'cbox_activation', 'cbox_plugin_check' );
