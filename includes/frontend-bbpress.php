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
 * Hotfixes and workarounds for bbPress.
 *
 * This class is autoloaded.
 *
 * @since 1.0.3
 */
class CBox_BBP_Autoload {
	/**
	 * Init method.
	 */
	public static function init() {
		new self();
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->is_site_public();
		
		$this->remove_dynamic_role_setter();
	}

	/**
	 * Changes how bbPress checks if a site is public.
	 *
	 * This class is autoloaded.
	 *
	 * If a WP site disables search engine indexing, no forum-related activity
	 * is recorded in BuddyPress.  Therefore, we force bbP so it's always public.
	 *
	 * @see https://bbpress.trac.wordpress.org/ticket/2151
	 */	
	public function is_site_public() {
		add_filter( 'bbp_is_site_public', '__return_true' );	
	}

	/**
	 * bbPress 2.2.x forces blog creators from the Administrator role to
	 * Participant, or to have no role at all.
	 *
	 * This is a hotfix to address bbPress 2.2.x; bbPress 2.3 fixes this.
	 *
	 * @see https://bbpress.trac.wordpress.org/ticket/2103
	 */
	public function remove_dynamic_role_setter() {
		if ( version_compare( bbp_get_version(), '2.3' ) < 0 ) {
			remove_action( 'switch_blog', 'bbp_set_current_user_default_role' );
		}
	}
}

