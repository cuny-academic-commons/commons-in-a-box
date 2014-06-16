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

		$this->enable_visual_editor();
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
		if ( function_exists( 'bbp_get_version' ) AND version_compare( bbp_get_version(), '2.3' ) < 0 ) {
			remove_action( 'switch_blog', 'bbp_set_current_user_default_role' );
		}
	}

	/**
	 * Re-enable TinyMCE in the forum textarea.
	 *
	 * bbPress 2.3 removed TinyMCE by default due to quirks in code formatting.
	 * We want to bring it back for backpat and UX reasons.
	 *
	 * @see https://github.com/cuny-academic-commons/commons-in-a-box/issues/76
	 */
	public function enable_visual_editor() {
		// create function to re-enable TinyMCE
		$enable_tinymce = create_function( '$retval', '
			// enable tinymce
			$retval["tinymce"] = true;

			// set teeny mode to false so we can use some additional buttons
			$retval["teeny"]   = false;

			// also manipulate some TinyMCE buttons
			CBox_BBP_Autoload::tinymce_buttons();

			return $retval;
		' );

		// add our function to bbPress
		add_filter( 'bbp_after_get_the_content_parse_args', $enable_tinymce );
	}

	/**
	 * Add / remove buttons to emulate WP's TinyMCE 'teeny' mode for bbPress.
	 *
	 * Since the 'pasteword' button can only be used if 'teeny' mode is false,
	 * we need to remove a bunch of buttons from WP's regular post editor to
	 * emulate teeny mode.
	 *
	 * @see https://github.com/cuny-academic-commons/commons-in-a-box/issues/91
	 */
	public static function tinymce_buttons() {
		// create function to add / remove some TinyMCE buttons
		$buttons = create_function( '$retval', '
			global $wp_version;

			// remove some buttons to emulate teeny mode
			$retval = array_diff( $retval, array(
				"wp_more",
				"underline",
				"justifyleft",
				"justifycenter",
				"justifyright",
				"wp_adv"
			) );

			// add the pasteword plugin
			$paste = ( version_compare( $wp_version, "3.9" ) >= 0 ) ? "paste" : "pasteword";

			// add back undo / redo from teeny mode
			// bbPress adds the image button so we should do it as well
			array_push( $retval, "image", $paste, "undo", "redo" );

			return $retval;
		' );

		// add our function to bbPress
		add_filter( 'mce_buttons',   $buttons, 20 );

		// wipe out the second row of TinyMCE buttons
		add_filter( 'mce_buttons_2', create_function( '', "return array();" ) );
	}

}

