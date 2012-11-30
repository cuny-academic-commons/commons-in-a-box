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
		// wp_get_referer() should contain the URL of the requesting
		// page. Check to see whether it's an admin page
		$is_admin = 0 === strpos( wp_get_referer(), admin_url() );
	} else {
		$is_admin = is_admin();
	}

	return $is_admin;
}
