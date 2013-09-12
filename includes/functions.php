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
