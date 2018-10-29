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
		$current_theme = cbox_get_theme();
	} else {
		$current_theme = wp_get_theme();
	}

	$retval = false;

	// get our package theme specs
	$package_theme = cbox_get_package_prop( 'theme' );
	if ( empty( $package_theme['download_url'] ) ) {
		return $retval;
	}

	// if current theme is not the CBOX package theme, no need to proceed!
	if ( ! empty( $package_theme ) ) {
		$check = true;
		if ( $current_theme->get_template() != $package_theme['directory_name'] ) {
			$check = false;
		}

		// child theme support
		// if child theme, we need to grab the CBOX parent theme's data
		if ( true === $check && $current_theme->get_stylesheet() != $package_theme['directory_name'] ) {
			$current_theme = cbox_get_theme( $package_theme['directory_name'] );
		}

		// check if current CBOX theme is less than our internal spec
		// if so, we want to update it!
		if ( true === $check && version_compare( $current_theme->Version, $package_theme['version'] ) < 0 ) {
			$retval = $package_theme['directory_name'];
		}
	}

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
	$step = '';

	// No package.
	if ( ! cbox_get_current_package_id() ) {
		$step = 'no-package';

	// Haven't installed before.
	} elseif ( ! cbox_get_installed_revision_date() ) {
		// Get required plugins.
		$required = CBox_Admin_Plugins::organize_plugins_by_state( CBox_Plugins::get_plugins( 'required' ) );
		unset( $required['deactivate'] );

		// Check to see if required plugins are needed.
		if ( ! empty( $required ) ) {
			$step = 'required-plugins';

		// Recommended plugins.
		} else {
			$recommended = CBox_Admin_Plugins::organize_plugins_by_state( CBox_Plugins::get_plugins( 'recommended' ) );
			unset( $recommended['deactivate'] );

			if ( ! empty( $recommended ) ) {
				$step = 'recommended-plugins';

			// Theme install.
			} elseif ( cbox_get_theme_prop( 'download_url' ) ) {
				$step = 'theme-prompt';
			}
		}

	// Theme needs an update.
	} elseif ( cbox_get_theme_to_update() ) {
		$step = 'theme-update';
	}

	return $step;
}

/**
 * Get a specific admin property for use with CBOX.
 *
 * @since 1.1.0
 *
 * @param  string $prop Prop to fetch. Either 'menu' or 'url'.
 * @param  mixed  $arg  Function argument passed for use.
 * @return string
 */
function cbox_admin_prop( $prop = '', $arg = '' ) {
	$retval = '';

	if ( 'menu' === $prop ) {
		$retval = is_network_admin() ? 'network_admin_menu' : 'admin_menu';
	} elseif ( 'url' === $prop ) {
		$retval = self_admin_url( $arg );
		if ( cbox_get_main_site_id() !== get_current_blog_id() ) {
			$retval = network_admin_url( $arg );
		}
	}

	return $retval;
}

/**
 * Wrapper for wp_get_theme() to account for main site ID.
 *
 * @since 1.1.0
 *
 * @param string|null $stylesheet Directory name for the theme. Optional. Defaults to current theme.
 */
function cbox_get_theme( $stylesheet = '' ) {
	if ( 1 !== cbox_get_main_site_id() ) {
		switch_to_blog( cbox_get_main_site_id );
	}

	$theme = wp_get_theme( $stylesheet );

	if ( 1 !== cbox_get_main_site_id() ) {
		restore_current_blog();
	}

	return $theme;
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

/** TEMPLATE *************************************************************/

/**
 * Locate the highest priority CBOX admin template file that exists.
 *
 * Tries to see if a registered CBOX package has a template file.  If not,
 * fall back to the 'base' template.  Similar to {@link locate_template()}.
 *
 * @since 1.1.0
 *
 * @param  string|array $template_names Template file(s) to search for, in order.
 * @param  string       $package_id     The CBOX package to grab the template for.
 * @param  bool         $load           If true the template file will be loaded if it is found.
 * @param  bool         $require_once   Whether to require_once or require. Default true. Has no effect if $load is false.
 * @return string The template filename if one is located.
 */
function cbox_locate_template( $template_names, $package_id = '', $load = false, $require_once = true ) {
	$located = '';
	foreach ( (array) $template_names as $template_name ) {
		if ( ! $template_name ) {
			continue;
		}

		$template_path = cbox_get_package_prop( 'template_path', $package_id );
		if ( ! empty( $template_path ) && file_exists( trailingslashit( $template_path ) . $template_name ) ) {
			$located = trailingslashit( $template_path ) . $template_name;
			break;

		} elseif ( file_exists( CBOX_PLUGIN_DIR . 'admin/templates/base/' . $template_name ) ) {
			$located = CBOX_PLUGIN_DIR . 'admin/templates/base/' . $template_name;
			break;
		}
	}

	if ( $load && '' != $located ) {
		load_template( $located, $require_once );
	}

	return $located;
}

/**
 * Load a CBOX admin template part.
 *
 * Basically, almost the same as {@link get_template_part()}.
 *
 * @since 1.1.0
 *
 * @param string $slug       The slug name for the generic template.
 * @param string $package_id Optional. The CBOX package to grab the template for. Defaults to current
 *                           package if available.
 */
function cbox_get_template_part( $slug, $package_id = '' ) {
	$templates = array();
	$templates[] = "{$slug}.php";

	/**
	 * Fires before the specified template part file is loaded.
	 *
	 * The dynamic portion of the hook name, `$slug`, refers to the slug name
	 * for the generic template part.
	 *
	 * @since 1.1.0
	 *
	 * @param string $slug       The slug name for the generic template.
	 * @param string $package_id The CBOX package to grab the template for.
	 */
	do_action( 'cbox_get_template_part', $slug, $package_id );

	cbox_locate_template( $templates, $package_id, true, false );

	/**
	 * Fires after the specified template part file is loaded.
	 *
	 * The dynamic portion of the hook name, `$slug`, refers to the slug name
	 * for the generic template part.
	 *
	 * @since 1.1.0
	 *
	 * @param string $slug       The slug name for the generic template.
	 * @param string $package_id The CBOX package to grab the template for.
	 */
	do_action( 'cbox_after_get_template_part', $slug, $package_id );
}

/**
 * Template tag to output CSS classes meant for the welcome panel admin block.
 *
 * @since 1.1.0
 */
function cbox_welcome_panel_classes() {
	// Default class for our welcome panel container.
	$classes = 'welcome-panel';

	// Get our user's welcome panel setting.
	$option = get_user_meta( get_current_user_id(), 'show_cbox_welcome_panel', true );

	// If welcome panel option isn't set, set it to "1" to show the panel by default
	if ( $option === '' ) {
		$option = 1;
	}

	// This sets the CSS class needed to hide the welcome panel if needed.
	if ( ! (int) $option ) {
		$classes .= ' hidden';
	}

	echo esc_attr( $classes );
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
			if ( ( ! empty( $obj->options['url'] ) && false === strpos( $obj->options['url'], 'github.com' ) ) && ( ! empty( $obj->options['url'] ) && false === strpos( $obj->options['url'], 'commons-in-a-box/includes/zip' ) ) ) {
				return $source;
			}

			global $wp_filesystem;

			// rename the theme folder to get rid of github's funky naming
			$new_location = $remote_source . '/' . cbox_get_theme_prop( 'directory_name' ) . '/';

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
