<?php
/**
 * bbPress Mods
 *
 * The following are modifications that CBOX does to the bbPress plugin.
 *
 * @since 1.0.1
 *
 * @package Commons_In_A_Box
 * @subpackage Frontend
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// setup globals for bbPress
cbox()->plugins->bbpress = new stdClass;
cbox()->plugins->bbpress->is_setup = function_exists( 'bbpress' );

/**
 * Changes how bbPress checks if a site is public.
 *
 * This class is autoloaded.
 *
 * If a WP site disables search engine indexing, no forum-related activity
 * is recorded in BuddyPress.  Therefore, we force bbP so it's always public.
 *
 * @see https://bbpress.trac.wordpress.org/ticket/2151
 *
 * @since 1.0.1
 */
class CBox_BBP_Site_Public {
	public static function init() {
		new self();
	}

	public function __construct() {
		add_filter( 'bbp_is_site_public', '__return_true' );
	}
}
