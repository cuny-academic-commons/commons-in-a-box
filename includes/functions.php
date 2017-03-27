<?php
/**
 * Global functions needed throughout Commons In A Box.
 *
 * @since 1.0
 *
 * @package Commons_In_A_Box
 * @subpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Are we looking at the WordPress admin?
 *
 * Because AJAX requests are sent to wp-admin/admin-ajax.php, WordPress's
 * is_admin() function returns true for AJAX requests. This is misleading for
 * our purposes, so this function acts as a wrapper.
 *
 * @since 1.0
 * @return bool
 */
function cbox_is_admin() {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		// if we're in the admin area, WP_NETWORK_ADMIN will be defined.
		// admin-ajax.php does not define this so this is a good check to see
		// if we're in the admin area
		$is_admin = defined( 'WP_NETWORK_ADMIN' );
	} else {
		$is_admin = is_admin();
	}

	return $is_admin;
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
 * Get all registered CBOX packages.
 *
 * @since 1.1.0
 *
 * @return array Key/value pairs (package name => class name)
 */
function cbox_get_packages() {
	/*
	 * Make some packages mandatory.
	 *
	 * @todo Might remove this restriction later.
	 */
	$default = array(
		'classic' => 'CBox_Package_Classic',
	);

	/**
	 * Filter to register a custom package.
	 *
	 * @since 1.1.0
	 *
	 * @var array $packages Array key is your internal package name, value is class name to
	 *                      instantiate the class.
	 */
	$third_party = apply_filters( 'cbox_register_packages', array() );

	return $default + (array) $third_party;
}

/**
 * Get the current, active CBOX package.
 *
 * @since 1.1.0
 */
function cbox_get_current_package_id() {
	$current = get_site_option( '_cbox_current_package' );

	// We've never saved a package into the DB before.
	if ( cbox_get_installed_revision_date() && empty( $current ) ) {
		/*
		 * If installed date is before 2018/01/01, save as 'classic' for backpat.
		 *
		 * @todo Change date to whenever we launch v1.1.0
		 */
		if ( cbox_get_installed_revision_date() < strtotime( '2018/01/01 UTC' ) ) {
			$current = 'classic';
			update_site_option( '_cbox_current_package', $current );
		}
	}

	return $current;
}

/**
 * Get a specific property from a registered CBOX package.
 *
 * @since 1.1.0
 *
 * @param  string $prop       The property to fetch from the CBOX package.
 * @param  string $package_id The CBOX package to query. If empty, falls back to current package ID.
 * @return mixed|false        Boolean false on failure, any other type on success.
 */
function cbox_get_package_prop( $prop = '', $package_id = '' ) {
	if ( empty( $package_id ) ) {
		$package_id = cbox_get_current_package_id();
	}

	if ( empty( $package_id ) ) {
		return false;
	}

	$packages = cbox_get_packages();
	if ( isset( $packages[$package_id] ) && class_exists( $packages[$package_id] ) ) {
		if ( 'name' === $prop || 'theme' === $prop ) {
			return $packages[$package_id]::$$prop;
		}

		// If we've never set up the package properties before, do it now.
		$props = $packages[$package_id]::get_props();
		if ( empty( $props ) ) {
			$packages[$package_id]::set_props();
			$props = $packages[$package_id]::get_props();
		}

		if ( isset( $props[$prop] ) ) {
			return $props[$prop];
		}
	}

	return false;
}